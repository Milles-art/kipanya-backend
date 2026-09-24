<?php

use App\Http\Controllers\Web\AccountController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home', ['title' => 'KP Wear — Everyday, made better'])->name('home');
Route::view('/shop', 'pages.catalog', ['title' => 'Shop — KP Wear'])->name('shop');
Route::view('/search', 'pages.catalog', ['title' => 'Search — KP Wear'])->name('search');
Route::view('/category/{slug}', 'pages.catalog', ['title' => 'Shop — KP Wear'])->name('category');
Route::view('/product/{slug}', 'pages.product')->name('product');
Route::view('/collections', 'pages.collections', ['title' => 'Collections — KP Wear'])->name('collections');
Route::view('/about', 'pages.about', ['title' => 'About — KP Wear'])->name('about');
Route::view('/contact', 'pages.contact', ['title' => 'Contact — KP Wear'])->name('contact');
Route::view('/collections/{slug}', 'pages.catalog', ['title' => 'Collection — KP Wear'])->name('collection');
Route::view('/wishlist', 'pages.wishlist', ['title' => 'Wishlist — KP Wear'])->name('wishlist');
Route::view('/cart', 'pages.cart', ['title' => 'Your Bag — KP Wear'])->name('cart');
Route::view('/checkout', 'pages.checkout', ['title' => 'Checkout — KP Wear'])->name('checkout');
Route::view('/login', 'pages.login', ['title' => 'Sign in — KP Wear'])->name('login');
Route::view('/register', 'pages.register', ['title' => 'Create account — KP Wear'])->name('register');
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    // Account pages are server-protected and server-rendered from real data.
    // The HttpOnly kp_web_session cookie is converted to a Sanctum bearer before
    // route middleware runs, so navigation between account pages uses the same
    // authentication contract as the API.
    Route::get('/account', [AccountController::class, 'dashboard'])->name('account');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{orderId}', [AccountController::class, 'orderDetail'])->name('account.order-detail');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::get('/account/security', [AccountController::class, 'security'])->name('account.security');
    Route::get('/account/payment-methods', [AccountController::class, 'paymentMethods'])->name('account.payment-methods');
    Route::get('/account/notifications', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::get('/account/returns', [AccountController::class, 'returns'])->name('account.returns');
    Route::get('/account/loyalty', [AccountController::class, 'loyalty'])->name('account.loyalty');
    Route::get('/account/size-profile', [AccountController::class, 'sizeProfile'])->name('account.size-profile');
    Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
});
Route::view('/orders/{orderNumber}', 'pages.order-status')->name('order-status');

require __DIR__.'/admin.php';
