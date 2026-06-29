<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewArrivalPublicHomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');

        // relation already loaded collection memory
        $translations = $this->whenLoaded('related_translations', collect());

        $nameTranslation = $translations
            ->where('language', $language)
            ->where('key', 'name')
            ->first();

        $descriptionTranslation = $translations
            ->where('language', $language)
            ->where('key', 'description')
            ->first();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $nameTranslation?->value ?? $this->name,
            'slug' => $this->slug,
            'description' => $descriptionTranslation?->value ?? $this->description,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'stock' => $this->variants?->isNotEmpty() ? $this->variants->sum(fn($variant) => $variant?->stocks->sum('qty')) : 0,
            'price' => optional($this->variants->first())->price,
            'special_price' => optional($this->variants->first())->special_price,
            'max_cart_qty' => $this->max_cart_qty,
            'singleVariant' => $this->variants->count() === 1
                ? $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'variant_slug' => $variant->variant_slug,
                        'sku' => $variant->sku,
                        'attributes' => $variant->attributes,
                        'price' => $variant->price,
                        'special_price' => $variant->special_price,
                        'effective_price' => $variant->effective_price,
                        'order_count' => $variant->order_count,
                        'status' => $variant->status,
                    ];
                })->values()
                : [],
            'discount_percentage' => $this->variants->isNotEmpty() && optional($this->variants->first())->price > 0 && optional($this->variants->first())->special_price > 0
                ? round(((optional($this->variants->first())->price - optional($this->variants->first())->special_price) / optional($this->variants->first())->price) * 100, 2)
                : 0,
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'flash_sale' => $this->isInFlashDeal(),
        ];
    }
}
