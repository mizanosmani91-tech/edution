<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->randomElement(['Science', 'Arts', 'Commerce']),
            'name_bn' => fake()->randomElement(['বিজ্ঞান', 'মানবিক', 'ব্যবসায় শিক্ষা']),
            'display_order' => fake()->numberBetween(1, 5),
        ];
    }
}
