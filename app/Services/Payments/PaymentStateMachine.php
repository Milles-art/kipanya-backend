<?php

namespace App\Services\Payments;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Commerce\StockReservation;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearOrder;
use App\Services\Commerce\InventoryReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Enforces the payment state machine transitions.
 * All state changes must go through this service.
 */
final class PaymentStateMachine
{
    /**
     * Attempt to transition a payment to a new status.
     * Throws ValidationException if the transition is not legal.
     *
     * @param  array<string, mixed>  $providerPayload  Additional provider data to store
     */
    public function transition(
        PaymentTransaction $payment,
        PaymentStatus $toStatus,
        array $providerPayload = []
    ): PaymentTransaction {
        $currentStatus = $payment->status;

        // If already in target state, still record provider state if provided
        if ($currentStatus === $toStatus) {
            if (! empty($providerPayload)) {
                return $this->recordProviderState($payment, $providerPayload);
            }

            return $payment->fresh('order');
        }

        // Check if transition is legal
        if (! $currentStatus->canTransitionTo($toStatus)) {
            throw ValidationException::withMessages([
                'payment' => "Illegal state transition from {$currentStatus->value} to {$toStatus->value}.",
            ]);
        }

        // If moving to Completed, do the full atomic fulfillment
        if ($toStatus === PaymentStatus::Paid) {
            return $this->completePayment($payment, $providerPayload);
        }

        // For terminal failure/cancellation states, use markFailed logic
        if ($toStatus->isTerminal() && $toStatus !== PaymentStatus::Paid) {
            return $this->failPayment($payment, $toStatus, $providerPayload);
        }

        // For pending-like states (InProgress, ReconciliationRequired, Pending), just update status
        return $this->updatePendingLikeState($payment, $toStatus, $providerPayload);
    }

