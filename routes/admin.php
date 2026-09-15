<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ControlPanelController;
use App\Http\Controllers\Admin\Wear\CategoryController;
use App\Http\Controllers\Admin\Wear\CustomerController;
use App\Http\Controllers\Admin\Wear\InventoryController;
use App\Http\Controllers\Admin\Wear\OrderController;
use App\Http\Controllers\Admin\Wear\PaymentController;
use App\Http\Controllers\Admin\Wear\ProductController;
use App\Http\Controllers\Admin\Wear\VariantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login/request-otp', [AuthController::class, 'requestOtp'])
        ->middleware('throttle:5,1')->name('login.request-otp');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')->name('login.submit');

    Route::middleware('admin.web')->group(function (): void {
        Route::get('/', [ControlPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/modules/{module}', [ControlPanelController::class, 'module'])->name('module');

        Route::prefix('wear')->name('wear.')->middleware('admin.permission:commerce.manage')->group(function (): void {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/{variant}/stock', [InventoryController::class, 'updateStock'])->name('inventory.stock');
            Route::resource('products.variants', VariantController::class)->except(['show']);
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        });

        Route::prefix('wear')->name('wear.')->middleware('admin.permission:payments.manage')->group(function (): void {
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
