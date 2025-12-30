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

        if (!$this->app->runningInConsole()) {
            $this->app['router']->pushMiddlewareToGroup('web', \Rosalana\Roles\Http\Middleware\EnsureUserIsNotSuspended::class);
        }

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'rosalana-roles-migrations');

        $this->publishes([
            __DIR__ . '/../Enums/RoleEnum.php' => app_path('Enums/RoleEnum.php'),
        ], 'rosalana-roles-role-enum');
    }

    protected function setRole(array $data): void
    {
        $user = collect($data['user']);
        
        if (!$user->has('local_id') && !$user->has('remote_id')) {
            logger()->warning('Missing ID in user hook payload', $user->all());
            return;
        }

        if ($user->has('local_id')) {
            $key = 'user.' . $user->get('local_id');
        } else {
            $result = array_key_first(App::context()->find('user.*', ['remote_id' => $user->get('remote_id')]));
            if (!$result) {
                logger()->warning('User not found for remote_id', $user->all());
                return;
            }
            $key = $result;
        }

        if (!$key) return;

        App::context()->scope($key)->put('role', $user->get('role'));
    }
}
