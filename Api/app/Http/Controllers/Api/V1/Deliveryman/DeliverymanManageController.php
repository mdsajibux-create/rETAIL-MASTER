<?php

namespace App\Http\Controllers\Api\V1\Deliveryman;

use App\Http\Controllers\Api\V1\Controller;
use App\Interfaces\DeliverymanManageInterface;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Services\MediaService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Deliveryman\app\Models\DeliveryMan;
use Modules\Deliveryman\app\Models\DeliverymanDeactivationReason;
use Modules\Order\app\Models\Order;
use Spatie\Permission\Models\Role;

class DeliverymanManageController extends Controller
{
    protected $mediaService;

    public function __construct(protected DeliverymanManageInterface $deliverymanRepo, MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:32',
            'phone' => 'required|unique:users,phone',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'area_id' => 'required|exists:store_areas,id',
            'identification_type' => 'required|in:nid,passport,driving_license',
            'identification_number' => 'required|string|unique:delivery_men,identification_number',
            'identification_photo_front' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:1024',
            'identification_photo_back' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Email already exists'
            ], 422);
        }

        try {

            // By default role ---->
            $roles = Role::where('available_for', 'delivery_level')->pluck('name');

            // When admin creates a Deliveryman ---->
            if (isset($request->roles)) {
                $roles[] = isset($request->roles->value) ? $request->roles->value : $request->roles;
            }

            $uploadPath = 'uploads/deliveryman/verification';
            $storedPaths = [];

            foreach (['front', 'back'] as $side) {
                $key = "identification_photo_{$side}";

                if ($request->hasFile($key)) {
                    $file = $request->file($key);

                    // Optional: generate a unique and meaningful filename
                    $fileName = Str::uuid() . '_' . $side . '.' . $file->getClientOriginalExtension();

                    // Store and collect the relative path
                    $storedPaths[$key] = $file->storeAs($uploadPath, $fileName, 'public');
                }
            }


            // Create the user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'slug' => username_slug_generator($request->first_name, $request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'activity_scope' => 'delivery_level',
                'branch_id' => null,
                'status' => 1,
            ]);

            Deliveryman::create([
                'user_id' => $user->id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'zone_id' => $request->zone_id,
                'identification_type' => $request->identification_type,
                'identification_number' => $request->identification_number,
                'identification_photo_front' => $storedPaths['identification_photo_front'] ?? null,
                'identification_photo_back' => $storedPaths['identification_photo_back'] ?? null,
                'status' => 'pending',
            ]);

            // Assign roles to the user
            $user->assignRole($roles);

            return response()->json([
                "status" => true,
                "status_code" => 200,
                "message" => __('messages.registration_success', ['name' => 'Deliveryman']),
                "token" => $user->createToken('auth_token')->plainTextToken,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                "permissions" => $user->getPermissionNames(),
                "role" => $user->getRoleNames(),
                "next_stage" => "2"
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "message" => __('messages.validation_failed', ['name' => 'Deliveryman']),
                "errors" => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                "message" => __('messages.error'),
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {

            if ($request->social_login && $request->platform === 'mobile') {
                $validator = Validator::make($request->all(), [
                    'access_token' => 'required|string',
                    'type' => 'required|string|in:facebook,google',
                    'role' => 'required|string|in:deliveryman',
                    'firebase_device_token' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                $accessToken = $request->access_token;
                $firebaseToken = $request->firebase_device_token;
                $type = $request->type;
                $role = $request->role;

                return socialLogin($accessToken, $type, $firebaseToken, $role);
            }

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8|max:32',
                'social_login' => 'nullable|boolean',
                'platform' => 'nullable|string|in:web,mobile',
            ]);

            // Attempt to find the user
            $user = User::where('email', $request->email)
                ->where('activity_scope', 'delivery_level') // Uncomment if needed
                ->where('status', 1)
                ->where('deleted_at', null)
                ->first();

            // Check if the user exists and if the password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    "status" => false,
                    "message" => __('messages.login_failed', ['name' => 'User']),
                    "token" => null,
                    "permissions" => [],
                ], 401);
            }

            // Check if the user's email is verified
            $email_verified = $user->hasVerifiedEmail();

            // Fetch permissions
            $permissions = $user->rolePermissionsQuery()
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->get();

            // update firebase device token
            $user->update([
                'firebase_token' => $request->firebase_device_token,
            ]);
            // Handle the "Remember Me" option
            $remember_me = $request->has('remember_me');

            // Set token expiration dynamically
            config(['sanctum.expiration' => $remember_me ? null : env('SANCTUM_EXPIRATION')]);

            $token = $user->createToken('auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)config('sanctum.expiration', 60));
            $accessToken->save();

            // Build and return the response
            return response()->json([
                "status" => true,
                "status_code" => 200,
                "message" => __('messages.login_success'),
                "token" => $token->plainTextToken,
                'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                "deliveryman_id" => $user->id,
                "email_verified" => $email_verified,
                "activity_notification" => (bool)$user->activity_notification,
                "account_status" => $user->deactivated_at ? 'deactivated' : 'active',
                "role" => $user->getRoleNames()->first(),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation error response
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => __('messages.validation_failed', ['name' => 'Deliveryman']),
                "errors" => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            // Handle other exceptions
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => __('messages.error'),
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        $plainToken = $request->bearerToken();

        if (!$plainToken) {
            return response()->json([
                'status' => false,
                'message' => 'Access token missing.',
            ], 401);
        }

        $tokenId = explode('|', $plainToken)[0];

        $token = PersonalAccessToken::find($tokenId);

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token not found.',
            ], 401);
        }

        $user = $token->tokenable;

        if ($token->expires_at && Carbon::parse($token->expires_at)->lt(now())) {
            $token->delete();
            $newToken = $user->createToken('auth_token');
            $accessToken = $newToken->accessToken;
            $accessToken->expires_at = now()->addMinutes((int)config('sanctum.expiration', 60));
            $accessToken->save();

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed.',
                'token' => $newToken->plainTextToken,
                'new_expires_at' => $accessToken->expires_at?->format('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Token is still valid.',
            'token' => $plainToken,
            'expires_at' => $token->expires_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function activeDeactiveAccount(Request $request)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:activate,deactivate',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deliveryman = auth('api')->user();
        $existing_orders = Order::where('confirmed_by', $deliveryman->id)
            ->whereIn('status', ['processing', 'shipped'])
            ->exists();
        if ($request->type == 'deactivate') {
            $alreadyDeactivated = $deliveryman->deactivated_at;
            if ($alreadyDeactivated) {
                return response()->json([
                    'message' => __('messages.account_already_deactivated')
                ], 422);
            }
            if ($existing_orders) {
                return response()->json([
                    'message' => __('messages.deliveryman_active_order_exists')
                ], 422);
            }
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            DeliverymanDeactivationReason::create([
                'deliveryman_id' => $deliveryman->id,
                'reason' => $request->reason ?? '',
                'description' => $request->description ?? ''
            ]);

            $deliveryman->update([
                'deactivated_at' => now(),
                'status' => 0,
            ]);

            return response()->json([
                'message' => __('messages.account_deactivate_successful')
            ], 200);
        }

        if ($request->type == 'activate') {
            $alreadyActivated = $deliveryman->deactivated_at == null;
            if ($alreadyActivated) {
                return response()->json([
                    'message' => __('messages.account_already_activated')
                ], 422);
            }
            $activate = $deliveryman->update([
                'deactivated_at' => null,
            ]);
            if ($activate) {
                return response()->json([
                    'message' => __('messages.account_activate_successful')
                ], 200);
            } else {
                return response()->json([
                    'message' => __('messages.account_activate_failed')
                ], 500);
            }
        } else {
            return response()->json([
                'message' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deliveryman = auth('api')->user();
        $deliveryman_details = DeliveryMan::where('user_id', $deliveryman->id)->first();
        $existing_orders = Order::where('confirmed_by', $deliveryman->id)
            ->whereIn('status', ['processing', 'shipped'])
            ->exists();
        if ($existing_orders) {
            return response()->json([
                'message' => __('messages.deliveryman_active_order_exists')
            ], 422);
        }
        try {
            DeliverymanDeactivationReason::create([
                'deliveryman_id' => $deliveryman->id,
                'reason' => $request->reason,
                'description' => $request->description
            ]);
            $deliveryman_details?->delete();
            $deliveryman->wallet()->delete();
            $deliveryman->delete(); // Soft delete
            $deliveryman->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.account_delete_successful')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function activityNotificationToggle()
    {
        $deliveryman = auth('api')->user();
        $deliveryman->activity_notification = !$deliveryman->activity_notification;
        $deliveryman->save();
        return response()->json([
            'message' => __('messages.account_activity_notification_update_success'),
            'status' => $deliveryman->activity_notification
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => $validator->errors()
            ], 422);
        }

        $result = $this->change_password($request->only(['old_password', 'new_password']));

        if ($result === 'incorrect_old_password') {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Incorrect password!'
            ], 400);
        }

        if (!$result) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.password_update_failed')
            ], 500);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.password_update_successful')
        ]);
    }

    private function change_password(array $data)
    {
        $deliveryman = User::where('email', auth('api')->user()->email)->first();

        if (!$deliveryman || !Hash::check($data['old_password'], $deliveryman->password)) {
            return 'incorrect_old_password';
        }

        try {
            $deliveryman->password = Hash::make($data['new_password']);
            $deliveryman->password_changed_at = now();
            $deliveryman->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function sendVerificationEmail(Request $request)
    {
        if (!auth('api')->check()) {
            return unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $user = auth('api')->user();

        try {
            $token = rand(100000, 999999);
            $user->email_verify_token = $token;
            $user->save();
            // Send email verification
            Mail::to($request->email)->send(new EmailVerificationMail($user));

            return response()->json(['status' => true, 'message' => 'Verification email sent.']);
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function updateEmail(Request $request)
    {
        if (!auth('api')->check()) {
            return unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'token' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        try {

            $userId = auth('api')->id();
            $user = User::find($userId);
            if ($user && $user->email_verify_token == $request->token) {
                $user->update([
                    'email' => $request->email,
                    'email_verify_token' => null,
                ]);
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => __('messages.update_success', ['name' => 'Deliveryman email']),
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'status_code' => 500,
                    'message' => __('messages.update_failed', ['name' => 'Deliveryman email']),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function isAvailableToggle()
    {

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => __('messages.authorization_invalid')
            ], 401);
        }

        if ($user->activity_scope !== 'delivery_level') {
            return response()->json([
                'message' => __('messages.access_denied')
            ], 403);
        }

        $activeOrders = Order::with(['orderMaster.orderAddress', 'store'])
            ->where('status', '!=', 'delivered') // Exclude delivered orders
            ->whereHas('orderDeliveryHistory', function ($query) use ($user) {
                $query->where('deliveryman_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->latest()
            ->first();
        if (empty($activeOrders)) {
            $user->is_available = !$user->is_available;
            $user->save();

            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Availability status']),
                'is_available' => $user->is_available
            ]);
        } else {
            return response()->json([
                'message' => __('messages.deliveryman_has_active_orders'),
            ], 422);
        }
    }
}
