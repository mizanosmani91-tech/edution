<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamResultWeightingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'target_exam_id' => Exam::factory(),
            'source_exam_id' => Exam::factory(),
            'contribution_type' => 'percentage',
            'weight_percentage' => 20,
            'require_source_pass' => false,
        ];
    }
}
