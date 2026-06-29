<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-User-Latitude') && $request->hasHeader('X-User-Longitude')) {
            $request->merge([
                'user_lat' => $request->header('X-User-Latitude'),
                'user_lng' => $request->header('X-User-Longitude'),
            ]);
        }

        return $next($request);
    }
}
