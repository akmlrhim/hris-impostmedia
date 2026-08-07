<?php

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Livewire\Admin\Attendance as AdminAttendance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\WorkingDay;
use App\Services\WorkScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 10:00:00', 'Asia/Makassar'));

    RolePermission::firstOrCreate(['role' => 'hr', 'permission' => 'manage_attendance']);
    $this->hr = User::factory()->create(['roles' => ['hr']]);

    $this->present = Employee::factory()->create(['full_name' => 'Adi Hadir']);
    $this->missing = Employee::factory()->create(['full_name' => 'Budi Belum']);

    Attendance::factory()->for($this->present)->onDate('2026-07-08')->create();
});

test('it lists employees who have not checked in yet', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->assertSee('Adi Hadir')
        ->assertSee('Budi Belum')
        ->assertSee('Belum Absen');
});

test('a day still running reads as belum absen, a past working day as tanpa keterangan', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->assertSee('Belum Absen')
        ->assertDontSee('Tanpa Keterangan')
        ->set('date', '2026-07-07')
        ->assertSee('Tanpa Keterangan')
        ->assertDontSee('Belum Absen');
});

test('a sunday reads as libur instead of belum absen', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('date', '2026-07-05')
        ->assertViewHas('dayOffReason', 'Hari Minggu')
        ->assertSee('Libur')
        ->assertDontSee('Tanpa Keterangan');
});

test('a holiday reads as libur and names itself', function () {
    Holiday::create(['date' => '2026-07-06', 'holiday_name' => 'Libur Nasional']);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('date', '2026-07-06')
        ->assertViewHas('dayOffReason', 'Libur Nasional')
        ->assertSee('Libur')
        ->assertDontSee('Tanpa Keterangan');
});

test('an employee is not blamed for days before they joined', function () {
    $this->missing->update(['contract_start_date' => '2026-07-08']);
    $this->present->update(['contract_start_date' => '2026-01-01']);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('date', '2026-07-07')
        // Budi belum bergabung, Adi sudah dan memang tidak absen hari itu.
        ->assertSee('Belum Bergabung')
        ->assertSee('Tanpa Keterangan');
});

test('it paints a recorded time green', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->assertSee('bg-emerald-50 font-semibold text-emerald-700', escape: false);
});

test('it can filter down to employees with no attendance', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('status', AdminAttendance::STATUS_MISSING)
        ->assertViewHas('employees', fn ($employees) => $employees->count() === 1
            && $employees->first()->is($this->missing));
});

test('it can filter by attendance status', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('status', 'present')
        ->assertViewHas('employees', fn ($employees) => $employees->count() === 1
            && $employees->first()->is($this->present));
});

test('a day with no attendance at all still lists everyone', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->set('date', '2026-07-07')
        ->assertViewHas('employees', fn ($employees) => $employees->count() === 2)
        ->assertSee('Tanpa Keterangan');
});

test('users without the permission cannot open the page', function () {
    $user = User::factory()->create(['roles' => ['employee']]);

    Livewire::actingAs($user)
        ->test(AdminAttendance::class)
        ->assertForbidden();
});

test('it opens on the daily tab with the current month preselected', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->assertSet('tab', AdminAttendance::TAB_DAILY)
        ->assertSet('month', '2026-07')
        ->assertSee('Rekap Bulanan');
});

test('the recap tab tallies attendance per employee for the month', function () {
    Attendance::factory()->for($this->present)->onDate('2026-07-06')->create();
    Attendance::factory()->for($this->present)->late()->onDate('2026-07-07')->create();

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertSet('tab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', function (array $recap) {
            $adi = $recap[$this->present->id];

            // Hadir 6 & 8 Juli, terlambat 7 Juli.
            return $adi['present'] === 2
                && $adi['late'] === 1
                && $adi['present_total'] === 3
                && $adi['recorded_total'] === 3
                && $adi['late_minutes'] > 0;
        });
});

test('saturday counts as a working day and sunday does not', function () {
    // 1–7 Juli 2026 tanpa Minggu (5 Juli) = 6 hari kerja; 8 Juli hari ini, belum dinilai.
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('workingDays', 6)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['absent'] === 6);
});

