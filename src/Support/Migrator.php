<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;
use Rosalana\Roles\Models\Role;

class Migrator
{
    public static function seedWithDefault(Model $model): void
    {
        $defaultRoles = Config::get($model::class)->get('default_roles');

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
