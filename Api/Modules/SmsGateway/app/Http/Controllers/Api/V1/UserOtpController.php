<?php

namespace Modules\SmsGateway\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Modules\Deliveryman\app\Models\DeliveryMan;
use Modules\SmsGateway\app\Models\UserOtp;
use Modules\SmsGateway\app\Services\Sms\SmsManager;
use Modules\SystemCore\app\Models\SettingOption;
use Propaganistas\LaravelPhone\PhoneNumber;
use Symfony\Component\HttpFoundation\Response;

class UserOtpController extends Controller
{
    /**
     * @throws \Exception
     */

    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $setting_options = SettingOption::where('option_name', 'otp_login_enabled_disable')
                ->value('option_value');

            if (empty($setting_options) || $setting_options === 'off') {
                return response()->json([
                    'message' => __('smsgateway::messages.setting_disabled', ['name' => 'Otp login']),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $next($request);
        });

    }

    public function sendOtp(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'region' => 'required|string',
            'phone' => 'required|string',
            'user_type' => 'required|string|in:customer,deliveryman',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $phone = new PhoneNumber($request->phone, $request->region);
        if (!$phone->isOfCountry($request->region)) {
            return response()->json([
                'message' => __('smsgateway::messages.invalid_region', ['name' => 'phone']),
            ]);
        }
        $formattedNumber = $this->formatPhoneNumber($request->get('phone'), $request->get('region'));

        $otp = rand(100000, 999999);
        $success = SmsManager::send($formattedNumber, $otp);

        if (!$success) {
            return response()->json([
                'message' => __('smsgateway::messages.send_failed', ['name' => 'Otp'])
            ], 500);
        }


        $userId = null;

        switch ($request->user_type) {
            case 'customer':
                $customer = Customer::firstOrCreate(['phone' => $formattedNumber]);
                $userId = $customer->id;
                break;

            case 'deliveryman':
                $deliveryMan = User::firstOrCreate([
                    'phone' => $formattedNumber,
                    'activity_scope' => 'delivery_level',
                ]);

                DeliveryMan::firstOrCreate([
                    'user_id' => $deliveryMan->id,
                ]);
                $userId = $deliveryMan->id;
                break;

            default:
                $user = User::firstOrCreate(['phone' => $formattedNumber]);
                $userId = $user->id;
        }

        UserOtp::updateOrCreate(
            [
                'user_id' => $userId,
                'user_type' => $request->user_type,
            ],
            [
                'otp_code' => $otp,
                'expired_at' => SmsManager::getExpireAt(),
            ]
        );

        return response()->json([
            'message' => __('smsgateway::messages.send_success', ['name' => 'Otp']),
        ]);
    }

