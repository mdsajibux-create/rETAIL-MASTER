<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {


        return [
            'id'                 => $this->id,
            'branch_id'          => $this->branch_id,
            'product'            => $this->whenLoaded('product', fn() => [
                'id'    => $this->product->id,
                'name'  => $this->product->name,
                'sku'   => $this->product->sku,
                'image' => $this->product->image ?? null,
            ]),
            'variants' => ProductVariantPublicResource::collection($this->product->variants),
        ];
    }
}
