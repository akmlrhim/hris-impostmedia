<?php

namespace Database\Factories;

use App\Enums\WorkType;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_number' => 'EMP-'.fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'nik' => (string) fake()->unique()->numerify('################'),
            'work_type' => WorkType::WFO,
            'last_education' => 'S1',
            'major_school_university' => 'Informatika',
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_number' => '081234567890',
            'is_active' => true,
        ];
    }
}
