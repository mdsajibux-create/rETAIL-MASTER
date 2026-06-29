<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAreaSettingsDetailsResource extends JsonResource
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
            "zone_id" => $this->zone_id,
            "delivery_time_per_km" => $this->delivery_time_per_km,
            "min_order_delivery_fee" => $this->min_order_delivery_fee,
            "delivery_charge_method" => $this->delivery_charge_method,
            "out_of_area_delivery_charge" => $this->out_of_area_delivery_charge,
            "fixed_charge_amount" => $this->fixed_charge_amount,
            "per_km_charge_amount" => $this->per_km_charge_amount,
            "product_types" => $this->productTypes->map(function ($productTypes) {
                return [
                    "id" => $productTypes->id,
                ];
            }),
            "charges" => $this->rangeCharges->map(function ($range) {
                return [
                    "id" => $range->id,
                    "min_km" => $range->min_km,
                    "max_km" => $range->max_km,
                    "charge_amount" => $range->charge_amount,

                ];
            })
        ];
    }
}
