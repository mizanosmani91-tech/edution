<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 30))->toDateString();

        return [
            'institution_id' => Institution::factory(),
            'title' => fake()->sentence(3),
            'type' => 'event',
            'start_date' => $start,
            'end_date' => $start,
            'is_off_day' => false,
        ];
    }
}
