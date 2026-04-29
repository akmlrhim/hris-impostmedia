<?php

namespace App\Livewire\Employee\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
{
    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $address = null;

    public function checkIn(): void
    {
        $employee = auth()->user()?->employee;
        abort_if(! $employee, 403, 'Akun belum terhubung dengan data karyawan.');

        $today = now()->toDateString();

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
        ]);

        if ($attendance->check_in_at) {
            $this->dispatch('notify', message: 'Anda sudah check-in hari ini.', type: 'warning');

            return;
        }

        $schedule = WorkSchedule::where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->with('shift')
            ->first();

        $shift = $schedule?->shift;
        $lateMinutes = 0;
        $status = AttendanceStatus::Present;

        if ($shift) {
            $shiftStart = Carbon::parse($today.' '.$shift->start_time);
            $diff = now()->diffInMinutes($shiftStart, false);
            if ($diff < -$shift->late_tolerance_minutes) {
                $lateMinutes = abs($diff) - $shift->late_tolerance_minutes;
                $status = AttendanceStatus::Late;
            }
        }

        $attendance->fill([
            'shift_id' => $shift?->id,
            'check_in_at' => now(),
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'check_in_address' => $this->address,
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ])->save();

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_in',
            'event_at' => now(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->dispatch('notify', message: 'Check-in berhasil!', type: 'success');
    }

    public function checkOut(): void
    {
        $employee = auth()->user()?->employee;
        abort_if(! $employee, 403);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            $this->dispatch('notify', message: 'Anda belum check-in hari ini.', type: 'warning');

            return;
        }

        if ($attendance->check_out_at) {
            $this->dispatch('notify', message: 'Anda sudah check-out.', type: 'warning');

            return;
        }

        $workMinutes = $attendance->check_in_at->diffInMinutes(now());

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'check_out_address' => $this->address,
            'work_minutes' => (int) $workMinutes,
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_out',
            'event_at' => now(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->dispatch('notify', message: 'Check-out berhasil!', type: 'success');
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;
        $today = now()->toDateString();

        $attendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->first()
            : null;

        $history = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', '<', $today)
                ->orderByDesc('attendance_date')
                ->limit(10)
                ->get()
            : collect();

        return view('livewire.employee.attendance.index', compact('attendance', 'history'));
    }
}
