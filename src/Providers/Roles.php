<?php

namespace Rosalana\Roles\Providers;

use Rosalana\Core\Contracts\Package;

class Roles implements Package
{
    public function resolvePublished(): bool
    {
        return true;
    }

    public function publish(): array
    {
        return [];
    }
}
