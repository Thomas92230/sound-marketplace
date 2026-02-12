<?php

use App\Http\Controllers\Api\TrackApiController;
use App\Http\Controllers\Api\PurchaseApiController;
use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;

// Les routes API utilisent Sanctum (token-based auth) au lieu de CSRF
// CSRF n'est pas nécessaire pour les APIs stateless avec tokens
// @phpstan-ignore-next-line php-csrf-missing-protection-ide
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Auth
    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:10,1');
    
    // Public tracks
    Route::get('/tracks', [TrackApiController::class, 'index']);
    Route::get('/tracks/{track}', [TrackApiController::class, 'show']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout'])->middleware('throttle:10,1');
        
        // Purchases
        Route::post('/tracks/{track}/purchase', [PurchaseApiController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/purchases', [PurchaseApiController::class, 'index']);
        
        // Artist uploads
        Route::post('/tracks', [TrackApiController::class, 'store'])->middleware(['role:artist,admin', 'throttle:5,1']);
    });
});