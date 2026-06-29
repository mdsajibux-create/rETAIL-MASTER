<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "order_id" => $this->order_id,
            "invoice" => $this->order?->invoice_number,

            "zone" => $this->zone?->name,
            "state"  => $this->order?->state?->name,
            "city"   => $this->order?->city?->name,
            "area" => $this->order?->area?->name,

            "customer" => $this->order?->customer?->full_name,
            "payment_gateway" => $this->order?->payment_gateway,
            "payment_status" => $this->order?->payment_status,
            "order_amount" => $this->order?->order_amount,
            "coupon_discount_amount" => $this->order?->coupon_discount_amount,
            "product_discount_amount" => $this->order?->product_discount_amount,
            "flash_discount_amount" => $this->order?->flash_discount_amount,
            "shipping_charge" => $this->order?->shipping_charge,
            "refund_status" => $this->order?->refund_status,
            "status" => $this->order?->status,
            "base_price" => $this->base_price,
            "price" => $this->price,
            "quantity" => $this->quantity,
            "line_total_price_with_qty" => $this->line_total_price_with_qty,
            "line_total_excluding_tax" => $this->line_total_excluding_tax,
            "tax_rate" => $this->tax_rate,
            "tax_amount" => $this->tax_amount,
            "total_tax_amount" => $this->total_tax_amount,
            "line_total_price" => $this->line_total_price,
            "additional_charge_name" => $this->order?->additional_charge_name,
            "additional_charge_amount" => $this->order?->additional_charge_amount,
        ];
    }
}
