<?php

use App\Http\Controllers\Api\TrackApiController;
use App\Http\Controllers\Api\PurchaseApiController;
use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    
    // Public tracks
    Route::get('/tracks', [TrackApiController::class, 'index']);
    Route::get('/tracks/{track}', [TrackApiController::class, 'show']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        
        // Purchases
        Route::post('/tracks/{track}/purchase', [PurchaseApiController::class, 'store']);
        Route::get('/purchases', [PurchaseApiController::class, 'index']);
        
        // Artist uploads
        Route::post('/tracks', [TrackApiController::class, 'store'])->middleware('role:artist,admin');
    });
});