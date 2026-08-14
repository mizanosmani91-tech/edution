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

    // DemoDataSeeder এ তৈরি হওয়া শিক্ষক/অভিভাবক ডেমো লগইন — লগইন পেজে
    // দেখানোর জন্য এখানে কনস্ট্যান্ট রাখা হলো (একই জায়গা থেকে দুই সিডার
    // ও ব্লেড ভিউ রেফারেন্স করবে, ডুপ্লিকেট স্ট্রিং এড়ানোর জন্য)।
    public const TEACHER_EMAIL = 'teacher@demo-edution.test';
    public const GUARDIAN_EMAIL = 'guardian@demo-edution.test';
    public const STAFF_PASSWORD = 'Demo@1234';

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
