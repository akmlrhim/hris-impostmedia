<?php

use App\Livewire\Admin\WorkingDay as WorkingDayPage;
use App\Models\Holiday;
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

    RolePermission::firstOrCreate(['role' => 'hr', 'permission' => 'manage_holidays']);
    $this->hr = User::factory()->create(['roles' => ['hr']]);

    $this->schedule = app(WorkScheduleService::class);
});

test('the company works monday to saturday out of the box', function () {
    expect($this->schedule->workingWeekdays())->toBe([1, 2, 3, 4, 5, 6])
        ->and($this->schedule->isWorkingDay('2026-07-04'))->toBeTrue()   // Sabtu
        ->and($this->schedule->isWorkingDay('2026-07-05'))->toBeFalse(); // Minggu
});

test('a holiday is not a working day even on a working weekday', function () {
    Holiday::create(['date' => '2026-07-06', 'holiday_name' => 'Libur Nasional']);

    expect($this->schedule->isWorkingDay('2026-07-06'))->toBeFalse()
        ->and($this->schedule->isWorkingWeekday('2026-07-06'))->toBeTrue()
        ->and($this->schedule->offDayReason('2026-07-06'))->toBe('Libur Nasional');
});

test('it names the weekly day off', function () {
    expect($this->schedule->offDayReason('2026-07-05'))->toBe('Hari Minggu')
        ->and($this->schedule->offDayReason('2026-07-06'))->toBeNull();
});

test('working dates between two days skip sundays and holidays', function () {
    Holiday::create(['date' => '2026-07-02', 'holiday_name' => 'Libur Nasional']);

    expect($this->schedule->workingDatesBetween('2026-07-01', '2026-07-07'))
        ->toBe(['2026-07-01', '2026-07-03', '2026-07-04', '2026-07-06', '2026-07-07']);
});

test('the page loads the stored schedule', function () {
    Livewire::actingAs($this->hr)
        ->test(WorkingDayPage::class)
        ->assertSet('schedule', [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => false])
        ->assertSee('Sabtu');
});

test('saving a new schedule persists it and takes effect immediately', function () {
    Livewire::actingAs($this->hr)
        ->test(WorkingDayPage::class)
        ->set('schedule.6', false)
        ->set('schedule.7', true)
        ->call('save');

    expect(WorkingDay::where('weekday', 6)->value('is_working'))->toBeFalse()
        ->and(WorkingDay::where('weekday', 7)->value('is_working'))->toBeTrue()
        ->and(app(WorkScheduleService::class)->workingWeekdays())->toBe([1, 2, 3, 4, 5, 7])
        ->and(app(WorkScheduleService::class)->isWorkingDay('2026-07-04'))->toBeFalse()
        ->and(app(WorkScheduleService::class)->isWorkingDay('2026-07-05'))->toBeTrue();
});

test('it refuses to save a week with no working day', function () {
    Livewire::actingAs($this->hr)
        ->test(WorkingDayPage::class)
        ->set('schedule', array_fill_keys(range(1, 7), false))
        ->call('save')
        ->assertDispatched('notify', type: 'warning');

    expect(WorkingDay::where('is_working', true)->count())->toBe(6);
});

test('users without the permission cannot open the page', function () {
    $employee = User::factory()->create(['roles' => ['employee']]);

    Livewire::actingAs($employee)
        ->test(WorkingDayPage::class)
        ->assertForbidden();
});
