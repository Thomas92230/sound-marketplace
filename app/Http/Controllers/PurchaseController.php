<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Track;
use App\Services\PayPalService;
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
    public function __construct(
        protected ArtistPayoutService $artistPayouts,
        protected PayPalService $paypal
    ) {
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
     * Crée une session de paiement
     */
    public function store(Request $request, Track $track)
    {
        $user = $request->user();
        $paymentMethod = $request->input('payment_method', 'stripe');

        // Vérifications communes
        if ($track->isPurchasedBy($user->id)) {
            return back()->with('error', 'Vous avez déjà acheté ce morceau.');
        }

        if ($track->user_id === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas acheter votre propre morceau.');
        }

        // Créer l'enregistrement d'achat
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'amount_cents' => $track->price_cents,
            'payment_method' => $paymentMethod,
            'payment_id' => 'pending',
            'status' => 'pending',
        ]);

        try {
            if ($paymentMethod === 'paypal') {
                return $this->createPayPalPayment($purchase);
            }
            
            return $this->createStripePayment($purchase, $track, $user);
        } catch (\Exception $e) {
            Log::error("Erreur paiement {$paymentMethod}: " . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création du paiement.');
        }
    }

    private function createStripePayment(Purchase $purchase, Track $track, $user)
    {
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
            'success_url' => route('purchases.success', $purchase) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('tracks.show', $track),
            'metadata' => ['purchase_id' => $purchase->id],
        ]);

        $purchase->update(['payment_id' => $session->id]);
        return redirect($session->url);
    }

    private function createPayPalPayment(Purchase $purchase)
    {
        $order = $this->paypal->createPayment($purchase);
        
        if (isset($order['id'])) {
            $purchase->update(['payment_id' => $order['id']]);
            
            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect($link['href']);
                }
            }
        }
        
        throw new \Exception('Erreur PayPal');
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

    /**
     * Checkout du panier complet
     */
    public function checkoutCart(Request $request)
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Panier vide.');
        }
        
        $tracks = Track::whereIn('id', array_keys($cart))->get();
        $total = $tracks->sum('price_cents');
        
        // Créer un achat groupé
        $purchase = Purchase::create([
            'user_id' => $request->user()->id,
            'track_id' => $tracks->first()->id, // Premier track comme référence
            'amount_cents' => $total,
            'payment_method' => 'stripe',
            'payment_id' => 'pending',
            'status' => 'pending',
        ]);
        
        try {
            $lineItems = [];
            foreach ($tracks as $track) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $track->title,
                            'description' => 'Par ' . $track->artist_name,
                        ],
                        'unit_amount' => $track->price_cents,
                    ],
                    'quantity' => 1,
                ];
            }
            
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('purchases.success', $purchase) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cart.index'),
                'metadata' => ['purchase_id' => $purchase->id],
            ]);
            
            $purchase->update(['payment_id' => $session->id]);
            session()->forget('cart');
            
            return redirect($session->url);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du paiement.');
        }
    }
}