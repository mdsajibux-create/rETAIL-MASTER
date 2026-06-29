<?php

namespace App\Http\Resources\Deliveryman;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverymanOrderRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $orderAddress = $this->orderMaster?->orderAddress;
        $formattedAddress = sprintf(
            '%s%s%s%s',
            $orderAddress['postal_code'] ?? '',
            isset($orderAddress['house']) ? ' House: ' . $orderAddress['house'] : '',
            isset($orderAddress['road']) ? ', Road: ' . $orderAddress['road'] : '',
            isset($orderAddress['address']) ? ', ' . $orderAddress['address'] : ''
        );

        return [
            "id" => $this->id,
            "payment_method" => $this->payment_gateway,
            "items" => $this->orderDetail->count(),
            "zone_id" => $this->zone_id,
            "invoice_number" => $this->invoice_number,
            "invoice_date" => $this->invoice_date,
            "order_type" => $this->order_type,
            "delivery_option" => $this->delivery_option,
            "delivery_type" => $this->delivery_type,
            "order_amount" => $this->order_amount,
            "status" => $this->status,
            "address" => $formattedAddress,
            "shipping_address_lat" => $this->orderAddress?->latitude,
            "shipping_address_lng" => $this->orderAddress?->longitude,
            "shipping_charge" => $this->shipping_charge,
            "created_at" => $this->created_at->diffForHumans(),
        ];
    }
}
