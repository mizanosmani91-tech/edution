<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->randomElement(['বাংলা', 'ইংরেজি', 'গণিত', 'বিজ্ঞান', 'সমাজ']),
            'code' => strtoupper(fake()->unique()->lexify('SUB???')),
        ];
    }
}
