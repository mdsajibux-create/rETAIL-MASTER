<?php

namespace App\Http\Resources\Customer;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\app\Transformers\ProductVariantPublicWebResource;

class WishListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $branchId = isWebBranch();

        $variants = $this->product?->variants ?? collect();
        $firstVariant = $variants->first();

        $branchStock = $variants->sum(function ($variant) use ($branchId) {
            return $variant->stocks
                ? $variant->stocks->where('branch_id', $branchId)->sum('qty')
                : 0;
        });

        return [
            'id' => $this->product?->id,
            'name' => $this->product?->name,
            'slug' => $this->product?->slug,
            'description' => $this->product?->description,
            'image_url' => ImageModifier::generateImageUrl($this->product?->image),
            'stock' => $branchStock,
            'price' => optional($this->product?->variants->first())->price,
            'special_price' => optional($this->product?->variants->first())->special_price,

            'singleVariant' => $variants->count() === 1
                ? [new ProductVariantPublicWebResource($firstVariant)]
                : [],

            'discount_percentage' => $firstVariant && $firstVariant->price > 0
                ? round(((optional($firstVariant)->price - optional($firstVariant)->special_price) / optional($firstVariant)->price) * 100, 2)
                : null,

            'wishlist' => auth('api_customer')->check() ? $this->product?->wishlist : false,
            'rating' => number_format((float)$this->product?->rating, 2, '.', ''),
            'review_count' => $this->product?->review_count,
        ];
    }
}
