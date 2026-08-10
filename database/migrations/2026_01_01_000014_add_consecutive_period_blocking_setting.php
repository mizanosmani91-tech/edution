<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * এখন পর্যন্ত এটা hard rule ছিল (memory অনুযায়ী) — এই migration সেটাকে
 * per-institution configurable করছে। default true রাখা হয়েছে যেন existing
 * institution গুলোর জন্য behavior না পাল্টায় (আগের হার্ড রুলের সমতুল্য),
 * চাইলে admin off করে দিতে পারবেন।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_settings', function (Blueprint $table) {
            $table->boolean('consecutive_period_blocking')->default(true)->after('has_departments');
        });
    }

    public function down(): void
    {
        Schema::table('institution_settings', function (Blueprint $table) {
            $table->dropColumn('consecutive_period_blocking');
        });
    }
};