    public function verifyOtp(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'region' => 'required|string',
            'otp' => 'required|string',
            'user_type' => 'required|string|in:customer,deliveryman',
            'firebase_device_token' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $formattedNumber = $this->formatPhoneNumber($request->get('phone'), $request->get('region'));

        $user = match ($request->user_type) {
            'customer' => Customer::where('phone', $formattedNumber)->first(),
            'deliveryman' => User::where([
                'phone' => $formattedNumber,
                'activity_scope' => 'delivery_level',
                'store_owner' => 0
            ])->first(),
            default => null,
        };

        if (!$user) {
            return response()->json([
                'message' => __('smsgateway::messages.data_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        $otp = UserOtp::where('user_id', $user->id)
            ->where('user_type', $request->user_type)
            ->where('otp_code', $request->otp)
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => __('smsgateway::messages.verification_failed', ['name' => 'Otp']),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($otp->expired_at < now()) {
            return response()->json([
                'message' => __('smsgateway::messages.expired', ['name' => 'Otp']),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $otp->delete();
        $user->update([
            'firebase_token' => $request->firebase_device_token,
        ]);

        if ($request->user_type === 'deliveryman') {
            // Check if the user's email is verified
            $email_verified = $user->hasVerifiedEmail();

            // Fetch permissions
            $permissions = $user->rolePermissionsQuery()
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->get();
            // Handle the "Remember Me" option
            $remember_me = $request->has('remember_me');

            // Set token expiration dynamically
            config(['sanctum.expiration' => $remember_me ? null : env('SANCTUM_EXPIRATION')]);
            $token = $user->createToken('auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)env('SANCTUM_EXPIRATION',60));
            $accessToken->save();
            // Build and return the response
            return response()->json([
                "status" => true,
                "status_code" => 200,
                "token" => $token->plainTextToken,
                'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                "deliveryman_id" => $user->id,
                "email_verified" => $email_verified,
                "activity_notification" => (bool)$user->activity_notification,
                "account_status" => $user->deactivated_at ? 'deactivated' : 'active',
                "role" => $user->getRoleNames()->first(),
            ], 200);
        }
        // Handle the "Remember Me" option
        $remember_me = $request->has('remember_me');

        // Set token expiration dynamically
        config(['sanctum.expiration' => $remember_me ? null : env('SANCTUM_EXPIRATION')]);
        $token = $user->createToken('customer_auth_token');
        $accessToken = $token->accessToken;
        $accessToken->expires_at = Carbon::now()->addMinutes((int)env('SANCTUM_EXPIRATION',60));
        $accessToken->save();

        return response()->json([
            "status" => true,
            "status_code" => 200,
            "message" => __('smsgateway::messages.login_success'),
            "token" => $token->plainTextToken,
            'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
            "email_verified" => (bool)$user->email_verified, // shorthand of -> $token->email_verified ? true : false
            "account_status" => $user->deactivated_at ? 'deactivated' : 'active',
            "marketing_email" => (bool)$user->marketing_email,
            "activity_notification" => (bool)$user->activity_notification,
        ]);
    }


    public function resendOtp(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'region' => 'required|string',
            'user_type' => 'required|string|in:customer,deliveryman',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $phone = new PhoneNumber($request->phone, $request->region);
        if (!$phone->isOfCountry($request->region)) {
            return response()->json([
                'message' => __('smsgateway::messages.invalid_region', ['name' => 'phone']),
            ]);
        }
        $formattedNumber = $this->formatPhoneNumber($request->get('phone'), $request->get('region'));

        // Find existing user by type
        $user = match ($request->user_type) {
            'customer' => Customer::where('phone', $formattedNumber)->first(),
            'deliveryman' => User::where([
                'phone' => $formattedNumber,
                'activity_scope' => 'delivery_level',
                'store_owner' => 0
            ])->first(),
            default => null,
        };

        if (!$user) {
            return response()->json([
                'message' => __('smsgateway::messages.data_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        // Check latest OTP created within last 60 seconds
        $lastOtp = UserOtp::where('user_id', $user->id)
            ->where('user_type', $request->user_type)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at > now()->subSeconds(60)) {
            $remaining = 60 - now()->diffInSeconds($lastOtp->created_at);
            return response()->json([
                'message' => __('smsgateway::messages.resend_wait', ['seconds' => $remaining]),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Generate and send new OTP
        $otp = rand(100000, 999999);
        $success = SmsManager::send($formattedNumber, $otp);

        if (!$success) {
            return response()->json([
                'message' => __('smsgateway::messages.send_failed', ['name' => 'Otp']),
            ], 500);
        }

        if (!$lastOtp) {
            return response()->json([
                'message' => __('smsgateway::messages.send_first', ['name' => 'Otp']),
            ]);
        }

        // Save new OTP
        UserOtp::where('user_id', $user->id)->update(
            [
                'user_type' => $request->user_type,
                'otp_code' => $otp,
                'expired_at' => SmsManager::getExpireAt(),
            ]);

        return response()->json([
            'message' => __('smsgateway::messages.send_success', ['name' => 'Otp']),
        ]);
    }

    private function formatPhoneNumber(string $msisdn, string $region)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $parsedNumber = $phoneUtil->parse($msisdn, $region);
        return $phoneUtil->format($parsedNumber, PhoneNumberFormat::E164);
    }

}
