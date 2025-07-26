<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Rosalana\Roles\Support\Config;
use Rosalana\Roles\Support\Validator;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'roleable_type',
        'roleable_id',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function (Role $role) {
            Validator::validatePermissions($role->roleable_type, collect($role->permissions ?? []));
        });
    }

    public function getPermissionsAttribute(): array
    {
        $attribute = $this->attributes['permissions'] ?? [];
        $permissions = is_array($attribute) ? $attribute : json_decode($attribute, true);

        return $this->resolvePermissions($permissions);
    }

    public function roleable(): MorphTo
    {
        return $this->morphTo('roleable', 'roleable_type', 'roleable_id');
    }

    public function users(): BelongsToMany
    {
        $table = Config::get($this->roleable_type)->get('pivot_table');
        $userModel = config('auth.providers.users.model', '\App\Models\User');

        return $this->belongsToMany(
            $userModel,
            $table,
            'role_id',
            'user_id'
        );
    }

    public function setPermissions(array $permissions): void
    {
        Validator::validatePermissions($this->roleable_type, collect($permissions));

        $this->permissions = $permissions;
        $this->save();
    }

    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->setPermissions($permissions);
        }
    }

    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (in_array($permission, $permissions)) {
            $permissions = array_diff($permissions, [$permission]);
            $this->setPermissions($permissions);
        }
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions ?? []));
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->permissions ?? []));
    }

    /**
     * Resolve permissions for the given role.
     */
    private function resolvePermissions(array $permissions): array
    {
        Validator::validatePermissions($this->roleable_type, collect($permissions));

        if (empty($permissions)) return [];

        $permissions = $this->resolveWildcardPermissions($permissions);
        $permissions = $this->processPermissionsAlias($permissions);

        return array_values(array_unique($permissions));
    }

    /**
     * Resolve wildcard permissions.
     *
     * If '*' is present, return all permissions for the roleable model.
     */
    private function resolveWildcardPermissions(array $permissions): array
    {
        if (in_array('*', $permissions)) {
            return $this->roleable::permissions();
        }

        return $permissions;
    }

    /**
     * Process permissions aliases.
     *
     * If an alias is defined for a permission, resolve it to the actual permissions.
     */
    private function processPermissionsAlias(array $permissions): array
    {
        $alias = Config::get($this->roleable_type)->get('alias');

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
