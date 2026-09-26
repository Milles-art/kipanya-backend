<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Server-renders the cart page for signed-in customers.
 *
 * The template expects `$cartItems` (a collection of CartItem models with
 * `name`, `size`, `color`, `unit_price` and `image_url` attributes set) and
 * an integer `$subtotal`. Guests get an empty bag: their cart lives behind
 * the X-Guest-Cart-Token header, which only JavaScript can send, so the
 * page shows the empty state with a call to action instead.
 */
final class CartPageController extends Controller
{
    public function show(Request $request, CartService $carts): View
    {
        $user = $request->user() ?? auth('sanctum')->user();

        $cartItems = collect();
        $subtotal = 0;

        if ($user) {
            $cart = $carts->current($user, null)->load('items.variant.product');

            foreach ($cart->items as $item) {
                $variant = $item->variant;
                $product = $variant?->product;

                if (! $variant || ! $product) {
                    continue;
                }

                $unit = (int) $product->price;
                $quantity = (int) $item->quantity;

                $item->setAttribute('name', $product->name);
                $item->setAttribute('size', $variant->size);
                $item->setAttribute('color', $variant->color);
                $item->setAttribute('unit_price', $unit);
                $item->setAttribute('image_url', $product->image_url);

                $cartItems->push($item);
                $subtotal += $unit * $quantity;
            }
        }

        return view('pages.cart', [
            'title' => 'Your Bag — KP Wear',
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
        ]);
    }
}
