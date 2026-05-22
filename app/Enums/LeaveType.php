<?php

namespace App\Enums;

enum LeaveType: string
{
    case Sick = 'sick';
    case Permission = 'permission';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Sick => 'Izin Sakit',
            self::Permission => 'Izin',
            self::Annual => 'Cuti',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sick => 'rose',
            self::Permission => 'amber',
            self::Annual => 'blue',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Sick => 'heart-pulse',
            self::Permission => 'clock',
            self::Annual => 'sun',
        };
    }
}
