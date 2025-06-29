<?php

namespace Rosalana\Roles\Support;

use Illuminate\Support\Collection;

class Registry
{
    protected static array $models = [];

    public static function register(string $class): void
    {
        static::validateClass($class);

        static::$models[$class] = collect([
            'pivot_table' => $class::getUsersPivotTable() ?? throw new \RuntimeException("Pivot table not defined for class {$class}."),
            'default_roles' => $class::defaultRoles() ?? throw new \RuntimeException("Default roles not defined for class {$class}."),
            'default_role' => $class::defaultRole() ?? array_key_first($class::defaultRoles()),
            'permissions' => $class::permissions(),
            'alias' => $class::permissionsAlias(),
        ]);
    }

    public static function all(): array
    {
        return static::$models;
    }

    public static function get(string $class): ?Collection
    {
        return static::$models[$class] ?? static::resolve($class);
    }

    protected static function validateClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class {$class} not found.");
        }

        if (!in_array('Rosalana\Roles\Traits\Roleable', class_uses_recursive($class))) {
            throw new \RuntimeException("Class {$class} does not use Roleable trait.");
        }
    }

    protected static function resolve(string $class): ?Collection
    {
        static::validateClass($class);

        static::register($class);
        return static::$models[$class] ?? null;
    }

    // Permissions potřebují být nějak processnuté - protože mají aliasy + windcards
}
