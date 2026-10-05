<?php

namespace App\Livewire\Employee;

use App\Enums\LeaveStatus;
use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AttendanceLatenessService;
use App\Services\AttendanceLeaderboardService;
use App\Services\WorkScheduleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Beranda')]
class Home extends Component
{
    public function render(AttendanceLatenessService $lateness, WorkScheduleService $schedule, AttendanceLeaderboardService $leaderboardService): mixed
    {
        $employee = auth()->user()?->employee;
        $today = now()->toDateString();

        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('attendance_date', $today)
                ->first()
            : null;

        $stats = null;

        if ($employee) {
            $stats = $leaderboardService->standingFor($employee, now()->year, now()->month);
            $stats['absent'] = $this->absentCount($employee, $schedule);
        }

        $announcements = Announcement::currentlyPublished()
            ->visibleTo(auth()->user())
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $isWfo = $employee?->work_type === WorkType::WFO;

        $isOffDay = $schedule->isOffDay($today);

        $isLateAlert = $employee
            && ! $isOffDay
            && ! $todayAttendance?->check_in_at
            && $lateness->isLate(now('Asia/Makassar'));

        $workStartLabel = $lateness->workStart(now('Asia/Makassar'))->format('H:i');

        return view('livewire.employee.home', compact(
            'employee',
            'todayAttendance',
            'stats',
            'announcements',
            'isWfo',
            'isLateAlert',
            'isOffDay',
            'workStartLabel'
        ));
    }

    private function absentCount(Employee $employee, WorkScheduleService $schedule): int
    {
        $start = now()->startOfMonth();
        $end = now()->subDay()->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $workingDates = $schedule->workingDatesBetween($start, $end);

        $attendanceDates = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('attendance_date')
            ->map(fn ($d) => $d->toDateString())
            ->flip();

        $leaveDates = [];

        LeaveRequest::where('employee_id', $employee->id)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get(['start_date', 'end_date'])
            ->each(function ($leave) use (&$leaveDates) {
                for ($day = $leave->start_date->copy()->startOfDay(); $day->lte($leave->end_date->startOfDay()); $day->addDay()) {
                    $leaveDates[$day->toDateString()] = true;
                }
            });

        $joined = $employee->contract_start_date?->toDateString();

        $absent = 0;

        foreach ($workingDates as $date) {
            if ($attendanceDates->has($date) || isset($leaveDates[$date])) {
                continue;
            }

            if ($joined && $date < $joined) {
                continue;
            }

            $absent++;
        }

        return $absent;
    }
}
