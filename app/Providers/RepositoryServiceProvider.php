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
        $this->app->bind(\App\Contracts\Repositories\LocationRepository::class, \App\Repositories\Eloquent\LocationRepositoryEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\RacksRepository::class, \App\Repositories\Eloquent\RacksRepositoryEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\WarehousesRepository::class, \App\Repositories\Eloquent\WarehousesRepositoryEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\TiersRepository::class, \App\Repositories\Eloquent\TiersRepositoryEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\SlotsRepository::class, \App\Repositories\Eloquent\SlotsRepositoryEloquent::class);
        //:end-bindings:
    }
}
