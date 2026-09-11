<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearProduct;
use Illuminate\Contracts\View\View;

final class WearController extends Controller
{
    public function catalog(): View
    {
        return view('pages.wear.home', ['title' => 'Kipanya Wear']);
    }

    public function shop(): View
    {
        return view('pages.wear.shop', ['title' => 'Shop — Kipanya Wear']);
    }

    public function product(WearProduct $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('variants');
        return view('pages.wear.product-design2', ['title' => $product->name, 'product' => $product]);
    }

    public function cart(): View
    {
        return view('pages.wear.cart-design2', ['title' => 'Shopping Bag — Kipanya Wear']);
    }

    public function checkout(): View
    {
        return view('pages.wear.checkout-design2', ['title' => 'Checkout — Kipanya Wear']);
    }

    public function about(): View
    {
        return view('pages.wear.about-design2', ['title' => 'About — Kipanya Wear']);
    }

    public function contact(): View
    {
        return view('pages.wear.contact-design2', ['title' => 'Contact — Kipanya Wear']);
    }

    public function orders(): View
    {
        return view('pages.wear.orders', ['title' => 'Orders — Kipanya Wear']);
    }

    public function orderConfirmation(string $orderNumber): View
    {
        return view('pages.wear.order-confirmation', ['title' => 'Order Confirmation', 'orderNumber' => $orderNumber]);
    }

    public function wishlist(): View
    {
        return view('pages.wear.wishlist', ['title' => 'Favorites — Kipanya Wear']);
    }
}
