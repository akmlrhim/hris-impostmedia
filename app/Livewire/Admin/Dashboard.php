<?php

namespace App\Livewire\Admin;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceLatenessService;
use App\Services\WorkScheduleService;
use Illuminate\Support\Facades\Cache;
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

        $stats = Cache::remember('admin.dashboard.stats.v2.'.$today, 300, function () use ($today) {
            $total = Employee::where('is_active', true)->count();

            $counts = fn (string $date) => [
                'present' => Attendance::where('attendance_date', $date)
                    ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                    ->count(),
                'late' => Attendance::where('attendance_date', $date)
                    ->where('status', AttendanceStatus::Late)
                    ->count(),
            ];

            $todayCounts = $counts($today);
            $yesterdayCounts = $counts(now()->subDay()->toDateString());

            $pct = fn (int $current, int $previous) => $previous > 0
                ? (int) round(($current - $previous) / $previous * 100)
                : null;

            $missing = max(0, $total - $todayCounts['present']);
            $missingYesterday = max(0, $total - $yesterdayCounts['present']);

            return [
                'total_employees' => $total,
                'present_today' => $todayCounts['present'],
                'late_today' => $todayCounts['late'],
                'missing_today' => $missing,
                'delta' => [
                    'present' => $pct($todayCounts['present'], $yesterdayCounts['present']),
                    'late' => $pct($todayCounts['late'], $yesterdayCounts['late']),
                    'missing' => $pct($missing, $missingYesterday),
                ],
            ];
        });

        $recentAttendance = Attendance::with('employee')
            ->where('attendance_date', $today)
            ->latest('check_in_at')
            ->limit(5)
            ->get();

        $myEmployee = auth()->user()?->employee;
        $myAttendance = $myEmployee
            ? Attendance::where('employee_id', $myEmployee->id)
                ->where('attendance_date', $today)
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
}
