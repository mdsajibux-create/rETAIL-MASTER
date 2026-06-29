<?php

namespace App\Http\Resources\Com;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => match ($this->activity_value) {
                'pending' => 'We’ve received your order and it’s waiting for confirmation.',

                'confirmed' => 'Your order has been confirmed and will be processed soon.',

                'processing' => 'We’re getting your order ready — it’s being packed and prepared.',

                'pickup' => 'The package is ready and waiting to be picked up by the delivery partner.',

                'shipped' => 'Your order is on the way and has left the warehouse/store.',

                'delivered' => 'Your order has been successfully delivered. We hope you enjoy your purchase!',

                'cancelled' => 'This order has been cancelled. For more details, please contact support.',

                'on_hold' => 'Your order is temporarily on hold. This may be due to payment or stock issues.',
                default => ucfirst($this->activity_value),
            },
            'status' => $this->activity_value,
            'created_at' => Carbon::parse($this->created_at)->format('d M Y, h:i A'),
        ];
    }
}
