<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = now()->addDay()->setTime(9, 0);

        return [
            'employee_id' => Employee::factory(),
            'type' => LeaveType::Annual,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays(2)->setTime(17, 0),
            'reason' => fake()->sentence(10),
            'status' => LeaveStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => LeaveStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => LeaveStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(8),
        ]);
    }

    public function ofType(LeaveType $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
