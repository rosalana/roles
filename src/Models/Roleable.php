<?php

namespace Rosalana\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Rosalana\Roles\Traits\Roleable as TraitsRoleable;

class Roleable extends Model
{
    use TraitsRoleable;

    // pomocny class pro extend roleable modelu ale možná nebude potřeba
}