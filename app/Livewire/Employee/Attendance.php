<?php

namespace App\Livewire\Employee;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Attendance as AttendanceModel;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\RemoteWorkRequest;
use App\Models\WebauthnCredential;
use App\Services\AttendanceLatenessService;
use App\Services\WorkScheduleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.mobile')]
#[Title('Absensi')]
class Attendance extends Component
{
    use WithPagination;

    public bool $showEarlyCheckoutWarning = false;

    public int $workedMinutes = 0;

    public string $webauthnChallenge = '';

    // Location captured during the (already biometric-verified) check-out attempt
    // that triggered the early-checkout warning, reused when the user confirms so
    // they don't have to scan their biometric a second time.
    public ?float $pendingCheckoutLatitude = null;

    public ?float $pendingCheckoutLongitude = null;

    public ?string $pendingCheckoutAddress = null;

    public ?string $pendingCheckoutTimezone = null;

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
        // attendance_date must match the date render() uses (app default timezone),
        // otherwise an employee in a different timezone gets a record under a
        // different date and the page keeps showing the check-in button.
        $today = now()->toDateString();

        if ($this->isNonWorkingDay($today)) {
            $this->dispatch('notify', message: 'Absensi tidak tersedia pada hari libur.', type: 'warning');

            return;
        }

