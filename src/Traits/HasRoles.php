<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Core\Facades\App;
use Rosalana\Roles\Enums\RoleEnum as DefaultRoleEnum;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;
use UnitEnum;

trait HasRoles
{
    /**
     * Get the role of the model from the context.
     */
    public function role(): UnitEnum|null
    {
        $enum = config('rosalana.roles.enum', DefaultRoleEnum::class);

        return $enum::tryFrom(App::context()->get('user.' . $this->id . '.role')) ?: null;
    }

    /**
     * Check if the user is suspended based on the configured banned roles.
     */
    public function isSuspended(): bool
    {
        $role = $this->role();
        $suspendedRoles = config('rosalana.roles.banned', []);
        if (is_array($suspendedRoles)) {
            return in_array($role?->value, $suspendedRoles);
        } elseif (is_string($suspendedRoles)) {
            return in_array($role?->value, explode(',', $suspendedRoles));
        }

        return false;
    }

    /**
     * Join a role to the model.
     */
    public function join(Model $model, string|Role|null $role = null): Role
    {
        Roles::on($model)->for($this)->assign($role);
        return $this->roleIn($model);
    }

    /**
     * Leave a role from the model.
     */
    public function leave(Model $model): void
    {
        Roles::on($model)->for($this)->detach();
    }

    /**
     * Get the role of the user in a specific context (model).
     */
    public function roleIn(Model $model): ?Role
    {
        return Roles::on($model)->for($this)->get();
    }

    /**
     * Get all permissions for the user in a specific context (model).
     */
    public function permissions(Model $model): Collection
    {
        return Roles::on($model)->for($this)->permissions();
    }

    /**
     * Change the role of the user in a specific context (model).
     */
    public function changeRole(string|Role $role, Model $model): Role
    {
        $this->join($model, $role);
        return $this->roleIn($model);
    }

    /**
     * Check if the user has a specific role or permission in a specific context (model).
     */
    public function hasRole(string|Role $role, Model $model): bool
    {
        return Roles::on($model)->for($this)->is($role);
    }

    /**
     * Check if the user does not have a specific role or permission in a specific context (model).
     */
    public function doesNotHaveRole(string|Role $role, Model $model): bool
    {
        return Roles::on($model)->for($this)->isNot($role);
    }

    /**
     * Check if the user has a specific permission in a specific context (model).
     */
    public function hasPermission(string $permission, Model $model): bool
    {
        return Roles::on($model)->for($this)->can($permission);
    }

    /**
     * Check if the user does not have a specific permission in a specific context (model).
     */
    public function doesNotHavePermission(string $permission, Model $model): bool
    {
        return Roles::on($model)->for($this)->cannot($permission);
    }

    /**
     * Check if the user has any of the specified permissions in a specific context (model).
     */
    public function hasAnyPermission(array $permissions, Model $model): bool
    {
        return Roles::on($model)->for($this)->canAny($permissions);
    }
}
