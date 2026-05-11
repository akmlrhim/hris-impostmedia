<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Attendance as AttendanceModel;
use App\Models\AttendanceLog;
use App\Models\OfficeLocation;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Attendance extends Component
{
    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $address = null;

    public ?string $checkInPhoto = null;

    public ?string $checkOutPhoto = null;

    public function checkIn(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            $this->dispatch('notify', message: 'Akun belum terhubung ke data karyawan.', type: 'error');

            return;
        }

        $today = now()->toDateString();

        $attendance = AttendanceModel::firstOrNew([
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

        $workType = $schedule?->work_type instanceof WorkType ? $schedule->work_type : WorkType::WFA;

        if ($workType->requiresGeofencing()) {
            if (! $this->latitude || ! $this->longitude) {
                $this->dispatch('notify', message: 'GPS diperlukan untuk absensi WFO. Izinkan akses lokasi.', type: 'error');

                return;
            }

            $offices = OfficeLocation::where('is_active', true)->get();
            $withinRadius = $offices->contains(fn ($office) => $office->isWithinRadius($this->latitude, $this->longitude));

            if (! $withinRadius) {
                $this->dispatch('notify', message: 'Anda berada di luar radius kantor. Absensi WFO tidak dapat diproses.', type: 'error');

                return;
            }
        }

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

        $photoPath = $this->savePhoto($this->checkInPhoto, "ci_{$employee->id}_".now()->format('Ymd_His'));

        $attendance->fill([
            'shift_id' => $shift?->id,
            'check_in_at' => now(),
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'check_in_address' => $this->address,
            'check_in_photo_path' => $photoPath,
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
            'photo_path' => $photoPath,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->checkInPhoto = null;
        $this->dispatch('notify', message: 'Check-in berhasil!', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    public function checkOut(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            $this->dispatch('notify', message: 'Akun belum terhubung ke data karyawan.', type: 'error');

            return;
        }

        $attendance = AttendanceModel::where('employee_id', $employee->id)
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
        $photoPath = $this->savePhoto($this->checkOutPhoto, "co_{$employee->id}_".now()->format('Ymd_His'));

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'check_out_address' => $this->address,
            'check_out_photo_path' => $photoPath,
            'work_minutes' => (int) $workMinutes,
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_out',
            'event_at' => now(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'photo_path' => $photoPath,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->checkOutPhoto = null;
        $this->dispatch('notify', message: 'Check-out berhasil!', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    private function savePhoto(?string $base64Data, string $filename): ?string
    {
        if (! $base64Data) {
            return null;
        }

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        $decoded = base64_decode($imageData, strict: true);

        if ($decoded === false) {
            return null;
        }

        $path = "attendance-photos/{$filename}.jpg";
        Storage::disk('local')->put($path, $decoded);

        return $path;
    }

    public function render(): mixed
    {
        $user = auth()->user();
        $employee = $user?->employee;
        $today = now()->toDateString();
        $isAdminPanelUser = $user?->role?->isAdminPanel() ?? false;

        $attendance = $employee
            ? AttendanceModel::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->first()
            : null;

        $history = $employee
            ? AttendanceModel::where('employee_id', $employee->id)
                ->whereDate('attendance_date', '<', $today)
                ->orderByDesc('attendance_date')
                ->limit(10)
                ->get()
            : collect();

        $schedule = $employee
            ? WorkSchedule::where('employee_id', $employee->id)
                ->whereDate('work_date', $today)
                ->first()
            : null;

        $workType = $schedule?->work_type instanceof WorkType ? $schedule->work_type : WorkType::WFA;

        $officeLocations = $workType->requiresGeofencing()
            ? OfficeLocation::where('is_active', true)->get(['id', 'name', 'latitude', 'longitude', 'radius_meters'])
            : collect();

        $faceDescriptor = $employee?->face_descriptor;

        return view('livewire.employee.attendance', compact(
            'employee', 'attendance', 'history', 'workType', 'officeLocations', 'faceDescriptor', 'isAdminPanelUser'
        ));
    }
}
