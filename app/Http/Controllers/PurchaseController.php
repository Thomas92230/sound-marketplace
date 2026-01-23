<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Services\ArtistPayoutService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class PurchaseController extends Controller
{
    public function __construct(protected ArtistPayoutService $artistPayouts)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Affiche les achats de l'utilisateur
     */
    public function index(Request $request): View
    {
        $purchases = Purchase::where('user_id', $request->user()->id)
            ->with('track')
            ->latest()
            ->get();

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Crée une session de paiement Stripe
     */
    public function store(Request $request, Track $track)
    {
        $user = $request->user();

        // Vérifier si l'utilisateur a déjà acheté ce morceau
        if ($track->isPurchasedBy($user->id)) {
            return back()->with('error', 'Vous avez déjà acheté ce morceau.');
        }

        // Vérifier que l'utilisateur n'achète pas son propre morceau
        if ($track->user_id === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas acheter votre propre morceau.');
        }

        try {
            // Créer l'enregistrement d'achat en attente
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'track_id' => $track->id,
                'amount_cents' => $track->price_cents,
                'payment_id' => 'pending',
                'payment_method' => 'stripe',
                'status' => 'pending',
            ]);

            // Créer une session de paiement Stripe Checkout
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $track->title,
                            'description' => 'Par ' . $track->artist_name,
                        ],
                        'unit_amount' => $track->price_cents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchases.success', ['purchase' => $purchase->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('tracks.show', $track) . '?canceled=1',
                'metadata' => [
                    'purchase_id' => $purchase->id,
                    'user_id' => $user->id,
                    'track_id' => $track->id,
                ],
            ]);

            // Mettre à jour l'achat avec l'ID de session Stripe
            $purchase->update([
                'payment_id' => $session->id,
            ]);

            // Rediriger vers Stripe Checkout
            return redirect($session->url);

        } catch (ApiErrorException $e) {
            Log::error('Erreur Stripe: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création du paiement. Veuillez réessayer.');
        }
    }

    /**
     * Page de succès après paiement
     */
    public function success(Request $request, Purchase $purchase)
    {
        if ($purchase->user_id !== $request->user()->id) {
            abort(403);
        }

        $sessionId = $request->query('session_id');

        if ($sessionId) {
            try {
                $session = Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    // Le webhook devrait avoir déjà mis à jour le statut, mais on vérifie quand même
                    if ($purchase->status !== 'completed') {
                        $purchase->update([
                            'status' => 'completed',
                            'payment_id' => $session->payment_intent ?? $sessionId,
                        ]);

                        // Créer le payout artiste (idempotent) + Transfer Connect si possible
                        $this->artistPayouts->createForPurchase($purchase);
                    }
                }
            } catch (ApiErrorException $e) {
                Log::error('Erreur lors de la vérification de la session Stripe: ' . $e->getMessage());
            }
        }

        return view('purchases.success', compact('purchase'));
    }

    /**
     * Affiche les détails d'un achat
     */
    public function show(Purchase $purchase): View
    {
        // Vérifier que l'utilisateur est propriétaire de l'achat
        if ($purchase->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $purchase->load('track', 'user');

        return view('purchases.show', compact('purchase'));
    }

    // NB: la logique payout est centralisée dans ArtistPayoutService (idempotent + Stripe Connect)
}