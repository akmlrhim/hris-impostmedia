<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Beranda')]
class Home extends Component
{
    public function render(): mixed
    {
        $employee = auth()->user()?->employee;
        $today = now()->toDateString();

        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
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

        $announcements = Announcement::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $isWfo = $employee?->work_type === WorkType::WFO;

        $isLateAlert = $employee
            && ! $todayAttendance?->check_in_at
            && now('Asia/Makassar')->hour >= 9;

        return view('livewire.employee.home', compact(
            'employee',
            'todayAttendance',
            'monthStats',
            'announcements',
            'isWfo',
            'isLateAlert'
        ));
    }
}
