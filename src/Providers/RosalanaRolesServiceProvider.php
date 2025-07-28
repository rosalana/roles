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

        App::hooks()->onUserLogin($this->setRole(...));
        App::hooks()->onUserRegister($this->setRole(...));
        App::hooks()->onUserRefresh($this->setRole(...));
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

    protected function setRole(array $data): void
    {
        $user = collect($data['user']);
        
        if (!$user->has('local_id')) {
            logger()->warning('Missing local_id in user hook payload', $user->all());
            return;
        }

        App::context()->put('user.' . $user->get('local_id') . '.role', $user->get('role'));
    }
}
