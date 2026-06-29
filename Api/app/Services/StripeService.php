<?php

namespace App\Services;

use Modules\PaymentGateways\app\Models\PaymentGateway;
use Stripe\StripeClient;

class StripeService
{
    protected ?StripeClient $stripe = null;

    protected function initStripe(): void
    {
        if ($this->stripe !== null) {
            return;
        }

        // Fetch Stripe gateway row
        $stripe = PaymentGateway::where('slug', 'stripe')->first();
        // Fallback in case the row doesn't exist
        if (!$stripe) {
            throw new \Exception('Stripe gateway configuration not found.');
        }

        // Decode stored JSON credentials
        $stripeCredentials = json_decode($stripe->auth_credentials ?? '{}', true);

        // Extract keys
        $secretKey = $stripeCredentials['stripe_secret_key'] ?? '';
        $publicKey = $stripeCredentials['stripe_public_key'] ?? '';

        // Validate key presence
        if (empty($secretKey)) {
            throw new \Exception('Stripe secret key is missing.');
        }

        // Initialize Stripe client dynamically
        $this->stripe = new StripeClient($secretKey);

    }

    /**
     * Create a Stripe Checkout Session and return the session object.
     *
     * $lineItems: array of ['price_data' => [...], 'quantity' => int]
     */
    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl, array $metadata = [])
    {
        $this->initStripe();
        return $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }
}
