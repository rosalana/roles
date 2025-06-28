<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Rosalana\Roles\Models\Role;

trait HasRoles
{
    public static function bootHasRoles()
    {
        //
    }

    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'assignee', 'assigned_roles');
    }
}
