<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ProductTypeRequest;
use App\Http\Resources\Admin\AdminProductTypeDetailsResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\ProductTypeManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettings\app\Transformers\StoreTypeResource;

class AdminProductTypeManageController extends Controller
{
    public function __construct(protected ProductTypeManageInterface $storeTypeRepo)
    {

    }

    public function allTypes(Request $request)
    {
        $filters = [
            'per_page' => $request->per_page,
            'type' => $request->type,
            'search' => $request->search,
            'status' => $request->status,
        ];

        $types = $this->storeTypeRepo->getAllStoreTypes($filters);

        return response()->json([
            'data' => StoreTypeResource::collection($types),
            'meta' => new PaginationResource($types)
        ], 200);
    }

    public function updateType(ProductTypeRequest $request)
    {
        $additional_charge_type = $request->get('charge_type');
        $additional_charge_amount = $request->charge_amount;
        $shouldRound = shouldRound();

        if ($shouldRound && $additional_charge_type === 'fixed' && is_float($additional_charge_amount)) {
            return response()->json([
                'message' => __('messages.should_round', ['name' => 'Additional charge']),
            ]);
        }

        $success = $this->storeTypeRepo->updateStoreType($request->all());
        createOrUpdateTranslation($request, $success, 'Modules\BusinessSettings\app\Models\ProductType', $this->storeTypeRepo->translationKeys());

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Product Type']),
            ], 201);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Product Type'])
            ], 500);
        }
    }

    public function typeDetails(Request $request)
    {
        $validator = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:product_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $storeType = $this->storeTypeRepo->getStoreTypeById($request->id);

        if ($storeType) {
            return response()->json(new AdminProductTypeDetailsResource($storeType), 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function changeStatus(Request $request)
    {
        $success = $this->storeTypeRepo->toogleStatus($request->id);
        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Product Type Settings status']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Product Type Settings status'])
            ], 500);
        }

    }
}
