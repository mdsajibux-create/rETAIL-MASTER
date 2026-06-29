<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\StoreProductBrandRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\ProductBrandByIdResource;
use App\Http\Resources\ProductBrandResource;
use App\Repositories\ProductBrandRepository;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Catalog\app\Models\ProductBrand;

class ProductBrandController extends Controller
{

    public function __construct(public ProductBrandRepository $repository)
    {
    }

    public function listBrands(Request $request)
    {
        // If request has limit
        $limit = $request->limit ?? 10;
        // If request has language
        $language = $request->language ?? config('app.default_language');;
        //Search parameters
        $search = $request->search;
        // Extract brands table with translations table with condition
        $brands = ProductBrand::leftJoin('translations', function ($join) use ($language) {
            $join->on('product_brand.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', ProductBrand::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'brand_name');
        })->select(
            'product_brand.*',
            DB::raw('COALESCE(translations.value, product_brand.brand_name) as brand_name')
        );

        // Apply search filter if search parameter exists
        if ($search) {
            $brands->where(function ($query) use ($search) {
                $query->where('translations.value', 'like', "%{$search}%")
                    ->orWhere('product_brand.brand_name', 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        $brands = $brands->orderBy($request->sortField ?? 'id', $request->sort ?? 'desc')
            ->paginate($limit ?? 10);

        // Return a collection of ProductBrandResource (including the image)
        return response()->json([
            'data' => ProductBrandResource::collection($brands),
            'meta' => new PaginationResource($brands)
        ]);
    }


    public function getBrandById(Request $request)
    {
        $brand = $this->repository->with(['related_translations'])->findOrFail($request->id);
        if ($brand) {
            return new ProductBrandByIdResource($brand);
        }
        return response()->json(['error' => 'Product Brand not found'], 404);
    }

    public function createBrand(StoreProductBrandRequest $request)
    {

        try {
            $brand = $this->repository->storeProductBrand($request);
            return $this->success(trans('messages.save_success', ['name' => 'Brand']));
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not create the product brand.' . $e);
        }
    }

    public function updateBrand(StoreProductBrandRequest $request)
    {
        try {
            $brand = $this->repository->storeProductBrand($request);
            if (empty($brand)) {
                return response()->json([
                    'message' => __('messages.data_not_found')
                ]);
            }
            return $this->success(trans('messages.save_success', ['name' => 'Brand']));
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not create the product brand.' . $e);
        }
    }

    /* Change or Approve product brand status (Admin only) */
    public function changeBrandStatus(Request $request)
    {
        $productBrand = ProductBrand::findOrFail($request->id);
        $productBrand->status = !$productBrand->status;
        $productBrand->save();
        return response()->json([
            'success' => true,
            'message' => 'Product brand status updated successfully',
            'status' => $productBrand->status
        ]);
    }

    public function deleteBrands(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'nullable|exists:product_brand,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $brandLogoIds = [];

        // Fetch brands and collect logo IDs
        $brands = ProductBrand::whereIn('id', $request->ids)->get();

        foreach ($brands as $brand) {
            if ($brand->brand_logo) {
                $brandLogoIds[] = $brand->brand_logo;
            }
            $brand->delete();
        }

        // Delete media files
        $mediaResult = app(MediaService::class)->bulkDeleteMediaImages($brandLogoIds);

        return response()->json([
            'success' => true,
            'message' => __('messages.delete_success', ['name' => 'Brands']),
            'deleted_brands' => count($brands),
            'deleted_media' => $mediaResult['deleted'],
            'failed_media' => $mediaResult['failed'],
        ]);
    }
}
