<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Branch\app\Models\Branch;
use Modules\Product\app\Models\FlashSale;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\FlashSaleProduct;

class FlashSaleService
{
    public function __construct(protected FlashSale $flashSale, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->flashSale->translationKeys;
    }

    public function createFlashSale(array $data)
    {
        DB::beginTransaction(); // Start transaction

        try {
            $flashSale = FlashSale::create($data);

            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                $flashSaleProducts = [];
                foreach ($data['product_ids'] as $productId) {
                    $flashSaleProducts[] = [
                        'flash_sale_id' => $flashSale->id,
                        'product_id' => $productId,
                        'created_by' => auth('api')->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                FlashSaleProduct::insert($flashSaleProducts);
            }

            DB::commit();

            return $flashSale->id;

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to create flash sale.'], 500);
        }
    }

    public function updateFlashSale(array $data)
    {
        DB::beginTransaction(); // Start transaction

        try {
            // Find existing Flash Sale
            $flashSale = FlashSale::findOrFail($data['id']);
            $flashSale->update($data);

            // Check if product_ids exist and are valid
            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                // Remove old associations first
                FlashSaleProduct::where('flash_sale_id', $flashSale->id)->delete();

                $flashSaleProducts = [];

                foreach ($data['product_ids'] as $productId) {
                    $flashSaleProducts[] = [
                        'flash_sale_id' => $flashSale->id,
                        'product_id' => $productId,
                        'created_by' => auth('api')->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert new products
                FlashSaleProduct::insert($flashSaleProducts);
            }

            DB::commit(); // Commit transaction if successful

            return $flashSale->id;

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['error' => 'Failed to update flash sale.'], 500);
        }
    }

    public function deleteFlashSale($id)
    {
        $flashSale = FlashSale::findorfail($id);
        $flashSale->delete();

        return true;
    }

    public function deleteFlashSaleProducts($id)
    {
        $flashSaleProducts = FlashSaleProduct::where('flash_sale_id', $id)->get();
        if (!empty($flashSaleProducts)) {
            foreach ($flashSaleProducts as $flashSaleProduct) {
                $flashSaleProduct->delete();
            }
            return true;
        } else {
            return false;
        }
    }

    public function associateProductsToFlashSale(int $flashSaleId, array $products)
    {
        $bulkData = array_map(function ($product) use ($flashSaleId) {
            return [
                'flash_sale_id' => $flashSaleId,
                'product_id' => $product,
                'created_by' => auth('api')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $products);

        FlashSaleProduct::insert($bulkData);

        return true;
    }

    public function updateFlashSaleProducts(int $flashSaleId, array $products)
    {
        $exists = FlashSaleProduct::whereIn('product_id', $products)
            ->where('flash_sale_id', $flashSaleId)
            ->get();

        if (!empty($exists)) {
            foreach ($exists as $exist) {
                $exist->delete();
            }
        }

        $bulkData = array_map(function ($product) use ($flashSaleId) {

            return [
                'flash_sale_id' => $flashSaleId,
                'product_id' => $product,
                'status' => 'active',
                'created_by' => auth('api')->id(),
                'updated_at' => now(),
            ];

        }, $products);

        FlashSaleProduct::insert($bulkData);

        return true;
    }


    public function getExistingFlashSaleProducts(array $productIds)
    {
        $existingProducts = FlashSaleProduct::whereIn('product_id', $productIds)
            ->with('product:id,name')
            ->get();

        if ($existingProducts->isEmpty()) {
            return null;
        }

        $flashSaleId = $existingProducts->first()->flash_sale_id;

        // Check if the flash sale is active
        $flashSale = FlashSale::where('id', $flashSaleId)
            ->where('start_time', '<=', Carbon::now())
            ->where('end_time', '>=', Carbon::now())
            ->first();

        if ($flashSale) {
            $productNames = $existingProducts->pluck('product.name')->filter()->implode(', ');
            return [
                'status' => false,
                'status_code' => 422,
                'message' => 'The following products are already in an active flash sale: ' . $productNames,
                'existing_products' => $existingProducts->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Unknown',
                        'flash_sale_start' => $item->flashSale->start_time ?? null,
                        'flash_sale_end' => $item->flashSale->end_time ?? null,
                    ];
                }),
            ];
        }
        return null; // No active flash sale found
    }

    public function checkProductsExistInStore(array $productIds)
    {
        $existingProducts = Product::whereIn('id', $productIds)
            ->pluck('id')
            ->toArray();

        // Find missing product IDs
        $missingProducts = array_diff($productIds, $existingProducts);
        if (!empty($missingProducts)) {
            return [
                'message' => 'Some products do not exist in the given store.',
            ];
        }

        return null;
    }

