<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Rosalana\Roles\Support\Registry;

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
        $table = Registry::get($this->roleable_type)['pivot_table'];
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        
        return $this->belongsToMany(
            $userModel,
            $table,
            'role_id',
            'user_id'
        );
    }
}
