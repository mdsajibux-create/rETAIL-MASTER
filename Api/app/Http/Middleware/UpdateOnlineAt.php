<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateOnlineAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin or Deliveryman or store (User model)
        if (auth()->guard('api')->check()) {
            auth()->guard('api')->user()->update(['online_at' => (new \DateTime())->format("Y-m-d H:i:s")]);
        }

        // Customer (Customer model)
        if (auth()->guard('api_customer')->check()) {
            auth()->guard('api_customer')->user()->update(['online_at' => (new \DateTime())->format("Y-m-d H:i:s")]);
        }

        return $next($request);
    }
}
