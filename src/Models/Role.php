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

    public function roleable(): MorphTo
    {
        return $this->morphTo('roleable', 'roleable_type', 'roleable_id');
    }

    public function users(): BelongsToMany
    {
        $table = Config::get($this->roleable_type)['pivot_table'];
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

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

    public function create(array $attributes = []): self
    {
        Validator::validatePermissions($this->roleable_type, collect($attributes['permissions'] ?? []));

        return parent::create($attributes);
    }
}
