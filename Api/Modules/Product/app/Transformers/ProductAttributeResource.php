<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeResource extends JsonResource
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
            'value' => $this->id,
            'label' => $translation?->where('key', 'name')->first()?->value ?? $this->name,
            'product_type' => $this->product_type,
            'attribute_values' => ProductAttributeValueResource::collection($this->attribute_values)
        ];
    }
}
