<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\Facades\Event;
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

        Event::listen('Rosalana\\Accounts\\Events\\UserLogin', $this->setRole(...));
        Event::listen('Rosalana\\Accounts\\Events\\UserRefresh', $this->setRole(...));
        Event::listen('Rosalana\\Accounts\\Events\\UserRegister', $this->setRole(...));
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
            __DIR__ . '/../Enums/Roles.php' => app_path('Enums/Roles.php'),
        ], 'rosalana-roles-role-enum');
    }

    /**
     * Set the user's role in the application context.
     * @param \Rosalana\\Accounts\\Events\\UserRegister|\Rosalana\\Accounts\\Events\\UserLogin|\Rosalana\\Accounts\\Events\\UserRefresh $event
     */
    protected function setRole($event): void
    {
        $logWarning = function (?string $message = null) use ($event) {
            logger()->warning($message ?? 'Failed to set user role during authentication event', [
                'event' => get_class($event),
                'user_id' => $event->user->getKey(),
                'response' => $event->response->json(),
            ]);
        };

        /** @var string $role */
        $role = $event->response->json('data.role');

        if (! $role) {
            $logWarning('Role not found in response');
            return;
        }

        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = $event->user;

        if (! $user->getKey()) {
            $logWarning('User missing local identifier');
            return;
        }

        $key = 'user.' . $user->getKey();

        App::context()->scope($key)->put('role', $role);
    }
}
