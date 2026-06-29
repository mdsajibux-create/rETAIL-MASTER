<?php

namespace Modules\Product\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Modules\Product\app\Models\ProductStock;

class ProductInventoryController extends Controller
{
    public function inventory(Request $request){
        $user     = auth('api')->user();

        $branchId = $user->activity_scope === 'branch_level'
            ? (int) $user->branch_id
            : ($request->filled('branch_id') ? (int) $request->branch_id : null);

        // ── Base Query
        $query = ProductStock::query()
            ->with([
                'product:id,name',
                'variant:id,sku',
                'branch:id,name',
            ])
            ->when($branchId !== null, fn($q) => $q->where('branch_id', $branchId));

        // ── Summary
        $base    = clone $query;
        $summary = [
            'total_products' => (clone $base)->count(),
            'in_stock'       => (clone $base)->whereColumn('qty', '>', 'reorder_point')->count(),
            'low_stock'      => (clone $base)->whereColumn('qty', '<=', 'reorder_point')->where('qty', '>', 0)->count(),
            'out_of_stock'   => (clone $base)->where('qty', '<=', 0)->count(),
            'total_qty'      => (clone $base)->sum('qty'),
            'total_damaged'  => (clone $base)->sum('qty_damaged'),
        ];

        // ── Paginate ──────────────────────────────────────────
        $perPage = max(1, (int) ($request->per_page ?? 15));
        $stocks  = $query->latest()->paginate($perPage);


        // ── Map ───────────────────────────────────────────────
        $items = $stocks->map(fn($stock) => [
            'id'                => $stock->id,
            'branch'            => $stock->branch?->name,
            'product_id'        => $stock->product_id,
            'product_name'      => $stock->product?->name,
            'img_url'           =>  com_option_get_id_wise_url($stock->product?->image),
            'variant_id'        => $stock->variant_id,
            'variant_name'      => $stock->variant?->name,
            'variant_img_url'   =>  com_option_get_id_wise_url($stock->variant?->image),
            'variant_sku'       => $stock->variant?->sku,
            'qty'               => $stock->qty,
            'qty_reserved'      => $stock->qty_reserved,
            'qty_incoming'      => $stock->qty_incoming,
            'qty_damaged'       => $stock->qty_damaged,
            'qty_available'     => max(0, $stock->qty - $stock->qty_reserved),
            'reorder_point'     => $stock->reorder_point,
            'reorder_qty'       => $stock->reorder_qty,
            'cost_price'        => $stock->cost_price,
            'selling_price'     => $stock->selling_price,
            'sale_price'        => $stock->sale_price,
            'stock_status'      => $this->stockStatus($stock),
            'is_active'         => $stock->is_active,
            'is_featured'       => $stock->is_featured,
            'last_restocked_at' => $stock->last_restocked_at,
        ]);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data'    => $items,
            'meta'    => [
                'current_page' => $stocks->currentPage(),
                'per_page'     => $stocks->perPage(),
                'total'        => $stocks->total(),
                'last_page'    => $stocks->lastPage(),
                'from'         => $stocks->firstItem(),
                'to'           => $stocks->lastItem(),
            ],
        ]);
    }

    // ── Stock status helper ───────────────────────────────────
    private function stockStatus(ProductStock $stock): string
    {
        if ($stock->qty <= 0) {
            return 'out_of_stock';
        }

        if ($stock->qty <= $stock->reorder_point) {
            return 'low_stock';
        }

        return 'in_stock';
    }

}
