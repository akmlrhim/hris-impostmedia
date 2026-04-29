<?php

namespace App\Livewire\Employee\Home;

use App\Enums\RequestStatus;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
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

        $pendingLeaves = $employee
            ? LeaveRequest::where('employee_id', $employee->id)
                ->where('status', RequestStatus::Pending)
                ->count()
            : 0;

        $announcements = Announcement::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('livewire.employee.home.index', compact('employee', 'todayAttendance', 'pendingLeaves', 'announcements'));
    }
}
