<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_settings', function (Blueprint $table) {
            $table->string('theme_primary_color')->default('#2563eb')->after('consecutive_period_blocking'); // ডিফল্ট blue-600
            $table->string('theme_accent_color')->default('#16a34a')->after('theme_primary_color'); // ডিফল্ট green-600
        });
    }

    public function down(): void
    {
        Schema::table('institution_settings', function (Blueprint $table) {
            $table->dropColumn(['theme_primary_color', 'theme_accent_color']);
        });
    }
};
