<?php

namespace Rosalana\Roles\Enums;

enum Roles: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case USER = 'user'; // default role for users
    case BANNED = 'banned';
    case UNKNOWN = 'unknown'; // error state or unrecognized role

    public function level(): int
    {
        return match ($this) {
            self::ADMIN => 100,
            self::MODERATOR => 50,
            self::USER => 10,
            self::BANNED => 0,
            self::UNKNOWN => -1,
        };
    }

    public function isAtLeast(self|string $role): bool
    {
        if (is_string($role)) {
            $role = self::tryFrom($role);

            if (!$role) {
                return false; // Invalid role string
            }
        }

        return $this->level() >= $role->level();
    }

    public function is(self|string $role): bool
    {
        if (is_string($role)) {
            $role = self::tryFrom($role);

            if (!$role) {
                return false; // Invalid role string
            }
        }

        return $this->value === $role->value;
    }
}
