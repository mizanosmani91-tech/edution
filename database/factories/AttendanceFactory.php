<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'date' => now()->toDateString(),
            'status' => 'present',
        ];
    }
}
