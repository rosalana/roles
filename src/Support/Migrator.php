<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Traits\Roleable;

class Migrator
{
    public static function seedWithDefault(Model&Roleable $model): void
    {
        $defaultRoles = Registry::get($model::class)->default_roles;

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

    public static function removeAllRoles(Model&Roleable $model): void
    {
        $model->roles()->delete();
    }

    public static function validatePermissions(Model&Roleable $model, Collection $permissions): void
    {
        $registeredPermissions = Registry::get($model::class)->permissions;
        $alias = Registry::get($model::class)->alias;

        foreach ($permissions as $p) {
            if (!in_array($p, $registeredPermissions)) {

                if (isset($alias[$p]) && in_array($alias[$p], $registeredPermissions)) {
                    // It's a valid alias, so we can skip it
                    continue;
                } else {
                    throw new \RuntimeException("Permission '{$p}' is not registered for model " . $model::class);
                }

                // -> pokud to dojde sem musí se udělat migrace - migrace by dělala i když by byl alias ale není to nutné.
            }
        }
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
