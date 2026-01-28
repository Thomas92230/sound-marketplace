<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('app.env') === 'production' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
    }

    public function createPayment(Purchase $purchase): array
    {
        $token = $this->getAccessToken();
        
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => number_format($purchase->amount_cents / 100, 2)
                    ],
                    'description' => "Achat: {$purchase->track->title}"
                ]],
                'application_context' => [
                    'return_url' => route('purchases.success', $purchase),
                    'cancel_url' => route('tracks.show', $purchase->track)
                ]
            ]);

        return $response->json();
    }

    public function capturePayment(string $orderId): array
    {
        $token = $this->getAccessToken();
        
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        return $response->json();
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        return $response->json()['access_token'];
    }
}