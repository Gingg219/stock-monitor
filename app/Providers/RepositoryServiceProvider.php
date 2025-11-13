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
        //:end-bindings:
    }
}
