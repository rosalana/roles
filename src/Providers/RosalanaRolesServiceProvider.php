<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Events\BasecampRequestSent;
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

        Event::listen(BasecampRequestSent::class, function (BasecampRequestSent $event) {
            if (in_array($event->alias, ['user.login', 'user.register', 'user.refresh'])) {
                // $this->setRole($event->response);
            }
        });

        // #fixme: Nebude fungovat protože potřebujeme BasecampResponse kvůli získání 'role' attributu - není v user modelu totiž.
        // Lepší pravděpodobně je udělat event listener na BasecampRequestSent a if v setRole pro type response (přidat request alias je bezpečnější)
        // Event::listen('Rosalana\\Accounts\\Events\\UserLogin', $this->setRole(...));
        // Event::listen('Rosalana\\Accounts\\Events\\UserRegister', $this->setRole(...));
        // Event::listen('Rosalana\\Accounts\\Events\\UserRefresh', $this->setRole(...));
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

    /**
     * Set the user's role in the application context.
     */
    protected function setRole(User $user): void
    {
        $remoteIdentifier = App::config('rosalana.accounts.identifier', 'rosalana_id');

        if (!$user->getAttribute($remoteIdentifier) && !$user->getKey()) {
            logger()->warning('User missing both local and remote identifiers', [
                'user_id' => $user->getKey(),
                'remote_id' => $user->getAttribute($remoteIdentifier),
            ]);
            return;
        }

        if ($user->getKey()) {
            $key = 'user.' . $user->getKey();
        } else {
            $match = App::context()->find('user.*', [$remoteIdentifier => $user->getAttribute($remoteIdentifier)]);
            if (! $match) {
                logger()->warning('User not found for remote identifier', [
                    'remote_id' => $user->getAttribute($remoteIdentifier),
                ]);
                return;
            }
        }

        if (! isset($key)) {
            logger()->warning('Unable to determine context key for user', [
                'user_id' => $user->getKey(),
                'remote_id' => $user->getAttribute($remoteIdentifier),
            ]);
            return;
        }

        App::context()->scope($key)->put('role', $user->getAttribute('role'));
    }
}
