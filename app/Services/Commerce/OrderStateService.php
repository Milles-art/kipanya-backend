<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OrderStateService
{
    public function __construct(
        private readonly InventoryReservationService $inventoryReservationService,
    ) {}

    /** @return array<int, OrderStatus> */
    public function allowedTransitions(OrderStatus $current): array
    {
        return match ($current) {
            OrderStatus::PendingPayment => [OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::Processing],
            OrderStatus::Processing => [OrderStatus::Shipped],
            OrderStatus::Shipped => [OrderStatus::Delivered],
            OrderStatus::Delivered, OrderStatus::Cancelled => [],
        };
    }

    public function transition(WearOrder $order, OrderStatus $target, ?User $actor = null, ?string $reason = null): WearOrder
    {
        return DB::transaction(function () use ($order, $target, $actor, $reason): WearOrder {
            $locked = WearOrder::query()->lockForUpdate()->findOrFail($order->id);
            $current = $locked->status;

            if ($current === $target) {
                return $locked->fresh(['items', 'payments', 'stockReservation.items']);
            }

            if ($target === OrderStatus::Confirmed && $locked->payment_status !== PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'status' => 'An order can only be confirmed after payment is marked as paid.',
                ]);
            }

            if ($target === OrderStatus::Cancelled && $locked->payment_status !== PaymentStatus::Pending && $locked->payment_status !== PaymentStatus::Failed) {
                throw ValidationException::withMessages([
                    'status' => 'A paid order cannot be cancelled until its payment has been refunded through the payment workflow.',
                ]);
            }

            if (! in_array($target, $this->allowedTransitions($current), true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf('Cannot change an order from %s to %s.', $current->value, $target->value),
                ]);
            }

            $changes = ['status' => $target];
            if ($target === OrderStatus::Shipped && ! $locked->shipped_at) {
                $changes['shipped_at'] = now();
            }
            if ($target === OrderStatus::Delivered && ! $locked->delivered_at) {
                $changes['delivered_at'] = now();
            }

            $locked->update($changes);
            $locked->statusHistory()->create([
                'from_status' => $current->value,
                'to_status' => $target->value,
                'reason' => $reason,
                'changed_by' => $actor?->id,
            ]);

            // Cancelling a pending payment frees the stock reservation right
            // away instead of leaving units locked until the 15-minute expiry.
            if ($target === OrderStatus::Cancelled) {
                $this->inventoryReservationService->release($locked);
            }

            return $locked->fresh(['items', 'payments', 'stockReservation.items']);
        });
    }
}
