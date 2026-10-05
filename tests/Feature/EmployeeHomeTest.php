<?php

use App\Enums\AttendanceStatus;
use App\Livewire\Employee\Home;
use App\Models\Announcement;
use App\Models\Attendance as AttendanceModel;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 12:00:00', 'Asia/Makassar'));

    $this->user = User::factory()->create(['roles' => ['employee']]);

    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'full_name' => 'Budi Santoso',
        'is_active' => true,
    ]);
});

function seedAttendance(Employee $employee, string $date, string $checkIn, AttendanceStatus $status = AttendanceStatus::Present): void
{
    AttendanceModel::create([
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

test('the mobile home page renders without a stdClass error', function () {
    seedAttendance($this->employee, '2026-07-01', '08:00');

    Livewire::actingAs($this->user)
        ->test(Home::class)
        ->assertOk();
});

test('the mobile home page renders for an employee with no attendance at all', function () {
    Livewire::actingAs($this->user)
        ->test(Home::class)
        ->assertOk();
});

test('the mobile home page renders when the leaderboard cache is already warm', function () {
    seedAttendance($this->employee, '2026-07-01', '08:00');

    Livewire::actingAs($this->user)->test(Home::class)->assertOk();
    Livewire::actingAs($this->user)->test(Home::class)->assertOk();
});

it('shows announcement for all users', function () {
    $announcement = Announcement::create([
        'author_id' => $this->user->id,
        'title' => 'Test All',
        'content' => 'All employees',
        'audience' => 'all',
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(Home::class)
        ->assertSee('Test All');
});

it('shows announcement targeted to user', function () {
    $announcement = Announcement::create([
        'author_id' => $this->user->id,
        'title' => 'Test Targeted',
        'content' => 'Only for you',
        'audience' => 'selected',
        'published_at' => now(),
    ]);
    $announcement->recipients()->attach($this->user->id);

    Livewire::actingAs($this->user)
        ->test(Home::class)
        ->assertSee('Test Targeted');
});

it('hides announcement not targeted to user', function () {
    $other = User::factory()->create(['roles' => ['employee']]);
    $announcement = Announcement::create([
        'author_id' => $this->user->id,
        'title' => 'Test Hidden',
        'content' => 'Only for other',
        'audience' => 'selected',
        'published_at' => now(),
    ]);
    $announcement->recipients()->attach($other->id);

    Livewire::actingAs($this->user)
        ->test(Home::class)
        ->assertDontSee('Test Hidden');
});
