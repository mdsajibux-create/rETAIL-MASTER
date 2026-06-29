<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\ProductCategoryByIdPublicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = auth('api')->user();
        $branchId = $authUser?->branch_id
            ? (int) $authUser->branch_id
            : (int) $request->branch_id;


        $firstVariant = $this->variants->first();
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
            'slug' => $this->slug,
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description, // If language is empty or not provided attribute
            'unit' => $this->unit?->name,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'wishlist' => auth('api_customer')->check() ? $this->wishlist : false, // Check if the customer is logged in,
            'rating' => number_format((float)$this->rating, 2, '.', ''),
            'review_count' => $this->review_count,
            'max_cart_qty' => $this->max_cart_qty,
            // branch wise stock only
            'stock' => $this->stocks->where('branch_id', $branchId)->sum('qty'),
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

            // do not return raw variant model
            'singleVariant' => $this->variants->count() === 1
                ? [[
                    'id' => $firstVariant->id,
                    'product_id' => $firstVariant->product_id,
                    'variant_slug' => $firstVariant->variant_slug,
                    'sku' => $firstVariant->sku,
                    'pack_quantity' => $firstVariant->pack_quantity,
                    'weight_major' => $firstVariant->weight_major,
                    'weight_gross' => $firstVariant->weight_gross,
                    'weight_net' => $firstVariant->weight_net,
                    'attributes' => $firstVariant->attributes
                        ? json_decode($firstVariant->attributes, true)
                        : [],
                    'size' => $firstVariant->size,
                    'price' => $firstVariant->price,
                    'special_price' => $firstVariant->special_price,
                    'effective_price' => $firstVariant->effective_price,
                    'unit_id' => $firstVariant->unit_id,
                    'length' => $firstVariant->length,
                    'width' => $firstVariant->width,
                    'height' => $firstVariant->height,
                    'image' => $firstVariant->image,
                    'image_url' => ImageModifier::generateImageUrl($firstVariant->image),
                    // branch wise stock only
                    'stock' => $firstVariant->stocks
                        ->where('branch_id', $branchId)
                        ->map(function ($stock) {
                            return [
                                'id' => $stock->id,
                                'branch_id' => $stock->branch_id,
                                'product_id' => $stock->product_id,
                                'variant_id' => $stock->variant_id,
                                'qty' => $stock->qty,
                                'qty_reserved' => $stock->qty_reserved,
                                'qty_incoming' => $stock->qty_incoming,
                                'qty_damaged' => $stock->qty_damaged,
                                'reorder_point' => $stock->reorder_point,
                                'reorder_qty' => $stock->reorder_qty,
                            ];
                        })
                        ->values(),
                ]]
                : [],

            'discount_percentage' => $firstVariant && $firstVariant->price > 0 && $firstVariant->special_price > 0
                ? round((($firstVariant->price - $firstVariant->special_price) / $firstVariant->price) * 100, 2)
                : 0,
            'flash_sale' => $this->isInFlashDeal(),
            'is_featured' => (bool)$this->is_featured,
            'category_name' => new ProductCategoryByIdPublicResource($this->category)
        ];
    }
}
