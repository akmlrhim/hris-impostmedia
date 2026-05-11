<?php

namespace App\Enums;

enum WorkType: string
{
    case WFO = 'wfo';
    case WFH = 'wfh';
    case WFA = 'wfa';

    public function label(): string
    {
        return match ($this) {
            WorkType::WFO => 'Work from Office',
            WorkType::WFH => 'Work from Home',
            WorkType::WFA => 'Work from Anywhere',
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
        };
    }
}
