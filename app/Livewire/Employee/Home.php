<?php

namespace App\Livewire\Employee;

use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
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

        $standing = null;
        $leaderboard = collect();

        if ($employee) {
            $all = $leaderboardService->standings(now()->year, now()->month, PHP_INT_MAX);
            $standing = $all->firstWhere('employee_id', $employee->id);
            if ($standing) {
                $standing['total'] = $all->count();
            }
            if (! $standing) {
                $standing = [
                    'rank' => null,
                    'total' => $all->count(),
                    'present' => 0,
                    'on_time' => 0,
                    'late' => 0,
                    'late_minutes' => 0,
                    'on_time_rate' => 0.0,
                ];
            }
            $leaderboard = $leaderboardService->board(now()->year, now()->month, 5);
        }

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
            'standing',
            'leaderboard',

            'announcements',
            'isWfo',
            'isLateAlert',
            'isOffDay',
            'workStartLabel'
        ));
    }
}
