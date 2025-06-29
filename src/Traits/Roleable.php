<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Support\Migrator;

trait Roleable
{
    public static function bootRoleable()
    {
        \Rosalana\Roles\Support\Config::register(static::class);

        static::created(function ($model) {
            Migrator::seedWithDefault($model);
        });

        static::deleted(function ($model) {
            Migrator::removeAllRoles($model);
        });
    }

    /* Relationships */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model', \App\Models\User::class), // později upravit na morth kdy může hasRoles být více modelů
            static::getUsersPivotTable(),
        )->withPivot('role_id');
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    /* Attributes */

    public static function getUsersPivotTable(): string
    {
        return strtolower(class_basename(static::class)) . '_users';
    }

    public static function permissions(): array
    {
        return [];
    }

    public static function permissionsAlias(): array
    {
        return [];
    }

    public static function defaultRoles(): array
    {
        return [
            'default' => ['*'],
        ];
    }

    public static function defaultRole(): ?string
    {
        return isset(static::defaultRoles()['default']) ? 'default' : null;
    }

    /* Methods */

    public function join(Model&HasRoles $user, string|Role|null $role = null): void
    {
        Roles::on($this)->for($user)->assign($role);
    }

    public function leave(Model&HasRoles $user): void
    {
        Roles::on($this)->for($user)->detach();
    }
}
