<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * রেজিস্ট্রেশন উইজার্ডের ধাপ ১ ও ৩ থেকে আসা অতিরিক্ত তথ্য সংরক্ষণের জন্য।
 * institution_type: school / madrasa / kindergarten
 * plan: প্রার্থীর পছন্দের প্ল্যান (basic/standard/premium) — approval এর সময় কাজে লাগে
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('institution_type')->nullable()->after('name');
            $table->string('plan')->nullable()->after('status');
            $table->string('student_count_estimate')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['institution_type', 'plan', 'student_count_estimate']);
        });
    }
};
