<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Support\Collection;

class GroupedProductStockResource
{
    protected Collection $stockRows;
    protected ?int $branchId;

    public function __construct(Collection $stockRows, ?int $branchId = null)
    {
        $this->stockRows = $stockRows;
        $this->branchId  = $branchId;
    }

    public function toArray(): array
    {
        $first   = $this->stockRows->first();
        $product = $first->product;

        // Branch filtered → only that branch | No filter → ALL branches
        $relevantRows = $this->branchId
            ? $this->stockRows->where('branch_id', $this->branchId)
            : $this->stockRows;

        return [
            'id'                => $first->id,
            'product_id'        => $product->id,
            'name'              => $product->name,
            'sku'               => $product->sku,
            'image'             => $product->image ?? null,
            'image_url'         => com_option_get_id_wise_url($product->image),
            'status'         => $product->status,
            'last_restocked_at' => $first->last_restocked_at,

            //  groupBy variant_id → merges duplicate variants → sums qty
            'variants' => $relevantRows
                ->groupBy('variant_id')
                ->map(function ($variantStocks) use ($product) {
                    $first = $variantStocks->first();

                    return [
                        'stock_id'      => $first->id,
                        'product_id'    => $product->id,
                        'variant_id'    => $first->variant_id,
                        'sku'           => $first->variant?->sku,
                        'variant_slug'  => $first->variant?->variant_slug,
                        'pack_quantity' => $first->variant?->pack_quantity,
                        'price'         => $first->variant?->price,
                        'special_price' => $first->variant?->special_price,
                        'attributes'    => json_decode($first->variant?->attributes, true),
                        'status'        => $first->variant?->status,
                        'weight_major'  => $first->variant?->weight_major,
                        'weight_gross'  => $first->variant?->weight_gross,
                        'weight_net'    => $first->variant?->weight_net,
                        'length'        => $first->variant?->length,
                        'width'         => $first->variant?->width,
                        'height'        => $first->variant?->height,
                        'image'        => $first->variant?->image,
                        'image_url'        => com_option_get_id_wise_url($first->variant?->image),

                        // SUM across all branches for same variant
                        'qty'           => $variantStocks->sum('qty'),
                        'qty_reserved'  => $variantStocks->sum('qty_reserved'),
                        'qty_incoming'  => $variantStocks->sum('qty_incoming'),
                        'qty_damaged'   => $variantStocks->sum('qty_damaged'),
                        'qty_available' => $variantStocks->sum('qty') - $variantStocks->sum('qty_reserved'),

                        'reorder_point' => $first->reorder_point,
                        'reorder_qty'   => $first->reorder_qty,
                        'is_low_stock'  => $variantStocks->sum('qty') <= $first->reorder_point,
                        'cost_price'    => $first->cost_price,
                        'selling_price' => $first->selling_price,
                        'sale_price'    => $first->sale_price,
                        'is_active'     => $first->is_active,
                        'is_featured'   => $first->is_featured,
                        'last_counted_at'   => $first->last_counted_at?->toDateTimeString(),
                        'last_restocked_at' => $first->last_restocked_at?->toDateTimeString(),
                        'created_at'        => $first->created_at?->toDateTimeString(),
                        'updated_at'        => $first->updated_at?->toDateTimeString(),
                    ];
                })
                ->values()->toArray(),
        ];
    }
}