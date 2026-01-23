<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Transfer;

class ArtistPayoutService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createForPurchase(Purchase $purchase): void
    {
        $purchase->loadMissing('track.artist');

        if (! $purchase->track || ! $purchase->track->artist) {
            return;
        }

        // Idempotency: ne jamais créer 2 payouts pour le même achat
        if (Payout::where('purchase_id', $purchase->id)->exists()) {
            return;
        }

        $artist = $purchase->track->artist;
        $artistAmount = (int) round($purchase->amount_cents * 0.70);

        $payout = Payout::create([
            'user_id' => $artist->id,
            'purchase_id' => $purchase->id,
            'amount_cents' => $artistAmount,
            'status' => 'pending',
        ]);

        // Si l'artiste a connecté Stripe, on tente un Transfer (plateforme -> compte connecté)
        if (! $artist->stripe_account_id) {
            return;
        }

        try {
            $transfer = Transfer::create([
                'amount' => $artistAmount,
                'currency' => 'eur',
                'destination' => $artist->stripe_account_id,
                'metadata' => [
                    'purchase_id' => (string) $purchase->id,
                    'payout_id' => (string) $payout->id,
                    'artist_id' => (string) $artist->id,
                ],
            ]);

            $payout->update([
                'payout_id' => $transfer->id,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect: Transfer échoué', [
                'purchase_id' => $purchase->id,
                'payout_id' => $payout->id,
                'artist_id' => $artist->id,
                'stripe_account_id' => $artist->stripe_account_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

