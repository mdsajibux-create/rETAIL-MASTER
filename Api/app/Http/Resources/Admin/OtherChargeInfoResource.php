<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OtherChargeInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_include_tax_amount' => $this->order_include_tax_amount ? true : false,
            'order_tax' => $this->order_tax,
            'product_additional_info' => $this->product_type,
        ];
    }
}
