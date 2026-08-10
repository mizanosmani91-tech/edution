<?php

namespace Database\Factories;

use App\Models\ExamSubject;
use App\Models\Institution;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamMarkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'exam_subject_id' => ExamSubject::factory(),
            'student_id' => Student::factory(),
            'marks_obtained' => fake()->numberBetween(20, 100),
            'is_absent' => false,
        ];
    }
}
