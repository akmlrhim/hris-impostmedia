<?php

namespace App\Livewire\Admin;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render(): mixed
    {
        $today = now()->toDateString();

        $stats = [
            'total_employees' => Employee::where('is_active', true)->count(),
            'present_today' => Attendance::whereDate('attendance_date', $today)
                ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                ->count(),
            'absent_today' => Attendance::whereDate('attendance_date', $today)
                ->where('status', AttendanceStatus::Absent)
                ->count(),
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

        return view('livewire.admin.dashboard', compact(
            'stats',
            'recentAttendance',
            'myEmployee',
            'myAttendance'
        ));
    }
}
