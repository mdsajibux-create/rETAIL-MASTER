<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\CustomerAddressRequest;
use App\Http\Resources\Customer\CustomerAddressResource;
use App\Interfaces\AddressManageInterface;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAddressManageController extends Controller
{
    public function __construct(protected AddressManageInterface $addressRepo)
    {

    }

    public function addAddress(CustomerAddressRequest $request)
    {

        // Check if the user is authenticated
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $validated_data = $request->validated();

        try {
            // Set the customer ID if authenticated
             $validated_data['customer_id'] = auth('api_customer')->user()->id;
            // Store the address using the repository
            $this->addressRepo->setAddress($validated_data);

            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => __('messages.save_success', ['name' => 'Address'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
            ], 500);
        }
    }

    public function updateAddress(CustomerAddressRequest $request)
    {
        // Check if the user is authenticated
        if (!auth('api_customer')->check()) {
            return unauthorized_response();
        }

        $validated_data = $request->validated();

        try {
            // Attempt to update the address using the repository
            $result = $this->addressRepo->updateAddress($request->id, $validated_data);

            if ($result !== true) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => $result,
                ], 404);
            }

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Address']),
            ], 200);

        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return response()->json([
                'status' => false,
                'status_code' => 500,
            ], 500);
        }
    }


    public function listAddresses(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $status = $request->input('status');
        $addresses = $this->addressRepo->getAddress($id, $type, $status);

        return response()->json(CustomerAddressResource::collection($addresses));
    }

    public function getAddressById(Request $request)
    {
        try {
            return response()->json(new CustomerAddressResource($this->addressRepo->getAddressById($request->id)));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
            ]);
        }
    }

    public function defaultAddress(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'is_default' => 'required|boolean',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 422);
        }
        try {
            CustomerAddress::findOrFail($request->id);
            // Set the customer ID if authenticated
            $request['customer_id'] = auth('api_customer')->user()->id;
            $this->addressRepo->handleDefaultAddress($request->all());
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Address'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
            ], 500);
        }
    }

    public function deleteAddress(Request $request)
    {
        try {
            $address = CustomerAddress::where('id', $request->id)
                ->where('customer_id', auth('api_customer')->user()->id)
                ->first();
            $address->delete();

            return response()->json([
                'message' => __('messages.delete_success', ['name' => 'Address']),
            ],200);
        } catch (\Exception $e) {
        }
    }
}
