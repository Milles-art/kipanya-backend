<?php

namespace App\Services\Payments;

use App\Enums\Commerce\PaymentStatus;
use App\Exceptions\SelcomGatewayException;
use App\Integrations\Payments\PaymentGateway;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly PaymentStateMachine $stateMachine,
    ) {}

    public function createPending(WearOrder $order, string $idempotencyKey, array $meta = []): PaymentTransaction
    {
        return $this->initiatePrepared($this->prepare($order, $idempotencyKey, $meta));
    }

    public static function gatewayOrderId(string $idempotencyKey): string
    {
        return 'KP'.substr(hash('sha256', $idempotencyKey), 0, 30);
    }

    public function prepare(WearOrder $order, string $idempotencyKey, array $meta = []): PaymentTransaction
    {
        return DB::transaction(function () use ($order, $idempotencyKey, $meta) {
            $order = WearOrder::query()->lockForUpdate()->findOrFail($order->id);
            $existing = PaymentTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                if ($existing->wear_order_id !== $order->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'This key is already in use.']);
                }
                return $existing;
            }
            $this->stateMachine->assertPayable($order);

            return PaymentTransaction::create([
                'wear_order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'selcom_checkout',
                'gateway_order_id' => self::gatewayOrderId($idempotencyKey),
                'idempotency_key' => $idempotencyKey,
                'amount' => $order->total,
                'currency' => 'TZS',
                'status' => PaymentStatus::Pending,
                'payload' => array_merge($meta, ['gateway_order_id' => self::gatewayOrderId($idempotencyKey)]),
            ]);
        });
    }

    public function initiatePrepared(PaymentTransaction $payment): PaymentTransaction
    {
        $claimed = DB::transaction(function () use ($payment) {
            $order = WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);
            $locked = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);
            if ($locked->initiated_at || ! $locked->status->isPendingLike()) {
                return false;
            }
            $this->stateMachine->assertPayable($order);
            $locked->update(['initiated_at' => now()]);
            return true;
        });

        if (! $claimed) {
            $payment->refresh();
            if ($payment->status->isPendingLike() && empty($payment->payload['payment_gateway_url'])) {
                throw ValidationException::withMessages(['payment' => 'This payment is being checked. Please check your order before trying again.']);
            }
            return $payment;
        }

        try {
            $response = $this->gateway->initiate($payment->order, $payment->idempotency_key);
        } catch (\Throwable $exception) {
            // A definitive 4xx rejection means the provider refused the request
            // outright: no gateway order exists, so no money can ever land on
            // this attempt. Release the claim so the same idempotency key can
            // simply retry. Only an ambiguous outcome (timeout, 5xx, unknown
            // error) may have created an order we never saw, so only those are
            // flagged for reconciliation.
            $definitiveRejection = $exception instanceof SelcomGatewayException
                && ! $exception->isTimeout
                && $exception->httpStatus !== null
                && $exception->httpStatus < 500;

            DB::transaction(function () use ($payment, $definitiveRejection) {
                WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);
                $locked = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);
                if (! $locked->status->isPendingLike()) {
                    return;
                }
                if ($definitiveRejection) {
                    $locked->update(['initiated_at' => null]);

                    return;
                }
                $locked->update(['status' => PaymentStatus::ReconciliationRequired,
                    'payload' => array_merge($locked->payload ?? [], ['needs_review' => true, 'initiation_uncertain' => true])]);
            });
            if ($exception instanceof SelcomGatewayException) {
                throw $this->translateGatewayFailure($exception);
            }
            throw $exception;
        }

        return DB::transaction(function () use ($payment, $response) {
            WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);
            $locked = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);
            $locked->update([
                'provider_reference' => $locked->provider_reference ?? $response['reference'],
                'payload' => array_merge($response['payload'], $locked->payload ?? []),
            ]);
            return $locked->fresh('order');
        });
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
        $orderId = $payment->gateway_order_id ?? $payment->payload['gateway_order_id'] ?? $payment->order->order_number;

        $providerState = $this->gateway->status((string) $orderId);
        if ($payment->gateway_order_id === null && $payment->provider_reference !== ($providerState['reference'] ?? null)) {
            throw ValidationException::withMessages(['payment' => 'Provider response does not identify this payment attempt.']);
        }

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
        $providerState = $this->gateway->status((string) ($payment->gateway_order_id ?? $payment->payload['gateway_order_id'] ?? $payment->order->order_number));
        $payment->update([
            'payload' => array_merge($payment->payload ?? [], [
                'reconciliation_attempts' => ($payment->payload['reconciliation_attempts'] ?? 0) + 1,
                'last_reconciliation_at' => now()->toISOString(),
                'last_reconciliation_status' => $providerState['status'] ?? 'unknown',
            ]),
        ]);
        if (in_array($this->normalizeProviderStatus((string) ($providerState['status'] ?? '')), ['pending', 'in_progress'], true)) {
            return $this->stateMachine->transition($payment, PaymentStatus::ReconciliationRequired, $providerState);
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

    /**
     * Turn a gateway transport failure into a customer-safe validation error.
     *
     * The provider's raw message and code are never surfaced to the customer or
     * echoed into logs here — only the classification is used, so a gateway
     * outage returns a retryable 422 instead of an opaque 500 while the actual
     * detail stays available to support through the gateway's own logs.
     */
    private function translateGatewayFailure(SelcomGatewayException $e): ValidationException
    {
        if ($e->isTimeout) {
            return ValidationException::withMessages([
                'payment' => 'The payment provider is not responding. Please try again in a moment.',
            ]);
        }

        $status = $e->httpStatus;

        if ($status === null || $status >= 500) {
            return ValidationException::withMessages([
                'payment' => 'The payment provider is temporarily unavailable. Please try again shortly.',
            ]);
        }

        return ValidationException::withMessages([
            'payment' => 'The payment provider could not start this payment. Please try another payment method.',
        ]);
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
