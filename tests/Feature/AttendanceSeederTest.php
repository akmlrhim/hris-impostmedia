<?php

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\AttendanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // A fixed working day (Wednesday), mid-morning so today stays open.
    $this->travelTo(Carbon::parse('2026-07-08 10:00:00', 'Asia/Makassar'));

    $this->user = User::factory()->create(['roles' => ['employee']]);
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
});

test('it fills the last month of attendance for active employees', function () {
    $this->seed(AttendanceSeeder::class);

    $attendances = Attendance::where('employee_id', $this->employee->id)->get();

    expect($attendances)->not->toBeEmpty()
        ->and($attendances->pluck('attendance_date')->unique())->toHaveCount($attendances->count())
        ->and(Employee::count())->toBe(1);

    $oldest = $attendances->min('attendance_date');
    expect($oldest->toDateString())->toBe(today()->subDays(29)->toDateString());
});

test('it skips sundays and company days off', function () {
    Holiday::create(['date' => '2026-07-06', 'holiday_name' => 'Cuti Bersama']);

    $this->seed(AttendanceSeeder::class);

    $dates = Attendance::pluck('attendance_date');

    expect($dates->contains(fn (Carbon $date) => $date->isSunday()))->toBeFalse()
        ->and($dates->contains(fn (Carbon $date) => $date->toDateString() === '2026-07-06'))->toBeFalse();
});

test('it leaves today open so the employee can still check out', function () {
    $this->seed(AttendanceSeeder::class);

    $today = Attendance::whereDate('attendance_date', today())->firstOrFail();

    expect($today->check_in_at)->not->toBeNull()
        ->and($today->check_out_at)->toBeNull()
        ->and($today->work_minutes)->toBe(0);
});

test('it records late arrivals with minutes counted from the official start time', function () {
    $this->seed(AttendanceSeeder::class);

    $late = Attendance::where('status', 'late')->get();

    expect($late)->not->toBeEmpty();

    $late->each(function (Attendance $attendance) {
        $expected = (int) $attendance->check_in_at->copy()->setTime(9, 0)->diffInMinutes($attendance->check_in_at);

        expect($attendance->late_minutes)->toBe($expected)
            ->and($attendance->late_minutes)->toBeGreaterThan(15);
    });
});

test('it keeps work minutes in step with the recorded times', function () {
    $this->seed(AttendanceSeeder::class);

    Attendance::whereNotNull('check_out_at')->get()->each(function (Attendance $attendance) {
        $worked = (int) $attendance->check_in_at->diffInMinutes($attendance->check_out_at);

        expect($attendance->work_minutes)->toBe($worked)
            ->and($worked)->toBeGreaterThanOrEqual(480);
    });
});

test('it writes a check-in and check-out log for every completed day', function () {
    $this->seed(AttendanceSeeder::class);

    $completed = Attendance::whereNotNull('check_out_at')->count();
    $openOrAway = Attendance::whereNotNull('check_in_at')->whereNull('check_out_at')->count();

    expect(AttendanceLog::count())->toBe($completed * 2 + $openOrAway);
});

test('it can be re-run without duplicating a day', function () {
    $this->seed(AttendanceSeeder::class);
    $firstRun = Attendance::count();

    $this->seed(AttendanceSeeder::class);

    expect(Attendance::count())->toBe($firstRun)
        ->and(Employee::count())->toBe(1);
});

test('it creates demo employees when none exist yet', function () {
    Attendance::query()->delete();
    $this->employee->forceDelete();

    $this->seed(AttendanceSeeder::class);

    expect(Employee::count())->toBeGreaterThan(0)
        ->and(Attendance::count())->toBeGreaterThan(0);
});
