<?php

namespace App\Repositories;

use App\Interfaces\AddressManageInterface;
use App\Models\CustomerAddress;

class AddressManageRepository implements AddressManageInterface
{
    public function __construct(protected CustomerAddress $address)
    {
    }

    /**
     * @param CustomerAddress $address
     */
    public function setAddress(array $data)
    {

        if (isset($data['is_default']) && $data['is_default']) {
            $this->address
                ->where('customer_id', $data['customer_id'])
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

       $this->address->create($data);

       return true;

    }

    public function updateAddress(int $id, array $data)
    {
        try {
            $customer_id =  auth('api_customer')->user()->id;

            // Handle the default address logic
            if (isset($data['is_default']) && $data['is_default']) {
                $this->address
                    ->where('customer_id', $customer_id)
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }


            // Retrieve the specific address by ID
            $address = $this->address->where('id', $id)
                ->where('customer_id', $customer_id)
                ->first();

            if (!$address) {
               return false;
            }

            // Update the address
            $address->update($data);

            return true;

        } catch (\Exception $e) {
            // Return the error message
        }
    }


    public function getAddress(?string $id, ?string $type, ?string $status)
    {
        try {

            $customerId = auth('api_customer')->user()->id;

            // Build query to get addresses
            $query = $this->address
                ->with(['zone', 'state', 'city', 'area'])
                ->where('customer_id', $customerId);

            // Apply filters only if they are provided and not empty
            if (isset($id)) {
                $query->where('id', $id);
            }

            if (isset($type)) {
                $query->where('type', $type);
            }

            if (isset($status)) {
                $query->where('status', $status);
            }

            $addresses = $query->latest()->get();

            return $addresses;

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
            ], 500);
        }
    }

    public function getAddressById(int $id)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }
        try {
            $customerId = auth('api_customer')->user()->id;

            $address = $this->address
                ->with(['zone', 'state', 'city', 'area'])
                ->where('customer_id', $customerId)
                ->where('id', $id)
                ->first();

            return $address;

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
            ]);
        }
    }

    public function handleDefaultAddress(array $data)
    {
        try {
            if (isset($data['is_default']) && $data['is_default']) {
                $this->address
                    ->where('customer_id', $data['customer_id'])
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }
            $this->address->where('id', $data['id'])
                ->where('customer_id', $data['customer_id'])
                ->update(['is_default' => $data['is_default']]);
            return true;
        } catch (\Exception $e) {
            // Return the error message
        }
    }
}