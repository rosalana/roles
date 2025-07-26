<?php

namespace Rosalana\Roles\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Rosalana\Roles\Traits\Roleable;

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

    /**
     * Warning: Do not use in production on every request!
     */
    public static function resolveAll(): void
    {
        $path = app_path('Models');

        $files = File::allFiles($path);

        $models = collect($files)
            ->map(fn($file) => Str::replaceLast('.php', '', Str::after($file->getRealPath(), base_path() . '/')))
            ->map(fn($class) => "\\" . str_replace('/', '\\', ucfirst($class)))
            ->filter(fn($class) => is_subclass_of($class, Model::class) && in_array(Roleable::class, class_uses_recursive($class)))
            ->values()
            ->all();

        foreach ($models as $model) {
            if (!isset(static::$models[$model])) {
                static::register($model);
            }
        }
    }
}
