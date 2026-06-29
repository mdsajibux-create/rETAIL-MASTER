<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutOfStockProductResource extends JsonResource
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
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
            'slug' => $this->slug,
            'type' => $this->type,
            'image_url' =>ImageModifier::generateImageUrl($this->image),
            'variants' => $this->outOfStockVariants()->map(function ($variant) {
                return [
                    'product_id' => $variant->product_id,
                    'id' => $variant->id,
                    'variant_slug' => $variant->variant_slug,
                    'attributes' => $variant->attributes ? json_decode($variant->attributes, true) : [], // Decode the JSON column
                    'sku' => $variant->sku,
                    'stock_quantity' => $variant->stock_quantity,
                    'price' => $variant->price,
                ];
            }),
        ];
    }
}
