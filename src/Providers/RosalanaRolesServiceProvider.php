<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\ServiceProvider;
use Rosalana\Roles\Services\RolePolicyResolver;
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

        App::hooks()->onUserLogin(function ($data) {
            $user = collect($data['user']);
            App::context()->put('user.' . $user->get('local_id') . '.role', $user->get('role', null));
        });

        App::hooks()->onUserRegister(function ($data) {
            $user = collect($data['user']);
            App::context()->put('user.' . $user->get('local_id') . '.role', $user->get('role', null));
        });

        App::hooks()->onUserRefresh(function ($data) {
            $user = collect($data['user']);
            App::context()->put('user.' . $user->get('local_id') . '.role', $user->get('role', null));
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
