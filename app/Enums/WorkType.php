<?php

namespace App\Enums;

enum WorkType: string
{
    case WFO = 'wfo';
    case WFH = 'wfh';
    case WFA = 'wfa';
    case WFC = 'wfc';

    public function label(): string
    {
        return match ($this) {
            WorkType::WFO => 'Work from Office',
            WorkType::WFH => 'Work from Home',
            WorkType::WFA => 'Work from Anywhere',
            WorkType::WFC => 'Work from Cafe',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            WorkType::WFO => 'WFO',
            WorkType::WFH => 'WFH',
            WorkType::WFA => 'WFA',
            WorkType::WFC => 'WFC',
        };
    }

    public function requiresGeofencing(): bool
    {
        return $this === WorkType::WFO;
    }

    public function color(): string
    {
        return match ($this) {
            WorkType::WFO => 'blue',
            WorkType::WFH => 'emerald',
            WorkType::WFA => 'purple',
            WorkType::WFC => 'amber',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            WorkType::WFO => 'building',
            WorkType::WFH => 'home',
            WorkType::WFA => 'laptop',
            WorkType::WFC => 'coffee',
        };
    }

    /** Tipe yang bisa diajukan oleh karyawan WFO sebagai pengajuan remote work. */
    public static function remoteRequestable(): array
    {
        return [self::WFA, self::WFH, self::WFC];
    }
}
