<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomerAccountStatus
{

    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        $user = Auth::user();
        // If the user is not authenticated, return an unauthorized response
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized, please log in.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user's account is deleted
        if ($user->deleted_at !== null) {
            return response()->json([
                'error' => 'Your account has been deleted. Please contact support.'
            ], Response::HTTP_GONE); // HTTP 410 Gone
        }

        // Check if the user's account is deactivated or disabled
        if ($user->status === 0) {
            return response()->json([
                'error' => 'Your account has been deactivated. Please contact support.'
            ], Response::HTTP_FORBIDDEN); // HTTP 403 Forbidden
        }

        if ($user->status === 2) {
            return response()->json([
                'error' => 'Your account has been suspended by the admin.'
            ], Response::HTTP_FORBIDDEN); // HTTP 403 Forbidden
        }
        return $next($request);
    }
}
