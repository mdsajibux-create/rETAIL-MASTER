<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\ProductCategoryByIdPublicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSuggestionPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        $firstVariant = $this->variants->first();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()  ? $translation->where('key', 'name')->first()->value : $this->name,
            'slug' => $this->slug,
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            // branch-wise stock only because controller already loaded branch stocks only
            'stock' => $this->variants->sum(function ($variant) {
                return $variant->stocks->sum('qty');
            }),
            'price' => optional($this->variants->first())->price,
            'special_price' => optional($this->variants->first())->special_price,
            'singleVariant' => $this->variants->count() === 1 ? [new ProductVariantPublicWebResource($firstVariant)] : [],
            'max_cart_qty' => $this->max_cart_qty,
            'discount_percentage' =>
                $firstVariant && $firstVariant->price > 0 && $firstVariant->special_price > 0
                    ? round((($firstVariant->price - $firstVariant->special_price) / $firstVariant->price) * 100, 2)
                    : 0,
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'flash_sale' => $this->isInFlashDeal(),
            'is_featured' => (bool)$this->is_featured,
            'category_name' => new ProductCategoryByIdPublicResource($this->category)
        ];
    }
}
