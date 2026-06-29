<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\FlashSaleTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminFlashSaleDetailsResource extends JsonResource
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
            "products" => $this->products->map(function ($flashSaleProduct) {
                return [
                    'id' => $flashSaleProduct?->product?->id,
                    'value' => $flashSaleProduct?->product?->id,
                    'label' => $flashSaleProduct?->product?->name,
                    'image' => ImageModifier::generateImageUrl($flashSaleProduct?->product?->image),
                ];
            }),
            "title" => $this->title,
            "description" => $this->description,
            "title_color" => $this->title_color,
            "description_color" => $this->description_color,
            "background_color" => $this->background_color,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "cover_image" => $this->cover_image,
            "cover_image_url" => ImageModifier::generateImageUrl($this->cover_image),
            "discount_type" => $this->discount_type,
            "discount_amount" => $this->discount_amount,
            "special_price" => $this->special_price,
            "purchase_limit" => $this->purchase_limit,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "status" => $this->status,
            "button_text" => $this->button_text,
            "button_text_color" => $this->button_text_color,
            "button_hover_color" => $this->button_hover_color,
            "button_bg_color" => $this->button_bg_color,
            "button_url" => $this->button_url,
            "timer_bg_color" => $this->timer_bg_color,
            "timer_text_color" => $this->timer_text_color,
            "related_translations" => FlashSaleTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
