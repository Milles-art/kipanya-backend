<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CartoonController;
use App\Http\Controllers\Api\V1\Admin\CollectionController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\EpisodeController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:admin.dashboard.view');

    Route::apiResource('categories', CategoryController::class)
        ->middleware('permission:content.categories.manage');

    Route::apiResource('cartoons', CartoonController::class)
        ->middleware('permission:content.cartoons.manage');
    Route::post('/cartoons/{cartoon}/publish', [CartoonController::class, 'publish'])
        ->middleware('permission:content.cartoons.manage');
    Route::post('/cartoons/{cartoon}/archive', [CartoonController::class, 'archive'])
        ->middleware('permission:content.cartoons.manage');
    Route::post('/cartoons/{cartoon}/feature', [CartoonController::class, 'feature'])
        ->middleware('permission:content.cartoons.manage');
    Route::delete('/cartoons/{cartoon}/feature', [CartoonController::class, 'unfeature'])
        ->middleware('permission:content.cartoons.manage');

    Route::apiResource('cartoons.episodes', EpisodeController::class)
        ->middleware('permission:content.episodes.manage');

    Route::apiResource('collections', CollectionController::class)
        ->middleware('permission:content.collections.manage');
    Route::put('/collections/{collection}/cartoons', [CollectionController::class, 'syncCartoons'])
        ->middleware('permission:content.collections.manage');
});
