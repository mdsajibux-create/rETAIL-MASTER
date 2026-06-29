<?php

namespace App\Http\Resources\Deliveryman;

use App\Http\Resources\Customer\CustomerDetailsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverymanMyOrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "order_id" => $this->order->id,
            "payment_method" => $this->order?->orderMaster?->payment_gateway,
            "customer" => new CustomerDetailsResource($this->order?->customer),
            "order_address" => $this->order?->orderAddress?->address,
            "items" => $this->order?->orderDetail->count(),
            "invoice_number" => $this->order?->invoice_number,
            "invoice_date" => $this->order?->invoice_date,
            "order_type" => $this->order?->order_type,
            "delivery_option" => $this->order?->delivery_option,
            "delivery_type" => $this->order?->delivery_type,
            "order_amount" => $this->order?->order_amount,
            "status" => $this->order?->status,
            "delivery_status" => $this->status,
            "created_at" => optional($this->order->created_at)->format('h:i A, F j, Y'), // Example: "8:00 AM, May 29, 2025"
        ];
    }
}
