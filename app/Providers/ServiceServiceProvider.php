<?php

namespace App\Providers;

use App\Services\Api\IncomeService;
use App\Services\Api\WarehouseService;
use App\Services\Contracts\IncomeServiceInterface;
use App\Services\Contracts\WarehouseServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    protected $services = [
        WarehouseServiceInterface::class => WarehouseService::class,
        IncomeServiceInterface::class => IncomeService::class,
    ];
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
        foreach ($this->services as $interface => $class) {
            $this->app->singleton($interface, $class);
        }
    }
}
