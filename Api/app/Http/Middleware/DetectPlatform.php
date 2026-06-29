<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectPlatform
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if platform is already provided in request
        if ($request->method() === 'GET') {
            if (!$request->has('platform')) {
                $platform = $this->detectPlatform($request);

                // Merge platform into request
                $request->merge(['platform' => $platform]);

                // Also add to request attributes for easy access
                $request->attributes->set('platform', $platform);
            }
        }

        return $next($request);
    }

    /**
     * Detect platform from User-Agent and other headers
     *
     * @param Request $request
     * @return string
     */
    private function detectPlatform(Request $request): string
    {
        // First check custom header (if Flutter/Next.js sends it)
        $customPlatform = $request->header('X-Platform');
        if ($customPlatform && in_array($customPlatform, ['web', 'android', 'ios', 'mobile'])) {
            return $customPlatform;
        }

        // Then check User-Agent
        $userAgent = strtolower($request->header('User-Agent', ''));

        // Check for Flutter apps
        if (str_contains($userAgent, 'flutter')) {
            if (str_contains($userAgent, 'android')) {
                return 'android';
            }
            if (str_contains($userAgent, 'ios') ||
                str_contains($userAgent, 'iphone') ||
                str_contains($userAgent, 'ipad')) {
                return 'ios';
            }
            return 'mobile';
        }

        // Check for React Native
        if (str_contains($userAgent, 'react native')) {
            if (str_contains($userAgent, 'android')) {
                return 'android';
            }
            if (str_contains($userAgent, 'ios')) {
                return 'ios';
            }
            return 'mobile';
        }

        // Check for mobile browsers
        if (preg_match('/(android|iphone|ipad|ipod|mobile|webos|blackberry)/i', $userAgent)) {
            if (str_contains($userAgent, 'android')) {
                return 'android';
            }
            if (str_contains($userAgent, 'iphone') ||
                str_contains($userAgent, 'ipad') ||
                str_contains($userAgent, 'ipod')) {
                return 'ios';
            }
            return 'mobile';
        }

        // Default to web
        return 'web';
    }
}
