<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Modules\Order\app\Models\Order;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Payment Gateway settings
        $stripe = PaymentGateway::where('slug', 'stripe')->first();

        if (!$stripe) {
            return response()->json(['success' => false, 'message' => 'Stripe configuration not found.'], 400);
        }

        $stripeCredentials = json_decode($stripe->auth_credentials ?? '{}', true);

        // Get webhook secret from database
        $webhookSecret = $stripeCredentials['stripe_webhook_secret'] ?? ''; // optional fallback

        if (empty($webhookSecret)) {
            return response()->json(['success' => false, 'message' => 'Stripe webhook secret not configuration.'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // invalid payload
            return response()->json(['success' => false, 'message' => 'Invalid payload.'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // invalid signature
            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 400);
        }

        // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Get metadata (we set order_id earlier)
            $order_id = $session->metadata->order_id ?? null;
            $sessionId = $session->id ?? null;
            $amount_total = $session->amount_total ?? null; // in cents

            if ($order_id) {
                $order = Order::with('orders')->find($order_id);
                if ($order && $order->payment_status !== 'paid') {
                    // Mark paid and store payment info
                    $order->payment_status = 'paid';
                    $order->payment_gateway = 'stripe';
                    $order->transaction_ref = $sessionId;
                    $order->paid_amount = $order->paid_amount ?? ($amount_total / 100);
                    $order->save();

                    // update child orders payment_status
                    $order->update([
                        'payment_status' => 'paid'
                    ]);

                    // run any post-payment logic you have (notifications, inventory finalization)
                    // dispatch job or call existing internal services
                }
            }

            // other event types can be handled (payment_intent.succeeded etc.)
            return response()->json(['success' => true, 'message' => 'Stripe webhook successful.'], 200);
        }

        // other event types can be handled (payment_intent.succeeded etc.)
        return response('Webhook handled', 200);
    }

    public function handleWebhookForWallet(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Payment Gateway settings
        $stripe = PaymentGateway::where('slug', 'stripe')->first();

        if (!$stripe) {
            return response()->json(['success' => false, 'message' => 'Stripe configuration not found.'], 400);
        }

        $stripeCredentials = json_decode($stripe->auth_credentials ?? '{}', true);

        // Get webhook secret from database
        $webhookSecret = $stripeCredentials['stripe_webhook_secret'] ?? ''; // optional fallback

        if (empty($webhookSecret)) {
            return response()->json(['success' => false, 'message' => 'Stripe webhook secret not configuration.'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // invalid payload
            return response()->json(['success' => false, 'message' => 'Invalid payload.'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // invalid signature
            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 400);
        }

        // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Get metadata (we set order_id earlier)
            $wallet_id = $session->metadata->wallet_id ?? null;
            $wallet_history_id = $session->metadata->wallet_history_id ?? null;
            $sessionId = $session->id ?? null;

            if ($wallet_history_id) {
                $wallet = Wallet::where('id', $wallet_id)->first();
                $wallet_history = WalletTransaction::where('id', $wallet_history_id)->first();

                // Check if the wallet history exists
                if (empty($wallet_history)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Wallet not found'
                    ], 404);
                }

                if ($wallet_history && $wallet_history->payment_status !== 'paid') {

                    // Update the wallet history
                    $wallet_history->payment_status = 'paid';
                    $wallet_history->payment_gateway = 'stripe';
                    $wallet_history->transaction_ref = $sessionId;
                    $wallet_history->status = 1;
                    $wallet_history->save();

                    // Update the wallet balance
                    $wallet->payment_status = 'paid';
                    $wallet->balance += $wallet_history->amount;
                    $wallet->save();
                }
            }

            // other event types can be handled (payment_intent.succeeded etc.)
            return response()->json(['success' => true, 'message' => 'Stripe webhook successful.'], 200);
        }

        // other event types can be handled (payment_intent.succeeded etc.)
        return response('Webhook handled', 200);
    }
}
