<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeStructureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'class_id' => SchoolClass::factory(),
            'fee_type' => 'monthly',
            'amount' => fake()->randomFloat(2, 300, 3000),
            'frequency' => 'monthly',
            'is_active' => true,
        ];
    }
}
