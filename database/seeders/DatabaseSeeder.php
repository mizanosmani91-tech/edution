<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * প্রতি ডিপ্লয়ে চলে — সবগুলো idempotent (SuperadminSeeder/DemoSeeder এ
     * updateOrCreate, DemoDataSeeder নিজে আগের ডেমো ডেটা মুছে ফ্রেশ বানায়)।
     *
     * ⚠️ ইচ্ছাকৃতভাবে WithoutModelEvents ব্যবহার করা হয়নি — আমাদের
     * UuidPrimaryKey trait মডেল creating() ইভেন্টের ওপর নির্ভর করে PHP-সাইডে
     * id সেট করার জন্য (DB gen_random_uuid() default শুধু SELECT এর পরেই
     * PHP অবজেক্টে দেখা যায়, create() এর সাথে সাথে না)। DemoDataSeeder-এ
     * একই রিকোয়েস্টে সদ্য তৈরি রেকর্ডের id চেইন করে পরের রেকর্ড বানানো হয়
     * (যেমন class তৈরি করেই তার id দিয়ে section) — events বন্ধ থাকলে সেই
     * id null থেকে যাবে আর foreign key constraint ভেঙে পুরো ডিপ্লয় ফেইল করবে।
     */
    public function run(): void
    {
        $this->call([
            SuperadminSeeder::class,
            DemoSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
