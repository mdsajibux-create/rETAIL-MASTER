<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCodeInput
{

    public function handle(Request $request, Closure $next)
    {
        $restrictedPatterns = [
            '/\bconsole\.log\b/i',
            '/\beval\b/i',
            '/\b<script\b/i',
            '/\b<?php\b/i',
            '/\bSELECT\b/i',
            '/\bINSERT\b/i',
            '/\bDELETE\b/i',
            '/\bUPDATE\b/i',
        ];

        foreach ($request->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($restrictedPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return response()->json(['error' => 'Invalid input detected'], 400);
                    }
                }
            }
        }

        return $next($request);
    }
}
