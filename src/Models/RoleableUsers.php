<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;

class RoleableUsers extends Model
{
    // Genericky model který dynamicky slouží mezi Roleable a HasRoles

    // TENTO MODEL NENÍ POTŘEBA - PIVOT TABULKY NEMUSÍ MÍT MODELY možná jen udělat class, který pomůže nějakým věcem v Roleable Modelech

    

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = strtolower(class_basename($this->resolveRoleableClass($attributes))) . '_users';
    }

    public function roleable()
    {
        return $this->belongsTo($this->resolveCasterClass($this->attributes), strtolower(class_basename($this->resolveRoleableClass($this->attributes))) . '_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }


    private function resolveRoleableClass(array $attributes): string
    {
        // find something that has *_id and it is not user_id or role_id
        // try App\Models\{string} and if its Roleable then return the class name

        return 'App\Models\Roleable'; // jen placeholder

        // throw exception pokud se to nepodaří najít
    }
}
