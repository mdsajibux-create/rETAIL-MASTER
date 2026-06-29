<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\DynamicFieldOptionRequest;
use App\Http\Requests\DynamicFieldOptionUpdateRequest;
use App\Http\Resources\Admin\DynamicFieldOptionResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\DynamicFieldOptionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DynamicFieldsOptionManageController extends controller
{
    public function __construct(protected DynamicFieldOptionInterface $dynamicFieldRepo) {

    }

    // Add constants for default values
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'asc';
    private const MAX_LIMIT = 100;

    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dynamic_field_id' => 'required|exists:dynamic_fields,id',
            'limit' => 'integer|min:1|max:' . self::MAX_LIMIT,
            'page' => 'integer|min:1',
            'language' => 'nullable|string|max:10',
            'search' => 'string|max:255',
            'sortField' => 'string|in:id,name,created_at,updated_at',
            'sort' => 'string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $data = $validator->validated();

        $params = [
            'dynamic_field_id' => $data['dynamic_field_id'],
            'limit' => $data['limit'] ?? self::DEFAULT_LIMIT,
            'page' => $data['page'] ?? self::DEFAULT_PAGE,
            'language' => $data['language'] ?? config('app.default_language', 'en'),
            'search' => $data['search'] ?? '',
            'sortField' => $data['sortField'] ?? self::DEFAULT_SORT_FIELD,
            'sort' => $data['sort'] ?? self::DEFAULT_SORT_DIRECTION,
            'filters' => [] // optional: add extra filters here if needed
        ];

        $dynamic_fields = $this->dynamicFieldRepo->getPaginatedDynamicField(
            $params['dynamic_field_id'],
            $params['limit'],
            $params['page'],
            $params['language'],
            $params['search'],
            $params['sortField'],
            $params['sort'],
            $params['filters']
        );

        return response()->json([
            'success' => true,
            'data' => DynamicFieldOptionResource::collection($dynamic_fields),
            'meta' => new PaginationResource($dynamic_fields)
        ], 200);
    }
    public function createDynamicFieldOption(DynamicFieldOptionRequest $request): JsonResponse
    {
        $dynamic_field = $this->dynamicFieldRepo->store($request->all());
        createOrUpdateTranslation($request, $dynamic_field, 'Modules\Catalog\app\Models\DynamicFieldValue', $this->dynamicFieldRepo->translationKeys());

        if ($dynamic_field === false) {
            return $this->success(translate('messages.save_failed',
                [
                    'name' => 'type not support options'
                ]));
        }

        if ($dynamic_field) {
            return $this->success(translate('messages.save_success', ['name' => 'Field Option']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Field Option']));
        }
    }

    public function getDynamicFieldByIdOption(Request $request)
    {
        $dynamic_field = $this->dynamicFieldRepo->getDynamicFieldById($request->id);
        if ($dynamic_field === false){
            return response()->json([
                "massage" => "Data was not found"
            ], 404);
        }

        return response()->json(new DynamicFieldOptionResource($dynamic_field));
    }
    public function updateDynamicFieldOption(DynamicFieldOptionUpdateRequest $request)
    {

        $dynamic_field = $this->dynamicFieldRepo->update($request->all());
        createOrUpdateTranslation($request, $dynamic_field, 'Modules\Catalog\app\Models\DynamicFieldValue', $this->dynamicFieldRepo->translationKeys());
        if ($dynamic_field) {
            return $this->success(translate('messages.update_success', ['name' => 'Dynamic Field Value']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Dynamic Field Value']));
        }
    }
    public function deleteDynamicFieldOption($id)
    {
        $this->dynamicFieldRepo->delete($id);
        return $this->success(translate('messages.delete_success'));
    }


}
