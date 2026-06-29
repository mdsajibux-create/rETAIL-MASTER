<?php

namespace Modules\Product\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\ProductManageInterface;
use App\Interfaces\ProductVariantInterface;
use App\Services\TrashService;
use Illuminate\Http\Request;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Transformers\ProductDetailsPublicResource;
use Modules\Product\app\Transformers\ProductListResource;
use Modules\Product\app\Transformers\ProductSelectListResource;

class BranchProductManageController extends Controller
{
    protected $trashService;
    public function __construct(protected ProductManageInterface $productRepo, protected ProductVariantInterface $variantRepo,TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    public function listProducts(Request $request)
    {
        $status = $request->status ?? '';
        $limit = $request->per_page ?? 10;
        $page = $request->page ?? 1;
        $locale = $request->language ?? 'en';
        $type = $request->type ?? '';
        $search = $request->search ?? '';
        $sortField = $request->sortField ?? 'id';
        $sortOrder = $request->sort ?? 'asc';
        $filters = [];

        $products = $this->productRepo->getPaginatedProductForBranch(
            $status,
            $limit,
            $page,
            $locale,
            $type,
            $search,
            $sortField,
            $sortOrder,
            $filters
        );

        return response()->json([
            'data' => ProductListResource::collection($products),
            'meta' => new PaginationResource($products)
        ]);
    }

    public function selectProducts(Request $request)
    {
        $status = $request->status ?? '';
        $limit = $request->per_page ?? 10;
        $page = $request->page ?? 1;
        $locale = $request->language ?? 'en';
        $type = $request->type ?? '';
        $search = $request->search ?? '';
        $sortField = $request->sortField ?? 'id';
        $sortOrder = $request->sort ?? 'asc';
        $filters = [];

        $products = $this->productRepo->getPaginatedProductForBranch(
            $status,
            $limit,
            $page,
            $locale,
            $type,
            $search,
            $sortField,
            $sortOrder,
            $filters
        );

        return response()->json([
            'data' => ProductSelectListResource::collection($products),
            'meta' => new PaginationResource($products)
        ]);
    }

    public function selectProductsAdmin(Request $request)
    {
        $status = $request->status ?? '';
        $limit = $request->per_page ?? 50;
        $page = $request->page ?? 1;
        $locale = $request->language ?? 'en';
        $type = $request->type ?? '';
        $search = $request->search ?? '';
        $sortField = $request->sortField ?? 'id';
        $sortOrder = $request->sort ?? 'asc';
        $filters = [];

        $products = $this->productRepo->getPaginatedProductForAdmin(
            $status,
            $limit,
            $page,
            $locale,
            $type,
            $search,
            $sortField,
            $sortOrder,
            $filters
        );

        return response()->json([
            'data' => ProductSelectListResource::collection($products),
        ]);
    }

    public function productDetails($product_slug)
    {
        $product = Product::with([
            'tags',
            'unit',
            'variants',
            'brand',
            'category',
            'related_translations',
        ])
            ->where('slug', $product_slug)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ],404);
        }
        return response()->json([
            'messages' => __('messages.data_found'),
            'data' => new ProductDetailsPublicResource($product),
        ], 200);
    }


}
