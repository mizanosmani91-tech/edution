<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * platform_settings — key/value সিঙ্গেল-টেবিল কনফিগ (auto-approve,
 * auto-suspend, বিলিং SMS, maintenance mode ইত্যাদি) — সুপার এডমিন
 * সেটিংস পেজ থেকে টগল করা হয়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
