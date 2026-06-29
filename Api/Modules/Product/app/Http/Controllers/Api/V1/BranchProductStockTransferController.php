<?php

namespace Modules\Product\app\Http\Controllers\Api\V1;

use App\Enums\ProductStockTransferType;
use App\Enums\StatusType;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Models\ProductStockTransfer;
use Modules\Product\app\Transformers\ProductStockTransferResource;

class BranchProductStockTransferController extends Controller
{

    // ── Valid status transition flow ──────────────────────────
    private array $allowedTransitions = [
        'pending'            => ['approved', 'rejected', 'cancelled'],
        'approved'           => ['in_transit', 'rejected', 'cancelled'],
        'in_transit'         => ['completed', 'partially_received', 'rejected'],
        'partially_received' => ['completed', 'cancelled'],
        'completed'          => [],
        'rejected'           => [],
        'cancelled'          => [],
    ];

    // ── Types that are direct (no from_branch, auto-complete) ─
    private array $directInTypes = [
        ProductStockTransferType::OPENING->value,   // 'opening'
        ProductStockTransferType::STOCK_IN->value,  // 'stock_in'
    ];

    // ── Resolve branch_id from auth or request ────────────────
    private function resolveBranchId(Request $request): int
    {
        $user = auth('api')->user();
        if ($user->activity_scope === 'branch_level') {
            return (int) $user->branch_id;
        }
        return (int) $request->branch_id;
    }

