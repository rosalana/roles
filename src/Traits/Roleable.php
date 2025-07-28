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

    /**
     * Get the users associated with this roleable model.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model', '\App\Models\User'), // později upravit na morth kdy může hasRoles být více modelů
            static::getUsersPivotTable(),
        )->withPivot('role_id');
    }

    /**
     * Get all roles associated with this roleable model.
     */
    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    /**
     * Get the pivot table name for users associated with this roleable model.
     * Should be overridden in child classes if needed.
     */
    public static function getUsersPivotTable(): string
    {
        return strtolower(class_basename(static::class)) . '_users';
    }

    /**
     * Get the permissions for this roleable model.
     * Should be overridden in child classes to define specific permissions.
     */
    public static function permissions(): array
    {
        return [];
    }

    /**
     * Get the permissions alias for this roleable model.
     * Should be overridden in child classes to define specific aliases.
     * Ment to be used when changing permissions array and not wanting to migrate db.
     */
    public static function permissionsAlias(): array
    {
        return [];
    }

    /**
     * Get the default roles which should be created with the model when created.
     * Should be overridden in child classes to define specific default roles.
     */
    public static function defaultRoles(): array
    {
        return [
            'default' => ['*'],
        ];
    }

    /**
     * Get the default role which will be assigned to the user when joining a roleable model.
     * Should be overridden in child classes to define a specific default role.
     */
    public static function defaultRole(): ?string
    {
        return isset(static::defaultRoles()['default']) ? 'default' : null;
    }

    /**
     * Join this roleable model by assigning a role to a user.
     */
    public function join(Model $user, string|Role|null $role = null): void
    {
        Roles::on($this)->for($user)->assign($role);
    }

    /**
     * Leave this roleable model by detaching the user from the role.
     */
    public function leave(Model $user): void
    {
        Roles::on($this)->for($user)->detach();
    }

    /**
     * Get the role of the user in this roleable model.
     */
    public function roleOf(Model $user): ?Role
    {
        return Roles::on($this)->for($user)->get();
    }

    /**
     * Create a new role for this roleable model.
     */
    public function newRole(string $name, array $permissions = ['*']): Role
    {
        return Role::create([
            'name' => $name,
            'roleable_type' => static::class,
            'roleable_id' => $this->getKey(),
            'permissions' => $permissions,
        ]);
    }

    /**
     * Check if the user has a specific role in this roleable model.
     */
    public function hasRole(string|Role $role): bool
    {
        $role = Roles::on($this)->for($this)->resolveRole($role);

        if (!$role) return false;

        return $this->roles()
            ->where('id', $role->getKey())
            ->exists();
    }

    /**
     * Remove a role from this roleable model.
     */
    public function removeRole(string|Role $role): void
    {
        $resolvedRole = Roles::on($this)->for($this)->resolveRole($role);

        if (!$resolvedRole) {
            throw new \RuntimeException("Role '{$role}' not found or could not be resolved.");
        }

        $resolvedRole->users()->detach($this->getKey());
        $resolvedRole->delete();
    }
}
