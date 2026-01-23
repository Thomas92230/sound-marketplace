<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class ArtistStripeConnectController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function start(Request $request)
    {
        $user = $request->user();

        try {
            if (! $user->stripe_account_id) {
                $account = Account::create([
                    'type' => 'express',
                    'country' => env('STRIPE_CONNECT_COUNTRY', 'FR'),
                    'email' => $user->email,
                    'capabilities' => [
                        'transfers' => ['requested' => true],
                    ],
                    'metadata' => [
                        'user_id' => (string) $user->id,
                    ],
                ]);

                $user->update([
                    'stripe_account_id' => $account->id,
                ]);
            }

            $link = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('artist.stripe.refresh'),
                'return_url' => route('artist.stripe.return'),
                'type' => 'account_onboarding',
            ]);

            return redirect($link->url);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect Express: onboarding start failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Impossible de démarrer la connexion Stripe pour le moment.');
        }
    }

    public function refresh(Request $request)
    {
        // Stripe redirige ici si l'utilisateur quitte ou expire l'onboarding
        return redirect()->route('artist.stripe.start');
    }

    public function return(Request $request)
    {
        // On n'exige pas une vérif complète ici; le statut réel dépend des requirements Stripe.
        return redirect()->route('artists.edit')->with('success', 'Retour Stripe Connect reçu. Votre compte est en cours de vérification.');
    }
}

