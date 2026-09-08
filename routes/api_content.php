<?php

use App\Http\Controllers\Api\V1\Content\CartoonController;
use Illuminate\Support\Facades\Route;

Route::prefix('content')->group(function () {
    Route::get('/cartoons', [CartoonController::class, 'index']);
    Route::get('/cartoons/{cartoon:slug}', [CartoonController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/cartoons/{cartoon}/favorite', [CartoonController::class, 'favorite']);
        Route::delete('/cartoons/{cartoon}/favorite', [CartoonController::class, 'unfavorite']);
    });
});
