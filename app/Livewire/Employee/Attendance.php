<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Attendance as AttendanceModel;
use App\Models\AttendanceLog;
use App\Models\OfficeLocation;
use App\Models\RemoteWorkRequest;
use App\Models\WebauthnCredential;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Absensi')]
class Attendance extends Component
{
    public bool $showEarlyCheckoutWarning = false;

    public int $workedMinutes = 0;

    public string $webauthnChallenge = '';

    private const MINIMUM_WORK_MINUTES = 480;

    public function mount(): void
    {
        $this->refreshChallenge();
    }

    public function refreshChallenge(): void
    {
        $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        session(['webauthn_auth_challenge' => $challenge]);
        $this->webauthnChallenge = $challenge;
    }

    private const VALID_TIMEZONES = ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'];

    public function checkIn(?string $credentialId = null, ?string $clientDataJSON = null, ?float $latitude = null, ?float $longitude = null, ?string $address = null, ?string $selectedWorkType = null, ?string $password = null, ?string $timezone = null): void
    {
        $user = auth()->user();
        $employee = $user?->employee;

        if (! $employee) {
            $this->dispatch('notify', message: 'Akun belum terhubung ke data karyawan.', type: 'error');

            return;
        }

        $tz = in_array($timezone, self::VALID_TIMEZONES) ? $timezone : config('app.timezone');
        $localNow = now($tz);
        $today = $localNow->toDateString();

        if ($localNow->isSunday()) {
            $this->dispatch('notify', message: 'Absensi tidak tersedia pada hari libur.', type: 'warning');

            return;
        }

        $storedCredential = WebauthnCredential::where('user_id', $user->id)->first();

        if ($storedCredential) {
            if (! $credentialId || ! $clientDataJSON) {
                $this->dispatch('notify', message: 'Verifikasi biometrik diperlukan.', type: 'error');

                return;
            }

            if (! $this->verifyWebauthnAssertion($credentialId, $clientDataJSON)) {
                $this->dispatch('notify', message: 'Verifikasi biometrik gagal. Coba lagi.', type: 'error');
                $this->refreshChallenge();

                return;
            }
        } else {
            if (! $password || ! Hash::check($password, $user->password)) {
                $this->dispatch('notify', message: 'Kata sandi salah. Coba lagi.', type: 'error');

                return;
            }
        }

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
            $effectiveWorkType = WorkType::tryFrom($selectedWorkType ?? 'wfa') ?? WorkType::WFA;
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

        $attendance->fill([
            'check_in_at' => $localNow->format('Y-m-d H:i:s'),
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_address' => $address,
            'check_in_photo_path' => null,
            'status' => AttendanceStatus::Present,
            'work_type' => $effectiveWorkType->value,
            'late_minutes' => 0,
            'timezone' => $tz,
        ])->save();

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_in',
            'event_at' => $localNow->format('Y-m-d H:i:s'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'photo_path' => null,
            'ip_address' => request()->ip(),
            'device_info' => substr(request()->userAgent() ?? '', 0, 255),
        ]);

        $this->dispatch('notify', message: 'Check-in berhasil!', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    public function checkOut(?string $credentialId = null, ?string $clientDataJSON = null, ?float $latitude = null, ?float $longitude = null, ?string $address = null, ?string $password = null, ?string $timezone = null): void
    {
        $user = auth()->user();
        $employee = $user?->employee;

        if (! $employee) {
            $this->dispatch('notify', message: 'Akun belum terhubung ke data karyawan.', type: 'error');

            return;
        }

        $tz = in_array($timezone, self::VALID_TIMEZONES) ? $timezone : config('app.timezone');
        $localNow = now($tz);
        $todayDate = $localNow->toDateString();

        if ($localNow->isSunday()) {
            $this->dispatch('notify', message: 'Absensi tidak tersedia pada hari libur.', type: 'warning');

            return;
        }

        $storedCredential = WebauthnCredential::where('user_id', $user->id)->first();

        if ($storedCredential) {
            if (! $credentialId || ! $clientDataJSON) {
                $this->dispatch('notify', message: 'Verifikasi biometrik diperlukan.', type: 'error');

                return;
            }

            if (! $this->verifyWebauthnAssertion($credentialId, $clientDataJSON)) {
                $this->dispatch('notify', message: 'Verifikasi biometrik gagal. Coba lagi.', type: 'error');
                $this->refreshChallenge();

                return;
            }
        } else {
            if (! $password || ! Hash::check($password, $user->password)) {
                $this->dispatch('notify', message: 'Kata sandi salah. Coba lagi.', type: 'error');

                return;
            }
        }

        $attendance = AttendanceModel::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            $this->dispatch('notify', message: 'Anda belum check-in hari ini.', type: 'warning');

            return;
        }

        if ($attendance->check_out_at) {
            $this->dispatch('notify', message: 'Anda sudah check-out.', type: 'warning');

            return;
        }

        // diffInMinutes is UTC-based — accurate regardless of timezone
        $workedMinutes = (int) $attendance->check_in_at->diffInMinutes($localNow);

        if ($workedMinutes < self::MINIMUM_WORK_MINUTES && ! $this->showEarlyCheckoutWarning) {
            $this->workedMinutes = $workedMinutes;
            $this->showEarlyCheckoutWarning = true;
            $this->refreshChallenge();

            return;
        }

        $this->showEarlyCheckoutWarning = false;
        $workMinutes = (int) $attendance->check_in_at->diffInMinutes($localNow);

        $attendance->update([
            'check_out_at' => $localNow->format('Y-m-d H:i:s'),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_address' => $address,
            'check_out_photo_path' => null,
            'work_minutes' => $workMinutes,
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'event' => 'check_out',
            'event_at' => $localNow->format('Y-m-d H:i:s'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'photo_path' => null,
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

    private function verifyWebauthnAssertion(string $credentialId, string $clientDataJSON): bool
    {
        $user = auth()->user();

        $credential = WebauthnCredential::where('user_id', $user->id)
            ->where('credential_id', $credentialId)
            ->first();

        if (! $credential) {
            return false;
        }

        $decoded = base64_decode(strtr($clientDataJSON, '-_', '+/'));
        $data = json_decode($decoded, true);

        if (! $data || ($data['type'] ?? '') !== 'webauthn.get') {
            return false;
        }

        $storedChallenge = session('webauthn_auth_challenge');
        if (! $storedChallenge || ($data['challenge'] ?? '') !== $storedChallenge) {
            return false;
        }

        session()->forget('webauthn_auth_challenge');

        return true;
    }

    public function render(): mixed
    {
        $user = auth()->user();
        $employee = $user?->employee;
        $today = now()->toDateString();
        $isAdminPanelUser = $user?->role?->isAdminPanel() ?? false;

        $isSunday = now()->isSunday();
        $isOffDay = $isSunday;
        $offDayName = $isSunday ? 'Hari Minggu' : '';

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

        $webauthnCredential = $user
            ? WebauthnCredential::where('user_id', $user->id)->first()
            : null;

        return view('livewire.employee.attendance', compact(
            'employee',
            'attendance',
            'history',
            'workType',
            'baseWorkType',
            'remoteRequest',
            'officeLocations',
            'webauthnCredential',
            'isAdminPanelUser',
            'isOffDay',
            'offDayName'
        ));
    }
}
