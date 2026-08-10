<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamSubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'exam_id' => Exam::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => SchoolClass::factory(),
            'full_marks' => 100,
            'pass_marks' => 33,
        ];
    }
}
