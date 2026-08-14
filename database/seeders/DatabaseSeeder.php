<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * প্রতি ডিপ্লয়ে চলে — উভয় seeder-ই idempotent (updateOrCreate)।
     */
    public function run(): void
    {
        $this->call([
            SuperadminSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
