<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Attendance as AttendanceModel;
use App\Models\AttendanceLog;
use App\Models\OfficeLocation;
use App\Models\RemoteWorkRequest;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Absensi')]
class Attendance extends Component
{
    public bool $showEarlyCheckoutWarning = false;

    public int $workedMinutes = 0;

    private const MINIMUM_WORK_MINUTES = 480;

    public function checkIn(?string $photo = null, ?float $latitude = null, ?float $longitude = null, ?string $address = null, ?string $selectedWorkType = null): void
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

        $baseWorkType = $employee->work_type ?? WorkType::WFA;

        $remoteRequest = RemoteWorkRequest::approvedFor($employee->id, $today);
        if ($remoteRequest) {
            $effectiveWorkType = $remoteRequest->work_type;
        } elseif ($baseWorkType === WorkType::Hybrid) {
            // Karyawan Hybrid memilih mode WFO atau WFA saat check-in
            $effectiveWorkType = WorkType::tryFrom($selectedWorkType ?? 'wfa') ?? WorkType::WFA;
            // Mode Hybrid hanya bisa WFO atau WFA
            if ($effectiveWorkType === WorkType::Hybrid) {
                $effectiveWorkType = WorkType::WFA;
            }
        } else {
            $effectiveWorkType = $baseWorkType;
        }

        if ($effectiveWorkType->requiresGeofencing()) {
            if (! $latitude || ! $longitude) {
                $this->dispatch('notify', message: 'GPS diperlukan untuk absensi WFO. Izinkan akses lokasi.', type: 'error');

                return;
            }

            $offices = OfficeLocation::where('is_active', true)->get();
            $withinRadius = $offices->contains(fn ($office) => $office->isWithinRadius($latitude, $longitude));

            if (! $withinRadius) {
                $this->dispatch('notify', message: 'Anda berada di luar radius kantor. Absensi WFO tidak dapat diproses.', type: 'error');

                return;
            }
        }

        $photoPath = $this->savePhoto($photo, "ci_{$employee->id}_".now()->format('Ymd_His'));

        $attendance->fill([
            'check_in_at' => now(),
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_address' => $address,
            'check_in_photo_path' => $photoPath,
            'status' => AttendanceStatus::Present,
            'work_type' => $effectiveWorkType->value,
            'late_minutes' => 0,
        ])->save();

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_in',
            'event_at' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'photo_path' => $photoPath,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->dispatch('notify', message: 'Check-in berhasil!', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    public function checkOut(?string $photo = null, ?float $latitude = null, ?float $longitude = null, ?string $address = null): void
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

        $workedMinutes = (int) $attendance->check_in_at->diffInMinutes(now());

        if ($workedMinutes < self::MINIMUM_WORK_MINUTES && ! $this->showEarlyCheckoutWarning) {
            $this->workedMinutes = $workedMinutes;
            $this->showEarlyCheckoutWarning = true;

            return;
        }

        $this->showEarlyCheckoutWarning = false;
        $workMinutes = (int) $attendance->check_in_at->diffInMinutes(now());
        $photoPath = $this->savePhoto($photo, "co_{$employee->id}_".now()->format('Ymd_His'));

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_address' => $address,
            'check_out_photo_path' => $photoPath,
            'work_minutes' => (int) $workMinutes,
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_out',
            'event_at' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'photo_path' => $photoPath,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->dispatch('notify', message: 'Check-out berhasil! Terima kasih.', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    public function cancelEarlyCheckout(): void
    {
        $this->showEarlyCheckoutWarning = false;
        $this->workedMinutes = 0;
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

        $baseWorkType = $employee?->work_type ?? WorkType::WFA;
        $remoteRequest = $employee ? RemoteWorkRequest::approvedFor($employee->id, $today) : null;
        $workType = $remoteRequest ? $remoteRequest->work_type : $baseWorkType;

        $officeLocations = ($workType->requiresGeofencing() || $workType === WorkType::Hybrid)
            ? OfficeLocation::where('is_active', true)->get(['id', 'name', 'latitude', 'longitude', 'radius_meters'])
            : collect();

        $faceDescriptor = $employee?->face_descriptor;

        return view('livewire.employee.attendance', compact(
            'employee', 'attendance', 'history', 'workType', 'baseWorkType',
            'remoteRequest', 'officeLocations', 'faceDescriptor', 'isAdminPanelUser'
        ));
    }
}
