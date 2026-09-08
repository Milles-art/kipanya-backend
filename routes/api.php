<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Cart\CartController;
use App\Http\Controllers\Api\V1\Commerce\AddressController;
use App\Http\Controllers\Api\V1\Commerce\WishlistController;
use App\Http\Controllers\Api\V1\Commerce\CheckoutController;
use App\Http\Controllers\Api\V1\Commerce\OrderController;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register/request-otp', [AuthenticationController::class, 'requestRegistrationOtp'])
            ->middleware('throttle:10,1');

        Route::post('/register', [AuthenticationController::class, 'register'])
            ->middleware('throttle:10,1');

        Route::post('/login/request-otp', [AuthenticationController::class, 'requestLoginOtp'])
            ->middleware('throttle:10,1');

        Route::post('/login', [AuthenticationController::class, 'login'])
            ->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthenticationController::class, 'logout']);
            Route::get('/me', [AuthenticationController::class, 'me']);
        });
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/items', [CartController::class, 'store']);
        Route::put('/items/{variant}', [CartController::class, 'update']);
        Route::delete('/items/{variant}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::middleware('auth:sanctum')->post('/merge', [CartController::class, 'merge']);
        Route::middleware('auth:sanctum')->get('/checkout/preview', [CheckoutController::class, 'preview']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/checkout', [CheckoutController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order:order_number}', [OrderController::class, 'show']);
        Route::post('/orders/{order:order_number}/cancel', [OrderController::class, 'cancel']);

        Route::apiResource('addresses', AddressController::class)->except(['show']);
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
    });

    require __DIR__.'/api_content.php';
    require __DIR__.'/api_admin.php';
});
