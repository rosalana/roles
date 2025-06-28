<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Get the model on which user has this role.
     */
    public function roleable(): MorphTo
    {
        return $this->morphTo('roleable', 'roleable_type', 'roleable_id');
    }

    public function assigned(): HasMany
    {
        return $this->hasMany(AssignedRole::class, 'role_id');
    }
}