        if (! $this->verifyAuthCredential($credentialId, $clientDataJSON, $password)) {
            return;
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
            if (is_null($latitude) || is_null($longitude)) {
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

        // Lateness applies the same way to every work type (WFO/WFA/Hybrid) — an
        // approved remote-work request changes where an employee works, not when.
        $lateness = app(AttendanceLatenessService::class);
        $isLate = $lateness->isLate($localNow);

        DB::transaction(function () use ($attendance, $localNow, $latitude, $longitude, $address, $effectiveWorkType, $tz, $employee, $lateness, $isLate) {
            $attendance->fill([
                'check_in_at' => $localNow->format('Y-m-d H:i:s'),
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'check_in_address' => $address,
                'check_in_photo_path' => null,
                'status' => $isLate ? AttendanceStatus::Late : AttendanceStatus::Present,
                'work_type' => $effectiveWorkType->value,
                'late_minutes' => $lateness->lateMinutes($localNow),
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
        });

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

        if (! $this->verifyAuthCredential($credentialId, $clientDataJSON, $password)) {
            return;
        }

        $attendance = $this->findTodayAttendance($employee->id);

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

        if ($workedMinutes < self::MINIMUM_WORK_MINUTES) {
            // Biometric already verified above. Stash the verified location so the
            // confirmation step can finalize without asking for biometrics again.
            $this->workedMinutes = $workedMinutes;
            $this->showEarlyCheckoutWarning = true;
            $this->pendingCheckoutLatitude = $latitude;
            $this->pendingCheckoutLongitude = $longitude;
            $this->pendingCheckoutAddress = $address;
            $this->pendingCheckoutTimezone = $tz;
            // Refresh so that cancelling and retrying check-out has a valid challenge
            // (the one just used above was consumed during verification).
            $this->refreshChallenge();

            return;
        }

        $this->finalizeCheckOut($attendance, $localNow, $workedMinutes, $latitude, $longitude, $address, $employee);
    }

    /**
     * Finalize an early check-out the user confirmed after the warning.
     * No biometric re-verification: the originating checkOut() call already
     * verified the credential before the warning was shown.
     */
    public function confirmEarlyCheckout(): void
    {
        // Guard: only reachable after a verified checkOut() raised the warning.
        if (! $this->showEarlyCheckoutWarning) {
            return;
        }

        $user = auth()->user();
        $employee = $user?->employee;

        if (! $employee) {
            $this->dispatch('notify', message: 'Akun belum terhubung ke data karyawan.', type: 'error');

            return;
        }

        $tz = in_array($this->pendingCheckoutTimezone, self::VALID_TIMEZONES) ? $this->pendingCheckoutTimezone : config('app.timezone');
        $localNow = now($tz);

        $attendance = $this->findTodayAttendance($employee->id);

        if (! $attendance || ! $attendance->check_in_at) {
            $this->resetEarlyCheckout();
            $this->dispatch('notify', message: 'Anda belum check-in hari ini.', type: 'warning');

            return;
        }

        if ($attendance->check_out_at) {
            $this->resetEarlyCheckout();
            $this->dispatch('notify', message: 'Anda sudah check-out.', type: 'warning');

            return;
        }

        $workedMinutes = (int) $attendance->check_in_at->diffInMinutes($localNow);

        $this->finalizeCheckOut(
            $attendance,
            $localNow,
            $workedMinutes,
            $this->pendingCheckoutLatitude,
            $this->pendingCheckoutLongitude,
            $this->pendingCheckoutAddress,
            $employee,
        );
    }

    /**
     * Find today's attendance only. A forgotten check-out from a previous day is
     * intentionally left open (empty) so the employee can check in for today
     * without having to close yesterday's session first.
     */
    private function findTodayAttendance(int $employeeId): ?AttendanceModel
    {
        return AttendanceModel::where('employee_id', $employeeId)
            ->where('attendance_date', now()->toDateString())
            ->first();
    }

    private function isNonWorkingDay(string $date): bool
    {
        return app(WorkScheduleService::class)->isOffDay($date);
    }

    private function finalizeCheckOut(AttendanceModel $attendance, Carbon $localNow, int $workedMinutes, ?float $latitude, ?float $longitude, ?string $address, Employee $employee): void
    {
        DB::transaction(function () use ($attendance, $localNow, $workedMinutes, $latitude, $longitude, $address, $employee) {
            $attendance->update([
                'check_out_at' => $localNow->format('Y-m-d H:i:s'),
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'check_out_address' => $address,
                'check_out_photo_path' => null,
                'work_minutes' => $workedMinutes,
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
        });

        $this->resetEarlyCheckout();
        $this->dispatch('notify', message: 'Check-out berhasil! Terima kasih.', type: 'success');
        $this->dispatch('attendance-recorded');
    }

    private function resetEarlyCheckout(): void
    {
        $this->showEarlyCheckoutWarning = false;
        $this->workedMinutes = 0;
        $this->pendingCheckoutLatitude = null;
        $this->pendingCheckoutLongitude = null;
        $this->pendingCheckoutAddress = null;
        $this->pendingCheckoutTimezone = null;
    }

    public function cancelEarlyCheckout(): void
    {
        $this->resetEarlyCheckout();
    }

    private function verifyAuthCredential(?string $credentialId, ?string $clientDataJSON, ?string $password): bool
    {
        $user = auth()->user();
        $storedCredential = WebauthnCredential::where('user_id', $user->id)->first();

        if ($storedCredential) {
            if (! $credentialId || ! $clientDataJSON) {
                $this->dispatch('notify', message: 'Verifikasi biometrik diperlukan.', type: 'error');

                return false;
            }

            if (! $this->verifyWebauthnAssertion($credentialId, $clientDataJSON)) {
                $this->dispatch('notify', message: 'Verifikasi biometrik gagal. Coba lagi.', type: 'error');
                $this->refreshChallenge();

                return false;
            }
        } else {
            if (! $password || ! Hash::check($password, $user->password)) {
                $this->dispatch('notify', message: 'Kata sandi salah. Coba lagi.', type: 'error');

                return false;
            }
        }

        return true;
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
        $isAdminPanelUser = $user?->isAdminPanel() ?? false;

        // Only today's record matters: a forgotten check-out from a previous day
        // is left open (empty) so the employee can check in for today directly.
        $attendance = $employee ? $this->findTodayAttendance($employee->id) : null;

        $schedule = app(WorkScheduleService::class);
        $isOffDay = $schedule->isOffDay($today);
        $offDayName = $schedule->offDayReason($today) ?? '';

        $history = $employee
            ? AttendanceModel::where('employee_id', $employee->id)
                ->where('attendance_date', '<', $today)
                ->orderByDesc('attendance_date')
                ->paginate(10)
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
