<?php

namespace App\Livewire\Admin\Employee;

use App\Concerns\HandlesAdminActions;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    use HandlesAdminActions;

    public Employee $employee;

    public function mount(Employee $employee): void
    {
        Gate::authorize('manage_employees');

        $this->employee = $employee->load('user');
    }

    public function toggleActive(): void
    {
        $this->safeAction(function () {
            $this->employee->update(['is_active' => ! $this->employee->is_active]);
            $this->employee->refresh();
            $this->toast('success', $this->employee->is_active ? 'Karyawan diaktifkan.' : 'Karyawan dinonaktifkan.');
        }, permission: 'manage_employees', genericError: 'Gagal mengubah status karyawan.');
    }

    public function render(): mixed
    {
        $emp = $this->employee;

        $attendanceStats = [
            'present' => Attendance::where('employee_id', $emp->id)
                ->whereYear('attendance_date', now()->year)
                ->whereMonth('attendance_date', now()->month)
                ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                ->count(),
            'late' => Attendance::where('employee_id', $emp->id)
                ->whereYear('attendance_date', now()->year)
                ->whereMonth('attendance_date', now()->month)
                ->where('status', AttendanceStatus::Late)
                ->count(),
        ];

        $recentPayrolls = Payroll::with('period')
            ->where('employee_id', $emp->id)
            ->latest()
            ->limit(6)
            ->get();

        return view('livewire.admin.employee.show', compact(
            'attendanceStats',
            'recentPayrolls',
        ))->title($this->employee->full_name.' - Karyawan');
    }
}
