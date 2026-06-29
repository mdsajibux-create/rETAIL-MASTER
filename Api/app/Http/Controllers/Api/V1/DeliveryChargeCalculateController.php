<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\DeliveryChargeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryChargeCalculateController extends Controller
{
    public function calculateDeliveryCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required',
            'customer_latitude' => 'required|numeric',
            'customer_longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $zone_id = $request->input('zone_id');
        $customerLat = $request->input('customer_latitude');
        $customerLng = $request->input('customer_longitude');

        $results = [];

            try {
                $charge = DeliveryChargeHelper::calculateDeliveryCharge($zone_id, $customerLat, $customerLng);

                $results[] = [
                    'zone_id' => $zone_id,
                    'delivery_charge' => $charge,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'zone_id' => $zone_id,
                ];
            }

        return response()->json($results);
    }
}
