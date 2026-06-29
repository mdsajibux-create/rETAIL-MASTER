<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\ImageModifier;
use App\Http\Controllers\Api\V1\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{

    public $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:32',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::where('email', $request->email)
            ->where('activity_scope', 'branch_level')
            ->where('status', 1)
            ->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => 'User not found',
            ], 422);
        }


        // Check if the user's account is deleted
        if ($user->deleted_at !== null) {
            return response()->json([
                'error' => 'Your account has been deleted. Please contact support.'
            ], Response::HTTP_GONE);
        }

        // Check if the user's account
        if ($user->status === 0) {
            return response()->json([
                'error' => 'Your account has been deactivated. Please contact support.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->status === 2) {
            return response()->json([
                'error' => 'Your account has been suspended by the admin.'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ["success" => false, "token" => null, "permissions" => []];
        }

        // Handle the "Remember Me" option
        $remember_me = $request->has('remember_me');
        // Set token expiration dynamically
        config(['sanctum.expiration' => $remember_me ? null : 1440]);

        $token = $user->createToken('auth_token');
        $accessToken = $token->accessToken;
        $accessToken->expires_at = Carbon::now()->addMinutes((int)1440);
        $accessToken->save();

        // update firebase device token
        $user->update([
            'firebase_token' => $request->firebase_device_token,
        ]);


        return [
            "success" => true,
            "message" => __('messages.login_success'),
            "token" => $token->plainTextToken,
            'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            "email_verification_settings" => com_option_get('com_user_email_verification', null, false) ?? 'off',
            'phone' => $user->phone,
            'image_url' => ImageModifier::generateImageUrl($user->image),
            "email_verified" => (bool)$user->email_verified,
            "branch_id" => $user->branch_id,
            "role" => $user->getRoleNames()

        ];
    }


}
