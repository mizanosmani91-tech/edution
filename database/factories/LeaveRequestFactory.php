<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'student_id' => Student::factory(),
            'requested_by' => User::factory(),
            'date_from' => now()->addDay()->toDateString(),
            'date_to' => now()->addDays(2)->toDateString(),
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
