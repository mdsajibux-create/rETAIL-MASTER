<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            "id" => $this->id,
            'product_name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'branch_name' => $this->branch?->name,
            'type' => $this->type,
            'order_count' => $this->order_count,
            'slug' => $this->slug,
            "variants" => ProductVariantDetailsForStock::collection($this->variants)
        ];
    }
}
