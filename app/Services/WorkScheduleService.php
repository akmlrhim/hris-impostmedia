<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\WorkingDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class WorkScheduleService
{
    private const CACHE_KEY = 'work_schedule.working_weekdays';

    private const FALLBACK_WEEKDAYS = [1, 2, 3, 4, 5, 6];

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

    public function isWorkingWeekday(Carbon|string $date): bool
    {
        return in_array($this->toCarbon($date)->isoWeekday(), $this->workingWeekdays(), true);
    }

    public function isWorkingDay(Carbon|string $date): bool
    {
        $day = $this->toCarbon($date);

        return $this->isWorkingWeekday($day) && ! Holiday::isHoliday($day->toDateString());
    }

    public function isOffDay(Carbon|string $date): bool
    {
        return ! $this->isWorkingDay($date);
    }

    public function offDayReason(Carbon|string $date): ?string
    {
        $day = $this->toCarbon($date);

        $holiday = Holiday::where('date', $day->toDateString())->first();

        return $holiday?->holiday_name ?? $this->weeklyOffLabel($day);
    }

    public function weeklyOffLabel(Carbon|string $date): ?string
    {
        $day = $this->toCarbon($date);

        return $this->isWorkingWeekday($day) ? null : 'Hari '.$day->translatedFormat('l');
    }

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

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function toCarbon(Carbon|string $date): Carbon
    {
        return $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
    }
}
