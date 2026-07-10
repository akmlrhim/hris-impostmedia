<?php

use App\Enums\AttendanceStatus;
use App\Livewire\Employee\Attendance;
use App\Models\Attendance as AttendanceModel;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // A fixed working day (Wednesday) so the Sunday/holiday guard never trips.
    $this->travelTo(Carbon::parse('2026-07-08 08:00:00', 'Asia/Jakarta'));

    $this->user = User::factory()->create(['roles' => ['employee']]);

    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_number' => 'EMP-001',
        'full_name' => 'Budi Santoso',
        'nik' => '1234567890123456',
        'work_type' => 'wfa',
        'last_education' => 'S1',
        'major_school_university' => 'Informatika',
        'emergency_contact_name' => 'Siti',
        'emergency_contact_number' => '081234567890',
    ]);
});

function createOpenAttendanceYesterday(Employee $employee): AttendanceModel
{
    return AttendanceModel::create([
        'employee_id' => $employee->id,
        'attendance_date' => now()->subDay()->toDateString(),
        'check_in_at' => now()->subDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
        'check_out_at' => null,
        'status' => AttendanceStatus::Present,
        'work_type' => 'wfa',
        'timezone' => 'Asia/Jakarta',
    ]);
}

test('employee who forgot to check out yesterday can check in today directly', function () {
    $yesterday = createOpenAttendanceYesterday($this->employee);

    Livewire::actingAs($this->user)
        ->test(Attendance::class)
        ->call('checkIn', null, null, null, null, null, null, 'password', 'Asia/Jakarta')
        ->assertDispatched('notify', type: 'success');

    $today = AttendanceModel::where('employee_id', $this->employee->id)
        ->whereDate('attendance_date', now()->toDateString())
        ->first();

    expect($today)->not->toBeNull()
        ->and($today->check_in_at)->not->toBeNull();

    // Yesterday's forgotten check-out stays empty and untouched.
    expect($yesterday->fresh()->check_out_at)->toBeNull();
});

test('attendance page shows the check-in button even when yesterday was never checked out', function () {
    createOpenAttendanceYesterday($this->employee);

    Livewire::actingAs($this->user)
        ->test(Attendance::class)
        ->assertSee('Check-in Sekarang')
        ->assertDontSee('Check-out Sekarang')
        ->assertDontSee('yang belum check-out');
});

test('check-out no longer targets a forgotten session from a previous day', function () {
    $yesterday = createOpenAttendanceYesterday($this->employee);

    Livewire::actingAs($this->user)
        ->test(Attendance::class)
        ->call('checkOut', null, null, null, null, null, 'password', 'Asia/Jakarta')
        ->assertDispatched('notify', type: 'warning');

    expect($yesterday->fresh()->check_out_at)->toBeNull();
});

test('check-out still closes a session opened today', function () {
    AttendanceModel::create([
        'employee_id' => $this->employee->id,
        'attendance_date' => now()->toDateString(),
        'check_in_at' => now()->setTime(9, 0)->format('Y-m-d H:i:s'),
        'status' => AttendanceStatus::Present,
        'work_type' => 'wfa',
        'timezone' => 'Asia/Jakarta',
    ]);

    // Past the 8-hour minimum so no early-checkout warning interferes.
    $this->travelTo(Carbon::parse('2026-07-08 17:30:00', 'Asia/Jakarta'));

    Livewire::actingAs($this->user)
        ->test(Attendance::class)
        ->call('checkOut', null, null, null, null, null, 'password', 'Asia/Jakarta')
        ->assertDispatched('notify', type: 'success');

    $today = AttendanceModel::where('employee_id', $this->employee->id)
        ->whereDate('attendance_date', now()->toDateString())
        ->first();

    expect($today->check_out_at)->not->toBeNull();
});
