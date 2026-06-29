<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(storage_path('installed'))) {
            // Social login info (Google and Facebook)
            config([
                'services.google.client_id' => com_option_get('com_google_app_id'),
                'services.google.client_secret' => com_option_get('com_google_client_secret'),
                'services.google.redirect' => com_option_get('com_google_client_callback_url'),
                'services.facebook.client_id' => com_option_get('com_facebook_app_id'),
                'services.facebook.client_secret' => com_option_get('com_facebook_client_secret'),
                'services.facebook.redirect' => com_option_get('com_facebook_client_callback_url'),
            ]);
        }
    }
}