    public function getAdminFlashSales($filters)
    {
        $query = FlashSale::with('related_translations');

        if (!empty($filters['title']) && isset($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['start_date']) && isset($filters['start_date'])) {
            $query->whereDate('start_time', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date']) && isset($filters['end_date'])) {
            $query->whereDate('end_time', '<=', $filters['end_date']);
        }
        $perPage = (!empty($filters['per_page']) && isset($filters['per_page'])) ? $filters['per_page'] : 10;
        return $query->latest()->paginate($perPage);
    }


    public function getAdminFlashSalesDropdown()
    {
        $flashSales = FlashSale::paginate(20);
        if ($flashSales->isEmpty()) {
            return null;
        } else {
            return $flashSales;
        }
    }

    public function getFlashSaleById($id)
    {
        $flashSale = FlashSale::with(['products.product', 'related_translations'])->where('id', $id)->first();
        if (!empty($flashSale)) {
            return $flashSale;
        } else {
            return null;
        }
    }


    public function getAllFlashSaleProducts(array $filters)
    {

        $query = FlashSaleProduct::query()->with([
            'product.related_translations',
            'flashSale.related_translations'
        ]);

        $query->when(isset($filters['flash_sale_id']), fn($q) => $q->where('flash_sale_id', $filters['flash_sale_id']));
        $query->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']));

        $query->when(isset($filters['start_date']), function ($q) use ($filters) {
            $q->whereDate('created_at', '>=', $filters['start_date']);
        });
        $query->when(isset($filters['end_date']), function ($q) use ($filters) {
            $q->whereDate('created_at', '<=', $filters['end_date']);
        });

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function getValidFlashSales(?array $filters = [])
    {
        $query = FlashSale::where('status', true)->get();

        if ($query->isEmpty()) {
            return null;
        }

        $query = FlashSale::with(['products', 'related_translations'])
            ->where('status', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhereHas('related_translations', function ($q2) use ($search) {
                        $q2->where('title', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('start_time', 'asc')->paginate($filters['per_page'] ?? 10);
    }

    public function toggleStatus(int $id): FlashSale
    {
        $flashSale = FlashSale::find($id);

        if ($flashSale->status === true){
            $status = false;
        }else{
            $status = true;
        }

        $flashSale->update([
            'status' => $status  // fixed syntax
        ]);

        return $flashSale;
    }

    public function deactivateExpiredFlashSales()
    {
        $expiredFlashSales = FlashSale::where('end_time', '<', now())
            ->where('status', true) // Ensure only active flash sales are updated
            ->update(['status' => false]); // Use the existing field name for status

        return $expiredFlashSales > 0; // Return true if any records were updated
    }

    public function getFlashSaleProductRequest()
    {
        $requests = FlashSaleProduct::with(['related_translations', 'flashSale', 'product', 'store'])->pending()->paginate(10);
        if (!empty($requests)) {
            return $requests;
        } else {
            return null;
        }
    }

    public function getFlashSaleProductRequestDetails(int $id)
    {
        $requests = FlashSaleProduct::find($id);
        if (!empty($requests)) {
            return $requests;
        } else {
            return null;
        }
    }

    public function approveFlashSaleProductRequest(array $productIds): bool
    {
        // Validate input
        if (!empty($productIds) && isset($productIds)) {
            // Bulk update the status to "approved"
            $updated = FlashSaleProduct::whereIn('id', $productIds)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'reviewed_at' => now(),
                    'updated_at' => now()
                ]);
            // Return true if at least one record was updated
            return $updated > 0;
        } else {
            return false;
        }
    }

    public function rejectFlashSaleProductRequest(array $productIds, string $rejectionReason): bool
    {
        // Validate input
        if (!empty($productIds) && isset($productIds) && !empty($rejectionReason)) {
            // Bulk update the status to "rejected" and set the rejection reason
            $updated = FlashSaleProduct::whereIn('id', $productIds)
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'reviewed_at' => now(),
                    'updated_at' => now()
                ]);
            // Return true if at least one record was updated
            return $updated > 0;
        } else {
            return false;
        }
    }

    public function storeTranslation($request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }
                    // Collect translation data
                    $translations[] = [
                        'translatable_type' => $refPath,
                        'translatable_id' => $refid,
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

    public function updateTranslation($request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }

                    $trans = $this->translation->where('translatable_type', $refPath)->where('translatable_id', $refid)
                        ->where('language', $translation['language_code'])->where('key', $key)->first();
                    if ($trans != null) {
                        $trans->value = $translatedValue;
                        $trans->save();
                    } else {
                        $translations[] = [
                            'translatable_type' => $refPath,
                            'translatable_id' => $refid,
                            'language' => $translation['language_code'],
                            'key' => $key,
                            'value' => $translatedValue,
                        ];
                    }
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }
}
