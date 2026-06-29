<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class InterfaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->bindInterfaces();
    }


    private function bindInterfaces(): void
    {

        $repositoriesDir = app_path('Repositories');
        $interfaceDir = app_path('Interfaces');
        $repositoryFiles = File::files($repositoriesDir);
        foreach ($repositoryFiles as $file) {
            $repositoryFileName = pathinfo($file, PATHINFO_FILENAME);
            $interfaceName = str_replace('Repository', '', $repositoryFileName) . 'Interface';
            $interfacePath = $interfaceDir . DIRECTORY_SEPARATOR . $interfaceName . '.php';

            if (File::exists($interfacePath)) {
                $interface = 'App\Interfaces\\' . $interfaceName;
                $repository = 'App\Repositories\\' . $repositoryFileName;
                $this->app->bind($interface, $repository);
            }
        }

    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
