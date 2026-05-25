<?php

namespace App\Enums;

enum WorkType: string
{
    case WFO = 'wfo';
    case WFA = 'wfa';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            WorkType::WFO => 'Work from Office',
            WorkType::WFA => 'Work from Anywhere',
            WorkType::Hybrid => 'Hybrid',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            WorkType::WFO => 'WFO',
            WorkType::WFA => 'WFA',
            WorkType::Hybrid => 'Hybrid',
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
            WorkType::WFA => 'purple',
            WorkType::Hybrid => 'teal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            WorkType::WFO => 'building',
            WorkType::WFA => 'laptop',
            WorkType::Hybrid => 'layers',
        };
    }

    /** Tipe yang bisa diajukan oleh karyawan WFO sebagai pengajuan remote work. */
    public static function remoteRequestable(): array
    {
        return [self::WFA];
    }
}
