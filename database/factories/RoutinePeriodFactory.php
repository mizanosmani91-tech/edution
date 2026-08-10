<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutinePeriodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'class_id' => SchoolClass::factory(),
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'period_number' => fake()->numberBetween(1, 8),
            'start_time' => '09:00',
            'end_time' => '09:40',
        ];
    }
}
