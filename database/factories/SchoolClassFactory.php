<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = \App\Models\SchoolClass::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'department_id' => null,
            'name' => fake()->randomElement(['ষষ্ঠ', 'সপ্তম', 'অষ্টম', 'নবম', 'দশম']) . ' শ্রেণি',
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }
}
