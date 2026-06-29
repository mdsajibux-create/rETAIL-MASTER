<?php

namespace Modules\SmsGateway\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\SmsGateway\app\Models\SmsProvider;
use Vonage\Verify\Check;

class SmsProviderController extends Controller
{
    public function smsProviderSettingUpdate(Request $request){
        if ($request->method() == "POST") {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|in:nexmo,twilio',
                'expire_time' => 'required|numeric',
                'credentials' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Optional: Add per-provider credential validation
            if ($request->name === 'twilio') {
                $validator = Validator::make($request->credentials, [
                    'twilio_sid' => 'required|string',
                    'twilio_auth_key' => 'required|string',
                ]);
            } elseif ($request->name === 'nexmo') {
                $validator = Validator::make($request->credentials, [
                    'nexmo_api_key' => 'required|string',
                    'nexmo_api_secret' => 'required|string',
                ]);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Credential validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            SmsProvider::updateOrCreate(
                ['slug' => $request->name],
                [
                    'name' => ucfirst($request->name),
                    'slug' => $request->name,
                    'expire_time' => $request->expire_time,
                    'credentials' => config('demoMode.check') ? null : json_encode($request->credentials),
                    'status' => 1,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'SMS Provider updated successfully.',
            ]);
        }

        $sms_gateway = SmsProvider::all();

        // check demo mode is true
        if (config('demoMode.check')){
            foreach ($sms_gateway as $sms_provider) {
                // Decode credentials to array
                $credentials = json_decode($sms_provider->credentials, true);

                if (is_array($credentials)) {
                    // Mask all keys
                    foreach ($credentials as $key => $value) {
                        $credentials[$key] = '*** DEMO ***';
                    }
                    // Reassign masked credentials back to the model
                    $sms_provider->credentials = $credentials;
                }

            }
        }

        return response()->json([
            'status' => true,
            'data' => $sms_gateway,
        ]);

    }

    public function smsProviderStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'name' => 'required|string|in:nexmo,twilio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Activate the selected one
        $smsProvider = SmsProvider::where('name', $request->name)->first();
        $status = $request->status;

        if ($smsProvider) {
            if ($status == 1) {
                $status = 1;
            } else {
                $status = 0;
            }

            SmsProvider::where('status', 1)->update(['status' => 0]);

            $smsProvider->update([
                'status' => $status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'SMS Provider activated successfully.',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'SMS Provider not found.',
        ], 404);


    }
    public function smsProviderLoginStatus(Request $request){
        if ($request->method() == "POST") {
            //  updates sms settings
            com_option_update('otp_login_enabled_disable', $request->otp_login_enabled_disable);
            return response()->json([
                'status' => true,
                'message' => 'OTP login status updated successfully.',
            ]);
        }

       $otp_login_enabled_disable = com_option_get('otp_login_enabled_disable');

        return response()->json([
            'status' => true,
            'otp_login_enabled_disable' => $otp_login_enabled_disable,
        ]);

    }

}
