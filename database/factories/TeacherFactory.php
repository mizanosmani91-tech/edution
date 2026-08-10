<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->name(),
            'teacher_id_no' => 'TCH-' . fake()->unique()->numberBetween(1000, 9999),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'designation' => fake()->randomElement(['সহকারী শিক্ষক', 'সিনিয়র শিক্ষক', 'বিভাগীয় প্রধান']),
            'joining_date' => fake()->date(),
        ];
    }
}
