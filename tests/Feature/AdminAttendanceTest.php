<?php

use App\Livewire\Admin\Attendance as AdminAttendance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RolePermission;
use App\Models\User;
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
        ->assertSee('Belum Absen');
});

test('users without the permission cannot open the page', function () {
    $user = User::factory()->create(['roles' => ['employee']]);

    Livewire::actingAs($user)
        ->test(AdminAttendance::class)
        ->assertForbidden();
});
