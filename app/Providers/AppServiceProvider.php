<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::define('is-admin', fn($user) => $user->role === 'admin');
        Gate::define('is-supervisor', fn($user) => in_array($user->role, ['admin','warehouse.supervisor']));
        Gate::define('is-operator', fn($user) => in_array($user->role, ['admin','warehouse.supervisor','warehouse.operator']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
