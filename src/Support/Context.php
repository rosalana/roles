<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;

class Context
{
    public static function resolveRoleable(string $roleable): string
    {
        $class = self::resolveClassNameFromString($roleable);

        if (!in_array('Rosalana\Roles\Traits\Roleable', class_uses_recursive($class))) {
            throw new \RuntimeException("Class {$class} does not use Roleable trait.");
        }

        return $class;
    }

    public static function resolveAssignee(string $roleable): string
    {
        $class = self::resolveClassNameFromString($roleable);

        if (!in_array('Rosalana\Roles\Traits\HasRoles', class_uses_recursive($class))) {
            throw new \RuntimeException("Class {$class} does not use HasRoles trait.");
        }

        return $class;
    }

    public static function resolvePivotTable(string $roleable): string
    {
        $class = self::resolveRoleable($roleable);

        return (new $class)->getUsersPivotTable();

    }

    private static function resolveClassNameFromString(string $class): string
    {
        if (class_exists($class)) return $class;

        $namespace = 'App\\Models\\' . ucfirst($class);
        if (class_exists($namespace)) return $namespace;

        throw new \RuntimeException("Class not found: {$class}");
    }
}
