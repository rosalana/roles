<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\Basecamp;
use Rosalana\Roles\Services\RolePolicyResolver;
use Illuminate\Http\Client\Response;

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

        /** TODO -> */
        Basecamp::after('user.login', function (Response $response) {
            $globalRole = $response->json('data.role', null);
            logger()->info('User logged in with role: ' . $globalRole);
        });

        Basecamp::after('user.register', function (Response $response) {
            $globalRole = $response->json('data.role', null);
            logger()->info('User registered with role: ' . $globalRole);
        });

        Basecamp::after('user.refresh', function (Response $response) {
            $globalRole = $response->json('data.role', null);
            logger()->info('User refreshed with role: ' . $globalRole);
        });

        Basecamp::after('user.current', function (Response $response) {
            $globalRole = $response->json('data.role', null);
            logger()->info('Current user role: ' . $globalRole);
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
