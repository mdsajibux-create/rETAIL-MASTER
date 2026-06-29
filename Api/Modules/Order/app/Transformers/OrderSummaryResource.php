<?php

namespace Modules\Order\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subtotal = round($this->orderDetail->sum('line_total_price_with_qty'), 2);
        $coupon_discount = round($this->orderDetail->sum('coupon_discount_amount'), 2);
        $total_tax_amount = round($this->orderDetail->sum('total_tax_amount'), 2);
        $product_discount_amount = round(abs($this->product_discount_amount), 2);
        $shipping_charge = round($this->shipping_charge, 2);
        $additional_charge = round($this->additional_charge_amount, 2) ?? 0;
        $total_amount = round($this->orderDetail->sum('line_total_price'), 2) + $shipping_charge + $additional_charge;

        return [
            'subtotal' => $subtotal,
            'coupon_discount' => $coupon_discount,
            'tax_rate' => round($this->orderDetail->sum('tax_rate'), 2) ?? 0,
            'total_tax_amount' => $total_tax_amount,
            'product_discount_amount' => $product_discount_amount,
            'shipping_charge' => $shipping_charge,
            'additional_charge' => $additional_charge,
            'total_amount' => round($total_amount, 2), // Final total amount
        ];
    }
}