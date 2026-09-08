<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\ReservationStatus;
use App\Models\Cart\Cart;
use App\Models\Commerce\StockReservation;
use App\Models\Commerce\StockReservationItem;
use App\Models\Wear\WearOrder;
use Illuminate\Validation\ValidationException;

final class InventoryReservationService
{
    public function reserve(WearOrder $order, Cart $cart, int $minutes = 15): StockReservation
    {
        $reservation = $order->stockReservation()->create([
            'status' => ReservationStatus::Active,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        foreach ($cart->items as $cartItem) {
            $variant = $cartItem->variant()->lockForUpdate()->first();
            $product = $variant?->product;

            if (! $variant || ! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'cart' => 'One or more cart items are no longer available.',
                ]);
            }

            $reserved = (int) StockReservationItem::query()
                ->where('wear_product_variant_id', $variant->id)
                ->whereHas('reservation', function ($query): void {
                    $query->where('status', ReservationStatus::Active->value)
                        ->where('expires_at', '>', now());
                })
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
        }

        return $reservation->fresh('items.variant.product');
    }

    public function release(WearOrder $order, ReservationStatus $status = ReservationStatus::Released): void
    {
        $reservation = $order->stockReservation()->lockForUpdate()->first();

        if (! $reservation || $reservation->status !== ReservationStatus::Active) {
            return;
        }

        $reservation->update([
            'status' => $status,
            'released_at' => now(),
        ]);
    }
}
