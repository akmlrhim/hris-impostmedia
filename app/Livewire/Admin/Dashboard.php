<?php

namespace App\Livewire\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceLatenessService;
use App\Services\WorkScheduleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render(AttendanceLatenessService $lateness, WorkScheduleService $schedule): mixed
    {
        $today = now()->toDateString();
        $isOffDay = $schedule->isOffDay($today);

        $stats = [
            'total_employees' => Employee::where('is_active', true)->count(),
            'present_today' => Attendance::whereDate('attendance_date', $today)
                ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                ->count(),
            'not_checked_in' => $this->countNotCheckedIn($today, $isOffDay),
        ];

        $recentAttendance = Attendance::with('employee')
            ->whereDate('attendance_date', $today)
            ->latest('check_in_at')
            ->limit(5)
            ->get();

        $myEmployee = auth()->user()?->employee;
        $myAttendance = $myEmployee
            ? Attendance::where('employee_id', $myEmployee->id)
                ->whereDate('attendance_date', $today)
                ->first()
            : null;

        $isLateAlert = $myEmployee
            && ! $isOffDay
            && ! $myAttendance?->check_in_at
            && $lateness->isLate(now('Asia/Makassar'));

        return view('livewire.admin.dashboard', compact(
            'stats',
            'recentAttendance',
            'myEmployee',
            'myAttendance',
            'isLateAlert',
            'isOffDay'
        ));
    }

    /**
     * Active employees who have not checked in today, ignoring anyone on
     * approved leave. Days off return 0 — nobody is expected in.
     *
     * Deliberately counted as "belum absen", not "absen": the day is still
     * running, so a missing check-in at 09:00 is not yet an absence. The
     * unexcused tally lives in the monthly recap, where the day has ended.
     */
    private function countNotCheckedIn(string $today, bool $isOffDay): int
    {
        if ($isOffDay) {
            return 0;
        }

        return Employee::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('contract_start_date')
                ->orWhereDate('contract_start_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('contract_end_date')
                ->orWhereDate('contract_end_date', '>=', $today))
            ->whereDoesntHave('attendances', fn ($q) => $q
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in_at'))
            ->whereDoesntHave('leaveRequests', fn ($q) => $q
                ->where('status', LeaveStatus::Approved)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today))
            ->count();
    }
}
