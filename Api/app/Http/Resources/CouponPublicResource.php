<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $coupon_translation = $this->coupon->related_translations->where('language', $language);
        return [
            "id" => $this->id,
            "coupon_title" => !empty($coupon_translation) && $coupon_translation->where('key', 'title')->first()
                ? $coupon_translation->where('key', 'title')->first()->value
                : $this->coupon?->title, // If language is empty or not provided attribute
            "coupon_description" => !empty($coupon_translation) && $coupon_translation->where('key', 'description')->first()
                ? $coupon_translation->where('key', 'description')->first()->value
                : $this->coupon?->description, // If language is empty or not provided attribute
            "coupon_image_url" => ImageModifier::generateImageUrl($this->coupon?->image) ?? null,
            "coupon_code" => $this->coupon_code,
            "discount_type" => $this->discount_type,
            "discount" => $this->discount,
            "min_order_value" => $this->min_order_value,
            "max_discount" => $this->max_discount,
            "start_date" => $this->start_date?->format('Y-m-d H:i:s'),
            "end_date" => $this->end_date?->format('Y-m-d H:i:s'),
        ];
    }
}
