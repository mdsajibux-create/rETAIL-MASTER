<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProductListPublicResource extends JsonResource
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
        $translation = $this->related_translations->where('language', $language);
        return [
            'id' => $this->id,
            'category' => $this->category->category_name ?? null,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
            'slug' => $this->slug,
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'stock' => $this->variants->isNotEmpty() ? $this->variants->sum('stock_quantity') : null,
            'price' => optional($this->variants->first())->price,
            'special_price' => optional($this->variants->first())->special_price,
            'singleVariant' => $this->variants->count() === 1 ? [$this->variants->first()] : [],
            'max_cart_qty' => $this->max_cart_qty,
            'discount_percentage' => $this->variants->isNotEmpty() && optional($this->variants->first())->price > 0
                ? round(((optional($this->variants->first())->price - optional($this->variants->first())->special_price) / optional($this->variants->first())->price) * 100, 2)
                : null,
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'flash_sale' => $this->isInFlashDeal(),
            'is_featured' => (bool)$this->is_featured
        ];
    }
}
