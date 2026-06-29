<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockTransferItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'status'          => $this->status,

            // ── Product ──────────────────────────────────────
            'product'         => $this->whenLoaded('product', fn() => [
                'id'   => $this->product->id,
                'name' => $this->product->name  ?? null,
                'sku'  => $this->product->sku   ?? null,
            ]),

            // ── Variant ──────────────────────────────────────
            'variant'         => $this->whenLoaded('variant', fn() => [
                'id'  => $this->variant->id,
                'sku' => $this->variant->sku ?? null,
            ]),

            // ── Quantities ───────────────────────────────────
            'qty_requested'   => $this->qty_requested,
            'qty_dispatched'  => $this->qty_dispatched,
            'qty_received'    => $this->qty_received,
            'qty_variance'    => ($this->qty_received ?? 0) - ($this->qty_dispatched ?? 0), // computed

            // ── Costing ──────────────────────────────────────
            'unit_cost'       => $this->unit_cost,
            'total_cost'      => $this->total_cost,

            'note'            => $this->note,
            'created_at'      => $this->created_at?->toDateTimeString(),
        ];
    }
}
