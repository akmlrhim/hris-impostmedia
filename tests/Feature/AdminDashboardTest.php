<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Attendance;
use App\Models\Employee;
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

test('it counts active employees and those present today', function () {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['total_employees'] === 2
            && $stats['present_today'] === 1)
        ->assertSee('Hadir Hari Ini');
});

test('the belum absen statistic is gone from the dashboard', function () {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => ! array_key_exists('not_checked_in', $stats))
        ->assertDontSee('Belum Absen');
});

test('an open check-in without check-out still counts as present', function () {
    Attendance::factory()->for($this->notYet)->openSession()->onDate('2026-07-08')->create();

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['present_today'] === 2);
});

test('a sunday is flagged as an off day', function () {
    $this->travelTo(Carbon::parse('2026-07-05 10:00:00', 'Asia/Makassar'));

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('isOffDay', true);
});

test('a saturday is a working day', function () {
    $this->travelTo(Carbon::parse('2026-07-04 10:00:00', 'Asia/Makassar'));

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertViewHas('isOffDay', false);
});
