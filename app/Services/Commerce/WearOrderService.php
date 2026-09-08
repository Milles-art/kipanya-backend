<?php

namespace App\Services\Commerce;

use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProductVariant;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class WearOrderService
{
    public function place(Request $request, array $data, WearCartService $cartService): WearOrder
    {
        $items = $cartService->items($request);
        if ($items === []) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $phone = PhoneNumber::normalize($data['customer_phone'])->value();

        return DB::transaction(function () use ($request, $data, $items, $phone, $cartService) {
            $lockedItems = [];
            foreach ($items as $item) {
                $variant = WearProductVariant::query()->lockForUpdate()->with('product')->find($item['variant']->id);
                if (! $variant || ! $variant->product?->is_active || $variant->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Stock changed for {$item['product']->name}. Please review your cart.",
                    ]);
                }
                $lockedItems[] = [$variant, $item['quantity']];
            }

            $subtotal = collect($items)->sum('line_total');
            $deliveryFee = 0.0;
            $order = WearOrder::create([
                'order_number' => $this->number(),
                'user_id' => $request->user()?->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_address' => $data['delivery_address'],
                'delivery_city' => $data['delivery_city'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'mobile_money',
                'placed_at' => now(),
            ]);

            foreach ($lockedItems as [$variant, $quantity]) {
                $unit = (float) $variant->product->price;
                $order->items()->create([
                    'wear_product_id' => $variant->product_id,
                    'wear_product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'line_total' => $unit * $quantity,
                ]);
                $variant->decrement('stock', $quantity);
            }

            $cartService->clear($request);
            return $order->load('items');
        });
    }

    private function number(): string
    {
        do {
            $number = 'KW-' . now()->format('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (WearOrder::where('order_number', $number)->exists());
        return $number;
    }
}