test('switching saturday off shrinks the working days', function () {
    WorkingDay::where('weekday', 6)->update(['is_working' => false]);
    app(WorkScheduleService::class)->forgetCache();

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('workingDays', 5)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['absent'] === 5);
});

test('a holiday is never counted against anyone', function () {
    Holiday::create(['date' => '2026-07-02', 'holiday_name' => 'Libur Nasional']);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('workingDays', 5)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['absent'] === 5);
});

test('approved leave covers the day instead of counting as absent', function () {
    LeaveRequest::create([
        'employee_id' => $this->missing->id,
        'type' => LeaveType::Annual,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-03',
        'reason' => 'Acara keluarga',
        'status' => LeaveStatus::Approved,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->missing->id,
        'type' => LeaveType::Sick,
        'start_date' => '2026-07-04',
        'end_date' => '2026-07-04',
        'reason' => 'Demam',
        'status' => LeaveStatus::Approved,
    ]);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', function (array $recap) {
            $budi = $recap[$this->missing->id];

            // 3 hari cuti + 1 hari sakit, sisa 2 hari kerja tanpa keterangan.
            return $budi['leave'] === 3
                && $budi['sick'] === 1
                && $budi['absent'] === 2;
        });
});

test('a pending leave request does not excuse the absence', function () {
    LeaveRequest::create([
        'employee_id' => $this->missing->id,
        'type' => LeaveType::Annual,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-03',
        'reason' => 'Belum disetujui',
        'status' => LeaveStatus::Pending,
    ]);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['leave'] === 0
            && $recap[$this->missing->id]['absent'] === 6);
});

test('an employee is only assessed from their contract start date', function () {
    $this->missing->update(['contract_start_date' => '2026-07-06']);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['working_days'] === 2
            && $recap[$this->missing->id]['absent'] === 2);
});

test('an active employee with an expired contract date is still assessed', function () {
    // Kontrak "berakhir" tapi orangnya masih aktif dan masih masuk kerja.
    $this->missing->update([
        'contract_start_date' => '2023-01-01',
        'contract_end_date' => '2026-01-01',
        'is_active' => true,
    ]);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['working_days'] === 6
            && $recap[$this->missing->id]['absent'] === 6);
});

test('a day with attendance always counts toward the working days', function () {
    $this->present->update(['contract_end_date' => '2026-01-01']);
    Attendance::factory()->for($this->present)->onDate('2026-07-06')->create();

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', function (array $recap) {
            $adi = $recap[$this->present->id];

            // 6 hari kerja dinilai: 6 Juli hadir, 5 sisanya tanpa keterangan.
            // Kehadiran 8 Juli tetap terhitung walau harinya belum dinilai.
            return $adi['working_days'] === 6
                && $adi['absent'] === 5
                && $adi['present_total'] === 2;
        });
});

test('a future month has nothing to assess yet', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->set('month', '2026-09')
        ->assertViewHas('workingDays', 0)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->missing->id]['absent'] === 0);
});

test('the recap only counts the selected month', function () {
    Attendance::factory()->for($this->present)->onDate('2026-06-10')->create();

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', AdminAttendance::TAB_RECAP)
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->present->id]['present_total'] === 1)
        ->set('month', '2026-06')
        ->assertViewHas('recap', fn (array $recap) => $recap[$this->present->id]['present_total'] === 1)
        ->assertViewHas('monthLabel', 'Juni 2026');
});

test('a malformed month in the url falls back to the current month', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class, ['month' => '2026-13'])
        ->assertSet('month', '2026-07');
});

test('an unknown tab value falls back to the daily tab', function () {
    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class, ['tab' => 'nonsense'])
        ->assertSet('tab', AdminAttendance::TAB_DAILY);

    Livewire::actingAs($this->hr)
        ->test(AdminAttendance::class)
        ->call('setTab', 'nonsense')
        ->assertSet('tab', AdminAttendance::TAB_DAILY);
});
