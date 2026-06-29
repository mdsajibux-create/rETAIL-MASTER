<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request is for login (POST) or any other non-authenticated routes
        $req_url = $request->path();
        // List of prefixes where authentication is required
        $authRequiredPrefixes = ['api/v1/customer', 'api/v1/seller', 'api/v1/admin', 'api/v1/delivery-man', 'api/user','api/user/me'];

        // Check if the request path starts with one of the required prefixes
        foreach ($authRequiredPrefixes as $prefix) {
            // Skip the login route from authentication check
            if ($req_url === 'api/v1/delivery-man/login' ||
                $req_url === 'api/v1/delivery-man/registration' ||
                $req_url === 'api/v1/customer/login' ||
                $req_url === 'api/v1/customer/google' ||
                $req_url === 'api/v1/customer/google/callback' ||
                $req_url === 'api/v1/customer/registration' ||
                $req_url === 'api/v1/customer/forget-password' ||
                $req_url === 'api/v1/customer/verify-token' ||
                $req_url === 'api/v1/customer/reset-password' ||
                $req_url === 'api/v1/seller/registration' ||
                $req_url === 'api/v1/seller/forget-password' ||
                $req_url === 'api/v1/seller/verify-token' ||
                $req_url === 'api/v1/seller/reset-password' ||
                $req_url === 'api/v1/seller/login' ||
                $req_url === 'api/token' ||
                $req_url === 'api/refresh-token' ||
                $req_url === 'api/v1/customer/refresh-token'
            ) {
                return $next($request);
            }

            if (strpos($req_url, $prefix) === 0) {
                // If the request is for a route that requires authentication, check the auth
                if (!auth('sanctum')->check()) {
                    return response()->json([
                        'message' => 'Unauthenticated. Please login again.',
                        'status' => 401
                    ], 401);
                }
                break; // Exit the loop as we've found a match
            }
        }

        // Allow the request to continue if authenticated or route is public (login/registration)
        return $next($request);
    }
}
