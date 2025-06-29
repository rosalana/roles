<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Support\Registry;

trait HasRoles
{
    public static function bootHasRoles()
    {
        static::retrieved(function ($model) {
            // Roles::for($model); // nešlo by to nastavit tady automaticky?
        });
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
