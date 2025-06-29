<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Rosalana\Roles\Support\Context;

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

    public function users(): BelongsToMany
    {
        $class = Context::resolveRoleable($this->roleable_type);
        $table = (new $class)->getUsersPivotTable();
        
        return $this->belongsToMany(
            'App\Models\User',
            $table,
            'role_id',
            'user_id'
        );
    }
}
