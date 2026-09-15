<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ControlPanelController;
use App\Http\Controllers\Admin\Wear\ProductController;
use App\Http\Controllers\Admin\Wear\CategoryController;
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
            Route::resource('products.variants', VariantController::class)->except(['show']);
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
