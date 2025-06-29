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

    public function roleOf(Model&HasRoles $user): ?Role
    {
        return Roles::on($this)->for($user)->get();
    }

    public function newRole(string $name, array $permissions = ['*']): Role
    {
        return Role::create([
            'name' => $name,
            'roleable_type' => static::class,
            'roleable_id' => $this->getKey(),
            'permissions' => $permissions,
        ]);
    }

    public function hasRole(string|Role $role): bool
    {
        $role = Roles::on($this)->for($this)->resolveRole($role);

        if (!$role) return false;

        return $this->roles()
            ->where('id', $role->getKey())
            ->exists();
    }

    public function removeRole(string|Role $role): void
    {
        $role = Roles::on($this)->for($this)->resolveRole($role);

        if (!$role) {
            throw new \RuntimeException("Role '{$role}' not found or could not be resolved.");
        }

        $role->users()->detach($this->getKey());
        $role->delete();
    }
}
