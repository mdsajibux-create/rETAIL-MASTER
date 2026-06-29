<?php

namespace App\Jobs;

use App\Services\GlobalEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Order\app\Models\Order;

class DispatchOrderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order_id;

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function handle(GlobalEmailService $globalEmailService)
    {
        // Eager load all necessary relationships
        $all_orders = Order::with([
            'customer',
            'orderAddress'
        ])->where('id', $this->order_id)
            ->get();

        $system_global_email = com_option_get('com_site_email');

        // Call service method to send emails
        $globalEmailService->DispatchOrderEmails($all_orders, $system_global_email);
    }
}
