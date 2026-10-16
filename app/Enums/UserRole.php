<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Agent = 'agent';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Agent => 'Support Agent',
            self::Customer => 'Customer',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Agent => 'success',
            self::Customer => 'secondary',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Admin, self::Agent], true);
    }
}
