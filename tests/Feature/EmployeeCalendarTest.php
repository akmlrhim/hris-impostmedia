<?php

use App\Livewire\Employee\Calendar;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 10:00:00', 'Asia/Makassar'));

    $this->user = User::factory()->create(['roles' => ['employee']]);
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
});

test('it opens on today', function () {
    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->assertSet('selectedDate', '2026-07-08');
});

test('it hands the browser a detail entry for every day of the month', function () {
    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->assertViewHas('dayDetails', fn (array $days) => count($days) === 31
            && array_key_first($days) === '2026-07-01'
            && array_key_exists('2026-07-31', $days));
});

test('it carries the attendance of a day into the detail data', function () {
    Attendance::factory()
        ->for($this->employee)
        ->onDate('2026-07-06')
        ->late()
        ->create();

    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->assertViewHas('dayDetails', function (array $days) {
            $day = $days['2026-07-06'];

            return $day['status'] === 'Terlambat'
                && $day['hasCheckIn'] === true
                && $day['checkIn'] !== '--:--'
                && collect($day['meta'])->contains(fn (string $m) => str_starts_with($m, 'Terlambat'));
        });
});

test('it marks a day without attendance as empty', function () {
    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->assertViewHas('dayDetails', fn (array $days) => $days['2026-07-02']['hasCheckIn'] === false
            && $days['2026-07-02']['empty'] === 'Belum ada data absensi pada tanggal ini.');
});

test('it names the company day off and sundays', function () {
    Holiday::create(['date' => '2026-07-17', 'holiday_name' => 'Libur Nasional']);

    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->assertViewHas('dayDetails', fn (array $days) => $days['2026-07-17']['note'] === 'Libur Nasional'
            && $days['2026-07-05']['note'] === 'Hari Minggu');
});

test('it follows the month when navigating away from today', function () {
    Livewire::actingAs($this->user)
        ->test(Calendar::class)
        ->call('previousMonth')
        ->assertSet('month', 6)
        ->assertSet('selectedDate', '2026-06-01')
        ->assertViewHas('dayDetails', fn (array $days) => count($days) === 30)
        ->call('nextMonth')
        ->assertSet('month', 7)
        ->assertSet('selectedDate', '2026-07-08');
});
