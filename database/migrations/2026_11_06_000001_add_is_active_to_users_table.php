<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ইউজারের লগইন চালু/বন্ধ করার সুইচ। ডিফল্ট true — বিদ্যমান সব ইউজার আগের মতোই
 * ঢুকতে পারবে, কেউ লক আউট হবে না। শিক্ষক/স্টাফকে "নিষ্ক্রিয়" করলে তার ইউজার
 * false হয়, ফলে নতুন লগইন ও চলমান সেশন দুটোই বন্ধ।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
