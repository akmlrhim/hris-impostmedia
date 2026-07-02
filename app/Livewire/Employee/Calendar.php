<?php

namespace App\Livewire\Employee;

use App\Models\Attendance as AttendanceModel;
use App\Models\Holiday;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Kalender')]
class Calendar extends Component
{
    public int $year;

    public int $month;

    public function mount(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonthNoOverflow();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonthNoOverflow();
        $this->year = $date->year;
        $this->month = $date->month;
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
        ]);
    }
}
