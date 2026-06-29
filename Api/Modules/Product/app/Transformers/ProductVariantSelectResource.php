<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantSelectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // stock get for product stock table
        $stock = $this->resource->relationLoaded('stock') ? $this->resource->stock : null;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_slug' => $this->variant_slug,
            'sku' => $this->sku,
            'pack_quantity' => $this->pack_quantity,
            'weight_major' => $this->weight_major,
            'weight_gross' => $this->weight_gross,
            'weight_net' => $this->weight_net,
            'attributes' => $this->attributes ? json_decode($this->attributes, true) : [], // Decode the JSON column
            'size' => $this->size,
            'price' => $this->price,
            'special_price' => $this->special_price,
            'stock_quantity' => $stock?->qty,
            'unit_id' => $this->unit_id,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'order_count' => $this->order_count,
            'status' => $this->status,
        ];
    }
}
