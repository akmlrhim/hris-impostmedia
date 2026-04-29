<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case HR = 'hr';
    case Manager = 'manager';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::HR => 'HR',
            self::Manager => 'Manager',
            self::Employee => 'Karyawan',
        };
    }

    public function isAdminPanel(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::HR, self::Manager], true);
    }
}
