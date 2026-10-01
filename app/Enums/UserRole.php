<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case HR = 'hr';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::HR => 'HR',
            self::Employee => 'Karyawan',
        };
    }

    public function isAdminPanel(): bool
    {
        return in_array($this, [self::Admin, self::HR], true);
    }
}
