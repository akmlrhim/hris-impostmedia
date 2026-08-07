<?php

namespace Database\Factories;

use App\Enums\RemoteWorkStatus;
use App\Enums\WorkType;
use App\Models\Employee;
use App\Models\RemoteWorkRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RemoteWorkRequest>
 */
class RemoteWorkRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = now()->setTime(9, 0);

        return [
            'employee_id' => Employee::factory(),
            'work_type' => WorkType::WFA,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->setTime(18, 0),
            'reason' => fake()->sentence(10),
            'status' => RemoteWorkStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => RemoteWorkStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => RemoteWorkStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(8),
        ]);
    }

    public function ofType(WorkType $type): static
    {
        return $this->state(fn () => ['work_type' => $type]);
    }
}
