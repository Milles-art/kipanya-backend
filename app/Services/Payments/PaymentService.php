<?php

namespace App\Services\Payments;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Integrations\Payments\PaymentGateway;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Commerce\StockReservation;
use App\Models\Wear\WearOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

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
                    $reservation->update(['status' => ReservationStatus::Expired]);
                }
                throw ValidationException::withMessages(['payment' => 'The order reservation has expired.']);
            }

            foreach ($reservation->items as $item) {
                $variant = $item->variant()->lockForUpdate()->first();
                if (! $variant || $variant->stock < $item->quantity) {
                    throw ValidationException::withMessages(['payment' => 'Stock is no longer available for this order.']);
                }
                $variant->decrement('stock', $item->quantity);
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

            if ($from !== OrderStatus::Confirmed->value) {
                $order->statusHistory()->create([
                    'from_status' => $from,
                    'to_status' => OrderStatus::Confirmed->value,
                    'reason' => 'Payment confirmed.',
                ]);
            }

            return $payment->fresh('order');
        });
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

            return $payment->fresh();
        });
    }
}
