<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Deliveryman\app\Models\DeliveryMan;
use Symfony\Component\HttpFoundation\Response;

class CheckDeliverymanApproveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = auth('api')->user();
        if (!$auth) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$auth->activity_scope == 'delivery_level') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $deliveryman = DeliveryMan::where('user_id', $auth->id)->first();

        if ($deliveryman && $deliveryman->status == 'approved') {
            return $next($request);
        } else {
            return response()->json([
                'message' => __('messages.not_allowed_status',
                    ['name' => $deliveryman?->status ?? 'not allowed'])
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
