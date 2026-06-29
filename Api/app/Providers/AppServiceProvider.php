<?php

namespace App\Providers;

use App\Interfaces\DynamicFieldOptionInterface;
use App\Interfaces\InventoryManageInterface;
use App\Interfaces\LocationManageInterface;
use App\Models\Customer;
use App\Models\User;
use App\Observers\CustomerObserver;
use App\Observers\OrderObserver;
use App\Observers\BranchWiseObserver;
use App\Observers\UserObserver;
use App\Repositories\DynamicFieldOptionRepository;
use App\Repositories\LocationManageRepository;
use App\Repositories\ProductInventoryManageRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Branch\app\Models\Branch;
use Modules\Order\app\Models\Order;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interface to repository implementation
        // Location
        $this->app->bind(
            LocationManageInterface::class,
            LocationManageRepository::class
        );

        // Inventory
        $this->app->bind(
            InventoryManageInterface::class,
            ProductInventoryManageRepository::class
        );

        // Dynamic field
        $this->app->bind(
            DynamicFieldOptionInterface::class,
            DynamicFieldOptionRepository::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // change
        User::observe(UserObserver::class);
        Customer::observe(CustomerObserver::class);
        Branch::observe(BranchWiseObserver::class);
        Order::observe(OrderObserver::class);

        // relationship add
        Relation::morphMap([
            'customer'     => Customer::class,
            'admin'        => User::class,
            'deliveryman'  => User::class,
        ]);

        $timezone =  'UTC';
        $globalCurrency =  'USD';
        if (file_exists(storage_path('installed'))) {
            $timezone = com_option_get('com_site_time_zone') ?? 'UTC';
            $globalCurrency = com_option_get('com_site_global_currency', config('app.default_currency'));
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
        config(['app.default_currency' => $globalCurrency]);
    }
}
