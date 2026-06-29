<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $excludedPaths = [
            'install/assets/*',
            'install*',
            'installer*',
        ];

        // check
        foreach ($excludedPaths as $excluded) {
            if ($request->is($excluded)) {
                return $next($request);
            }
        }

        if ($request->is('api/v1/*') && !config('app.installed')) {
            return response()->json([
                'message' => 'Installation is not completed yet.',
                'success' => false
            ], 403);
        }

        return $next($request);
    }
}
