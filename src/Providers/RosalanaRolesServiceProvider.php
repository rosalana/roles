<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\Basecamp;
use Rosalana\Roles\Services\RolePolicyResolver;
use Illuminate\Http\Client\Response;
use Rosalana\Core\Facades\App;

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
            if (class_exists("Rosalana\Accounts\Facades\Accounts")) {
                $user = \Rosalana\Accounts\Facades\Accounts::users()->toLocal($response->json('data'));
                App::context()->put($user, [
                    'role' => $response->json('data.role', 'unknown'),
                ]);
            } else {
                logger()->warning('Accounts facade not found. User context not set.');
            }
        });

        Basecamp::after('user.register', function (Response $response) {
            if (class_exists("Rosalana\Accounts\Facades\Accounts")) {
                $user = \Rosalana\Accounts\Facades\Accounts::users()->toLocal($response->json('data'));
                App::context()->put($user, [
                    'role' => $response->json('data.role', 'unknown'),
                ]);
            } else {
                logger()->warning('Accounts facade not found. User context not set.');
            }
        });

        Basecamp::after('user.refresh', function (Response $response) {
            if (class_exists("Rosalana\Accounts\Facades\Accounts")) {
                $user = \Rosalana\Accounts\Facades\Accounts::users()->toLocal($response->json('data'));
                App::context()->put($user, [
                    'role' => $response->json('data.role', 'unknown'),
                ]);
            } else {
                logger()->warning('Accounts facade not found. User context not set.');
            }
        });

        Basecamp::after('user.current', function (Response $response) {
            if (class_exists("Rosalana\Accounts\Facades\Accounts")) {
                $user = \Rosalana\Accounts\Facades\Accounts::users()->toLocal($response->json('data'));
                App::context()->put($user, [
                    'role' => $response->json('data.role', 'unknown'),
                ]);
            } else {
                logger()->warning('Accounts facade not found. User context not set.');
            }
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
