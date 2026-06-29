<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantPublicWebResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // stock get for product stock table
        $stock = $this->resource->relationLoaded('stocks') ? $this->resource->stocks?->first() : null;

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
            'stock_quantity' => $stock?->qty ?? 0,
            'unit_id' => $this->unit_id,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'order_count' => $this->order_count,
            'status' => $this->status,
            'stock' => $stock ? [
                'id'            => $stock->id,
                'branch_id'     => $stock->branch_id,
                'qty'           => $stock->qty,
                'qty_reserved'  => $stock->qty_reserved,
                'qty_incoming'  => $stock->qty_incoming,
                'qty_damaged'   => $stock->qty_damaged,
                'qty_available' => $stock->qty - $stock->qty_reserved,
                'reorder_point' => $stock->reorder_point,
                'reorder_qty'   => $stock->reorder_qty,
                'is_low_stock'  => $stock->qty <= $stock->reorder_point,
                'is_active'     => $stock->is_active,
                'is_featured'   => $stock->is_featured,
                'last_restocked_at' => $stock->last_restocked_at?->toDateTimeString(),
            ] : null,
        ];
    }
}
