<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rosalana\Roles\Models\Role;

trait Roleable
{
    protected $permissions = [];
    
    protected $permissionsAlias = [];

    public static function bootRoleable()
    {
        static::retrieved(function ($model) {
            // validate permissions and log or migrate if necessary
        });
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
    }
}