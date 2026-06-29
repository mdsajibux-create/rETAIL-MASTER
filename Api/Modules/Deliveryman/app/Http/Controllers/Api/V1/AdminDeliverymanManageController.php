<?php

namespace Modules\Deliveryman\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\DeliverymanRequest;
use App\Http\Requests\VehicleTypeRequest;
use App\Http\Resources\Admin\AdminDeliverymanDetailsResource;
use App\Http\Resources\Admin\AdminDeliverymanRequestResource;
use App\Http\Resources\Admin\AdminDeliverymanResource;
use App\Http\Resources\Admin\AdminDeliverymanTrashList;
use App\Http\Resources\Admin\AdminVehicleDetailsResource;
use App\Http\Resources\Admin\AdminVehicleResource;
use App\Http\Resources\Admin\AdminVehicleTypeDropdownResource;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Deliveryman\DeliverymanDropdownResource;
use App\Http\Resources\DeliverymanDashboardResource;
use App\Interfaces\DeliverymanManageInterface;
use App\Mail\DynamicEmail;
use App\Models\User;
use App\Services\TrashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Deliveryman\Models\DeliveryMan;

class AdminDeliverymanManageController extends Controller
{
    private $trashService;

    public function __construct(protected DeliverymanManageInterface $deliverymanRepo, TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliveryman_id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|max:32',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $deliveryman = $this->deliverymanRepo->change_password($request->deliveryman_id, $request->password);
        if ($deliveryman) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Deliveryman password']),
            ]);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found'),
            ]);
        }
    }

    public function index(Request $request)
    {

        $filters = [
            'search' => $request->input('search', null),
            'vehicle_type_id' => $request->input('vehicle_type_id', null),
            'zone_id' => $request->input('zone_id', null),
            'state_id' => $request->input('state_id', null),
            'city_id' => $request->input('city_id', null),
            'area_id' => $request->input('area_id', null),
            'status' => $request->input('status', null),
            'identification_type' => $request->input('identification_type', null),
            'created_by' => $request->input('created_by', null),
            'per_page' => $request->input('per_page')
        ];

        $deliverymen = $this->deliverymanRepo->getAllDeliveryman($filters);

        return response()->json([
            'data' => AdminDeliverymanResource::collection($deliverymen),
            'meta' => new PaginationResource($deliverymen)
        ]);
    }

    public function store(DeliverymanRequest $request)
    {
        $deliveryman = $this->deliverymanRepo->store($request->all());

        if ($deliveryman) {
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Deliveryman'])
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.save_failed', ['name' => 'Deliveryman'])
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user_id,
            'phone' => 'nullable|string|max:15',
            'status' => 'nullable|integer',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'zone_id' => 'nullable|exists:zones,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'address' => 'nullable|string|max:255',
            'identification_type' => 'nullable|in:nid,passport,driving_license',
            'identification_number' => 'nullable',
            'identification_photo_front' => 'nullable',
            'identification_photo_back' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $deliveryman = $this->deliverymanRepo->update($request->all());

        if ($deliveryman) {
            return $this->success(__('messages.update_success', ['name' => 'Deliveryman']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Deliveryman']));
        }
    }

    public function show(Request $request)
    {
        $deliveryman = $this->deliverymanRepo->getDeliverymanById($request->id);
        if ($deliveryman) {
            return response()->json(new AdminDeliverymanDetailsResource($deliveryman));
        } else {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }

    }

    public function destroy(int $id)
    {
        $deleted = $this->deliverymanRepo->delete($id);
        if ($deleted) {
            return $this->success(__('messages.delete_success', ['name' => 'Deliveryman']));
        } else {
            return $this->failed(__('messages.delete_failed', ['name' => 'Deliveryman']));
        }
    }

    public function deliverymanRequest()
    {
        $deliverymen = $this->deliverymanRepo->getDeliverymanRequests();
        if ($deliverymen) {
            return response()->json([
                'data' => AdminDeliverymanRequestResource::collection($deliverymen),
                'meta' => new PaginationResource($deliverymen),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
            ]);
        }
    }

    public function handleRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliveryman_ids' => 'required|array',
            'deliveryman_ids.*' => 'required|integer|exists:delivery_men,id',
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $status = $request->status;
        $ids = $request->deliveryman_ids;

        if ($status === 'approved') {
            $success = $this->deliverymanRepo->approveDeliverymen($ids);
            $message = $success
                ? __('messages.approve.success', ['name' => 'Deliveryman'])
                : __('messages.approve.failed', ['name' => 'Deliveryman']);
        } elseif ($status === 'rejected') {
            $success = $this->deliverymanRepo->rejectDeliverymen($ids);
            $message = $success
                ? __('messages.reject.success', ['name' => 'Deliveryman'])
                : __('messages.reject.failed', ['name' => 'Deliveryman']);
        } else {
            return $this->failed(__('messages.action.invalid'));
        }

        return $success ? $this->success($message) : $this->failed($message);
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_men,id',
            'status' => 'required|in:pending,approved,inactive,rejected',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $success = $this->deliverymanRepo->changeStatus($request->all());
        if ($success) {
            return $this->success(__('messages.update_success', ['name' => 'Deliveryman status']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Deliveryman status']));
        }
    }

    public function indexVehicle(Request $request)
    {

        $filters = [
            'search' => $request->search,
            'min_capacity' => $request->min_capacity,
            'max_capacity' => $request->max_capacity,
            'speed_range' => $request->speed_range,
            'created_by' => $request->created_by,
            'fuel_type' => $request->fuel_type,
            'min_distance' => $request->min_distance,
            'max_distance' => $request->max_distance,
            'max_extra_charge' => $request->max_extra_charge,
            'min_fuel_cost' => $request->min_fuel_cost,
            'max_fuel_cost' => $request->max_fuel_cost,
            'status' => $request->status,
            'sort_by' => $request->sort_by ?? 'name',
            'sort_order' => $request->sort_order ?? 'asc',
            'per_page' => $request->per_page ?? 10,
        ];
        $vehicles = $this->deliverymanRepo->getAllVehicles($filters);
        return response()->json(
            [
                'data' => AdminVehicleResource::collection($vehicles),
                'meta' => new PaginationResource($vehicles),
            ]
        );
    }

    public function storeVehicle(VehicleTypeRequest $request)
    {
        $vehicle = $this->deliverymanRepo->addVehicle($request->all());
        createOrUpdateTranslation($request, $vehicle, 'Modules\Deliveryman\Models\VehicleType', $this->deliverymanRepo->translationKeys());
        if ($vehicle) {
            return $this->success(__('messages.save_success', ['name' => 'Vehicle type']));
        } else {
            return $this->failed(__('messages.save_failed', ['name' => 'Vehicle type']), 500);
        }
    }

    public function showVehicle(Request $request)
    {

        $vehicle = $this->deliverymanRepo->getVehicleById($request->id);
        if ($vehicle) {
            return response()->json(new AdminVehicleDetailsResource($vehicle));
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
            ]);
        }
    }

    public function updateVehicle(VehicleTypeRequest $request)
    {
        $vehicle = $this->deliverymanRepo->updateVehicle($request->all());
        createOrUpdateTranslation($request, $vehicle, 'Modules\Deliveryman\Models\VehicleType', $this->deliverymanRepo->translationKeys());
        if ($vehicle) {
            return $this->success(__('messages.update_success', ['name' => 'Vehicle type']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Vehicle type']));
        }
    }

    public function vehicleTypeDropdown()
    {
        $vehicle_types = $this->deliverymanRepo->vehicleTypeDropdown();
        if ($vehicle_types) {
            return response()->json(AdminVehicleTypeDropdownResource::collection($vehicle_types), 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function destroyVehicle(int $id)
    {
        $success = $this->deliverymanRepo->deleteVehicle($id);
        if ($success) {
            return $this->success(__('messages.delete_success', ['name' => 'Vehicle type']));
        } else {
            return $this->failed(__('messages.delete_failed', ['name' => 'Vehicle type']));
        }
    }



    public function changeVehicleStatus(Request $request)
    {
        $success = $this->deliverymanRepo->toggleVehicleStatus($request->id);
        if ($success) {
            return $this->success(__('messages.update_success', ['name' => 'Vehicle type status']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Vehicle type status']));
        }
    }

    public function deliverymanDropdownList(Request $request)
    {
        $filter = [
            'search' => $request->search,
        ];

        $deliverymen = $this->deliverymanRepo->deliverymanListDropdown($filter);

        return response()->json([
            'data' => DeliverymanDropdownResource::collection($deliverymen),
        ], 200);
    }

    public function deliverymanDashboard(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], [
            'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deliveryman = User::where('id', $request->id)->where('activity_scope', 'delivery_level')->first();
        if (!$deliveryman) {
            return response()->json([
                'message' => __('messages.user_invalid', ['user' => 'deliveryman'])
            ], 422);
        }
        $data = $this->deliverymanRepo->getDeliverymanDashboard($request->id);
        return response()->json(new DeliverymanDashboardResource((object)$data));
    }

    public function deliverymanVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_men,id',
            'status' => 'required|in:0,1,2',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deliveryman = DeliveryMan::find($request->id);
        if (!$deliveryman) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ]);
        }
        $deliveryman->is_verified = (int)$request->status;

        $deliveryman->verified_at = $request->status == 1 ? Carbon::now() : null;

        $deliveryman->save();

        try {
            $statusText = match ((int)$request->status) {
                1 => 'Verified',
                2 => 'Rejected',
                default => 'Pending',
            };

            $deliverymanMessage = "Your account verification status is now: {$statusText}.";

            Mail::to($deliveryman->email)->send(new DynamicEmail('Delivery account verification', $deliverymanMessage));
        } catch (\Exception $e) {
        }
        return response()->json([
            'message' => __('messages.update_success', ['name' => 'Deliveryman verification status'])
        ]);
    }

    public function getTrashList(Request $request)
    {
        $trash = $this->trashService->listTrashed('deliveryman', $request->per_page ?? 10, ['deliveryman']);
        return response()->json([
            'data' => AdminDeliverymanTrashList::collection($trash),
            'meta' => new PaginationResource($trash)
        ]);
    }

    public function restoreTrashed(Request $request)
    {
        $ids = $request->ids;
        $restored = $this->trashService->restore('deliveryman', $ids);
        return response()->json([
            'message' => __('messages.restore_success', ['name' => 'Deliverymen']),
            'restored' => $restored,
        ]);
    }

    public function deleteTrashed(Request $request)
    {
        $ids = $request->ids;
        $deleted = $this->trashService->forceDelete('deliveryman', $ids);
        return response()->json([
            'message' => __('messages.force_delete_success', ['name' => 'Deliverymen']),
            'deleted' => $deleted
        ]);
    }
}
