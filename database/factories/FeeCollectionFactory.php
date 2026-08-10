<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeCollectionFactory extends Factory
{
    public function definition(): array
    {
        $due = fake()->randomFloat(2, 500, 2000);

        return [
            'institution_id' => Institution::factory(),
            'student_id' => Student::factory(),
            'fee_type' => 'monthly',
            'amount_due' => $due,
            'amount_paid' => 0,
            'payment_method' => 'cash',
            'due_month' => now()->format('Y-m'),
            'status' => 'due',
        ];
    }
}
