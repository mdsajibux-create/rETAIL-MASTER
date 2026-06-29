<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\BusinessSettings\app\Models\ProductType;

class StoreDetailsForOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations?->where('language', $language) ?? collect();
        $product_type_info = ProductType::where('type', $this->store_type)->first() ?? new ProductType();

        return [
            "id" => $this->id,
            "name" => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            "area_id" => $this->area_id,
            "slug" => $this->slug,
            "phone" => $this->phone,
            "email" => $this->email,
            "store_type" => $this->store_type,
            "logo" => ImageModifier::generateImageUrl($this->logo),
            "tax" => $this->tax,
            "delivery_time" => $this->delivery_time,
            "address" => $this->address,
            "latitude" => $this->area?->center_latitude,
            "longitude" => $this->area?->center_longitude,
            "rating" => $this->rating,
            "additional_charge_name" => $product_type_info->additional_charge_enable_disable ? $product_type_info->additional_charge_name : null,
            "additional_charge_amount" => $product_type_info->additional_charge_enable_disable ? round($product_type_info->additional_charge_amount) : 0,
            "additional_charge_type" => $product_type_info->additional_charge_enable_disable ? $product_type_info->additional_charge_type : 'fixed',
            "type" => "store",
        ];
    }
}