    // ─────────────────────────────────────────────────────────
    // GET transfer/list
    // ─────────────────────────────────────────────────────────
    public function listTransfers(Request $request)
    {
        $branchId = (int) $request->branch_id;

        $transfers = ProductStockTransfer::with(['fromBranch', 'toBranch', 'requestedBy'])
            ->withCount('items')

            ->when($branchId, function ($q) use ($branchId) {
                $q->where(function ($sub) use ($branchId) {
                    $sub->where('from_branch_id', $branchId)
                        ->orWhere('to_branch_id', $branchId);
                });
            })

            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) =>
            $q->where('reference_number', 'like', "%{$request->search}%")
            )

            ->latest()
            ->paginate($request->per_page ?? 15);


        return response()->json([
            'success' => true,
            'data'    => ProductStockTransferResource::collection($transfers),
            'meta'    => new PaginationResource($transfers),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET transfer/details
    // ─────────────────────────────────────────────────────────
    public function getTransferById(Request $request, $transfer_id)
    {
        $branchId = $this->resolveBranchId($request);

        $transfer = ProductStockTransfer::with([
            'fromBranch', 'toBranch',
            'requestedBy', 'approvedBy', 'dispatchedBy', 'receivedBy',
            'items.product', 'items.variant',
        ])
            ->where(fn($q) =>
            $q->where('from_branch_id', $branchId)
                ->orWhere('to_branch_id', $branchId)
            )
            ->findOrFail($transfer_id);

        return response()->json([
            'success' => true,
            'data'    => new ProductStockTransferResource($transfer),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST transfer/create
    // ─────────────────────────────────────────────────────────
    public function createTransfer(Request $request)
    {
        $branchId   = $this->resolveBranchId($request);
        $validTypes = array_map(fn($e) => $e->value, ProductStockTransferType::cases());
        $isDirect   = in_array($request->type, $this->directInTypes);

        $validator = Validator::make($request->all(), [
            'type'                    => 'required|string|in:' . implode(',', $validTypes),
            'to_branch_id'            => $isDirect ? 'nullable|integer' : 'required|integer|different:' . $branchId,
            'expected_at'             => 'nullable|date',
            'notes'                   => 'nullable|string',
            'reason'                  => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|integer|exists:products,id',
            'items.*.variant_id'      => 'nullable|integer|exists:product_variants,id',
            'items.*.qty_requested'   => 'required|integer|min:1',
            'items.*.unit_cost'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // ── Extra validation: check stock conflict before DB hit ──
        // product_id is unique in product_stocks
        // Rule: if product has NO variant → only 1 stock row allowed per branch
        //       if product HAS variant    → 1 stock row per variant per branch
        if ($isDirect) {
            $conflictErrors = [];

            foreach ($request->items as $index => $item) {
                $productId = (int) $item['product_id'];
                $variantId = isset($item['variant_id']) ? (int) $item['variant_id'] : null;

                if ($variantId) {
                    // variant exists: check branch + product + variant combo
                    $exists = ProductStock::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->where('variant_id', $variantId)
                        ->exists();

                    if ($exists) {
                        $conflictErrors["items.{$index}.variant_id"] =
                            "Stock already exists for product ID {$productId} with variant ID {$variantId} in this branch. Use stock update instead.";
                    }
                } else {
                    // no variant: check branch + product combo
                    $exists = ProductStock::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->whereNull('variant_id')
                        ->exists();

                    if ($exists) {
                        $conflictErrors["items.{$index}.product_id"] =
                            "Stock already exists for product ID {$productId} in this branch. Use stock update instead.";
                    }
                }
            }

            if (!empty($conflictErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock conflict detected.',
                    'errors'  => $conflictErrors,
                ], 409);
            }
        }

        DB::beginTransaction();
        try {

            // ── Direct: opening — no approval flow, auto-complete ─
            if ($isDirect) {

                $transfer = ProductStockTransfer::create([
                    'type'           => $request->type,
                    'from_branch_id' => null,
                    'to_branch_id'   => $branchId,
                    'requested_by'   => auth('api')->id(),
                    'received_by'    => auth('api')->id(),
                    'notes'          => $request->notes,
                    'reason'         => $request->reason,
                    'status'         => StatusType::COMPLETED->value,
                    'received_at'    => now(),
                ]);

                foreach ($request->items as $item) {
                    $transfer->items()->create([
                        'product_id'     => (int) $item['product_id'],
                        'variant_id'     => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                        'qty_requested'  => (int) $item['qty_requested'],
                        'qty_dispatched' => (int) $item['qty_requested'],
                        'qty_received'   => (int) $item['qty_requested'],
                        'unit_cost'      => isset($item['unit_cost']) ? (float) $item['unit_cost'] : null,
                        'total_cost'     => ($item['unit_cost'] ?? 0) * $item['qty_requested'],
                        'status'         => StatusType::COMPLETED->value,
                    ]);

                    $this->adjustStock(
                        branchId:  $branchId,
                        productId: (int) $item['product_id'],
                        variantId: isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                        qty:       (int) $item['qty_requested'],
                        direction: 'in',
                        costPrice: isset($item['unit_cost']) ? (float) $item['unit_cost'] : null,
                    );
                }

                // ── Branch-to-branch — requires approval flow ─────────
            } else {

                $transfer = ProductStockTransfer::create([
                    'type'           => $request->type,
                    'from_branch_id' => $branchId,
                    'to_branch_id'   => (int) $request->to_branch_id,
                    'requested_by'   => auth('api')->id(),
                    'notes'          => $request->notes,
                    'reason'         => $request->reason,
                    'expected_at'    => $request->expected_at,
                    'status'         => StatusType::PENDING->value,
                ]);

                foreach ($request->items as $item) {
                    $transfer->items()->create([
                        'product_id'    => (int) $item['product_id'],
                        'variant_id'    => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                        'qty_requested' => (int) $item['qty_requested'],
                        'unit_cost'     => isset($item['unit_cost']) ? (float) $item['unit_cost'] : null,
                        'total_cost'    => ($item['unit_cost'] ?? 0) * $item['qty_requested'],
                        'status'        => StatusType::PENDING->value,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Transfer created successfully'),
                'data'    => new ProductStockTransferResource(
                    $transfer->load('items.product', 'items.variant')
                ),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    // POST transfer/update-status
    // ─────────────────────────────────────────────────────────
    public function updateStatus(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $validator = Validator::make($request->all(), [
            'transfer_id'            => 'required|integer|exists:product_stock_transfers,id',
            'status'                 => 'required|string|in:approved,in_transit,completed,partially_received,rejected,cancelled',
            'rejection_reason'       => 'required_if:status,rejected|required_if:status,cancelled|nullable|string',
            'items'                  => 'nullable|array',
            'items.*.id'             => 'required|integer|exists:product_stock_transfer_items,id',
            'items.*.qty_dispatched' => 'nullable|integer|min:0',
            'items.*.qty_received'   => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $transfer = ProductStockTransfer::with('items')
            ->where(fn($q) =>
            $q->where('from_branch_id', $branchId)
                ->orWhere('to_branch_id', $branchId)
            )
            ->findOrFail($request->transfer_id);

        // ── Loop through each requested item ─────────────────
        $errors = [];

        foreach ($request->items as $requestItem) {
            $item = $transfer->items->firstWhere('id', $requestItem['id']);
            // Item not found
            if (!$item) {
                $errors[] = "Item ID {$requestItem['id']} not found in this transfer.";
                continue;
            }

            // Validate transition
            $allowed = $this->allowedTransitions[$item->status] ?? [];
            if (!in_array($request->status, $allowed)) {
                $errors[] = "Item ID {$item->id}: Cannot transition from '{$item->status}' to '{$request->status}'";
                continue;
            }

            // Update status
            $item->update(['status' => $request->status]);
        }

        // ── Return errors if any ──────────────────────────────
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors'  => $errors,
            ], 422);
        }

        DB::beginTransaction();

        try {

            $statusData = match($request->status) {
                'approved'           => ['approved_by'      => auth('api')->id(), 'approved_at'   => now()],
                'in_transit'         => ['dispatched_by'    => auth('api')->id(), 'dispatched_at' => now()],
                'completed'          => ['received_by'      => auth('api')->id(), 'received_at'   => now()],
                'partially_received' => ['received_by'      => auth('api')->id(), 'received_at'   => now()],
                'rejected',
                'cancelled'          => ['rejection_reason' => $request->rejection_reason],
                default              => [],
            };

            $transfer->update(array_merge(['status' => $request->status], $statusData));

            // ── Update item quantities ────────────────────────
            if ($request->items) {
                foreach ($request->items as $itemData) {
                    $transfer->items()
                        ->where('id', (int) $itemData['id'])
                        ->update(array_filter([
                            'qty_dispatched' => isset($itemData['qty_dispatched']) ? (int) $itemData['qty_dispatched'] : null,
                            'qty_received'   => isset($itemData['qty_received'])   ? (int) $itemData['qty_received']   : null,
                            'status'         => $request->status,
                        ], fn($v) => !is_null($v)));
                }
            }

            // ── Adjust stock on completed / partially_received ─
            if (in_array($request->status, ['completed', 'partially_received'])) {

                $transfer->load('items');

                foreach ($transfer->items as $item) {
                    $qtyReceived = (int) ($item->qty_received ?? $item->qty_requested);

                    // stock IN → destination branch
                    $this->adjustStock(
                        branchId:  (int) $transfer->to_branch_id,
                        productId: (int) $item->product_id,
                        variantId: $item->variant_id ? (int) $item->variant_id : null,
                        qty:       $qtyReceived,
                        direction: 'in',
                        costPrice: $item->unit_cost ? (float) $item->unit_cost : null,
                    );

                    // stock OUT → source branch
                    if ($transfer->from_branch_id) {
                        $this->adjustStock(
                            branchId:  (int) $transfer->from_branch_id,
                            productId: (int) $item->product_id,
                            variantId: $item->variant_id ? (int) $item->variant_id : null,
                            qty:       (int) ($item->qty_dispatched ?? $qtyReceived),
                            direction: 'out',
                        );
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Transfer status updated successfully'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    // DELETE transfer/remove/{id}
    // ─────────────────────────────────────────────────────────
    public function deleteTransfer(Request $request, int $id)
    {
        $branchId = $this->resolveBranchId($request);

        $transfer = ProductStockTransfer::where('from_branch_id', $branchId)
            ->where('status', StatusType::PENDING->value)
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $transfer->items()->delete();
            $transfer->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Transfer removed successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    // GET transfer/deleted/records
    // ─────────────────────────────────────────────────────────
    public function deletedTransfers(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $transfers = ProductStockTransfer::onlyTrashed()
            ->with(['fromBranch', 'toBranch'])
            ->where(fn($q) =>
            $q->where('from_branch_id', $branchId)
                ->orWhere('to_branch_id', $branchId)
            )
            ->latest('deleted_at')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => ProductStockTransferResource::collection($transfers),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE — stock adjustment helper
    // ─────────────────────────────────────────────────────────
    private function adjustStock(
        ?int   $branchId,
        int    $productId,
        ?int   $variantId,
        int    $qty,
        string $direction,
        ?float $costPrice = null,
    ): void {
        if (!$branchId) {
            return;
        }

        // ── Find existing stock row ───────────────────────────
        // product with variant  → match branch + product + variant
        // product without variant → match branch + product + null variant
        $query = ProductStock::where('branch_id', $branchId)
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $stock = $query->first();

        // ── Create if not exists ──────────────────────────────
        if (!$stock) {
            $stock = ProductStock::create([
                'branch_id'   => $branchId,
                'product_id'  => $productId,
                'variant_id'  => $variantId,
                'qty' => 0,
                'is_active'   => true,
            ]);
        }

        // ── Adjust qty ────────────────────────────────────────
        if ($direction === 'in') {
            $stock->increment('qty', $qty);
            $stock->update([
                'last_restocked_at' => now(),
                'cost_price'        => $costPrice ?? $stock->cost_price,
                'is_active'         => true,
            ]);
        } else {
            $newQty = max(0, $stock->qty - $qty);
            $stock->update(['qty' => $newQty]);
        }
    }
    
}
