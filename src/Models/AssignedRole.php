<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssignedRole extends Model
{
    protected $table = 'assigned_roles';

    protected $fillable = [
        'role_id',
        'assignee_type',
        'assignee_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function assignee(): MorphTo
    {
        return $this->morphTo('assignee', 'assignee_type', 'assignee_id');
    }
}
