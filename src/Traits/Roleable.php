<?php

namespace Rosalana\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Models\Role;

trait Roleable
{
    public static function bootRoleable()
    {
        parent::boot();

        \Rosalana\Roles\Support\Registry::register(static::class);


        // tyto věci uděláme později.. až nakonec
        // static::retrieved(function ($model) {
        //     // validate permissions (jestli nemá nějaké které neexistuje) and log or migrate if necessary
        //     // registrovat permissions do nějakého globálního stavu at víme, které permissions jsou právě používány
        // });

        // static::creating(function ($model) {
        //     // assign default roles if any
        // });

        // static::created(function ($model) {
        //     //
        // });
    }

    /**
     * Returns the pivot table for this roleable model.
     */
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

    // Relationships

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Models\User', // Adjust the namespace as needed
            $this->getUsersPivotTable(),
        )->withPivot('role_id');
    }


    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    // Methods

    public function join(Model&HasRoles $user, string|Role|null $role = null): void
    {
        Roles::on($this)->for($user)->assign($role);
    }

    public function leave(Model&HasRoles $user): void
    {
        Roles::on($this)->for($user)->detach();
    }
}
