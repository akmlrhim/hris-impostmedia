<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Services\AttendanceLatenessService;
use App\Services\WorkScheduleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Beranda')]
class Home extends Component
{
    public function render(AttendanceLatenessService $lateness, WorkScheduleService $schedule): mixed
    {
        $employee = auth()->user()?->employee;
        $today = now()->toDateString();

        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('attendance_date', $today)
                ->first()
            : null;

        $monthStats = null;
        if ($employee) {
            $monthStats = [
                'hadir' => Attendance::where('employee_id', $employee->id)
                    ->whereYear('attendance_date', now()->year)
                    ->whereMonth('attendance_date', now()->month)
                    ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                    ->count(),
                'terlambat' => Attendance::where('employee_id', $employee->id)
                    ->whereYear('attendance_date', now()->year)
                    ->whereMonth('attendance_date', now()->month)
                    ->where('status', AttendanceStatus::Late)
                    ->count(),
                'tidak_hadir' => Attendance::where('employee_id', $employee->id)
                    ->whereYear('attendance_date', now()->year)
                    ->whereMonth('attendance_date', now()->month)
                    ->where('status', AttendanceStatus::Absent)
                    ->count(),
            ];
        }

        $recentAttendances = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereNotNull('check_in_at')
                ->where('attendance_date', '<', $today)
                ->orderByDesc('attendance_date')
                ->limit(3)
                ->get()
            : collect();

        $announcements = Announcement::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
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
            'monthStats',
            'recentAttendances',
            'announcements',
            'isWfo',
            'isLateAlert',
            'isOffDay',
            'workStartLabel'
        ));
    }
}
