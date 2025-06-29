<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;

trait HasRoles
{
    public static function bootHasRoles()
    {
        //
    }

    public function join(Model&Roleable $model, string|Role|null $role = null): void
    {
        Roles::on($model)->for($this)->assign($role);
    }

    public function leave(Model&Roleable $model): void
    {
        Roles::on($model)->for($this)->detach();
    }

    public function role(Model&Roleable $model): ?Role
    {
        return Roles::on($model)->for($this)->get();
    }

    public function permissions(Model&Roleable $model): Collection
    {
        return Roles::on($model)->for($this)->permissions();
    }

    public function changeRole(string|Role $role, Model&Roleable $model): void
    {
        $this->join($model, $role);
    }

    public function hasRole(string|Role $role, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->is($role);
    }

    public function doesNotHaveRole(string|Role $role, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->isNot($role);
    }

    public function hasPermission(string $permission, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->can($permission);
    }

    public function doesNotHavePermission(string $permission, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->cannot($permission);
    }

    public function hasAnyPermission(array $permissions, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->canAny($permissions);
    }
}
