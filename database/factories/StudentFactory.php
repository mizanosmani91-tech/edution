<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->name(),
            'student_id_no' => 'STU-' . fake()->unique()->numberBetween(10000, 99999),
            'guardian_phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->date(),
            'status' => 'active',
        ];
    }
}
