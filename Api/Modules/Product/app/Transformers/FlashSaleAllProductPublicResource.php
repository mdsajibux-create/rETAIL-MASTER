<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\StoreDetailsForOrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleAllProductPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Filter variants by price range in the resource
        $filteredVariants = collect($this->product?->variants)->filter(function ($variant) use ($request) {
            return (!$request->min_price || $variant->price >= $request->min_price) &&
                (!$request->max_price || $variant->price <= $request->max_price);
        });

        $firstVariant = $filteredVariants->first();

        $language = $request->input('language', 'en');
        $translation = $this->product?->related_translations->where('language', $language);

        return [
            "id" => $this->product?->id,
            "name" => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->product?->name,
            "slug" => $this->product?->slug,
            "description" => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->product?->description,
            'image' => $this->product?->image,
            'image_url' => ImageModifier::generateImageUrl($this->product?->image),
            'stock' => $this->product?->variants?->isNotEmpty()
                ? $this->product->variants->sum(fn ($variant) => $variant?->stocks?->sum('qty') ?? 0)
                : 0,
            'wishlist' => auth('api_customer')->check() ? $this->product?->wishlist : false,
            'rating' => number_format((float)$this->product?->rating, 2, '.', ''),
            'review_count' => $this->product?->review_count,
            'discount_type' => $this->flashSale?->discount_type,
            'discount_amount' => $this->flashSale?->discount_amount,
            'purchase_limit' => $this->flashSale?->purchase_limit,
            'flash_sale_id' => $this->flashSale?->id,
            'price' => optional($firstVariant)->price,
            'special_price' => optional($firstVariant)->special_price,
            'singleVariant' => $filteredVariants->count() === 1 ? [$firstVariant] : [],
            'discount_percentage' => $firstVariant && $firstVariant->price > 0 && $firstVariant->special_price > 0
                ? round((($firstVariant->price - $firstVariant->special_price) / $firstVariant->price) * 100, 2)
                : 0,
            'flash_sale' => $this->product?->isInFlashDeal(),
        ];
    }
}
