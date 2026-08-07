<?php

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Livewire\Admin\Dashboard;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Rabu, hari kerja.
    $this->travelTo(Carbon::parse('2026-07-08 10:00:00', 'Asia/Makassar'));

    $this->admin = User::factory()->create(['roles' => ['admin']]);

    $this->checkedIn = Employee::factory()->create(['full_name' => 'Adi Hadir']);
    $this->notYet = Employee::factory()->create(['full_name' => 'Budi Belum']);

    Attendance::factory()->for($this->checkedIn)->onDate('2026-07-08')->create();
});

test('it counts active employees who have not checked in today', function () {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['present_today'] === 1
            && $stats['not_checked_in'] === 1)
        ->assertSee('Belum Absen');
});

test('an employee on approved leave is not counted', function () {
    LeaveRequest::create([
        'employee_id' => $this->notYet->id,
        'type' => LeaveType::Annual,
        'start_date' => '2026-07-07',
        'end_date' => '2026-07-09',
        'reason' => 'Acara keluarga',
        'status' => LeaveStatus::Approved,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 0);
});

test('a pending leave request still leaves the employee counted', function () {
    LeaveRequest::create([
        'employee_id' => $this->notYet->id,
        'type' => LeaveType::Annual,
        'start_date' => '2026-07-08',
        'end_date' => '2026-07-08',
        'reason' => 'Belum disetujui',
        'status' => LeaveStatus::Pending,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 1);
});

test('an inactive employee is not counted', function () {
    $this->notYet->update(['is_active' => false]);

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 0);
});

test('an employee who has not started yet is not counted', function () {
    $this->notYet->update(['contract_start_date' => '2026-08-01']);

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 0);
});

test('an open check-in without check-out still counts as present', function () {
    Attendance::factory()->for($this->notYet)->openSession()->onDate('2026-07-08')->create();

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 0);
});

test('nobody is expected in on a sunday', function () {
    $this->travelTo(Carbon::parse('2026-07-05 10:00:00', 'Asia/Makassar'));

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('isOffDay', true)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 0)
        ->assertSee('Libur');
});

test('saturday is a working day so the count still runs', function () {
    $this->travelTo(Carbon::parse('2026-07-04 10:00:00', 'Asia/Makassar'));

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('isOffDay', false)
        ->assertViewHas('stats', fn (array $stats) => $stats['not_checked_in'] === 2);
});
