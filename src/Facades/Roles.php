<?php

namespace Rosalana\Roles\Facades;

use Illuminate\Support\Facades\Facade;

class Roles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rosalana.roles';
    }
}
