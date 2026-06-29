<?php

namespace Modules\Product\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Models\ProductStockLog;
use Modules\Product\app\Transformers\GroupedProductStockResource;
use Modules\Product\app\Transformers\ProductStockResource;

class BranchProductStockManageController extends Controller
{

    public function listStockiii(Request $request)
    {
        // branch id
        if (auth('api')->user()->activity_scope === 'branch_level'){
            $branchId = auth('api')->user()->branch_id;
        }else{
            $branchId = $request->branch_id;
        }

        if (!empty($request->view)){
            $stocks = ProductStock::with(['product', 'variant'])
                ->whereHas('product')
                ->when($request->search, fn($q) => $q->whereHas('product', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                ))
                ->when($request->is_active !== null, fn($q) =>
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN))
                )
                ->when($request->low_stock, fn($q) =>
                $q->whereRaw('qty <= reorder_point')
                )
                ->latest()
                ->paginate($request->per_page ?? 15);

            $stocks_paginate_data = $stocks;
        }if (!empty($branchId)){
          $stocks = ProductStock::with(['product', 'variant'])
            ->whereHas('product')
            ->where('branch_id', $branchId)
            ->when($request->search, fn($q) => $q->whereHas('product', fn($q) =>
            $q->where('name', 'like', "%{$request->search}%")
            ))
            ->when($request->is_active !== null, fn($q) =>
            $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN))
            )
            ->when($request->low_stock, fn($q) =>
            $q->whereRaw('qty <= reorder_point')
            )
            ->latest()
            ->paginate($request->per_page ?? 15);

        $stocks_paginate_data = $stocks;
        }else{
            $stocks = ProductStock::with(['product', 'variant'])
                ->whereHas('product')
                ->when($request->search, fn($q) => $q->whereHas('product', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                ))
                ->latest()
                ->paginate($request->per_page ?? 50);

             $stocks_paginate_data = $stocks;
        }

        // Group rows by product_id → each product gets all its variant stocks
        $grouped = $stocks->getCollection()
            ->groupBy('product_id')
            ->map(fn($rows) => (new GroupedProductStockResource($rows))->toArray())
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $grouped,                    //  plain array, no Resource wrapping
            'meta'    => new PaginationResource($stocks_paginate_data), // uses original paginator for correct counts
        ]);
    }


    public function listStock(Request $request)
    {
        $branchId = auth('api')->user()->activity_scope === 'branch_level'
            ? auth('api')->user()->branch_id
            : $request->branch_id;

        $perPage = $request->per_page ?? 15;
        $page    = $request->page ?? 1;

        // ── Step 1: Distinct product_ids with pagination ──────────
        $productQuery = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->whereNull('products.deleted_at')
            ->when($request->search, fn($q) =>
            $q->where('products.name', 'like', "%{$request->search}%")
            )
            ->when($request->is_active !== null, fn($q) =>
            $q->where('product_stocks.is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN))
            )
            ->when($request->low_stock, fn($q) =>
            $q->whereRaw('product_stocks.qty <= product_stocks.reorder_point')
            )
            ->when(!empty($branchId), fn($q) =>
            $q->where('product_stocks.branch_id', $branchId)
            )
            ->select('product_stocks.product_id')
            ->groupBy('product_stocks.product_id')
            ->orderByDesc('product_stocks.product_id');

        $total      = $productQuery->clone()->get()->count();
        $productIds = $productQuery->clone()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->pluck('product_id');

        // ── Step 2: ALL branch rows for those products (no branch filter) ──
        $allStocks = ProductStock::with(['product', 'variant'])
            ->whereIn('product_id', $productIds)
            ->get();

        // ── Step 3: Group and transform ───────────────────────────
        $grouped = $allStocks
            ->groupBy('product_id')
            ->map(fn($rows) => (new GroupedProductStockResource($rows, $branchId))->toArray())
            ->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'data'    => $grouped,
            'meta'    => new PaginationResource($paginator),
        ]);
    }


    public function getStockById($stock_id)
    {
        // branch id
        if (auth('api')->user()->activity_scope === 'branch_level'){
            $branch_id = auth('api')->user()->branch_id;
        }

        $stock = ProductStock::with(['product.variants.stock'])
            ->where('id', (int) $stock_id)
            ->where('branch_id', (int) $branch_id)
            ->whereHas('product')
            ->first();

     if (empty($stock)){
         return response()->json([
             'success' => false,
             'message' => 'Stock not found',
         ]);
     }

        return response()->json([
            'success' => true,
            'data' => new ProductStockResource($stock),
        ]);

    }

    public function getStockByIdAdmin(Request $request, $stock_id)
    {
        $stock = ProductStock::with(['product.variants.stock'])
            ->where('id', (int) $stock_id)
            ->whereHas('product')        // exclude soft-deleted products
            ->first();

        if (empty($stock)){
            return response()->json([
                'success' => false,
                'message' => 'Stock not found',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductStockResource($stock),
        ]);

    }

    public function addStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'                    => 'required|integer|exists:products,id',
            'variants'                      => 'required|array|min:1',
            'variants.*.variant_id'         => 'nullable|integer|exists:product_variants,id',
            'variants.*.qty'        => 'required|integer|min:0',
            'variants.*.qty_reserved'       => 'nullable|integer|min:0',
            'variants.*.qty_incoming'       => 'nullable|integer|min:0',
            'variants.*.qty_damaged'        => 'nullable|integer|min:0',
            'variants.*.reorder_point'      => 'nullable|integer|min:0',
            'variants.*.reorder_qty'        => 'nullable|integer|min:0',
            'variants.*.cost_price'         => 'nullable|numeric|min:0',
            'variants.*.selling_price'      => 'nullable|numeric|min:0',
            'variants.*.sale_price'         => 'nullable|numeric|min:0',
            'variants.*.is_active'          => 'nullable|boolean',
            'variants.*.is_featured'        => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // ── Resolve branch_id ─────────────────────────────────
        $branchId = auth('api')->user()->activity_scope === 'branch_level'
            ? (int) auth('api')->user()->branch_id
            : (int) $request->branch_id;

        $productId = (int) $request->product_id;

        // ── Pre-flight: check all variants for duplicates ─────
        $conflictErrors = [];
        foreach ($request->variants as $index => $variant) {
            $variantId = isset($variant['variant_id']) ? (int) $variant['variant_id'] : null;

            $query = ProductStock::where('branch_id', $branchId)
                ->where('product_id', $productId);

            if ($variantId) {
                $query->where('variant_id', $variantId);
            } else {
                $query->whereNull('variant_id');
            }

            if ($query->exists()) {
                $conflictErrors["variants.{$index}.variant_id"] = $variantId
                    ? "Stock already exists for variant ID {$variantId}. Use update instead."
                    : "Stock already exists for this product (no variant). Use update instead.";
            }
        }

        if (!empty($conflictErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Some variants already have stock entries.',
                'errors'  => $conflictErrors,
            ], 409);
        }

        // ── Insert all variants ───────────────────────────────
        DB::beginTransaction();
        try {
            $created = [];

            foreach ($request->variants as $variant) {
                $variantId = isset($variant['variant_id']) ? (int) $variant['variant_id'] : null;

                $stock = ProductStock::create([
                    'branch_id'         => $branchId,
                    'product_id'        => $productId,
                    'variant_id'        => $variantId,
                    'qty'               => (int) $variant['qty'],
                    'qty_reserved'      => (int) ($variant['qty_reserved']  ?? 0),
                    'qty_incoming'      => (int) ($variant['qty_incoming']  ?? 0),
                    'qty_damaged'       => (int) ($variant['qty_damaged']   ?? 0),
                    'reorder_point'     => (int) ($variant['reorder_point'] ?? 5),
                    'reorder_qty'       => (int) ($variant['reorder_qty']   ?? 0),
                    'cost_price'        => $variant['cost_price']    ?? null,
                    'selling_price'     => $variant['selling_price'] ?? null,
                    'sale_price'        => $variant['sale_price']    ?? null,
                    'is_active'         => $variant['is_active']     ?? false,
                    'is_featured'       => $variant['is_featured']   ?? false,
                    'last_restocked_at' => now(),
                ]);

                // ── Log — works for both null and non-null variant ────
                ProductStockLog::create([
                    'branch_id'   => $branchId,
                    'product_id'  => $productId,
                    'variant_id'  => $variantId,   // null safe
                    'stock_id'    => $stock->id,
                    'type'        => 'stock_in',
                    'qty_before'  => 0,
                    'qty_changed' => (int) $variant['qty'] ?? 0,
                    'qty_after'   => (int) $variant['qty'] ?? 0,
                    'qty_damaged'   => (int) $variant['qty_damaged'] ?? 0,
                    'cost_price'  => $variant['cost_price'] ?? null,
                    'reason'      => $request->reason ?? null,
                    'notes'       => $request->notes  ?? null,
                    'created_by'  => auth('api')->id(),
                ]);

                $created[] = new ProductStockResource($stock->load('variant'));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' stock entries added successfully.',
                'data'    => $created,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'                => 'required|integer|exists:products,id',
            'variants'                  => 'required|array|min:1',
            'variants.*.variant_id'     => 'nullable|integer|exists:product_variants,id',
            'variants.*.qty'            => 'required|integer|min:0',
            'variants.*.qty_reserved'   => 'nullable|integer|min:0',
            'variants.*.qty_incoming'   => 'nullable|integer|min:0',
            'variants.*.qty_damaged'    => 'nullable|integer|min:0',
            'variants.*.reorder_point'  => 'nullable|integer|min:0',
            'variants.*.reorder_qty'    => 'nullable|integer|min:0',
            'variants.*.cost_price'     => 'nullable|numeric|min:0',
            'variants.*.selling_price'  => 'nullable|numeric|min:0',
            'variants.*.sale_price'     => 'nullable|numeric|min:0',
            'variants.*.is_active'      => 'nullable|boolean',
            'variants.*.is_featured'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $branchId  = auth('api')->user()->activity_scope === 'branch_level'
            ? (int) auth('api')->user()->branch_id
            : (int) $request->branch_id;

        $productId = (int) $request->product_id;

        try {
            $result = [];

            foreach ($request->variants as $variant) {
                $variantId = isset($variant['variant_id']) ? (int) $variant['variant_id'] : null;

                $searchCriteria = [
                    'branch_id'  => $branchId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                ];

                // ── FIX 1: Capture qty BEFORE any update ─────────
                $stock     = ProductStock::where($searchCriteria)->first();
                $qtyBefore = $stock ? (int) $stock->qty : 0;
                $wasNew    = is_null($stock);

                // Use DB::update for raw increments ──────
                //     cannot handle DB::raw expressions.
                if ($stock) {
                    ProductStock::where($searchCriteria)->update([
                        'qty'               => DB::raw('qty + '           . (int) $variant['qty']),
                        'qty_reserved'      => DB::raw('qty_reserved + '  . (int) ($variant['qty_reserved'] ?? 0)),
                        'qty_incoming'      => DB::raw('qty_incoming + '  . (int) ($variant['qty_incoming'] ?? 0)),
                        'qty_damaged'       => DB::raw('qty_damaged + '   . (int) ($variant['qty_damaged']  ?? 0)),
                        'is_active'         => (bool) ($variant['is_active']   ?? false),
                        'is_featured'       => (bool) ($variant['is_featured'] ?? false),
                        'last_restocked_at' => now(),
                    ]);

                    $stock->refresh(); // safe to refresh AFTER update now

                } else {
                    $stock = ProductStock::create(array_merge($searchCriteria, [
                        'qty'               => (int) $variant['qty'],
                        'qty_reserved'      => (int) ($variant['qty_reserved'] ?? 0),
                        'qty_incoming'      => (int) ($variant['qty_incoming'] ?? 0),
                        'qty_damaged'       => (int) ($variant['qty_damaged']  ?? 0),
                        'reorder_point'     => (int) ($variant['reorder_point'] ?? 5),
                        'reorder_qty'       => (int) ($variant['reorder_qty']   ?? 0),
                        'cost_price'        => $variant['cost_price']    ?? null,
                        'selling_price'     => $variant['selling_price'] ?? null,
                        'sale_price'        => $variant['sale_price']    ?? null,
                        'is_active'         => (bool) ($variant['is_active']   ?? false),
                        'is_featured'       => (bool) ($variant['is_featured'] ?? false),
                        'last_restocked_at' => now(),
                    ]));
                }

                // ── Stock log (now uses correct before/after) ─────
                $qtyChanged = (int) $variant['qty'];

                ProductStockLog::create([
                    'branch_id'   => $branchId,
                    'product_id'  => $productId,
                    'variant_id'  => $variantId,
                    'stock_id'    => $stock->id,
                    'type'        => 'adjustment',
                    'qty_before'  => $qtyBefore,
                    'qty_changed' => $qtyChanged,
                    'qty_after'   => $qtyBefore + $qtyChanged,   //  accurate
                    'qty_damaged' => (int) $stock->qty_damaged,  //  already has the DB total
                    'cost_price'  => $variant['cost_price'] ?? null,
                    'reason'      => $request->reason ?? null,
                    'notes'       => $request->notes  ?? null,
                    'created_by'  => auth('api')->id(),
                ]);

                $result[] = [
                    'action' => $wasNew ? 'created' : 'updated',
                    'data'   => new ProductStockResource($stock->load('variant')),
                ];
            }

            $createdCount = collect($result)->where('action', 'created')->count();
            $updatedCount = collect($result)->where('action', 'updated')->count();

            return response()->json([
                'success' => true,
                'message' => "{$createdCount} created, {$updatedCount} updated successfully.",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('update failed'),
            ], 500);
        }
    }

    
}
