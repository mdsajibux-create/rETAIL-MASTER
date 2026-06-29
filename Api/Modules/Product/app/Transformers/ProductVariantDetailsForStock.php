<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantDetailsForStock extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $branchId = $request->input('branch_id');
        // Branch filtered → only that branch stocks | No filter → ALL branches (sum)
        $stocks = $branchId
            ? $this->stocks->where('branch_id', $branchId)
            : $this->stocks;

        // SUM across all relevant branch rows
        $totalQty          = $stocks->sum('qty');
        $totalQtyReserved  = $stocks->sum('qty_reserved');
        $totalQtyIncoming  = $stocks->sum('qty_incoming');
        $totalQtyDamaged   = $stocks->sum('qty_damaged');

        // Use first row for non-summable fields
        $first = $stocks->first();

        return [
            'product_id'    => $this->product_id,
            'id'            => $this->id,
            'sku'           => $this->sku,
            'variant_slug'  => $this->variant_slug,
            'attributes'    => $this->attributes ? json_decode($this->attributes, true) : [],

            // Summed across branches
            'stock_quantity'   => $totalQty,                              // 150+500=650
            'qty_reserved'     => $totalQtyReserved,
            'qty_incoming'     => $totalQtyIncoming,
            'qty_damaged'      => $totalQtyDamaged,

            // Low stock: total qty vs reorder_point
            'reorder_point'    => $first?->reorder_point ?? 0,
            'reorder_qty'      => $first?->reorder_qty ?? 0,
            'stock_status'     => $this->resolveStockStatus($totalQty, $first?->reorder_point ?? 0),

            'price'            => $this->price,
        ];

    }


    //  Fixed stock status using TOTAL qty across branches
    private function resolveStockStatus(int $totalQty, int $reorderPoint): string
    {
        if ($totalQty <= 5) {
            return 'out_of_stock';
        }

        if ($totalQty <= $reorderPoint) {
            return 'low_stock';             // e.g. qty=3, reorder_point=5 → low_stock
        }

        return 'in_stock';
    }

}
