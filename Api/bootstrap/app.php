<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        using: function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
            Route::middleware('api')->prefix('api/v1')->group(base_path('routes/admin-api.php'));
            Route::middleware('api')->prefix('api/v1')->group(base_path('routes/branch-api.php'));
            Route::middleware('api')->prefix('api/v1')->group(base_path('routes/customer-api.php'));
            Route::middleware('api')->prefix('api/v1')->group(base_path('routes/deliveryman-api.php'));
            Route::middleware('web')->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            \App\Http\Middleware\ApiAuthMiddleware::class,
            \App\Http\Middleware\LocaleMiddleware::class,
            \App\Http\Middleware\DemoModeMiddleware::class,
            \App\Http\Middleware\SetUserLocation::class,
            \App\Http\Middleware\CheckIfInstalled::class,
        ]);

        $middleware->alias([
            'detect.platform' => \App\Http\Middleware\DetectPlatform::class,
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'verify_api_csrf_token' => \App\Http\Middleware\VerifyApiCsrfToken::class,
            'online.track' => \App\Http\Middleware\UpdateOnlineAt::class,
            'verify.hmac' => \App\Http\Middleware\VerifyHmacSignature::class,
            'demo' => \App\Http\Middleware\DemoModeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        //

    })->create();
