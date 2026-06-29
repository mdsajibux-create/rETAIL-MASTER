<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\DynamicFieldRequest;
use App\Http\Resources\Admin\DynamicFieldForProductCreateResource;
use App\Http\Resources\Admin\DynamicFieldResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\DynamicFieldInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Catalog\app\Models\DynamicField;
use Modules\Catalog\app\Models\ProductSpecification;

class DynamicFieldsManageController extends Controller
{
    public function __construct(protected DynamicFieldInterface $dynamicFieldRepo) {

    }

    // Add constants for default values
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'asc';
    private const MAX_LIMIT = 100;

    public function list(Request $request)
    {
        // Validate input parameters
        $validated = $request->validate([
            'limit' => 'integer|min:1|max:' . self::MAX_LIMIT,
            'page' => 'integer|min:1',
            'language' => 'string|max:10',
            'search' => 'string|max:255',
            'sortField' => 'string|in:id,name,created_at,updated_at',
            'sort' => 'string|in:asc,desc',
        ]);

        // Use validated data with fallbacks
        $params = [
            'limit' => $validated['limit'] ?? self::DEFAULT_LIMIT,
            'page' => $validated['page'] ?? self::DEFAULT_PAGE,
            'language' => $validated['language'] ?? config('app.default_language', DEFAULT_LANGUAGE),
            'search' => $validated['search'] ?? '',
            'sortField' => $validated['sortField'] ?? self::DEFAULT_SORT_FIELD,
            'sort' => $validated['sort'] ?? self::DEFAULT_SORT_DIRECTION,
            'filters' => []
        ];

        // get paginated data
        $dynamic_field = $this->dynamicFieldRepo->getPaginatedDynamicField(...array_values($params));

        return response()->json([
            'success' => true,
            'data'=> DynamicFieldResource::collection($dynamic_field),
            'meta'=> new PaginationResource($dynamic_field),
        ],200);
    }


    public function getDynamicOptionForProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // return data
        $dynamic_field = $this->dynamicFieldRepo->getDynamicOptionForProduct($request->product_type);

        // if data not fund
        if ($dynamic_field === false){
            return response()->json([
                "massage" => "Data was not found"
            ], 404);
        }

        return response()->json([
            'data' => DynamicFieldForProductCreateResource::collection($dynamic_field),
        ]);
    }

    public function createDynamicField(DynamicFieldRequest $request): JsonResponse
    {
        $dynamic_field = $this->dynamicFieldRepo->store($request->all());
        createOrUpdateTranslation($request, $dynamic_field, 'Modules\Catalog\app\Models\DynamicField', $this->dynamicFieldRepo->translationKeys());
        if ($dynamic_field) {
            return $this->success(translate('messages.save_success', ['name' => 'Dynamic Field']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Dynamic Field']));
        }
    }

    public function getDynamicFieldById(Request $request)
    {
        $dynamic_field = $this->dynamicFieldRepo->getDynamicFieldById($request->id);
        if ($dynamic_field === false){
            return response()->json([
                "massage" => "Data was not found"
            ], 404);
        }

        return response()->json(new DynamicFieldResource($dynamic_field));
    }
    public function updateDynamicField(DynamicFieldRequest $request)
    {
        $dynamic_field = $this->dynamicFieldRepo->update($request->all());
        createOrUpdateTranslation($request, $dynamic_field, 'Modules\Catalog\app\Models\DynamicField', $this->dynamicFieldRepo->translationKeys());
        if ($dynamic_field) {
            return $this->success(translate('messages.update_success', ['name' => 'Dynamic Field']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Dynamic Field']));
        }
    }
    public function deleteDynamicField($id)
    {
        // Check if the dynamic field exists in any product specification
        if (ProductSpecification::where('dynamic_field_id', $id)->exists()) {
            return $this->failed(translate('messages.cannot_delete_in_use'));
        }

        if (!DynamicField::where('id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Dynamic Field Not Found')
            ],404);
        }

        $this->dynamicFieldRepo->delete($id);
        return $this->success(translate('messages.delete_success'));
    }

    public function changeDynamicFieldStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:dynamic_fields,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dynamic_field = DynamicField::find($request->id);
        if ($dynamic_field) {
            $is_req =  $dynamic_field->is_required ? 0 : 1;
            $dynamic_field->update(['is_required' => $is_req]);
            return response()->json([
                "message" => __("messages.status_is_required"),
            ], 200);
        } else {
            return response()->json([
                "message" => __("messages.data_not_found"),
            ], 404);
        }
    }

}
