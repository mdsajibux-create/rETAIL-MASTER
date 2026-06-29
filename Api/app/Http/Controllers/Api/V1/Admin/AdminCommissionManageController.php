<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\SystemCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCommissionManageController extends Controller
{
    public function settings(Request $request)
    {
        if ($request->isMethod('POST')) {
            // Validate input
            $validator = Validator::make($request->all(), [
                'zone_system_enable' => 'boolean',
                'order_shipping_charge' => 'nullable|numeric|min:0',
                'order_confirmation_by' => 'nullable',
                'order_include_tax_amount' => 'boolean',
                'order_tax' => 'nullable',
                'order_additional_charge_enable_disable' => 'nullable|boolean',
                'order_additional_charge_name' => 'nullable|string|max:255',
                'order_additional_charge_amount' => 'nullable|numeric|min:0',
                'deliveryman_earning_type' => 'nullable|string',
                'deliveryman_commission_type' => 'nullable|string',
                'deliveryman_commission_value' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();


            $commission_type = $request->get('commission_type');
            $commission_amount = $request->get('commission_amount');
            $shouldRound = shouldRound();


            if ($shouldRound && $commission_type === 'fixed' && is_float($commission_amount)) {
                return response()->json([
                    'message' => __('messages.should_round', ['name' => 'Commission']),
                ]);
            }

            // Update or create settings
            $systemCommission = SystemCharge::first();
            if (!$systemCommission) {
                $systemCommission = new SystemCharge();
            }

            $systemCommission->fill($validatedData);
            $systemCommission->save();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'settings Updated Successfully',
            ]);
        }

        // Handle GET request: Retrieve existing settings
        $response = SystemCharge::first();

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Commission settings not found',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }


}
