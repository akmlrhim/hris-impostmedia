<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AttendanceLatenessService
{
    private const TIMEZONE = 'Asia/Makassar';

    private const START_HOUR = 9;

    private const START_MINUTE = 0;

    private const TOLERANCE_MINUTES = 15;

    public function workStart(Carbon $localDate): Carbon
    {
        return $this->toWita($localDate)->setTime(self::START_HOUR, self::START_MINUTE, 0);
    }

    public function lateThreshold(Carbon $localDate): Carbon
    {
        return $this->workStart($localDate)->addMinutes(self::TOLERANCE_MINUTES);
    }

    public function isLate(Carbon $checkInLocal): bool
    {
        return $this->toWita($checkInLocal)->startOfMinute()->gt($this->lateThreshold($checkInLocal));
    }

    public function lateMinutes(Carbon $checkInLocal): int
    {
        if (! $this->isLate($checkInLocal)) {
            return 0;
        }

        return (int) $this->workStart($checkInLocal)->diffInMinutes($this->toWita($checkInLocal)->startOfMinute());
    }

    private function toWita(Carbon $moment): Carbon
    {
        return $moment->copy()->setTimezone(self::TIMEZONE);
    }
}
