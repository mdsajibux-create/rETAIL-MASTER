<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */

    protected $except = [
        'api/*',
        'api/v1/*',
        'api/v1/customer/*',
        'api/v1/deliveryman/*', // Add other specific routes as necessary
    ];

}
