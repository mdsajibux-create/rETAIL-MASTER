<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTransactionReportResource extends JsonResource
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
            "invoice" => $this->invoice_number,
            "zone"                    => $this->zone?->name,
            "state"                   => $this->state?->name,
            "city"                    => $this->city?->name,
            "area"                    => $this->area?->name,
            "customer" => $this->customer?->full_name,
            "total_product_amount" => $this->orderDetail?->sum('line_total_price_with_qty') + $this->orderDetail?->sum('admin_discount_amount'),
            "product_discount_amount" => $this->product_discount_amount,
            "flash_discount_amount" => $this->flash_discount_amount,
            "coupon_discount_amount" => $this->orderdetail?->sum('coupon_discount_amount'),
            "total_tax_amount" => $this->orderDetail?->sum('total_tax_amount'),
            "shipping_charge" => $this->shipping_charge,
            "delivery_charge" => $this->delivery_charge_admin,
            "order_amount" => $this->order_amount,
            "additional_charge_amount" => $this->additional_charge_amount,
            "payment_gateway" => $this->payment_gateway,
            "payment_status" => $this->payment_status,
            "refund_status" => $this->refund_status,
            "status" => $this->status,
        ];
    }
}
