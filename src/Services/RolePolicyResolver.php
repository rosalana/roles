<?php

namespace Rosalana\Roles\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Rosalana\Roles\Facades\Roles;
use Rosalana\Roles\Support\Config;

class RolePolicyResolver
{
    public static function register(): void
    {   
        foreach (Config::all() as $class => $config) {

            $permissions = static::processWithAlias($config->get('permissions', []), $config->get('alias', []));

            foreach ($permissions as $permission) {
                Gate::define($permission, function (Authenticatable $user, Model $model) use ($permission) {
                    return Roles::on($model)->for($user)->can($permission);
                });
            }
        }
    }

    public static function processWithAlias(array $permissions, array $alias): array
    {
        if (empty($alias)) return $permissions;

        $resolvedPermissions = [];
        foreach ($permissions as $permission) {
            if (isset($alias[$permission])) {
                $resolvedPermissions = array_merge($resolvedPermissions, $alias[$permission]);
            } else {
                $resolvedPermissions[] = $permission;
            }
        }

        return array_unique($resolvedPermissions);
    }
}