<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\ReservationStatus;
use App\Models\Cart\Cart;
use App\Models\Commerce\StockReservation;
use App\Models\Commerce\StockReservationItem;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProductVariant;
use Illuminate\Validation\ValidationException;

final class InventoryReservationService
{
    public function __construct(private readonly InventoryMovementService $movements) {}
    public function reserve(WearOrder $order, Cart $cart, int $minutes = 15): StockReservation
    {
        $reservation = $order->stockReservation()->create([
            'status' => ReservationStatus::Active,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        // Always lock variants in a stable order: two carts holding the same variants in
        // different orders would otherwise deadlock.
        foreach ($cart->items->sortBy('wear_product_variant_id') as $cartItem) {
            $variant = $cartItem->variant()->lockForUpdate()->first();
            $product = $variant?->product;

            if (! $variant || ! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'cart' => 'One or more cart items are no longer available.',
                ]);
            }

            // Lock the matching reservation rows so this availability check
            // reads the latest committed quantities rather than the
            // transaction's older snapshot (MySQL REPEATABLE READ). Without the
            // lock, a concurrent commit can be missed and the variant oversold.
            $reserved = (int) StockReservationItem::query()
                ->where('wear_product_variant_id', $variant->id)
                ->whereHas('reservation', function ($query): void {
                    $query->where('status', ReservationStatus::Active->value)
                        ->where('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->get(['quantity'])
                ->sum('quantity');

            $available = (int) $variant->stock - $reserved;

            if ($available < $cartItem->quantity) {
                throw ValidationException::withMessages([
                    'cart' => "Not enough stock for {$product->name}.",
                ]);
            }

            $reservation->items()->create([
                'wear_product_variant_id' => $variant->id,
                'quantity' => $cartItem->quantity,
            ]);

            // Reservations do not consume physical stock, but recording the zero
            // stock movement keeps the inventory timeline auditable.
            $this->movements->record(
                $variant,
                0,
                (int) $variant->stock,
                (int) $variant->stock,
                'Reservation',
                "Reserved {$cartItem->quantity} unit(s) for {$order->order_number}.",
            );
        }

        return $reservation->fresh('items.variant.product');
    }

    public function release(WearOrder $order, ReservationStatus $status = ReservationStatus::Released): void
    {
        $reservation = $order->stockReservation()->lockForUpdate()->first();

        if (! $reservation || $reservation->status !== ReservationStatus::Active) {
            return;
        }

        $reservation->load('items');
        $reservation->update([
            'status' => $status,
            'released_at' => now(),
        ]);

        foreach ($reservation->items as $item) {
            $variant = WearProductVariant::query()->find($item->wear_product_variant_id);
            if ($variant) {
                $this->movements->record(
                    $variant,
                    0,
                    (int) $variant->stock,
                    (int) $variant->stock,
                    $status === ReservationStatus::Expired ? 'Reservation expired' : 'Reservation released',
                    "Released {$item->quantity} reserved unit(s) for {$order->order_number}.",
                );
            }
        }
    }
}
