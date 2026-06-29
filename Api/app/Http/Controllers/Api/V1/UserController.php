<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role as UserRole;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\Deliveryman\DeliverymanDetailsResource;
use App\Http\Resources\UserAllDetailsResource;
use App\Http\Resources\UserResource;
use App\Mail\EmailVerificationMail;
use App\Models\Customer;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\MediaService;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{

    public $repository;
    protected $mediaService;

    public function __construct(UserRepository $repository, MediaService $mediaService)
    {
        $this->repository = $repository;
        $this->mediaService = $mediaService;
    }


    public function redirectToFacebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $role = $request->role ?? 'user'; // Default to 'user' if not provided
        return Socialite::driver('facebook')
            ->with([
                'client_id' => com_option_get('com_facebook_app_id'),
                'client_secret' => com_option_get('com_facebook_client_secret'),
                'redirect_uri' => com_option_get('com_facebook_client_callback_url'),
                'state' => $role
            ])
            ->scopes(['email']) // Request the 'email' scope
            ->stateless()
            ->redirect();
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            $user = Socialite::driver('facebook')->with([
                    'client_id' => com_option_get('com_facebook_app_id'),
                    'client_secret' => com_option_get('com_facebook_client_secret'),
                    'redirect_uri' => com_option_get('com_facebook_client_callback_url'),
                ])
                ->stateless()
                ->user();

            $facebook_id = $user->id;
            $email = $user->email;
            $name = $user->name;

            // Retrieve the role from the OAuth state parameter
            $role = $request->input('state', 'user'); // Default to 'user'
            $frontendUrl = config('app.frontend_url');

            // Find or create a user in the database
            if ($role == 'customer') {
                $existingUser = Customer::where('facebook_id', $facebook_id)
                    ->orWhere('email', $email)->first();
            } elseif ($role == 'deliveryman') {
                $existingUser = User::where('facebook_id', $facebook_id)
                    ->orWhere('email', $email)->first();
            } else {
                $existingUser = User::where('facebook_id', $facebook_id)
                    ->orWhere('email', $email)->first();
            }

            if ($existingUser) {
                // Update Facebook ID if missing
                if (!$existingUser->facebook_id) {
                    $existingUser->update(['facebook_id' => $facebook_id]);
                }

                // Generate a Sanctum token for API access
                $token = $existingUser->createToken('social_auth_token');
                $accessToken = $token->accessToken;
                $accessToken->expires_at = Carbon::now()->addMinutes((int)config('sanctum.expiration', 60));
                $accessToken->save();

                return redirect()->away($frontendUrl . '?' . http_build_query([
                        'success' => true,
                        'message' => __('auth.social.login'),
                        "token" => $token->plainTextToken,
                        'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                        'email_verified' => (bool)$existingUser->email_verified,
                        'account_status' => $existingUser->deactivated_at ? 'deactivated' : 'active',
                        'marketing_email' => (bool)$existingUser->marketing_email,
                        'activity_notification' => (bool)$existingUser->activity_notification,
                    ]));
            }

            // Create a new user
            if ($role == 'customer') {
                $newUser = Customer::create([
                    'first_name' => $name,
                    'email' => $email,
                    'facebook_id' => $facebook_id,
                    'password' => Hash::make('123456dummy'), // Dummy password
                    'email_verified' => 1,
                    'email_verified_at' => Carbon::now(),
                ]);
            } elseif ($role == 'deliveryman') {
                $newUser = User::create([
                    'first_name' => $name,
                    'email' => $email,
                    'slug' => username_slug_generator($name),
                    'facebook_id' => $facebook_id,
                    'password' => Hash::make('123456dummy'),
                    'activity_scope' => 'delivery_level',
                    'branch_id' => null,
                    'status' => 0,
                    'email_verified' => 1,
                    'email_verified_at' => Carbon::now(),
                ]);
            } else {
                $newUser = User::create([
                    'first_name' => $name,
                    'email' => $email,
                    'slug' => username_slug_generator($name),
                    'facebook_id' => $facebook_id,
                    'password' => Hash::make('123456dummy'),
                    'activity_scope' => null,
                    'branch_id' => null,
                    'status' => 1,
                    'email_verified' => 1,
                    'email_verified_at' => Carbon::now(),
                ]);
            }

            // Generate a Sanctum token for the new user
            $token = $newUser->createToken('social_auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)config('sanctum.expiration', 60));
            $accessToken->save();

            return redirect()->away($frontendUrl . '?' . http_build_query([
                    'success' => true,
                    'message' => __('auth.social.login'),
                    "token" => $token->plainTextToken,
                    'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                    'email_verified' => $newUser->email_verified,
                    'account_status' => $newUser->deactivated_at ? 'deactivated' : 'active',
                    'marketing_email' => $newUser->marketing_email,
                    'activity_notification' => $newUser->activity_notification,
                ]));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook authentication failed!',
            ], 500);
        }
    }


    public function redirectToGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $role = $request->role ?? 'user'; // Default to 'user' if not provided
        /** @var \Laravel\Socialite\Two\GoogleProvider */
        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->with([
                'client_id' => com_option_get('com_google_app_id'),
                'redirect_uri' => com_option_get('com_google_client_callback_url'),
                'prompt' => 'select_account',  // Forces Google to ask for account selection
                'state' => $role
            ])
            ->stateless()
            ->redirect();
    }


    public function handleGoogleCallback(Request $request)
    {
        $user = Socialite::driver('google')->with([
            'client_id' => com_option_get('com_google_app_id'),
            'client_secret' => com_option_get('com_google_client_secret'),
            'redirect_uri' => com_option_get('com_google_client_callback_url'),
        ]);

        $user->stateless()->user();
        $google_id = $user->user()->id;
        $google_email = $user->user()->email;
        $name = $user->user()->name;
        // Retrieve the role from the OAuth state parameter
        $role = $request->input('state', 'user'); // Default to 'user'
        if ($role == 'customer') {
            $frontendUrl = config('app.frontend_url');
        } elseif ($role == 'seller') {
            $frontendUrl = config('app.frontend_url') . '/seller/dashboard';
        }
        // Find or create a user in the database
        if ($role == 'customer') {
            $existingUser = Customer::where('google_id', $google_id)
                ->orWhere('email', $google_email)->first();
        } else {
            $existingUser = User::where('google_id', $google_id)
                ->orWhere('email', $google_email)->first();
        }

        if ($existingUser) {
            // Update the user's Google ID if it's missing
            if (!$existingUser->google_id) {
                $existingUser->update(['google_id' => $google_id]);
            }

            // Generate a Sanctum token for the existing user
            $token = $existingUser->createToken('social_auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)config('sanctum.expiration', 60));
            $accessToken->save();
            return redirect()->away($frontendUrl . '?' . http_build_query([
                    'success' => true,
                    'message' => __('auth.social.login'),
                    "token" => $token->plainTextToken,
                    'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                    'email_verified' => (bool)$existingUser->email_verified,
                    'account_status' => $existingUser->deactivated_at ? 'deactivated' : 'active',
                    'marketing_email' => (bool)$existingUser->marketing_email,
                    'activity_notification' => (bool)$existingUser->activity_notification,
                ]));
        } else {
            // Create a new user in the database
            if ($role == 'customer') {
                $newUser = Customer::create([
                    'first_name' => $name,
                    'email' => $google_email,
                    'google_id' => $google_id,
                    'email_verified' => 1,
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('123456dummy'),
                ]);
            }  elseif ($role == 'deliveryman') {
                $newUser = User::create([
                    'first_name' => $name,
                    'email' => $google_email,
                    'slug' => username_slug_generator($name),
                    'google_id' => $google_id,
                    'password' => Hash::make('123456dummy'),
                    'activity_scope' => 'delivery_level',
                    'branch_id' => null,
                    'email_verified' => 1,
                    'email_verified_at' => Carbon::now(),
                    'status' => 1,
                ]);
            } else {
                $newUser = User::create([
                    'first_name' => $name,
                    'email' => $google_email,
                    'slug' => username_slug_generator($name),
                    'google_id' => $google_id,
                    'password' => Hash::make('123456dummy'),
                    'activity_scope' => null,
                    'branch_id' => null,
                    'status' => 0,
                ]);
            }

            // Generate a Sanctum token for the new user
            $token = $newUser->createToken('social_auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)config('sanctum.expiration', 60));
            $accessToken->save();

            return redirect()->away($frontendUrl . '?' . http_build_query([
                    'success' => true,
                    'message' => __('auth.social.login'),
                    "token" => $token->plainTextToken,
                    'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                    'email_verified' => $newUser->email_verified,
                    'account_status' => $newUser->deactivated_at ? 'deactivated' : 'active',
                    'marketing_email' => $newUser->marketing_email,
                    'activity_notification' => $newUser->activity_notification,
                ]));
        }
    }


    public function login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8|max:32',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

            $user = User::where('email', $request->email)
                ->where('activity_scope', 'system_level')
                ->where('status', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => 'User is not an admin!'
                ],422);
            }

            // Check if the user exists and if the password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    "status" => false,
                    "message" => __('messages.login_failed', ['name' => 'Admin']),
                    "token" => null,
                    "permissions" => [],
                ], 401);
            }

            // Check if the user's email is verified
            $email_verified = $user->hasVerifiedEmail();

            // Fetch permissions
            $user->rolePermissionsQuery()
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->get();
            // Handle the "Remember Me" option
            $remember_me = $request->has('remember_me');

            // Set token expiration dynamically
            config(['sanctum.expiration' => $remember_me ? null : 1440]);

            $token = $user->createToken('auth_token');
            $accessToken = $token->accessToken;
            $accessToken->expires_at = Carbon::now()->addMinutes((int)1440);
            $accessToken->save();

           //  update firebase device token
            $user->update([
                'firebase_token' => $request->firebase_device_token,
            ]);

            return response()->json([
                "status" => true,
                "status_code" => 200,
                "message" => __('messages.login_success'),
                "token" => $token->plainTextToken,
                'expires_at' => $accessToken->expires_at->format('Y-m-d H:i:s'),
                "email_verified" => $email_verified,
                "role" => $user->getRoleNames()->first(),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => __('messages.validation_failed', ['name' => 'Admin']),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => __('messages.error'),
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
            ], 404);
        }
        $user = $token->tokenable;

        if ($token->expires_at && Carbon::parse($token->expires_at)->lt(now())) {
            $token->delete();
            $newToken = $user->createToken('auth_token');
            $accessToken = $newToken->accessToken;
            $accessToken->expires_at = now()->addMinutes((int)1440);
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

    public function me(Request $request)
    {
        return new UserResource(auth()->guard('api')->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return true;
        }
        $request->user()->currentAccessToken()->delete();
        return $this->success(__('auth.logout'));
    }

    public function register(UserCreateRequest $request)
    {
        try {
            // Prevent unauthorized role assignment
            $notAllowedRoles = [UserRole::SUPER_ADMIN];

            if ((isset($request->roles->value) && in_array($request->roles->value, $notAllowedRoles)) ||
                (isset($request->roles) && in_array($request->roles, $notAllowedRoles))
            ) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }

            // Fetch roles available for customer-level
            $roles = Role::where('available_for', 'customer_level')->pluck('name');

            if (isset($request->roles)) {
                $roles[] = isset($request->roles->value) ? $request->roles->value : $request->roles;
            }

            // Create the user
            $user = $this->repository->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'slug' => username_slug_generator($request->first_name, $request->last_name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 1
            ]);

            // Assign roles to the user
            $user->assignRole($roles);

            // Return a successful response with the token and permissions
            return response()->json([
                "status" => true,
                "status_code" => 200,
                "message" => __('messages.registration_success', ['name' => 'Customer']),
                "token" => $user->createToken('auth_token')->plainTextToken,
                "permissions" => $user->getPermissionNames(),
                "role" => $user->getRoleNames()->first()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "message" => __('messages.validation_failed', ['name' => 'Customer']),
                "errors" => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => __('messages.error'),
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleUserStatus(Request $request)
    {
        $userToToggle = User::findOrFail($request->id);
        $user = $request->user();
        if ($user && $user->hasPermissionTo(UserRole::SUPER_ADMIN) && $user->id != $request->id) {
            $userToToggle->status = !$userToToggle->status;
            $userToToggle->save();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'status' => $userToToggle->status
            ]);
        }
        throw new AuthorizationException(NOT_AUTHORIZED);
    }

    public function banUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(UserRole::SUPER_ADMIN) && $user->id != $request->id) {
                $banUser = User::find($request->id);
                $banUser->status = 0;
                $banUser->save();
                return $banUser;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (Exception $th) {
            throw new AuthorizationException(SOMETHING_WENT_WRONG);
        }
    }

    public function activeUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(UserRole::SUPER_ADMIN) && $user->id != $request->id) {
                $activeUser = User::find($request->id);
                $activeUser->status = 1;
                $activeUser->save();
                return $activeUser;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (Exception $th) {
            throw new AuthorizationException(SOMETHING_WENT_WRONG);
        }
    }


    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()
            ], 422);
        }
        try {
            $result = $this->sendVerificationEmail($request->email);

            if (!$result) {
                return response()->json([
                    'status' => false,
                    'status_code' => 500,
                    'message' => __('messages.data_not_found')
                ], 404);
            }
            return response()->json(['status' => true, 'message' => 'Verification email sent.']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function sendVerificationEmail(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        try {
            $token = rand(100000, 999999);
            $user->email_verify_token = $token;
            $user->save();
            // Send email verification
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function verifyToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $validator->errors()
            ]);
        }

        $result = $this->verify_token($request->all());

        if (!$result) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.token.invalid')
            ], 400);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.token.verified')
        ]);
    }

    private function verify_token(array $data)
    {
        $user = User::where('email', $data['email'])
            ->where('email_verify_token', $data['token'])
            ->first();

        if (!$user) {
            return false;
        }

        try {
            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "status_code" => 422,
                "errors" => $validator->errors()
            ], 422);
        }

        $result = $this->reset_password($request->all());

        if (!$result) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.token.invalid')
            ], 400);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.password_update_successful')
        ]);
    }

    private function reset_password(array $data)
    {
        $user = User::where('email', $data['email'])
            ->where('email_verify_token', $data['token'])
            ->first();
        if (!$user) {
            return false;
        }
        try {
            $user->update([
                'password' => Hash::make($data['password']),
                'password_changed_at' => now(),
                'email_verify_token' => null
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function assignRole(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        if (isset($request->roles)) {
            $user->syncRoles($request->roles);
        }
        return redirect()->route('users')->with('success', 'Role assign successfully!');
    }

    public function assignPermissions(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        if (isset($request->permissions)) {
            $user->syncPermissions($request->permissions);
        }
        return response()->json([
            'success' => true,
            'message' => 'PermissionKey assign successfully!',
        ]);
    }

    public function userProfile()
    {
        try {
            if (!auth()->guard('api')->user()) {
                return unauthorized_response();
            }

            $userId = auth('api')->id();
            $user = User::findOrFail($userId);

            if ($user->isDeliveryman()) {
                $user = User::with('deliveryman')->findOrFail($userId);
                return response()->json(new DeliverymanDetailsResource($user));
            }

            return response()->json(new UserAllDetailsResource($user));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function userProfileUpdate(Request $request)
    {
        try {
            if (!auth()->guard('api')->user()) {
                return unauthorized_response();
            }

            $userId = auth('api')->id();
            $user = User::findOrFail($userId);

            if ($user) {
                if ($user->isDeliveryman()) {
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
                    $user->update([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'slug' => username_slug_generator($request->first_name, $request->last_name),
                        'image' => $request->image,
                        'phone' => $request->phone,
                        'activity_scope' => 'delivery_level',
                        'store_owner' => 0,
                    ]);
                    $deliverymanDetails = DeliveryMan::where('user_id', $user->id);
                    $deliverymanDetails->update([
                        'vehicle_type_id' => $request->vehicle_type_id,
                        'area_id' => $request->area_id,
                        'identification_type' => $request->identification_type,
                        'identification_number' => $request->identification_number,
                        'identification_photo_front' => $storedPaths['identification_photo_front'] ?? null,
                        'identification_photo_back' => $storedPaths['identification_photo_back'] ?? null,
                    ]);
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => __('messages.update_success', ['name' => 'Deliveryman']),
                    ]);
                } else {
                    $user->update($request->only('first_name', 'last_name', 'phone', 'image', 'email'));
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => __('messages.update_success', ['name' => 'User']),
                    ]);
                }

            } else {
                return response()->json([
                    'status' => true,
                    'status_code' => 500,
                    'message' => __('messages.update_failed'),
                ]);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function userEmailUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            if (!auth()->guard('api')->user()) {
                return unauthorized_response();
            }
            $userId = auth('api')->id();
            $user = User::findOrFail($userId);
            if ($user) {
                $user->update($request->only('email'));
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => __('messages.update_success', ['name' => 'User']),
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'status_code' => 500,
                    'message' => __('messages.update_failed'),
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

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|different:old_password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user is authenticated
            if (!auth()->guard('api')->user()) {
                return unauthorized_response();
            }

            $userId = auth('api')->id();
            $user = User::findOrFail($userId);

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'message' => __('messages.old_password_invalid'),
                ], 401);
            }

            // Update the password with the new one
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Password']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function deactivateAccount()
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $user = auth('api')->user();
        $user->update([
            'status' => 0,
            'deactivated_at' => now(),
        ]);
        $success = $user->currentAccessToken()->delete();
        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.account_deactivate_successful')
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.account_deactivate_failed')
            ]);
        }
    }

    public function deleteAccount()
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $user = auth('api')->user();
        $user->delete(); // Soft delete
        $success = $user->currentAccessToken()->delete();
        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.account_delete_successful')
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => __('messages.account_delete_failed')
            ]);
        }
    }

}
