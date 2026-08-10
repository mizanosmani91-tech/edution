<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'class_id' => SchoolClass::factory(),
            'name' => fake()->randomElement(['এ', 'বি', 'সি']),
            'capacity' => fake()->numberBetween(30, 60),
        ];
    }
}
