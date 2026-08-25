<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\WorkingDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for which calendar days the company works.
 *
 * A day counts as a working day when its weekday is switched on in the
 * working_days table and it is not listed as a holiday in days_off. Every
 * off-day check in the app should go through here so changing the schedule
 * in the admin panel takes effect everywhere at once.
 */
class WorkScheduleService
{
    private const CACHE_KEY = 'work_schedule.working_weekdays';

    /** Used when the table has not been migrated/seeded yet (Mon–Sat). */
    private const FALLBACK_WEEKDAYS = [1, 2, 3, 4, 5, 6];

    /**
     * ISO-8601 weekday numbers the company works on (1 = Monday … 7 = Sunday).
     *
     * @return array<int, int>
     */
    public function workingWeekdays(): array
    {
        $weekdays = Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = WorkingDay::query()
                ->where('is_working', true)
                ->orderBy('weekday')
                ->pluck('weekday')
                ->all();

            return $stored ?: self::FALLBACK_WEEKDAYS;
        });

        return array_map('intval', $weekdays);
    }

    /** True when the company works on $date's weekday, ignoring holidays. */
    public function isWorkingWeekday(Carbon|string $date): bool
    {
        return in_array($this->toCarbon($date)->isoWeekday(), $this->workingWeekdays(), true);
    }

    /** True when $date is a normal working day: right weekday and not a holiday. */
    public function isWorkingDay(Carbon|string $date): bool
    {
        $day = $this->toCarbon($date);

        return $this->isWorkingWeekday($day) && ! Holiday::isHoliday($day->toDateString());
    }

    /** Inverse of isWorkingDay(), for the many call sites phrased as "is this a day off?". */
    public function isOffDay(Carbon|string $date): bool
    {
        return ! $this->isWorkingDay($date);
    }

    /**
     * Human-readable reason $date is off ("Hari Minggu", "Idul Fitri"), or null
     * when it is a normal working day.
     */
    public function offDayReason(Carbon|string $date): ?string
    {
        $day = $this->toCarbon($date);

        $holiday = Holiday::where('date', $day->toDateString())->first();

        return $holiday?->holiday_name ?? $this->weeklyOffLabel($day);
    }

    /**
     * "Hari Minggu" when the company does not work that weekday, otherwise null.
     * Holidays are not considered — callers that already loaded them handle those.
     */
    public function weeklyOffLabel(Carbon|string $date): ?string
    {
        $day = $this->toCarbon($date);

        return $this->isWorkingWeekday($day) ? null : 'Hari '.$day->translatedFormat('l');
    }

    /**
     * Every working date between $start and $end inclusive, as Y-m-d strings.
     *
     * @return array<int, string>
     */
    public function workingDatesBetween(Carbon|string $start, Carbon|string $end): array
    {
        $from = $this->toCarbon($start)->startOfDay();
        $to = $this->toCarbon($end)->startOfDay();

        if ($from->gt($to)) {
            return [];
        }

        $weekdays = $this->workingWeekdays();
        $holidays = Holiday::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date instanceof Carbon ? $date->toDateString() : (string) $date)
            ->flip();

        $dates = [];

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();

            if (in_array($day->isoWeekday(), $weekdays, true) && ! $holidays->has($key)) {
                $dates[] = $key;
            }
        }

        return $dates;
    }

    /** Drop the cached schedule after the admin edits it. */
    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function toCarbon(Carbon|string $date): Carbon
    {
        return $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
    }
}
