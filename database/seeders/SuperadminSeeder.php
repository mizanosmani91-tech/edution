<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * panel.edution.xyz এ লগইন করার জন্য superadmin ইউজার নিশ্চিত করে।
 * প্রতি ডিপ্লয়ে চলে (idempotent — updateOrCreate), তাই বারবার চালালেও সমস্যা নেই।
 * ⚠️ প্রথম লগইনের পরই পাসওয়ার্ড পরিবর্তন করে নিন।
 */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'admin@edution.xyz');
        $password = env('SUPERADMIN_PASSWORD', 'EdutionAdmin@2026');

        User::updateOrCreate(
            ['email' => $email, 'institution_id' => null],
            [
                'name' => 'Edution Superadmin',
                'password' => Hash::make($password),
                'role' => 'superadmin',
            ]
        );
    }
}
