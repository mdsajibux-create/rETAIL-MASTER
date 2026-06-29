<?php

namespace Modules\Customer\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Customer\CustomerDetailsResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Interfaces\CustomerManageInterface;
use App\Models\Customer;
use App\Services\TrashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerManageController extends Controller
{
    protected $trashService;

    public function __construct(protected CustomerManageInterface $customerManageRepo, TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    public function listCustomers(Request $request)
    {
        $query = Customer::query();

        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'LIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            });
        }

        if (isset($request->status)) {
            $query->where("status", $request->status);
        }

        $customers = $query->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'customers' => CustomerResource::collection($customers),
            'meta' => new PaginationResource($customers)
        ]);
    }

    public function registerCustomer(CustomerRequest $request)
    {
        try {
            $customer = Customer::create($request->all());
            // Return a successful response with the token and permissions
            if ($customer) {
                return response()->json([
                    "message" => __('messages.registration_success', ['name' => 'Customer']),
                ]);
            } else {
                return response()->json([
                    "message" => __('messages.registration_failed', ['name' => 'Customer']),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function getCustomerById(Request $request)
    {
        $customer = Customer::find($request->id);

        if (!$customer){
            return response()->json([
               'message' => __('messages.not_found', ['name' => 'Customer'])
            ],404);
        }

        return response()->json(new CustomerDetailsResource($customer));
    }

    public function changeCustomerStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ]);
        }

        $customer = Customer::findOrFail($request->id);
        $customer->status = $request->status;
        $customer->save();

        return $this->success(translate('messages.update_success', ['name' => 'Customer']));
    }

    public function changeCustomerPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'password' => 'required|string|min:8|max:32',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $customer = $this->change_password($request->customer_id, $request->password);
        if ($customer) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Customer password']),
            ]);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found'),
            ], 404);
        }
    }

    private function change_password(int $customer_id, string $password)
    {
        if (auth('api')->check()) {
            unauthorized_response();
        }
        $customer = Customer::where('id', $customer_id)->first();
        if (!$customer) {
            return false;
        }
        $customer->password = Hash::make($password);
        $customer->save();
        return $customer;
    }

    public function verifyCustomerEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return response()->json([
                'message' => __('messages.data_not_found'),
            ], 404);
        }
        $customer->email_verified = 1;
        $customer->email_verified_at = now();
        $customer->save();
        return response()->json([
            'message' => __('messages.email.verify.success'),
        ]);
    }

    public function updateCustomerProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string',
            'image' => 'nullable|string',
            'birth_day' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'nullable|string|in:male,female,others',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => $validator->errors()
            ]);
        }
        try {
            if (!auth('sanctum')->check()) {
                return unauthorized_response();
            }

            $userId = $request->customer_id;
            $user = Customer::findOrFail($userId);

            if ($user) {
                $user->update($request->only('first_name', 'last_name', 'email', 'phone', 'image', 'birth_day', 'gender'));
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => __('messages.update_success', ['name' => 'Customer']),
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'status_code' => 500,
                    'message' => __('messages.update_failed', ['name' => 'Customer']),
                ]);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function suspendCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return response()->json([
                'message' => __('messages.data_not_found'),
            ], 404);
        }
        $customer->status = 2;
        $customer->save();
        return response()->json([
            'message' => __('messages.suspended', ['name' => 'Customer']),
        ]);
    }

    public function deleteCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $deleted = 0;
        $skipped = [];
        $failed = [];

        foreach ($request->customer_ids as $customerId) {
            $customer = Customer::find($customerId);

            if (!$customer) {
                $failed[] = $customerId;
                continue;
            }

            if ($customer->hasRunningOrders()) {
                $skipped[] = $customerId;
                continue;
            }

            $success = $this->customerManageRepo->deleteCustomerRelatedAllData($customerId);

            if ($success) {
                $deleted++;
            } else {
                $failed[] = $customerId;
            }
        }
        $skippedCustomerNames = [];

        foreach ($skipped as $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $skippedCustomerNames[] = $customer->full_name;
            }
        }

        $skippedNames = implode(', ', $skippedCustomerNames);

        return response()->json([
            'message' => "Processed: $deleted deleted, " . count($skipped) . " skipped (Customer(s): $skippedNames have running orders), " . count($failed) . " failed.",
            'deleted' => $deleted,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);
    }

    public function getCustomerTrashList(Request $request)
    {
        $trash = $this->trashService->listTrashed('customer', $request->per_page ?? 10);
        return response()->json([
            'data' => CustomerResource::collection($trash),
            'meta' => new PaginationResource($trash)
        ]);
    }

    public function restoreCustomerTrashed(Request $request)
    {
        $ids = $request->ids;
        $restored = $this->trashService->restore('customer', $ids);
        return response()->json([
            'message' => __('messages.restore_success', ['name' => 'Customers']),
            'restored' => $restored,
        ]);
    }

    public function deleteCustomerTrashed(Request $request)
    {
        $ids = $request->ids;
        $deleted = $this->trashService->forceDelete('customer', $ids);
        return response()->json([
            'message' => __('messages.force_delete_success', ['name' => 'Customers']),
            'deleted' => $deleted
        ]);
    }
}
