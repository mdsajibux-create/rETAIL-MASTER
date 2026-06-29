<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\ProductCategoryByIdPublicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelatedProductPublicResource extends JsonResource
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

        $variants = $this->variants ?? collect();

        $totalStock = $variants->sum(function ($variant) {
            $stocks = $variant->relationLoaded('stocks')
                ? $variant->stocks
                : collect();

            return $stocks->sum('qty');
        });

        $firstVariant = $variants->first();


        return [
            'id' => $this->id,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
            'slug' => $this->slug,
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'max_cart_qty' => $this->max_cart_qty,
            'views' => $this->views,
            'singleVariant' => $variants->count() === 1 ? [$firstVariant] : [],
            'stock' => $totalStock,
            'price' => $this->variants->isNotEmpty() ? $this->variants[0]->price : null,
            'special_price' => $this->variants->isNotEmpty() ? $this->variants[0]->special_price : null,
            'discount_percentage' => $this->variants->isNotEmpty() && $this->variants[0]->price > 0 && $this->variants[0]->special_price > 0
                ? round((($this->variants[0]->price - $this->variants[0]->special_price) / $this->variants[0]->price) * 100, 2)
                : 0,
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'flash_sale' => $this->isInFlashDeal(),
            'category_name' => new ProductCategoryByIdPublicResource($this->category),
        ];
    }
}
