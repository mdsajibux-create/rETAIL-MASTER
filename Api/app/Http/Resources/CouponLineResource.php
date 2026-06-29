<?php

namespace App\Http\Resources;

use App\Http\Resources\Customer\CustomerPublicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coupon' => new CouponResource($this->coupon),
            'customer' => new CustomerPublicResource($this->customer),
            'coupon_code' => $this->coupon_code,
            'discount_type' => $this->discount_type,
            'discount' => $this->discount,
            'min_order_value' => $this->min_order_value ?? null, // Handle nullable
            'max_discount' => $this->max_discount ?? null, // Handle nullable
            'usage_limit' => $this->usage_limit ?? null, // Handle nullable
            'usage_count' => $this->usage_count,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d H:i:s') : null, // Format datetime
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d H:i:s') : null, // Format datetime
            'status' => $this->status,
        ];
    }
}
