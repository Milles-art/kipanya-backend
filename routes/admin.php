<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ControlPanelController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StorefrontController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Wear\AnalyticsController;
use App\Http\Controllers\Admin\Wear\CategoryController;
use App\Http\Controllers\Admin\Wear\CollectionController;
use App\Http\Controllers\Admin\Wear\CustomerController;
use App\Http\Controllers\Admin\Wear\EnquiryController;
use App\Http\Controllers\Admin\Wear\InventoryController;
use App\Http\Controllers\Admin\Wear\OrderController;
use App\Http\Controllers\Admin\Wear\PaymentController;
use App\Http\Controllers\Admin\Wear\ProductController;
use App\Http\Controllers\Admin\Wear\ReturnRequestController;
use App\Http\Controllers\Admin\Wear\VariantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login/request-otp', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1')->name('login.request-otp');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');

    Route::middleware('admin.web')->group(function (): void {
        Route::get('/', [ControlPanelController::class, 'dashboard'])->name('dashboard');

        Route::prefix('users')->name('users.')->middleware('admin.permission:users.manage')->group(function (): void {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::post('/{user}/status', [UserController::class, 'toggleStatus'])->name('status');
        });


        Route::prefix('wear')->name('wear.')->middleware('admin.permission:commerce.manage')->group(function (): void {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('/orders/{order}/delivery', [OrderController::class, 'updateDelivery'])->name('orders.delivery');
            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/{variant}/stock', [InventoryController::class, 'updateStock'])->name('inventory.stock');
            Route::resource('products.variants', VariantController::class)->except(['show']);
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::resource('collections', CollectionController::class)->except(['show']);
            Route::get('/returns', [ReturnRequestController::class, 'index'])->name('returns.index');
            Route::get('/returns/{returnRequest}', [ReturnRequestController::class, 'show'])->name('returns.show');
            Route::post('/returns/{returnRequest}/status', [ReturnRequestController::class, 'updateStatus'])->name('returns.status');
            Route::post('/returns/{returnRequest}/refund', [ReturnRequestController::class, 'markRefunded'])->middleware('admin.permission:payments.manage')->name('returns.refund');
            Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
            Route::get('/enquiries/{message}', [EnquiryController::class, 'show'])->name('enquiries.show');
            Route::post('/enquiries/{message}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.status');
        });

        Route::prefix('wear')->name('wear.')->middleware('admin.permission:analytics.view')->group(function (): void {
            Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        });

        Route::prefix('wear')->name('wear.')->middleware('admin.permission:payments.manage')->group(function (): void {
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        });

        Route::middleware('admin.permission:settings.manage')->group(function (): void {
            Route::get('/storefront', [StorefrontController::class, 'index'])->name('storefront.index');
            Route::put('/storefront', [StorefrontController::class, 'update'])->name('storefront.update');
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::prefix('audit')->name('audit.')->middleware('admin.permission:admin.dashboard.view')->group(function (): void {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
