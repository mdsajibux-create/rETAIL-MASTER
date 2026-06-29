<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPublicHomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstVariant = $this->variants->first();
        $language = $request->input('language', 'en');

        $translations = $this->whenLoaded('related_translations', collect());
        $langTranslations = $translations->where('language', $language)->keyBy('key');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $langTranslations->get('name')?->value ?? $this->name,
            'slug' => $this->slug,
            'description' => $langTranslations->get('description')?->value ?? $this->description,
            'unit' => $this->unit?->name,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'max_cart_qty' => $this->max_cart_qty,
            'stock' => $this->variants?->isNotEmpty() ? $this->variants->sum(fn($variant) => $variant?->stocks->sum('qty')) : 0,
            'attributes' => $this->variants->pluck('attributes')->map(function ($attribute) {
                return json_decode($attribute, true);
            })->toArray(),
            'effective_price' => optional($firstVariant)->effective_price
                ?? (
                optional($firstVariant)->special_price && optional($firstVariant)->special_price < optional($firstVariant)->price
                    ? optional($firstVariant)->special_price
                    : optional($firstVariant)->price
                ),
            'price' => shouldRound() ? round(optional($firstVariant)->price) : round(optional($firstVariant)->price, 2),
            'special_price' => shouldRound() ? round(optional($firstVariant)->special_price) : round(optional($firstVariant)->special_price, 2),
            'singleVariant' => $this->variants->count() === 1 ? [$firstVariant] : [],
            'discount_percentage' => $firstVariant && $firstVariant->price > 0 && $firstVariant->special_price > 0
                ? round((($firstVariant->price - $firstVariant->special_price) / $firstVariant->price) * 100, 2)
                : 0,
            'flash_sale' => $this->isInFlashDeal(),
            'is_featured' => (bool)$this->is_featured,
        ];
    }
}
