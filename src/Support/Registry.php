<?php

namespace Rosalana\Roles\Support;

class Registry
{
    protected static array $models = [];

    public static function register(string $class): void
    {
        static::$models[$class] = [
            'pivot_table' => $class::getUsersPivotTable() ?? throw new \RuntimeException("Pivot table not defined for class {$class}."),
            'default_roles' => $class::defaultRoles(),
            'permissions' => $class::permissions(),
            'alias' => $class::permissionsAlias(),
        ];
    }

    public static function all(): array
    {
        return static::$models;
    }

    public static function get(string $class): ?array
    {
        return static::$models[$class] ?? static::resolve($class);
    }

    protected static function resolve(string $class): ?array
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class {$class} not found.");
        }

        if (!in_array('Rosalana\Roles\Traits\Roleable', class_uses_recursive($class))) {
            throw new \RuntimeException("Class {$class} does not use Roleable trait.");
        }

        static::register($class);
        return static::$models[$class] ?? null;
    }

    // Permissions potřebují být nějak processnuté - protože mají aliasy + windcards
}