    /**
     * Atomically complete a payment with all order/inventory effects.
     * Uses row locks to prevent race conditions.
     */
    private function completePayment(
        PaymentTransaction $payment,
        array $providerPayload
    ): PaymentTransaction {
        return DB::transaction(function () use ($payment, $providerPayload): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);

            // Double-check current state after acquiring lock
            if ($payment->status === PaymentStatus::Paid) {
                return $payment->fresh('order');
            }

            // Verify transition is still legal
            if (! $payment->status->canTransitionTo(PaymentStatus::Paid)) {
                throw ValidationException::withMessages([
                    'payment' => "Cannot complete payment from status {$payment->status->value}.",
                ]);
            }

            // Race-safe check: if another payment already has this provider_transid, don't double-fulfill
            $transid = $providerPayload['transid'] ?? null;
            if ($transid !== null) {
                $duplicate = PaymentTransaction::query()
                    ->where('provider_transid', $transid)
                    ->where('id', '!=', $payment->id)
                    ->where('status', PaymentStatus::Paid)
                    ->lockForUpdate()
                    ->first();
                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'payment' => 'This transaction ID was already used for another payment.',
                    ]);
                }
            }

            // Verify order and reservation are still valid
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            // Check if reservation is still valid; if not, transition to Failed instead of throwing
            try {
                $this->verifyOrderCanComplete($payment, $order);
            } catch (ValidationException $e) {
                // Reservation expired or stock unavailable - transition to Failed
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'provider_transid' => $providerPayload['transid'] ?? $payment->provider_transid,
                    'payload' => array_merge($payment->payload ?? [], [
                        'provider_callback' => $providerPayload,
                        'fulfillment_failed' => true,
                        'failure_reason' => $e->getMessage(),
                    ]),
                    'failed_at' => now(),
                ]);

                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

                // Release reservation if active
                $reservation = StockReservation::query()
                    ->lockForUpdate()
                    ->where('wear_order_id', $order->id)
                    ->where('status', ReservationStatus::Active->value)
                    ->first();
                if ($reservation) {
                    app(InventoryReservationService::class)
                        ->release($order, ReservationStatus::Released);
                }

                return $payment->fresh('order');
            }

            // Perform inventory fulfillment
            $this->fulfillInventory($order);

            // Update payment to Paid
            $payment->update([
                'status' => PaymentStatus::Paid,
                'provider_transid' => $providerPayload['transid'] ?? $payment->provider_transid,
                'payload' => array_merge($payment->payload ?? [], ['provider_callback' => $providerPayload]),
                'completed_at' => now(),
            ]);

            // Update order to Confirmed
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

    private function verifyOrderCanComplete(PaymentTransaction $payment, WearOrder $order): void
    {
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
            throw ValidationException::withMessages(['payment' => 'Stock reservation is no longer available.']);
        }

        foreach ($reservation->items as $item) {
            $variant = $item->variant()->lockForUpdate()->first();
            if (! $variant || $variant->stock < $item->quantity) {
                throw ValidationException::withMessages(['payment' => 'Stock is no longer available for this order.']);
            }
        }
    }

    private function fulfillInventory(WearOrder $order): void
    {
        $reservation = StockReservation::query()
            ->with('items')
            ->where('wear_order_id', $order->id)
            ->where('status', ReservationStatus::Active->value)
            ->firstOrFail();

        foreach ($reservation->items as $item) {
            $variant = $item->variant()->lockForUpdate()->firstOrFail();
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
    }

    /**
     * Transition to a terminal failure/cancellation state (not Completed).
     */
    private function failPayment(
        PaymentTransaction $payment,
        PaymentStatus $toStatus,
        array $providerPayload
    ): PaymentTransaction {
        return DB::transaction(function () use ($payment, $toStatus, $providerPayload): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === PaymentStatus::Paid) {
                return $payment;
            }

            if (! $payment->status->canTransitionTo($toStatus)) {
                throw ValidationException::withMessages([
                    'payment' => "Cannot transition from {$payment->status->value} to {$toStatus->value}.",
                ]);
            }

            $payment->update([
                'status' => $toStatus,
                'provider_transid' => $providerPayload['transid'] ?? $payment->provider_transid,
                'payload' => array_merge($payment->payload ?? [], ['provider_callback' => $providerPayload]),
                'failed_at' => now(),
            ]);

            $order = WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);
            if ($order->payment_status !== PaymentStatus::Paid) {
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => $toStatus,
                ]);
            }

            // Release reservation if active
            $reservation = StockReservation::query()
                ->lockForUpdate()
                ->where('wear_order_id', $order->id)
                ->where('status', ReservationStatus::Active->value)
                ->first();
            if ($reservation) {
                app(InventoryReservationService::class)
                    ->release($order, ReservationStatus::Released);
            }

            return $payment->fresh('order');
        });
    }

    /**
     * Record provider state without changing the payment status.
     */
    private function recordProviderState(
        PaymentTransaction $payment,
        array $providerPayload
    ): PaymentTransaction {
        $transid = $providerPayload['transid'] ?? null;

        // Race-safe: if transid is provided and already exists on a completed payment, don't overwrite
        if ($transid !== null) {
            $existing = PaymentTransaction::query()
                ->where('provider_transid', $transid)
                ->where('id', '!=', $payment->id)
                ->where('status', PaymentStatus::Paid)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                return $payment->fresh('order');
            }
        }

        $payment->update([
            'provider_transid' => $transid ?? $payment->provider_transid,
            'payload' => array_merge($payment->payload ?? [], ['provider_state' => $providerPayload]),
        ]);

        return $payment->fresh('order');
    }

    /**
     * Update to a pending-like state (InProgress, ReconciliationRequired, Pending).
     */
    private function updatePendingLikeState(
        PaymentTransaction $payment,
        PaymentStatus $toStatus,
        array $providerPayload
    ): PaymentTransaction {
        $transid = $providerPayload['transid'] ?? null;

        // Race-safe: if transid is provided and already exists on a completed payment, don't overwrite
        if ($transid !== null) {
            $existing = PaymentTransaction::query()
                ->where('provider_transid', $transid)
                ->where('id', '!=', $payment->id)
                ->where('status', PaymentStatus::Paid)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                return $payment->fresh('order');
            }
        }

        $payment->update([
            'status' => $toStatus,
            'provider_transid' => $transid ?? $payment->provider_transid,
            'payload' => array_merge($payment->payload ?? [], ['provider_state' => $providerPayload]),
        ]);

        return $payment->fresh('order');
    }
}
