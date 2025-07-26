<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Traits\Roleable;

class Migrator
{
    public static function seedWithDefault(Model $model): void
    {
        $defaultRoles = Config::get($model::class)->default_roles;

        if (empty($defaultRoles)) return;

        foreach ($defaultRoles as $role => $permissions) {
            Role::updateOrCreate([
                'name' => $role,
                'roleable_type' => $model::class,
                'roleable_id' => $model->getKey(),
                'permissions' => $permissions,
            ]);
        }
    }

    public static function removeAllRoles(Model $model): void
    {
        $model->roles()->delete();
    }

    public static function migrate(): void
    {
        // migrate permissions when validating fails
    }

    public static function rollback(): void
    {
        // rollback the migration
    }
}
