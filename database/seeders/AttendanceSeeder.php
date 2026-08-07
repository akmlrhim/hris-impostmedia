<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Enums\WorkType;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fills the attendance history of every active employee for the last month so
 * the mobile home screen, calendar and admin reports have realistic data.
 *
 * Safe to re-run: the seeded window is wiped first, so no duplicates and no
 * clashes with the unique (employee_id, attendance_date) index.
 */
class AttendanceSeeder extends Seeder
{
    /** Number of days to generate, counting back from today. */
    private const DAYS = 30;

    private const TIMEZONE = 'Asia/Makassar';

    /** Only used when the database has no employees to attach attendance to. */
    private const DEMO_EMPLOYEES = 5;

    /** Chance (out of 100) of each irregular outcome on a working day. */
    private const CHANCE_ABSENT = 4;

    private const CHANCE_AWAY = 8;

    private const CHANCE_LATE = 28;

    public function run(): void
    {
        $employees = Employee::where('is_active', true)->get();

        if ($employees->isEmpty()) {
            $employees = $this->createDemoEmployees();
            $this->command?->info("Tidak ada karyawan aktif - dibuat {$employees->count()} karyawan demo (kata sandi: password).");
        }

        $today = Carbon::today(self::TIMEZONE);
        $startDate = $today->copy()->subDays(self::DAYS - 1);

        $daysOff = Holiday::whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->pluck('date')
            ->map(fn (Carbon $date) => $date->toDateString())
            ->all();

        // whereDate, not whereBetween: attendance_date is written as a full
        // timestamp, so a plain string range would leave today's rows behind
        // and collide with the unique (employee_id, attendance_date) index.
        Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $today->toDateString())
            ->delete();

        $created = DB::transaction(function () use ($employees, $startDate, $today, $daysOff): int {
            $count = 0;

            foreach ($employees as $employee) {
                for ($date = $startDate->copy(); $date->lte($today); $date->addDay()) {
                    if ($date->isSunday() || in_array($date->toDateString(), $daysOff, true)) {
                        continue;
                    }

                    $this->recordDay($employee, $date->copy(), $date->isSameDay($today));
                    $count++;
                }
            }

            return $count;
        });

        $this->command?->info("Absensi dibuat: {$created} baris untuk {$employees->count()} karyawan (".self::DAYS.' hari terakhir).');
    }

    /** Build one day of attendance for one employee. */
    private function recordDay(Employee $employee, Carbon $date, bool $isToday): void
    {
        $factory = Attendance::factory()->for($employee)->onDate($date);

        if ($this->worksRemotely($employee)) {
            $factory = $factory->remote();
        }

        // Today is left open until the working day is over, so the app shows a
        // pending check-out instead of a day that is somehow already finished.
        if ($isToday) {
            $attendance = now(self::TIMEZONE)->hour < 17
                ? $factory->openSession()->create()
                : $factory->create();

            $this->logEvents($attendance);

            return;
        }

        $roll = random_int(1, 100);

        $attendance = match (true) {
            $roll <= self::CHANCE_ABSENT => $factory->absent()->create(),
            $roll <= self::CHANCE_AWAY => $factory->away(fake()->randomElement([
                AttendanceStatus::Leave,
                AttendanceStatus::Sick,
            ]))->create(),
            $roll <= self::CHANCE_LATE => $factory->late()->create(),
            default => $factory->create(),
        };

        $this->logEvents($attendance);
    }

    /** Mirror the check-in/check-out audit trail the app writes for real taps. */
    private function logEvents(Attendance $attendance): void
    {
        foreach (['check_in' => $attendance->check_in_at, 'check_out' => $attendance->check_out_at] as $event => $happenedAt) {
            if (! $happenedAt) {
                continue;
            }

            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'event' => $event,
                'event_at' => $happenedAt->format('Y-m-d H:i:s'),
                'ip_address' => '127.0.0.1',
                'device_info' => 'AttendanceSeeder',
            ]);
        }
    }

    private function worksRemotely(Employee $employee): bool
    {
        return match ($employee->work_type) {
            WorkType::WFA => true,
            WorkType::Hybrid => random_int(1, 100) <= 50,
            default => false,
        };
    }

    /**
     * @return Collection<int, Employee>
     */
    private function createDemoEmployees(): Collection
    {
        return Employee::factory()
            ->count(self::DEMO_EMPLOYEES)
            ->create()
            ->each(function (Employee $employee): void {
                $employee->user?->update(['roles' => [UserRole::Employee->value]]);
            });
    }
}
