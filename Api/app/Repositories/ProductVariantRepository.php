<?php

namespace App\Repositories;

use App\Interfaces\ProductVariantInterface;
use Modules\Product\app\Models\ProductVariant;

class ProductVariantRepository implements ProductVariantInterface
{
    public function __construct(protected ProductVariant $variant) {}
    public function model(): string
    {
        return ProductVariant::class;
    }
    public function getPaginatedVariant(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $variant = ProductVariant::with(['product', 'unit'])
            ->select('product_variants.*');

        // Apply search filter if search parameter exists
        if ($search) {
            $variant->where(function ($query) use ($search) {
                $query->where('product_variants.name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('products.name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unit', function ($q) use ($search) {
                        $q->where('units.name', 'like', "%{$search}%");
                    });
            });
        }
        // Apply sorting and pagination
        return $variant
            ->orderBy($sortField, $sort)
            ->paginate($limit);
    }
    public function store(array $data)
    {
        try {
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {

                    // Create the product variant
                    ProductVariant::create($variantData);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function update(array $data)
    {
        try {
            $variant = ProductVariant::findOrFail($data['id']);
            if ($variant) {
                $variant->update($data);
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getVariantById(int|string $id)
    {
        try {
            $variant = ProductVariant::with(['product', 'unit'])->find($id);
            if ($variant) {
                return response()->json([
                    "data" => $variant->toArray(),
                    "massage" => "Data was found"
                ], 201);
            } else {
                return response()->json([
                    "massage" => "Data was not found"
                ], 404);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function delete(int|string $id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            $variant->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function records(bool $onlyDeleted = false)
    {
        try {
            switch ($onlyDeleted) {
                case true:
                    $records = ProductVariant::onlyTrashed()->get();
                    break;

                default:
                    $records = ProductVariant::withTrashed()->get();
                    break;
            }
            return $records;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
