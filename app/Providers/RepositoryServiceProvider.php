<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Repositories\Contracts\LocationRepository::class, \App\Repositories\Eloquent\LocationRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\RacksRepository::class, \App\Repositories\Eloquent\RacksRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\WarehousesRepository::class, \App\Repositories\Eloquent\WarehousesRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\TiersRepository::class, \App\Repositories\Eloquent\TiersRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\SlotsRepository::class, \App\Repositories\Eloquent\SlotsRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\IncomesRepository::class, \App\Repositories\Eloquent\IncomesRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\IncomeLinesRepository::class, \App\Repositories\Eloquent\IncomeLinesRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\PartsRepository::class, \App\Repositories\Eloquent\PartsRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\VendorsRepository::class, \App\Repositories\Eloquent\VendorsRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\Contracts\StorageUnitsRepository::class, \App\Repositories\Eloquent\StorageUnitsRepositoryEloquent::class);
        //:end-bindings:
    }
}
