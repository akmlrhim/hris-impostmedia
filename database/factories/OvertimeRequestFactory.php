<?php

namespace Database\Factories;

use App\Enums\OvertimeStatus;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OvertimeRequest>
 */
class OvertimeRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->setTime(18, 0);

        return [
            'employee_id' => Employee::factory(),
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addHours(3),
            'reason' => fake()->sentence(10),
            'head_approval_path' => 'overtime-approvals/bukti.jpg',
            'work_documentation' => null,
            'status' => OvertimeStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => OvertimeStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => OvertimeStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(8),
        ]);
    }
}
