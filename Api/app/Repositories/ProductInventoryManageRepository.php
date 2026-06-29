<?php

namespace App\Repositories;

use App\Interfaces\InventoryManageInterface;
use Illuminate\Support\Facades\DB;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductVariant;

class ProductInventoryManageRepository implements InventoryManageInterface
{

    public function getInventories(?array $filters)
    {
        $inventories = Product::query();

        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $inventories->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (isset($filters['branch_id'])) {
            $inventories->whereHas('stocks', fn($q) =>
            $q->where('branch_id', $filters['branch_id'])
            );
        }

        if (isset($filters['category_id'])) {
            $inventories->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand_id'])) {
            $inventories->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['type'])) {
            $inventories->where('type', $filters['type']);
        }

        if (isset($filters['stock_status'])) {
            if ($filters['stock_status'] === 'low_stock') {
                $inventories->whereHas('stocks', function ($query) use ($filters) {
                    $query->when($filters['branch_id'] ?? null, fn($q, $b) => $q->where('branch_id', $b))
                        ->whereColumn('qty', '<=', 'reorder_point')
                        ->where('qty', '>', 0);
                });
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $inventories->whereHas('stocks', function ($query) use ($filters) {
                    $query->when($filters['branch_id'] ?? null, fn($q, $b) => $q->where('branch_id', $b))
                        ->where('qty', 0);
                });
            }
        }

        return $inventories->with([
            'category',
            'brand',
            'related_translations',
            'variants',
            'variants.stocks',
        ])->paginate($filters['per_page'] ?? 10);
    }


    public function deleteProductsWithVariants(array $productIds)
    {
        $products = Product::whereIn('id', $productIds)->get();

        if ($products->isEmpty()) {
            return false; // No products found
        }

        foreach ($products as $product) {
            $this->deleteProductWithVariants($product);
        }

        return true;
    }

    public function deleteProductWithVariants(Product $product)
    {
        $product->delete();
    }

}
