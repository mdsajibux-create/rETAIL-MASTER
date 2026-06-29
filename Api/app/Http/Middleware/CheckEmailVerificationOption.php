<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CheckEmailVerificationOption
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        foreach ($roles as $role) {
            if ($role === 'customer') {
                $authCustomer = auth('api_customer')->user();
                $isVerified = \App\Models\Customer::where('email', $authCustomer->email)
                    ->where('email_verified', 1)
                    ->exists();
            } elseif ($role === 'seller') {
                $authSeller = auth('api')->user();
                $isVerified = \App\Models\User::where('email', $authSeller->email)
                    ->where('email_verified', 1)
                    ->exists();
            } else {
                continue; // Unknown role, skip
            }

            // Check email verification setting
            $emailVerificationEnabled = DB::table('setting_options')
                ->where('option_name', 'com_user_email_verification')
                ->value('option_value');

            if (!$isVerified && $emailVerificationEnabled !== null) {
                return response()->json([
                    'status' => false,
                    'status_code' => Response::HTTP_FORBIDDEN,
                    'email_verified' => false,
                    'message' => 'Email verification is not completed.',
                ]);
            }
        }

        return $next($request);
    }
}
