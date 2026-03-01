<?php

namespace Rosalana\Roles\Contracts;

interface RoleEnum
{
    public function level(): int;

    public function isAtLeast(self|string $role): bool;

    public function is(self|string $role): bool;
}
