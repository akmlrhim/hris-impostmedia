<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\RequestStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Reimbursement;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
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
            'on_leave_today' => LeaveRequest::where('status', RequestStatus::Approved)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count(),
            'pending_leaves' => LeaveRequest::where('status', RequestStatus::Pending)->count(),
            'pending_reimbursements' => Reimbursement::where('status', RequestStatus::Pending)->count(),
        ];

        $recentLeaves = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', RequestStatus::Pending)
            ->latest()
            ->limit(5)
            ->get();

        $recentAttendance = Attendance::with('employee')
            ->whereDate('attendance_date', $today)
            ->latest('check_in_at')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard.index', compact('stats', 'recentLeaves', 'recentAttendance'));
    }
}
