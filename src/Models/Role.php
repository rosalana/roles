<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}