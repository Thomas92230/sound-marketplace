<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtistDashboardController;
use App\Http\Controllers\ArtistStripeConnectController;
use Illuminate\Support\Facades\Route;

// Toutes les routes web sont automatiquement protégées par CSRF via le middleware 'web'
// défini dans bootstrap/app.php, sauf les webhooks explicitement exclus

// Servir les fichiers audio
Route::get('/storage/tracks/{filename}', function ($filename) {
    $path = storage_path('app/public/tracks/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    $mimeTypes = [
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4',
        'aac' => 'audio/aac',
        'flac' => 'audio/flac',
    ];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $contentType = $mimeTypes[$ext] ?? 'audio/mpeg';
    return response()->file($path, ['Content-Type' => $contentType]);
})->where('filename', '.*\.(mp3|wav|ogg|m4a|aac|flac)');

// Page d'accueil : catalogue des morceaux
Route::get('/', [TrackController::class, 'index'])->name('home');

// Détails d'un morceau
Route::get('/tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');
Route::get('/tracks/{track}/download', [TrackController::class, 'download'])->name('tracks.download')->middleware('auth');

// Profils d'artistes (public)
Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');

// Upload de morceaux (réservé aux artistes)
Route::middleware(['auth', 'role:artist,admin', 'web'])->group(function () {
    Route::get('/upload', [TrackController::class, 'showForm'])->name('tracks.upload.form');
    Route::post('/upload', [TrackController::class, 'store'])->middleware('throttle:5,1')->name('tracks.upload.store');
    Route::get('/tracks/{track}/edit', [TrackController::class, 'edit'])->name('tracks.edit');
    Route::patch('/tracks/{track}', [TrackController::class, 'update'])->middleware('throttle:10,1')->name('tracks.update');
    Route::delete('/tracks/{track}', [TrackController::class, 'destroy'])->middleware('throttle:10,1')->name('tracks.destroy');
    Route::delete('/tracks-bulk', [TrackController::class, 'bulkDestroy'])->middleware('throttle:5,1')->name('tracks.bulk-delete');
    
    // Profil artiste
    Route::get('/artist/profile', [ArtistController::class, 'edit'])->name('artists.edit');
    Route::post('/artist/profile', [ArtistController::class, 'update'])->middleware('throttle:10,1')->name('artists.update');
});

// Dashboard
Route::get('/dashboard', [TrackController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard artiste
Route::middleware(['auth', 'role:artist,admin', 'web'])->group(function () {
    Route::get('/artist/dashboard', [ArtistDashboardController::class, 'index'])->name('artist.dashboard');
    Route::get('/artist/stripe/connect', [ArtistStripeConnectController::class, 'start'])->name('artist.stripe.start');
    Route::get('/artist/stripe/refresh', [ArtistStripeConnectController::class, 'refresh'])->name('artist.stripe.refresh');
    Route::get('/artist/stripe/return', [ArtistStripeConnectController::class, 'return'])->name('artist.stripe.return');
});

// Achats
Route::middleware(['auth', 'web'])->group(function () {
    // Panier
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{track}', [\App\Http\Controllers\CartController::class, 'add'])->middleware('throttle:20,1')->name('cart.add');
    Route::delete('/cart/{track}', [\App\Http\Controllers\CartController::class, 'remove'])->middleware('throttle:20,1')->name('cart.remove');
    Route::delete('/cart', [\App\Http\Controllers\CartController::class, 'clear'])->middleware('throttle:10,1')->name('cart.clear');
    Route::post('/cart/checkout', [PurchaseController::class, 'checkoutCart'])->middleware('throttle:5,1')->name('cart.checkout');
    
    // Achats individuels
    Route::post('/tracks/{track}/purchase', [PurchaseController::class, 'store'])->middleware('throttle:10,1')->name('purchases.store');
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
Route::middleware(['auth', 'role:admin', 'web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->middleware('throttle:10,1')->name('users.update-role');
    Route::get('/tracks', [AdminController::class, 'tracks'])->name('tracks.index');
    Route::delete('/tracks/{track}', [AdminController::class, 'deleteTrack'])->middleware('throttle:10,1')->name('tracks.delete');
    Route::get('/purchases', [AdminController::class, 'purchases'])->name('purchases.index');
    Route::get('/payouts', [AdminController::class, 'payouts'])->name('payouts.index');
    Route::patch('/payouts/{payout}/mark-paid', [AdminController::class, 'markPayoutPaid'])->middleware('throttle:10,1')->name('payouts.mark-paid');
});

// Profil utilisateur
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:10,1')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:5,1')->name('profile.destroy');
});

require __DIR__.'/auth.php';
