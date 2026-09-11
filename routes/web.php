<?php

use App\Http\Controllers\Web\WearController;
use Illuminate\Support\Facades\Route;

Route::prefix('wear')->group(function (): void {
    Route::get('/', [WearController::class, 'catalog'])
        ->name('wear');

    Route::get('/shop', [WearController::class, 'shop'])
        ->name('wear.shop');

    Route::get('/about', [WearController::class, 'about'])
        ->name('wear.about');

    Route::get('/contact', [WearController::class, 'contact'])
        ->name('wear.contact');

    Route::get('/products/{product:slug}', [WearController::class, 'product'])
        ->name('wear.product');

    Route::get('/cart', [WearController::class, 'cart'])
        ->name('wear.cart');

    Route::get('/checkout', [WearController::class, 'checkout'])
        ->name('wear.checkout');

    Route::get('/orders', [WearController::class, 'orders'])
        ->name('wear.orders');

    Route::get('/orders/{orderNumber}', [WearController::class, 'orderConfirmation'])
        ->name('wear.order-confirmation');

    Route::get('/wishlist', [WearController::class, 'wishlist'])
        ->name('wear.wishlist');
});
