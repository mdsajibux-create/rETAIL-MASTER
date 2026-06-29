<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\app\Models\Order;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['customer', 'orderAddress']);
    }

    public function broadcastOn()
    {
        $channels = [];

        // Send to customer if available
        $customerId = $this->order->customer_id;
        if ($customerId) {
            $channels[] = new Channel("customer.$customerId");
        }

        // Always notify admin
        $channels[] = new Channel('admin');

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'customer_id' => $this->order->customer_id ?? 0,
            'order_details' => $this->order,
            'message' => 'Order ID #' . $this->order->id . ' placed successfully.',
        ];
    }

    public function broadcastAs()
    {
        return 'order.placed';
    }

}
