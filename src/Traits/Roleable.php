<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rosalana\Roles\Models\Role;

trait Roleable
{
    public static function bootRoleable()
    {
        // tyto věci uděláme později.. až nakonec
        static::retrieved(function ($model) {
            // validate permissions (jestli nemá nějaké které neexistuje) and log or migrate if necessary
            // registrovat permissions do nějakého globálního stavu at víme, které permissions jsou právě používány
        });

        static::creating(function ($model) {
            // assign default roles if any
        });

        static::created(function ($model) {
            //
        });
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
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

    // public static function createWithRoles(array $attributes, array $roles): self
}
