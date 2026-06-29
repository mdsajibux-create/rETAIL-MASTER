<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\DeliveryChargeHelper;
use App\Http\Resources\Admin\OtherChargeInfoResource;
use App\Models\SystemCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettings\app\Models\ProductType;

class OtherChargeInfoController extends Controller
{
    public function otherChargeInformation(){

        $data = SystemCharge::first();
        $product_type = ProductType::select('name','type', 'description', 'charge_status', 'charge_name', 'charge_amount', 'charge_type')->where('status', 1)->get();

        // attach dynamically
        $data->product_type = $product_type;

        return response()->json([
            'success' => true,
            'tax_info' => new OtherChargeInfoResource($data)
        ]);
    }

    public function checkoutInfo(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|integer',
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

        // Handle delivery charge calculation
        $zone_id = $request->input('zone_id');
        $customerLat = $request->input('customer_latitude');
        $customerLng = $request->input('customer_longitude');
        $deliveryCharge = DeliveryChargeHelper::calculateDeliveryCharge($zone_id, $customerLat, $customerLng);

        // Handle other charges
        $otherCharges = SystemCharge::first();
        $otherChargeInfo = ($otherCharges && $otherCharges->order_additional_charge_enable_disable)
            ? new OtherChargeInfoResource($otherCharges)
            : 'no additional charge';

        return response()->json([
            'success' => true,
            'delivery_charge' => $deliveryCharge,
            'other_charge_info' => $otherChargeInfo
        ]);
    }


}
