<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'amount' => 499,
            'method' => 'bkash',
            'transaction_ref' => strtoupper(fake()->bothify('TXN########')),
            'for_month' => now()->format('Y-m'),
            'status' => 'pending',
        ];
    }
}
