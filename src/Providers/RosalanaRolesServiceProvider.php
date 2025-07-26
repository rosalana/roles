<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\ServiceProvider;
use Rosalana\Roles\Services\RolePolicyResolver;

class RosalanaRolesServiceProvider extends ServiceProvider
{
    /**
     * Register everything in the container.
     */
    public function register()
    {
        $this->app->singleton('rosalana.roles', function ($app) {
            return new \Rosalana\Roles\Services\RolesManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {

        RolePolicyResolver::register();

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'rosalana-roles-migrations');
    }
}
