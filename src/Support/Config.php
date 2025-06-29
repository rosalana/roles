<?php

namespace Rosalana\Roles\Support;

use Illuminate\Support\Collection;

class Config
{
    protected static array $models = [];

    public static function register(string $class): void
    {
        Validator::validateClass($class);

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

    protected static function resolve(string $class): ?Collection
    {
        Validator::validateClass($class);

        static::register($class);
        return static::$models[$class] ?? null;
    }
}
