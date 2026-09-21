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
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // The provider says the money was captured, but locally the payment was
        // already cancelled/rejected/failed. Never throw and never lose it:
        // record it for refund/reconciliation instead.
        if ($toStatus === PaymentStatus::Paid && $this->isTerminalUnpaid($currentStatus)) {
            return $this->flagLatePayment($payment, $providerPayload, 'confirmed_after_'.$currentStatus->value);
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

            // Check if the reservation is still valid. If not, the provider has ALREADY
            // captured the customer's money, so this must never be recorded as a plain
            // failure: flag it for refund/reconciliation and keep the order cancelled.
            try {
                $this->verifyOrderCanComplete($payment, $order);
            } catch (ValidationException $e) {
                $this->markNeedsRefund($payment, $order, $providerPayload, 'fulfilment_unavailable: '.$e->getMessage());

                // Release the reservation if it is still active.
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

    /**
     * The provider says "completed" but the payment cannot be verified (for example
     * the amount is missing). Never auto-fulfil an unverifiable payment and never
     * throw it away either: hold it for manual review without touching the order.
     * A later, complete callback can still settle it (reconciliation_required -> paid).
     */
    public function holdForReview(PaymentTransaction $payment, array $providerPayload, string $reason): PaymentTransaction
    {
        return DB::transaction(function () use ($payment, $providerPayload, $reason): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);

            if (! in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Processing, PaymentStatus::InProgress], true)) {
                return $payment->fresh('order');
            }

            $payment->update([
                'status' => PaymentStatus::ReconciliationRequired,
                'provider_transid' => $providerPayload['transid'] ?? $payment->provider_transid,
                'payload' => array_merge($payment->payload ?? [], [
                    'provider_callback' => $providerPayload,
                    'needs_review' => true,
                    'reconciliation_reason' => $reason,
                ]),
            ]);

            Log::warning('Payment reported completed but could not be verified - manual review required', [
                'payment_id' => $payment->id,
                'reason' => $reason,
            ]);

            app(AuditLogger::class)->log(null, 'payment.needs_review', $payment, [
                'provider_transid' => $providerPayload['transid'] ?? null,
                'reason' => $reason,
            ]);

            return $payment->fresh('order');
        });
    }

    /**
     * Payment states that mean "we do not expect money" locally.
     */
    private function isTerminalUnpaid(PaymentStatus $status): bool
    {
        return in_array($status, [
            PaymentStatus::Cancelled,
            PaymentStatus::UserCancelled,
            PaymentStatus::Rejected,
            PaymentStatus::Failed,
        ], true);
    }

    /**
     * The provider confirmed a payment that this system had already closed
     * (customer cancelled, rejected, failed). Record it for refund.
     *
     * Idempotent: repeated callbacks do not re-flag or duplicate the audit entry.
     */
    public function flagLatePayment(
        PaymentTransaction $payment,
        array $providerPayload,
        string $reason = 'confirmed_after_close'
    ): PaymentTransaction {
        return DB::transaction(function () use ($payment, $providerPayload, $reason): PaymentTransaction {
            $payment = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);

            if (in_array($payment->status, [PaymentStatus::Paid, PaymentStatus::ReconciliationRequired, PaymentStatus::Refunded], true)) {
                return $payment->fresh('order');
            }

            $order = WearOrder::query()->lockForUpdate()->findOrFail($payment->wear_order_id);

            $this->markNeedsRefund($payment, $order, $providerPayload, $reason);

            return $payment->fresh('order');
        });
    }

    /**
     * Shared by the "late payment" and "cannot fulfil" paths. Must run inside a
     * transaction with $payment and $order already locked.
     */
    private function markNeedsRefund(
        PaymentTransaction $payment,
        WearOrder $order,
        array $providerPayload,
        string $reason
    ): void {
        $alreadyFlagged = $payment->status === PaymentStatus::ReconciliationRequired
            && ($payment->payload['needs_refund'] ?? false) === true;

        $previous = $payment->status;

        $payment->update([
            'status' => PaymentStatus::ReconciliationRequired,
            'provider_transid' => $providerPayload['transid'] ?? $payment->provider_transid,
            'payload' => array_merge($payment->payload ?? [], [
                'provider_callback' => $providerPayload,
                'needs_refund' => true,
                'reconciliation_reason' => $reason,
                'previous_status' => $previous->value,
            ]),
        ]);

        // Money was captured but no goods are reserved: the order stays cancelled,
        // and its payment status tells staff that a refund decision is pending.
        $order->update([
            'status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::ReconciliationRequired,
        ]);

        if ($alreadyFlagged) {
            return;
        }

        Log::critical('Payment captured but order cannot be fulfilled - refund required', [
            'payment_id' => $payment->id,
            'order_number' => $order->order_number,
            'provider_transid' => $providerPayload['transid'] ?? null,
            'reason' => $reason,
        ]);

        app(AuditLogger::class)->log(null, 'payment.needs_refund', $payment, [
            'order_number' => $order->order_number,
            'provider_transid' => $providerPayload['transid'] ?? null,
            'amount' => $payment->amount,
            'reason' => $reason,
        ]);
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

            // Captured money awaiting a refund decision must never be downgraded to a
            // plain cancellation by a later (stale) provider status.
            if (($payment->payload['needs_refund'] ?? false) === true) {
                return $payment->fresh('order');
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
