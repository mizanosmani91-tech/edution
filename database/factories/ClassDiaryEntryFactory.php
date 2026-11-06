<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassDiaryEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'class_id' => SchoolClass::factory(),
            'entry_date' => now()->toDateString(),
            'topic' => fake()->sentence(3),
            'classwork' => fake()->sentence(),
        ];
    }
}
