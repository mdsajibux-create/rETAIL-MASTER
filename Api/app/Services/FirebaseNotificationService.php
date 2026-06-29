<?php

namespace App\Services;

use App\Models\UniversalNotification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseNotificationService
{

    // This method sends a notification to the customer and the seller for a single order
    public function sendOrderPlacedNotification($order, $customerToken, $sellerToken): void
    {
        $title = "Order #{$order->id} Placed";
        $body = "Order placed successfully.";

        $data = [
            'order_id' => $order->id,
            'click_action' => url("/order/{$order->id}"),
        ];

        // Send notification to the customer
        $this->sendToTokens([$customerToken], $title, "Your order has been placed.", $data);

        // Send notification to the seller
        $this->sendToTokens([$sellerToken], $title, "You have received a new order.", $data);
    }

    // This method sends a notification to the admin
    public function sendAdminNotification($orderMaster): void
    {
        // Admin token is typically saved in a config or from a database
        $adminToken = config('app.admin_fcm_token');

        if (!$adminToken) return; // Ensure admin token is available

        // Send notification to admin
        $this->sendToTokens(
            [$adminToken],
            "New Order Placed",
            "OrderMaster ID #{$orderMaster->id} has new orders.",
            ['click_action' => url("/admin/orders/{$orderMaster->id}")]
        );
    }

    // Helper method to send push notifications to a list of tokens
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $messaging = Firebase::messaging();

        $notification = UniversalNotification::create($title, $body);

        foreach ($tokens as $token) {
            if (!$token) continue;

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $messaging->send($message);
        }
    }
}
