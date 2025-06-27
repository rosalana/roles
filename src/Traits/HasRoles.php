<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Rosalana\Roles\Models\Role;

trait HasRoles
{
    public function roleFor(Model&Roleable $model)
    {
        // blbě ..
        $relation = config('roles.pivot', 'users');
        return $model->{$relation}()->where('user_id', $this->getKey())->first()?->role;
    }

    public function hasPermissionTo(string $permission, Model $model)
    {
        // ..
    }

    public function hasAnyPermissionTo(array $permissions, Model $model)
    {
        // ..
    }

    public function assignRole(string $role, Model $model)
    {
        // wierd shit... creating rolí je trochu možná složitější ne? 
    }
}