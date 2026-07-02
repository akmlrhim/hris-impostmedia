<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Single source of truth for the company's work-start time and late-arrival
 * grace period, so check-in lateness and "haven't checked in yet" reminders
 * stay consistent instead of drifting across the codebase.
 */
class AttendanceLatenessService
{
    private const START_HOUR = 9;

    private const START_MINUTE = 0;

    private const TOLERANCE_MINUTES = 15;

    /** Official work-start time on the same calendar day as $localDate. */
    public function workStart(Carbon $localDate): Carbon
    {
        return $localDate->copy()->setTime(self::START_HOUR, self::START_MINUTE, 0);
    }

    /** Latest check-in time still considered on-time (start time + grace period). */
    public function lateThreshold(Carbon $localDate): Carbon
    {
        return $this->workStart($localDate)->addMinutes(self::TOLERANCE_MINUTES);
    }

    public function isLate(Carbon $checkInLocal): bool
    {
        return $checkInLocal->gt($this->lateThreshold($checkInLocal));
    }

    /** Minutes late relative to the official start time (0 when within the grace period). */
    public function lateMinutes(Carbon $checkInLocal): int
    {
        if (! $this->isLate($checkInLocal)) {
            return 0;
        }

        return (int) $this->workStart($checkInLocal)->diffInMinutes($checkInLocal);
    }
}
