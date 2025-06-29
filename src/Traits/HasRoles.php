<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;

trait HasRoles
{
    public static function bootHasRoles()
    {
        static::retrieved(function ($model) {
            // Roles::for($model); // nešlo by to nastavit tady automaticky?
        });
    }

    // Attributes


    // Relationships

    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'assignee', 'assigned_roles');
    }

    // Methods


    public function join(Model&Roleable $model, string|Role|null $role = null): void
    {
        Roles::on($model)->for($this)->assign($role);
    }

    public function leave(Model&Roleable $model): void
    {
        Roles::on($model)->for($this)->detach();
    }

    public function role(Model&Roleable $model)
    {
        return Roles::on($model)->for($this)->get();
    }

    public function permissions(Model&Roleable $model): array
    {
        return Roles::on($model)->for($this)->permissions();
    }

    public function assignRole(string|Role $role, Model&Roleable $model): void
    {
        Roles::on($model)->for($this)->assign($role);
    }

    public function detachRole(string|Role $role, Model&Roleable $model): void
    {
        Roles::on($model)->for($this)->detach($role);
    }

    public function hasRole(string|Role $role, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->has($role);
    }

    public function hasPermission(string $permission, Model&Roleable $model): bool
    {
        return Roles::on($model)->for($this)->can($permission);
    }
}
