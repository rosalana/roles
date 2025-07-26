<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Roles\Traits\Roleable;

class Validator
{
    public static function validateClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class {$class} not found.");
        }

        if (!in_array('Rosalana\Roles\Traits\Roleable', class_uses_recursive($class))) {
            throw new \RuntimeException("Class {$class} does not use Roleable trait.");
        }
    }

    public static function validatePermissions(string $model, Collection $permissions): void
    {
        Validator::validateClass($model);

        $registeredPermissions = Config::get($model)->get('permissions');
        $alias = Config::get($model)->get('alias');

        foreach ($permissions as $p) {
            if ($p === '*') continue;
            if (!in_array($p, $registeredPermissions)) {

                if (isset($alias[$p]) && in_array($alias[$p], $registeredPermissions)) {
                    // It's a valid alias, so we can skip it
                    continue;
                } else {
                    throw new \RuntimeException("Permission '{$p}' is not registered for model " . $model);
                }

                // -> pokud to dojde sem musí se udělat migrace - migrace by dělala i když by byl alias ale není to nutné.
            }
        }
    }
}
