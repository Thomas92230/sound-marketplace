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

// Route temporaire pour devenir artiste
Route::get('/become-artist', function() {
    if (auth()->check()) {
        auth()->user()->update(['role' => \App\Enums\UserRole::Artist]);
        return redirect('/')->with('success', 'Vous êtes maintenant artiste!');
    }
    return redirect('/login');
})->middleware('auth');

// Route de debug pour vérifier les pistes
Route::get('/debug-tracks', function() {
    $tracks = \App\Models\Track::latest()->take(5)->get();
    $count = \App\Models\Track::count();
    
    $html = "<h1>Debug Tracks</h1>";
    $html .= "<p>Total tracks: {$count}</p>";
    
    if ($count > 0) {
        $html .= "<ul>";
        foreach ($tracks as $track) {
            $fileExists = \Storage::disk('public')->exists($track->full_file_key ?? '') ? 'OUI' : 'NON';
            $html .= "<li>ID: {$track->id} - {$track->title} par {$track->artist_name} - Prix: {$track->price_cents}c - Fichier: {$fileExists}</li>";
        }
        $html .= "</ul>";
    } else {
        $html .= "<p>Aucune piste trouvée</p>";
    }
    
    return $html;
});

// Upload simple pour test
Route::post('/test-upload', function(\Illuminate\Http\Request $request) {
    try {
        $user = $request->user();
        if (!$user || !$user->isArtist()) {
            return response()->json(['error' => 'Pas artiste']);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255', 
            'price_cents' => 'required|integer|min:50',
            'track' => 'required|file|mimes:mp3|max:20000'
        ]);
        
        $path = $request->file('track')->store('tracks', 'public');
        
        $track = \App\Models\Track::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'artist_name' => $request->artist_name,
            'price_cents' => $request->price_cents,
            'full_file_key' => $path,
            'preview_url' => \Storage::disk('public')->url($path)
        ]);
        
        return response()->json(['success' => true, 'track_id' => $track->id]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->middleware('auth');

Route::get('/test-upload', function() {
    return view('test-upload');
})->middleware('auth');

// Profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
