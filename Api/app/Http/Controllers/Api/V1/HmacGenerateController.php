<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HmacGenerateController extends Controller
{
    public function generateHmac(Request $request)
    {
        // check auth
         $apiUser = Auth::guard('api')->user();
        if($apiUser && $apiUser->activity_scope === 'branch_level'){
            $user =  Auth::guard('api')->user();
            $rules  = [
                'store_id' => 'nullable|integer',
                'wallet_history_id' => 'nullable|integer',
            ];
            $reference_id = $request->store_id ?? $request->wallet_history_id;
        }else{
            $user = Auth::guard('api_customer')->user();
            $rules  = [
                'order_id' => 'nullable|integer',
                'wallet_history_id' => 'nullable|integer',
            ];
            $reference_id = $request->order_id ?? $request->wallet_history_id;
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // generate timestamp here
        $timestamp = time();
        $payload   = "{$user->email}|{$reference_id}|{$timestamp}";
        $hmac      = hash_hmac(config('hmac.algo'), $payload, config('hmac.secret'));


        return response()->json([
            'hmac' => $hmac,
            'timestamp' => $timestamp,
        ]);
    }
}
