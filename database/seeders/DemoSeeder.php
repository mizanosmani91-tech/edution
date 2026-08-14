<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * edution.xyz/login এ পাবলিকলি দেখানো ডেমো একাউন্ট নিশ্চিত করে।
 * কাস্টমাররা সাইনআপ ছাড়াই এই একাউন্ট দিয়ে সরাসরি dashboard ঘুরে দেখতে পারবে।
 * প্রতি ডিপ্লয়ে চলে (idempotent)।
 */
class DemoSeeder extends Seeder
{
    public const EMAIL = 'demo@edution.xyz';
    public const PASSWORD = 'Demo@1234';
    public const SLUG = 'demo';

    public function run(): void
    {
        $institution = Institution::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'name' => 'ডেমো প্রতিষ্ঠান',
                'institution_type' => 'school',
                'status' => 'active',
                'plan' => 'standard',
                'phone' => '01700000000',
                'address' => 'ঢাকা, বাংলাদেশ',
                'registration_email' => self::EMAIL,
            ]
        );

        User::updateOrCreate(
            ['email' => self::EMAIL, 'institution_id' => $institution->id],
            [
                'name' => 'ডেমো এডমিন',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'admin',
                'must_change_password' => false,
            ]
        );
    }
}
