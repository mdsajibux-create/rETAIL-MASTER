<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('hmac.secret');
        $algo = config('hmac.algo');
        $header = config('hmac.header_signature');
        $timestamp = $request->input('timestamp') ?? '';


        // Reject if missing timestamp
        if (!$timestamp || !is_numeric($timestamp)) {
            return response()->json(['error' => 'Missing or invalid timestamp'], 400);
        }

        // Check allowed window (5 min default)
        $currentTime = time();
        if (abs($currentTime - intval($timestamp)) > 900) {
            return response()->json(['error' => 'Expired timestamp'], 401);
        }

        // Example: sign email + order_id
        $apiUser = Auth::guard('api')->user();
        if($apiUser && $apiUser->activity_scope === 'store_level'){
            $user_email =  Auth::guard('api')->user()->email;
            $reference_id = $request->store_id ?? $request->wallet_history_id;
        }else{
            $user_email = Auth::guard('api_customer')->user()->email;
            $reference_id = $request->order_id ?? $request->wallet_history_id;
        }

        if (empty($user_email) || empty($reference_id)) {
            return response()->json(['error' => 'Missing payload data'], 400);
        }

        $payload   = "{$user_email}|{$reference_id}|{$timestamp}";
        $provided  = $request->header($header);


        if (!$provided) {
            return response()->json(['error' => 'Missing HMAC'], 400);
        }

        $calculated = hash_hmac($algo, $payload, $secret);

        if (!hash_equals($calculated, $provided)) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 403);
        }

        return $next($request);
    }
}
