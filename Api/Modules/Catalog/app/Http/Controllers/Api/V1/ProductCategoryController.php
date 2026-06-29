<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Com\ProductCategoryListResource;
use App\Http\Resources\Com\ProductCategoryResource;
use App\Http\Resources\ProductCategoryByIdResource;
use App\Repositories\ProductCategoryRepository;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Catalog\app\Models\ProductCategory;

class ProductCategoryController extends Controller
{

    public function __construct(
        public ProductCategoryRepository $repository,
    )
    {
    }

    public function listProductCategories(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $language = $request->language ?? config('app.default_language');;
        $search = $request->search;
        $type = $request->type; // Get the type filter

        $categories = ProductCategory::leftJoin('translations', function ($join) use ($language) {
            $join->on('product_category.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', ProductCategory::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'category_name');
        })
            ->select(
                'product_category.*',
                DB::raw('COALESCE(translations.value, product_category.category_name) as category_name')
            );

        // Apply type filter if type is provided
        if ($type) {
            $categories->where('product_category.type', $type);
        }

        // Apply search filter if search parameter exists
        if ($search) {
            $categories->where(function ($query) use ($search) {
                $query->where('translations.value', 'like', "%{$search}%")
                    ->orWhere('product_category.category_name', 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        $sortField = $request->sortField ?? 'display_order';
        $sortOrder = $request->sort ?? 'asc';

        if ($request->pagination == "false") {
            $categories = $categories->whereNull('parent_id')
                ->orderBy($sortField, $sortOrder)
                ->get();
            return response()->json([
                'data' => ProductCategoryResource::collection($categories)
            ]);
        } elseif ($request->list) {
            $categories = $categories->whereNull('parent_id')
                ->orderBy($sortField, $sortOrder)
                ->paginate($per_page);
            return response()->json([
                'data' => ProductCategoryListResource::collection($categories),
                'meta' => new PaginationResource($categories)
            ]);
        } else {
            $categories = $categories
                ->orderBy($sortField, $sortOrder)
                ->paginate($per_page);
            return response()->json([
                'data' => ProductCategoryResource::collection($categories),
                'meta' => new PaginationResource($categories)
            ]);
        }
    }

    public function getProductCategoryById(Request $request)
    {
        $category = $this->repository->with(['related_translations'])->findOrFail($request->id);
        if ($category) {
            return response()->json(new ProductCategoryByIdResource($category));
        } else {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }
    }

    public function createProductCategory(StoreProductCategoryRequest $request)
    {
        try {
            $this->repository->storeProductCategory($request);
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Product Category']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeProductCategoryStatus(Request $request)
    {
        $productCategory = ProductCategory::find($request->id);
        if ($productCategory) {
            $productCategory->status = !$productCategory->status;
            $productCategory->save();
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Product category status']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function deleteProductCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'nullable|exists:product_category,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $mainCategories = ProductCategory::whereIn('id', $request->ids)->get();

        // Get direct children
        $childCategories = ProductCategory::whereIn('parent_id', $request->ids)->get();

        // Combine all categories
        $allCategories = $mainCategories->concat($childCategories);

        // Collect all related media IDs
        $mediaIds = [];
        foreach ($allCategories as $cat) {
            if ($cat->category_thumb) {
                $mediaIds[] = $cat->category_thumb;
            }
        }

        // Delete categories
        $deletedCount = ProductCategory::whereIn('id', $allCategories->pluck('id'))->delete();

        // Delete associated media
        $mediaService = app(MediaService::class);
        $mediaResult = $mediaService->bulkDeleteMediaImages(array_unique($mediaIds));

        return response()->json([
            'success' => true,
            'message' => __('messages.delete_success', ['name' => 'Product categories']),
            'deleted_categories' => $deletedCount,
            'deleted_media' => $mediaResult['deleted'],
            'failed_media' => $mediaResult['failed'],
        ]);
    }
}
