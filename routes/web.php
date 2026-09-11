<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home', ['title' => 'KP Wear — Everyday, made better'])->name('home');
Route::view('/shop', 'pages.catalog', ['title' => 'Shop — KP Wear'])->name('shop');
Route::view('/search', 'pages.catalog', ['title' => 'Search — KP Wear'])->name('search');
Route::view('/category/{slug}', 'pages.catalog', ['title' => 'Shop — KP Wear'])->name('category');
Route::view('/product/{slug}', 'pages.product')->name('product');
Route::view('/collections', 'pages.collections', ['title' => 'Collections — KP Wear'])->name('collections');
Route::view('/collections/{slug}', 'pages.catalog', ['title' => 'Collection — KP Wear'])->name('collection');
Route::view('/wishlist', 'pages.wishlist', ['title' => 'Wishlist — KP Wear'])->name('wishlist');
Route::view('/cart', 'pages.cart', ['title' => 'Your Bag — KP Wear'])->name('cart');
Route::view('/checkout', 'pages.checkout', ['title' => 'Checkout — KP Wear'])->name('checkout');
Route::view('/login', 'pages.login', ['title' => 'Sign in — KP Wear'])->name('login');
Route::view('/register', 'pages.register', ['title' => 'Create account — KP Wear'])->name('register');
Route::view('/otp-verification', 'pages.otp', ['title' => 'Verify — KP Wear'])->name('otp');
Route::view('/account', 'account.dashboard', ['title' => 'My Account — KP Wear'])->name('account');
Route::view('/account/orders', 'account.orders', ['title' => 'My Orders — KP Wear'])->name('account.orders');
Route::view('/account/orders/{orderId}', 'account.order-detail')->name('account.order-detail');
Route::view('/account/profile', 'account.profile', ['title' => 'Profile — KP Wear'])->name('account.profile');
Route::view('/account/security', 'account.security', ['title' => 'Security — KP Wear'])->name('account.security');
Route::view('/account/addresses', 'account.addresses', ['title' => 'Addresses — KP Wear'])->name('account.addresses');
Route::view('/account/wishlist', 'account.wishlist', ['title' => 'Wishlist — KP Wear'])->name('account.wishlist');
Route::view('/orders/{orderNumber}', 'pages.order-status')->name('order-status');
