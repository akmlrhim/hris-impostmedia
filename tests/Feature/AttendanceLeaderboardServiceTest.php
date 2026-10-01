<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance as AttendanceModel;
use App\Models\Employee;
use App\Services\AttendanceLeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 12:00:00', 'Asia/Makassar'));

    $this->employee = Employee::factory()->create([
        'full_name' => 'Budi Santoso',
        'is_active' => true,
    ]);
});

function attendanceOn(Employee $employee, string $date, string $checkIn, AttendanceStatus $status = AttendanceStatus::Present): AttendanceModel
{
    return AttendanceModel::create([
        'employee_id' => $employee->id,
        'attendance_date' => $date,
        'check_in_at' => $date.' '.$checkIn.':00',
        'check_out_at' => $date.' 17:00:00',
        'status' => $status,
        'late_minutes' => $status === AttendanceStatus::Late ? 20 : 0,
        'work_minutes' => 480,
        'timezone' => 'Asia/Makassar',
    ]);
}

test('the cache payload holds plain arrays so unserialize never needs a class', function () {
    attendanceOn($this->employee, '2026-07-01', '08:00');

    $service = app(AttendanceLeaderboardService::class);
    $service->standings(2026, 7, PHP_INT_MAX);

    $payload = Cache::get('standings.v2.2026.7');

    expect($payload)->toBeArray();
    expect($payload[0])->toBeArray();
    expect($payload[0]['present'])->toBe(1);
});

test('standings ranks by earliest check-in and exposes every counter', function () {
    attendanceOn($this->employee, '2026-07-01', '08:00');
    attendanceOn($this->employee, '2026-07-02', '08:30', AttendanceStatus::Late);

    $rows = app(AttendanceLeaderboardService::class)->standings(2026, 7, PHP_INT_MAX);

    expect($rows)->toHaveCount(1);

    $row = $rows->first();

    expect($row['present'])->toBe(2)
        ->and($row['on_time'])->toBe(1)
        ->and($row['late'])->toBe(1)
        ->and($row['late_minutes'])->toBe(20)
        ->and($row['rank'])->toBe(1)
        ->and((float) $row['on_time_rate'])->toBe(50.0);
});

test('a warm cache read returns the same ranking as a cold one', function () {
    attendanceOn($this->employee, '2026-07-01', '08:00');

    $service = app(AttendanceLeaderboardService::class);

    $cold = $service->standings(2026, 7, PHP_INT_MAX);
    $warm = $service->standings(2026, 7, PHP_INT_MAX);

    expect($warm->first()['present'])->toBe($cold->first()['present'])
        ->and($warm->first()['rank'])->toBe($cold->first()['rank']);
});

test('board attaches the standings counters onto the employee', function () {
    attendanceOn($this->employee, '2026-07-01', '08:00');

    $board = app(AttendanceLeaderboardService::class)->board(2026, 7, 5);

    expect($board)->toHaveCount(1);

    $row = $board->first();

    expect($row->id)->toBe($this->employee->id)
        ->and($row->rank)->toBe(1)
        ->and($row->present)->toBe(1);
});

test('standingFor reports an unranked employee as null instead of dropping them', function () {
    $other = Employee::factory()->create(['is_active' => true]);
    attendanceOn($other, '2026-07-01', '08:00');

    $standing = app(AttendanceLeaderboardService::class)->standingFor($this->employee, 2026, 7);

    expect($standing['rank'])->toBeNull()
        ->and($standing['present'])->toBe(0)
        ->and($standing['total'])->toBe(1);
});
