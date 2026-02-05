<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtistDashboardController;
use App\Http\Controllers\ArtistStripeConnectController;
use Illuminate\Support\Facades\Route;

// Page d'accueil : catalogue des morceaux
Route::get('/', [TrackController::class, 'index'])->name('home');

// Détails d'un morceau
Route::get('/tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');
Route::get('/tracks/{track}/download', [TrackController::class, 'download'])->name('tracks.download')->middleware('auth');

// Profils d'artistes (public)
Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');

// Upload de morceaux (réservé aux artistes)
Route::middleware(['auth', 'role:artist,admin'])->group(function () {
    Route::get('/upload', [TrackController::class, 'showForm'])->name('tracks.upload.form');
    Route::post('/upload', [TrackController::class, 'store'])->name('tracks.upload.store');
    Route::get('/tracks/{track}/edit', [TrackController::class, 'edit'])->name('tracks.edit');
    Route::patch('/tracks/{track}', [TrackController::class, 'update'])->name('tracks.update');
    Route::delete('/tracks/{track}', [TrackController::class, 'destroy'])->name('tracks.destroy');
    Route::delete('/tracks-bulk', [TrackController::class, 'bulkDestroy'])->name('tracks.bulk-delete');
    
    // Profil artiste
    Route::get('/artist/profile', [ArtistController::class, 'edit'])->name('artists.edit');
    Route::post('/artist/profile', [ArtistController::class, 'update'])->name('artists.update');
});

// Dashboard
Route::get('/dashboard', [TrackController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard artiste
Route::middleware(['auth', 'role:artist,admin'])->group(function () {
    Route::get('/artist/dashboard', [ArtistDashboardController::class, 'index'])->name('artist.dashboard');
    Route::get('/artist/stripe/connect', [ArtistStripeConnectController::class, 'start'])->name('artist.stripe.start');
    Route::get('/artist/stripe/refresh', [ArtistStripeConnectController::class, 'refresh'])->name('artist.stripe.refresh');
    Route::get('/artist/stripe/return', [ArtistStripeConnectController::class, 'return'])->name('artist.stripe.return');
});

// Achats
Route::middleware('auth')->group(function () {
    // Panier
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{track}', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{track}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/checkout', [PurchaseController::class, 'checkoutCart'])->name('cart.checkout');
    
    // Achats individuels
    Route::post('/tracks/{track}/purchase', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/success', [PurchaseController::class, 'success'])->name('purchases.success');
});

// Webhook Stripe (doit être en dehors du middleware CSRF)
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->middleware('web')
    ->name('webhook.stripe');

// Webhook PayPal
Route::post('/webhook/paypal', [\App\Http\Controllers\PayPalWebhookController::class, 'handleWebhook'])
    ->name('webhook.paypal');

// Panneau d'administration
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    Route::get('/tracks', [AdminController::class, 'tracks'])->name('tracks.index');
    Route::delete('/tracks/{track}', [AdminController::class, 'deleteTrack'])->name('tracks.delete');
    Route::get('/purchases', [AdminController::class, 'purchases'])->name('purchases.index');
    Route::get('/payouts', [AdminController::class, 'payouts'])->name('payouts.index');
    Route::patch('/payouts/{payout}/mark-paid', [AdminController::class, 'markPayoutPaid'])->name('payouts.mark-paid');
});

// Profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
