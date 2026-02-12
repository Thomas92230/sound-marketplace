<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\ArtistPayoutService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function __construct(
        protected PayPalService $paypal,
        protected ArtistPayoutService $artistPayouts
    ) {}

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        
        Log::info('PayPal Webhook reçu', $payload);

        if ($payload['event_type'] === 'CHECKOUT.ORDER.APPROVED') {
            $orderId = $payload['resource']['id'];
            
            $purchase = Purchase::where('payment_id', '=', $orderId)->first();
            
            if ($purchase && $purchase->status === 'pending') {
                // Capturer le paiement
                $result = $this->paypal->capturePayment($orderId);
                
                if ($result['status'] === 'COMPLETED') {
                    $purchase->update(['status' => 'completed']);
                    $this->artistPayouts->createForPurchase($purchase);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}