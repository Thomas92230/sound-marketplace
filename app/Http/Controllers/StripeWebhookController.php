<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\ArtistPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __construct(protected ArtistPayoutService $artistPayouts)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Gère les webhooks Stripe
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook Stripe: Payload invalide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payload invalide'], Response::HTTP_BAD_REQUEST);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook Stripe: Signature invalide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Signature invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Gérer les différents types d'événements
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            default:
                Log::info('Webhook Stripe: Événement non géré', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Gère l'événement checkout.session.completed
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        $purchaseId = $session->metadata->purchase_id ?? null;

        if (!$purchaseId) {
            Log::warning('Webhook Stripe: purchase_id manquant dans les métadonnées', ['session_id' => $session->id]);
            return;
        }

        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            Log::warning('Webhook Stripe: Achat non trouvé', ['purchase_id' => $purchaseId]);
            return;
        }

        // Mettre à jour le statut de l'achat
        if ($purchase->status !== 'completed') {
            $purchase->update([
                'status' => 'completed',
                'payment_id' => $session->payment_intent ?? $session->id,
            ]);

            // Créer le payout artiste (idempotent) + Transfer Connect si possible
            $this->artistPayouts->createForPurchase($purchase);

            Log::info('Webhook Stripe: Achat complété', ['purchase_id' => $purchase->id]);
        }
    }

    /**
     * Gère l'événement payment_intent.succeeded
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Chercher l'achat par payment_id
        $purchase = Purchase::where('payment_id', $paymentIntent->id)
            ->orWhere('payment_id', $paymentIntent->id)
            ->first();

        if ($purchase && $purchase->status !== 'completed') {
            $purchase->update([
                'status' => 'completed',
                'payment_id' => $paymentIntent->id,
            ]);

            $this->artistPayouts->createForPurchase($purchase);

            Log::info('Webhook Stripe: Paiement réussi', ['purchase_id' => $purchase->id]);
        }
    }

    /**
     * Gère l'événement payment_intent.payment_failed
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $purchase = Purchase::where('payment_id', $paymentIntent->id)->first();

        if ($purchase) {
            $purchase->update([
                'status' => 'failed',
            ]);

            Log::info('Webhook Stripe: Paiement échoué', ['purchase_id' => $purchase->id]);
        }
    }

    // NB: la logique payout est centralisée dans ArtistPayoutService (idempotent + Stripe Connect)
}