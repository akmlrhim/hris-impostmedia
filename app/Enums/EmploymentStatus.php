<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Permanent = 'permanent';
    case Contract = 'contract';
    case Probation = 'probation';
    case Internship = 'internship';
    case PartTime = 'part_time';
    case Outsourced = 'outsourced';
    case Resigned = 'resigned';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Tetap',
            self::Contract => 'Kontrak',
            self::Probation => 'Probation',
            self::Internship => 'Magang',
            self::PartTime => 'Paruh Waktu',
            self::Outsourced => 'Outsource',
            self::Resigned => 'Resign',
            self::Terminated => 'Terminated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Permanent => 'green',
            self::Contract, self::Probation => 'yellow',
            self::Internship, self::PartTime, self::Outsourced => 'blue',
            self::Resigned, self::Terminated => 'red',
        };
    }
}
