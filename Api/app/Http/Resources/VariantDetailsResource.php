<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "variant_slug" => $this->variant_slug,
            "sku" => $this->sku,
            "pack_quantity" => $this->pack_quantity,
            "weight_major" => $this->weight_major,
            "weight_gross" => $this->weight_gross,
            "weight_net" => $this->weight_net,
            "attributes" => json_decode($this->attributes),
            "price" => $this->price,
            "special_price" => $this->special_price,
            "stock_quantity" => $this->stock_quantity,
            "unit_id" => $this->unit_id,
            "length" => $this->length,
            "width" => $this->width,
            "height" => $this->height,
            "image" => (int)$this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "order_count" => $this->order_count,
            "status" => $this->status,
            "deleted_at" => $this->deleted_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
