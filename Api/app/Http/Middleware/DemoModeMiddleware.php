<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoModeMiddleware
{

    protected array $blockedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    protected array $protectedPaths = [
        // for admin
        'api/v1/admin/business-operations*',
        'api/v1/admin/payment-gateways*',
        'api/v1/admin/system-management*',
        'api/v1/admin/sms-provider/settings*',
        // for seller
        'api/v1/seller/store/settings*',
        // for customer
        'api/v1/media-upload/delete',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (env('DEMO_MODE') === true) {
            if (in_array($request->method(), $this->blockedMethods)) {
                foreach ($this->protectedPaths as $path) {
                    if ($request->is("$path*")) {
                        return response()->json([
                            'message' => 'This action is not allowed in demo mode',
                        ], 405);
                    }
                }
            }
        }

        return $next($request);
    }
}
