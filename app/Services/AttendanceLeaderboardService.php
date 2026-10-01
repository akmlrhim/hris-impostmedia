<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Ranks active employees by attendance for a month.
 *
 * One grouped query backs both the board and a single employee's own standing,
 * so the rank a person sees always comes from the same tally the board shows.
 */
class AttendanceLeaderboardService
{
    public const DEFAULT_LIMIT = 10;

    /**
     * @return Collection<int, object> rows with employee, present, on_time, late, late_minutes, rank
     */
    public function standings(int $year, int $month, int $limit = self::DEFAULT_LIMIT): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->where('employees.is_active', true)
            ->whereNull('employees.deleted_at')
            ->whereBetween('attendances.attendance_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('attendances.check_in_at')
            ->groupBy('attendances.employee_id')
            ->select('attendances.employee_id')
            ->selectRaw('MIN(attendances.check_in_at) as earliest_check_in')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as on_time', [AttendanceStatus::Present->value])
            ->selectRaw('SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as present', [
                AttendanceStatus::Present->value,
                AttendanceStatus::Late->value,
            ])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as late', [AttendanceStatus::Late->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN late_minutes ELSE 0 END), 0) as late_minutes', [
                AttendanceStatus::Late->value,
            ])
            ->get()
            ->filter(fn ($row) => (int) $row->present > 0);

        return $rows
            ->sort(function ($a, $b) {
                if ($a->earliest_check_in && $b->earliest_check_in) {
                    return $a->earliest_check_in <=> $b->earliest_check_in;
                }
                if ($b->present != $a->present) {
                    return $b->present <=> $a->present;
                }
                if ($b->on_time != $a->on_time) {
                    return $b->on_time <=> $a->on_time;
                }

                return (int) $a->late_minutes <=> (int) $b->late_minutes;
            })
            ->values()
            ->take($limit)
            ->map(function ($row, $index) {
                $row->rank = $index + 1;
                $row->on_time_rate = (int) $row->present > 0
                    ? round(((int) $row->on_time / (int) $row->present) * 100, 1)
                    : 0.0;

                return $row;
            });
    }

    /**
     * @return Collection<int, Employee>
     */
    public function board(int $year, int $month, int $limit = self::DEFAULT_LIMIT): Collection
    {
        $rows = $this->standings($year, $month, $limit);

        return Employee::whereIn('id', $rows->pluck('employee_id'))
            ->get()
            ->keyBy('id')
            ->map(fn (Employee $employee) => tap(clone $employee, function ($e) {}))
            ->values()
            ->map(function (Employee $employee) use ($rows) {
                $row = $rows->firstWhere('employee_id', $employee->id);

                $employee->setAttribute('rank', $row->rank);
                $employee->setAttribute('present', (int) $row->present);
                $employee->setAttribute('on_time', (int) $row->on_time);
                $employee->setAttribute('late', (int) $row->late);
                $employee->setAttribute('late_minutes', (int) $row->late_minutes);
                $employee->setAttribute('on_time_rate', $row->on_time_rate);

                return $employee;
            })
            ->sortBy('rank')
            ->values();
    }

    /**
     * @return array{rank: int|null, total: int, present: int, on_time: int, late: int, late_minutes: int, on_time_rate: float}
     */
    public function standingFor(Employee $employee, int $year, int $month): array
    {
        $rows = $this->standings($year, $month, PHP_INT_MAX);
        $row = $rows->firstWhere('employee_id', $employee->id);

        if (! $row) {
            return [
                'rank' => null,
                'total' => $rows->count(),
                'present' => 0,
                'on_time' => 0,
                'late' => 0,
                'late_minutes' => 0,
                'on_time_rate' => 0.0,
            ];
        }

        return [
            'rank' => $row->rank,
            'total' => $rows->count(),
            'present' => (int) $row->present,
            'on_time' => (int) $row->on_time,
            'late' => (int) $row->late,
            'late_minutes' => (int) $row->late_minutes,
            'on_time_rate' => $row->on_time_rate,
        ];
    }
}
