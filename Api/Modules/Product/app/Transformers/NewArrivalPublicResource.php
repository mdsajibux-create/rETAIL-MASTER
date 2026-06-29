<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\ProductCategoryByIdPublicResource;
use App\Http\Resources\StoreDetailsForOrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewArrivalPublicResource extends JsonResource
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

        return [
            'id' => $this->id,
            'store' => new StoreDetailsForOrderResource($this->whenLoaded('store')),
            'store_id' => $this->store->id ?? null,
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
            'max_cart_qty' => $this->max_cart_qty,
            'singleVariant' => $this->variants->count() === 1 ? [$this->variants->first()] : [],
            'discount_percentage' => $this->variants->isNotEmpty() && optional($this->variants->first())->price > 0 && optional($this->variants->first())->special_price > 0
                ? round(((optional($this->variants->first())->price - optional($this->variants->first())->special_price) / optional($this->variants->first())->price) * 100, 2)
                : 0,
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'flash_sale' => $this->isInFlashDeal(),
            'category_name' => new ProductCategoryByIdPublicResource($this->category)

        ];
    }
}
