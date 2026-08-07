<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceLatenessService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /** Attendance is always recorded in the employee's local timezone. */
    private const TIMEZONE = 'Asia/Makassar';

    /**
     * A normal, on-time working day: in before the grace period, out after a
     * full eight-hour day.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = Carbon::today(self::TIMEZONE)->setTime(8, fake()->numberBetween(20, 58));

        return [
            'employee_id' => Employee::factory(),
            'attendance_date' => $checkIn->toDateString(),
            'check_in_at' => $checkIn->format('Y-m-d H:i:s'),
            'check_out_at' => $this->checkOutAfter($checkIn),
            'check_in_address' => 'Kantor Pusat',
            'check_out_address' => 'Kantor Pusat',
            'status' => AttendanceStatus::Present,
            'work_type' => WorkType::WFO,
            'late_minutes' => 0,
            'work_minutes' => 0,
            'timezone' => self::TIMEZONE,
        ];
    }

    /**
     * Keep work_minutes in sync with whatever check-in/check-out the caller
     * ended up with, so no state has to recompute it by hand.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Attendance $attendance): void {
            $attendance->work_minutes = $attendance->check_in_at && $attendance->check_out_at
                ? (int) $attendance->check_in_at->diffInMinutes($attendance->check_out_at)
                : 0;
        });
    }

    /** Checked in after the company grace period. */
    public function late(): static
    {
        return $this->state(function (array $attributes) {
            $lateness = app(AttendanceLatenessService::class);
            $date = Carbon::parse($attributes['attendance_date'], self::TIMEZONE);
            $checkIn = $lateness->lateThreshold($date)->addMinutes(fake()->numberBetween(1, 75));

            return [
                'check_in_at' => $checkIn->format('Y-m-d H:i:s'),
                'check_out_at' => $this->checkOutAfter($checkIn),
                'status' => AttendanceStatus::Late,
                'late_minutes' => $lateness->lateMinutes($checkIn),
            ];
        });
    }

    /** Checked in but not checked out yet — the session is still open. */
    public function openSession(): static
    {
        return $this->state(fn () => [
            'check_out_at' => null,
            'check_out_address' => null,
        ]);
    }

    /** No check-in at all for the day. */
    public function absent(): static
    {
        return $this->state(fn () => [
            'check_in_at' => null,
            'check_out_at' => null,
            'check_in_address' => null,
            'check_out_address' => null,
            'status' => AttendanceStatus::Absent,
            'late_minutes' => 0,
        ]);
    }

    /** Away on an approved leave, sick note or day off. */
    public function away(AttendanceStatus $status = AttendanceStatus::Leave): static
    {
        return $this->absent()->state(fn () => [
            'status' => $status,
            'notes' => $status->label(),
        ]);
    }

    /** Worked from anywhere instead of the office. */
    public function remote(): static
    {
        return $this->state(fn () => [
            'work_type' => WorkType::WFA,
            'check_in_address' => 'Lokasi remote',
            'check_out_address' => 'Lokasi remote',
        ]);
    }

    /** Move the whole record — date and both timestamps — onto $date. */
    public function onDate(Carbon|string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $target = $date instanceof Carbon ? $date->copy() : Carbon::parse($date, self::TIMEZONE);

            $shift = fn (?string $timestamp) => $timestamp
                ? Carbon::parse($timestamp, self::TIMEZONE)
                    ->setDate($target->year, $target->month, $target->day)
                    ->format('Y-m-d H:i:s')
                : null;

            return [
                'attendance_date' => $target->toDateString(),
                'check_in_at' => $shift($attributes['check_in_at']),
                'check_out_at' => $shift($attributes['check_out_at']),
            ];
        });
    }

    /** A full working day after $checkIn, never running past the evening. */
    private function checkOutAfter(Carbon $checkIn): string
    {
        return $checkIn->copy()
            ->addMinutes(fake()->numberBetween(485, 560))
            ->min($checkIn->copy()->setTime(20, 0))
            ->format('Y-m-d H:i:s');
    }
}
