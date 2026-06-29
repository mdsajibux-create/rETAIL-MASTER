<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerMiddlewares();
    }

    /**
     * Dynamically register middlewares.
     */
    private function registerMiddlewares(): void
    {
        $middlewareDir = app_path('Http/Middleware');
        $middlewareFiles = File::files($middlewareDir);

        foreach ($middlewareFiles as $file) {
            $middlewareClass = 'App\Http\Middleware\\' . pathinfo($file, PATHINFO_FILENAME);
            $alias = $this->generateAlias($middlewareClass);

            if (class_exists($middlewareClass)) {
                $this->app['router']->aliasMiddleware($alias, $middlewareClass);
            }
        }
    }

    /**
     * Generate an alias for the middleware based on its class name.
     */
    private function generateAlias(string $middlewareClass): string
    {
        // Convert class name to a kebab-case alias
        $className = class_basename($middlewareClass);
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1.$2', $className));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
