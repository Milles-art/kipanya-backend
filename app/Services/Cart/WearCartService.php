<?php

namespace App\Services\Cart;

use App\Models\Wear\WearProductVariant;
use Illuminate\Http\Request;

final class WearCartService
{
    private const SESSION_KEY = 'wear_cart';

    public function items(Request $request): array
    {
        $raw = $request->session()->get(self::SESSION_KEY, []);
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $variants = WearProductVariant::query()
            ->with('product')
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($raw as $variantId => $quantity) {
            $variant = $variants->get((int) $variantId);
            if (! $variant || ! $variant->product?->is_active) {
                continue;
            }
            $qty = max(1, min((int) $quantity, $variant->stock));
            if ($qty < 1) {
                continue;
            }
            $items[] = [
                'variant' => $variant,
                'product' => $variant->product,
                'quantity' => $qty,
                'line_total' => (float) $variant->product->price * $qty,
            ];
        }

        return $items;
    }

    public function add(Request $request, WearProductVariant $variant, int $quantity = 1): void
    {
        abort_unless($variant->product?->is_active, 404);
        abort_if($variant->stock < 1, 422, 'This size and color is currently out of stock.');

        $cart = $request->session()->get(self::SESSION_KEY, []);
        $id = (string) $variant->id;
        $cart[$id] = min($variant->stock, max(1, (int) ($cart[$id] ?? 0) + $quantity));
        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public function setQuantity(Request $request, int $variantId, int $quantity): void
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $variant = WearProductVariant::with('product')->findOrFail($variantId);
        $id = (string) $variantId;
        if ($quantity <= 0) {
            unset($cart[$id]);
        } elseif ($variant->stock > 0 && $variant->product?->is_active) {
            $cart[$id] = min($variant->stock, $quantity);
        }
        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(Request $request, int $variantId): void
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        unset($cart[(string) $variantId]);
        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public function count(Request $request): int
    {
        return array_sum(array_map('intval', $request->session()->get(self::SESSION_KEY, [])));
    }

    public function subtotal(Request $request): float
    {
        return array_sum(array_map(fn ($item) => $item['line_total'], $this->items($request)));
    }
}
