<?php

namespace App\Livewire\Employee;

use App\Models\Attendance as AttendanceModel;
use App\Models\Holiday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Kalender')]
class Calendar extends Component
{
    public int $year;

    public int $month;

    /** Day whose attendance detail is shown below the grid, as Y-m-d. */
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
        $this->selectedDate = now()->toDateString();
    }

    public function previousMonth(): void
    {
        $this->goToMonth(Carbon::create($this->year, $this->month, 1)->subMonthNoOverflow());
    }

    public function nextMonth(): void
    {
        $this->goToMonth(Carbon::create($this->year, $this->month, 1)->addMonthNoOverflow());
    }

    /** Land on the month's first day, or on today when the month contains it. */
    private function goToMonth(Carbon $date): void
    {
        $this->year = $date->year;
        $this->month = $date->month;
        $this->selectedDate = $date->isSameMonth(now())
            ? now()->toDateString()
            : $date->startOfMonth()->toDateString();
    }

    /**
     * @return array<int, array<int, ?Carbon>>
     */
    private function buildWeeks(Carbon $monthStart, Carbon $monthEnd): array
    {
        $cursor = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $lastCell = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        while ($cursor->lte($lastCell)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $cursor->month === $monthStart->month ? $cursor->copy() : null;
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $monthStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $monthLabel = $monthStart->translatedFormat('F Y');

        $holidays = Holiday::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn (Holiday $holiday) => $holiday->date->toDateString());

        $attendances = $employee
            ? AttendanceModel::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get()
                ->keyBy(fn (AttendanceModel $attendance) => $attendance->attendance_date->toDateString())
            : collect();

        return view('livewire.employee.calendar', [
            'weeks' => $this->buildWeeks($monthStart, $monthEnd),
            'monthLabel' => $monthLabel,
            'holidays' => $holidays,
            'attendances' => $attendances,
            'isCurrentMonth' => $monthStart->isSameMonth(now()),
            'dayDetails' => $this->buildDayDetails($monthStart, $monthEnd, $attendances, $holidays),
        ]);
    }

    /**
     * Pre-render every day of the month so tapping a date can swap the detail
     * panel in the browser instead of waiting on a round trip.
     *
     * @param  Collection<string, AttendanceModel>  $attendances
     * @param  Collection<string, Holiday>  $holidays
     * @return array<string, array<string, mixed>>
     */
    private function buildDayDetails(Carbon $monthStart, Carbon $monthEnd, Collection $attendances, Collection $holidays): array
    {
        $details = [];

        for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
            $key = $day->toDateString();
            $attendance = $attendances->get($key);
            $holiday = $holidays->get($key);
            $status = $attendance?->status;

            $meta = [];
            if ($attendance?->work_minutes) {
                $meta[] = 'Durasi kerja '.intdiv($attendance->work_minutes, 60).'j '.$attendance->work_minutes % 60 .'m';
            }
            if ($attendance?->late_minutes) {
                $meta[] = 'Terlambat '.$attendance->late_minutes.' menit';
            }
            if ($attendance?->work_type) {
                $meta[] = $attendance->work_type->shortLabel();
            }

            $details[$key] = [
                'title' => $day->translatedFormat('l, d F Y'),
                'note' => $holiday?->holiday_name ?? ($day->isSunday() ? 'Hari Minggu' : null),
                'noteClass' => $holiday ? 'text-red-500' : 'text-navy-400',
                'status' => $status?->label(),
                'statusClass' => $status ? "bg-{$status->color()}-100 text-{$status->color()}-700" : '',
                'hasCheckIn' => (bool) $attendance?->check_in_at,
                'checkIn' => $attendance?->check_in_at?->format('H:i') ?? '--:--',
                'checkOut' => $attendance?->check_out_at?->format('H:i') ?? '--:--',
                'meta' => $meta,
                'empty' => $attendance
                    ? ($attendance->notes ?: 'Tidak ada catatan absensi masuk pada tanggal ini.')
                    : 'Belum ada data absensi pada tanggal ini.',
            ];
        }

        return $details;
    }
}
