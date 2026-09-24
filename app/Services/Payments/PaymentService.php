<?php

namespace App\Services\Payments;

use App\Enums\Commerce\PaymentStatus;
use App\Integrations\Payments\PaymentGateway;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearOrder;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly PaymentStateMachine $stateMachine,
    ) {}

    public function createPending(WearOrder $order, string $idempotencyKey, array $meta = []): PaymentTransaction
    {
        $existing = PaymentTransaction::query()
            ->with('order')
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            if ($existing->wear_order_id !== $order->id) {
                throw ValidationException::withMessages([
                    'idempotency_key' => 'This idempotency key has already been used for another order.',
                ]);
            }

            return $existing;
        }

        $gatewayResponse = $this->gateway->initiate($order, $idempotencyKey);

        try {
            return PaymentTransaction::create([
                'wear_order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => $gatewayResponse['provider'],
                'provider_reference' => $gatewayResponse['reference'],
                'idempotency_key' => $idempotencyKey,
                'amount' => $order->total,
                'currency' => 'TZS',
                'status' => PaymentStatus::Pending,
                'payload' => array_merge($gatewayResponse['payload'], $meta),
                'initiated_at' => now(),
            ]);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), '1062') && ! str_contains(strtolower($e->getMessage()), 'unique')) {
                throw $e;
            }

            return PaymentTransaction::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
        }
    }

    /**
     * Apply a provider-reported state to a payment after verifying it.
     *
     * The amount reported by the provider is verified against the server-side
     * order total, and the currency (when provided) against the payment, before
     * any state change. Only a verified COMPLETED state reaches markSuccessful;
     * every other documented state leaves the payment pending/in-progress.
     */
    public function applyProviderStatus(PaymentTransaction $payment, array $providerState): PaymentTransaction
    {
        $payment->loadMissing('order');
        $order = $payment->order;

        $status = $this->normalizeProviderStatus((string) ($providerState['status'] ?? ''));
        $amount = $providerState['amount'] ?? null;

        $hasAmount = $amount !== null && (string) $amount !== '';

        if ($hasAmount) {
            // Compare numerically: "56000", "56000.00" and 56000 are the same amount.
            if (! is_numeric($amount) || abs((float) $amount - (float) $order->total) >= 0.005) {
                throw ValidationException::withMessages([
                    'amount' => 'Provider callback amount does not match the order.',
                ]);
            }
        } elseif ($status === 'completed') {
            // Fail closed: a payment we cannot amount-check is never auto-fulfilled.
            return $this->stateMachine->holdForReview(
                $payment,
                [
                    'transid' => $providerState['transid'] ?? null,
                    'reference' => $providerState['reference'] ?? null,
                    'raw' => $providerState['payload'] ?? [],
                ],
                'completed_without_amount',
            );
        }

        if (isset($providerState['currency']) && $providerState['currency'] !== '') {
            if (strtoupper((string) $providerState['currency']) !== strtoupper((string) $payment->currency)) {
                throw ValidationException::withMessages([
                    'currency' => 'Provider callback currency does not match the payment.',
                ]);
            }
        }

        $info = [
            'provider' => 'selcom_checkout',
            'transid' => $providerState['transid'] ?? null,
            'reference' => $providerState['reference'] ?? null,
            'channel' => $providerState['channel'] ?? null,
            'amount' => $amount,
            'raw' => $providerState['payload'] ?? [],
        ];

        // Map normalized status string to PaymentStatus enum
        $toStatus = match ($status) {
            'completed' => PaymentStatus::Paid,
            'cancelled' => PaymentStatus::Cancelled,
            'user_cancelled' => PaymentStatus::UserCancelled,
            'rejected' => PaymentStatus::Rejected,
            'in_progress' => PaymentStatus::InProgress,
            'pending' => PaymentStatus::Pending,
            default => PaymentStatus::Pending,
        };

        // Use state machine for all transitions
        return $this->stateMachine->transition($payment, $toStatus, $info);
    }

    /**
     * Query the provider for the current state of a payment and apply the
     * verified result. Payments are never assumed settled without the provider
     * reporting COMPLETED.
     */
    public function refreshStatus(PaymentTransaction $payment): PaymentTransaction
    {
        $payment->loadMissing('order');
        $orderId = $payment->payload['gateway_order_id'] ?? $payment->order->order_number;

        $providerState = $this->gateway->status((string) $orderId);

        return $this->applyProviderStatus($payment, $providerState);
    }

    /**
     * Handle ambiguous/timeout situations by querying the provider for the
     * actual status instead of assuming failure. This is used when a
     * Selcom API call times out or returns an ambiguous response.
     */
    public function reconcileAfterTimeout(PaymentTransaction $payment): PaymentTransaction
    {
        $payment->loadMissing('order');

        // Do not mark as failed - instead query the provider for actual status
        $providerState = $this->gateway->status((string) $payment->payload['gateway_order_id'] ?? $payment->order->order_number);

        // Store the reconciliation attempt
        $payment->update([
            'payload' => array_merge($payment->payload ?? [], [
                'reconciliation_attempts' => ($payment->payload['reconciliation_attempts'] ?? 0) + 1,
                'last_reconciliation_at' => now()->toISOString(),
                'last_reconciliation_status' => $providerState['status'] ?? 'unknown',
            ]),
        ]);

        // If reconciliation status is pending/in_progress, move to ReconciliationRequired
        $status = $this->normalizeProviderStatus((string) ($providerState['status'] ?? ''));
        if (in_array($status, ['pending', 'in_progress'])) {
            return $this->stateMachine->transition(
                $payment,
                PaymentStatus::ReconciliationRequired,
                $providerState
            );
        }

        return $this->applyProviderStatus($payment, $providerState);
    }

    private function normalizeProviderStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
            'USERCANCELED', 'USERCANCELLED' => 'user_cancelled',
            'REJECTED' => 'rejected',
            'INPROGRESS' => 'in_progress',
            'PENDING' => 'pending',
            default => 'pending',
        };
    }

    public function markFailed(PaymentTransaction $transaction, array $providerPayload = []): PaymentTransaction
    {
        return $this->stateMachine->transition($transaction, PaymentStatus::Failed, $providerPayload);
    }

    /**
     * Mark a payment as successful (backward compatibility method).
     * Uses the state machine internally.
     */
    public function markSuccessful(PaymentTransaction $transaction, array $providerPayload = []): PaymentTransaction
    {
        return $this->stateMachine->transition($transaction, PaymentStatus::Paid, $providerPayload);
    }
}
