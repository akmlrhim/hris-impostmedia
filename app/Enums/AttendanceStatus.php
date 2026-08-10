<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Leave = 'leave';
    case Holiday = 'holiday';
    case OffDay = 'off_day';
    case Sick = 'sick';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Late => 'Terlambat',
            self::Absent => 'Absen',
            self::Leave => 'Cuti',
            self::Holiday => 'Libur',
            self::OffDay => 'Off',
            self::Sick => 'Sakit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'green',
            self::Late => 'yellow',
            self::Absent => 'red',
            self::Leave, self::Sick => 'blue',
            self::Holiday, self::OffDay => 'gray',
        };
    }
}
