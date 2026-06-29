<?php

namespace Modules\Order\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class InvoiceResource extends JsonResource
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

        // Total Amount Calculation
        $total_amount = ($subtotal + $total_tax_amount + $shipping_charge + $additional_charge) - $coupon_discount;

        return [
            'customer' => $this->customer ? [
                'name' => $this->customer?->first_name . ' ' . $this->customer?->last_name,
                'email' => $this->customer?->email,
                'phone' => $this->customer?->phone,
                'shipping_address' => $this->orderAddress ? [
                    'house' => $this->orderAddress?->house,
                    'road' => $this->orderAddress?->road,
                    'floor' => $this->orderAddress?->floor,
                    'address' => $this->orderAddress?->address,
                    'postal_code' => $this->orderAddress?->postal_code,
                    'contact' => $this->orderAddress?->contact_number
                ] : null
            ] : null,
            'invoice_number' => '#' . $this->invoice_number,
            'invoice_date' => $this->invoice_date ? Carbon::parse($this->invoice_date)->format('d-M-Y') : null,
            'payment_status' => $this->payment_status,
            'items' => $this->orderDetail?->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product?->name,
                    'description' => $item->product?->description,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'variant' => json_decode($item->variant_details),
                    'amount' => $item->line_total_price_with_qty,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'total_tax_amount' => $item->total_tax_amount,
                ];
            }),
            'subtotal' => $subtotal,
            'coupon_discount' => $coupon_discount,
            'tax_rate' => optional($this->orderDetail->first())->tax_rate,
            'total_tax_amount' => $total_tax_amount,
            'product_discount_amount' => $product_discount_amount,
            'shipping_charge' => $shipping_charge,
            'additional_charge_name' => $this->additional_charge_name,
            'additional_charge' => $additional_charge,
            'total_amount' => round($total_amount, 2),
        ];
    }
}
