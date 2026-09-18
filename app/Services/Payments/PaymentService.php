<?php

namespace App\Services\Payments;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Integrations\Payments\PaymentGateway;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Commerce\StockReservation;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearOrder;
use App\Services\Commerce\InventoryReservationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly InventoryReservationService $inventoryReservationService,
    ) {}

    public function createPending(WearOrder $order, string $idempotencyKey): PaymentTransaction
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
                'payload' => $gatewayResponse['payload'],
                'initiated_at' => now(),
            ]);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), '1062') && ! str_contains(strtolower($e->getMessage()), 'unique')) {
                throw $e;
            }

            return PaymentTransaction::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
        }
    }

    public function markSuccessful(PaymentTransaction $transaction, array $providerPayload = []): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $providerPayload): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $order = WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);

            if ($payment->status === PaymentStatus::Paid) {
                return $payment->fresh('order');
            }

            if ($payment->status !== PaymentStatus::Pending && $payment->status !== PaymentStatus::Processing) {
                throw ValidationException::withMessages(['payment' => 'This payment cannot be completed from its current state.']);
            }

            $reservation = StockReservation::query()
                ->with('items')
                ->where('wear_order_id', $order->id)
                ->whereIn('status', [ReservationStatus::Active->value])
                ->lockForUpdate()
                ->first();

            if (! $reservation || $reservation->status !== ReservationStatus::Active || $reservation->expires_at?->isPast()) {
                if ($reservation && $reservation->status === ReservationStatus::Active) {
                    $reservation->update([
                        'status' => ReservationStatus::Expired,
                    ]);
                }

                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'payload' => array_merge($payment->payload ?? [], [
                        'provider_callback' => $providerPayload,
                        'reconciliation_required' => true,
                    ]),
                    'failed_at' => now(),
                ]);

                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

                return $payment->fresh('order');
            }

            foreach ($reservation->items as $item) {
                $variant = $item->variant()->lockForUpdate()->first();
                if (! $variant || $variant->stock < $item->quantity) {
                    throw ValidationException::withMessages(['payment' => 'Stock is no longer available for this order.']);
                }
                $before = (int) $variant->stock;
                $quantity = (int) $item->quantity;
                $variant->decrement('stock', $quantity);
                WearInventoryMovement::query()->create([
                    'wear_product_variant_id' => $variant->id,
                    'quantity' => -$quantity,
                    'stock_before' => $before,
                    'stock_after' => $before - $quantity,
                    'reason' => 'Sale',
                    'notes' => 'Order '.$order->order_number,
                ]);
            }

            $reservation->update([
                'status' => ReservationStatus::Fulfilled,
                'fulfilled_at' => now(),
            ]);

            $payment->update([
                'status' => PaymentStatus::Paid,
                'payload' => array_merge($payment->payload ?? [], ['provider_callback' => $providerPayload]),
                'completed_at' => now(),
            ]);

            $from = $order->status;
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Confirmed,
            ]);

            if ($from !== OrderStatus::Confirmed) {
                $order->statusHistory()->create([
                    'from_status' => $from?->value,
                    'to_status' => OrderStatus::Confirmed->value,
                    'reason' => 'Payment confirmed.',
                ]);
            }

            return $payment->fresh('order');
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

        if ($amount !== null && (string) $amount !== '') {
            $expected = (string) ((int) round((float) $order->total));
            if ((string) $amount !== $expected) {
                throw ValidationException::withMessages([
                    'amount' => 'Provider callback amount does not match the order.',
                ]);
            }
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

        return match ($status) {
            'completed' => $this->markSuccessful($payment, $info),
            'cancelled', 'user_cancelled', 'rejected' => $payment->status === PaymentStatus::Pending || $payment->status === PaymentStatus::Processing
                ? $this->markFailed($payment, $info)
                : $this->recordProviderState($payment, $info),
            default => $this->recordProviderState($payment, $info),
        };
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

    private function recordProviderState(PaymentTransaction $payment, array $info): PaymentTransaction
    {
        $payment->update([
            'payload' => array_merge($payment->payload ?? [], ['provider_state' => $info]),
        ]);

        return $payment->fresh('order');
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
        return DB::transaction(function () use ($transaction, $providerPayload): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($payment->status === PaymentStatus::Paid) {
                return $payment;
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => array_merge($payment->payload ?? [], ['provider_callback' => $providerPayload]),
                'failed_at' => now(),
            ]);

            $order = WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);
            if ($order->payment_status !== PaymentStatus::Paid) {
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);
            }

            $reservation = StockReservation::query()
                ->lockForUpdate()
                ->where('wear_order_id', $order->id)
                ->where('status', ReservationStatus::Active->value)
                ->first();
            if ($reservation) {
                $this->inventoryReservationService->release($order, ReservationStatus::Released);
            }

            return $payment->fresh('order');
        });
    }
}
