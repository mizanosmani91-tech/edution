<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * superadmin প্যানেল থেকে প্রতিটা প্রতিষ্ঠানের মডিউল অ্যাক্সেস ও
 * শিক্ষার্থী সীমা override করার সুবিধা — সুপার এডমিন কন্ট্রোল প্যানেলের
 * "প্রতিষ্ঠান পরিচালনা" মোডাল এই দুইটা কলাম ব্যবহার করে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->json('enabled_modules')->nullable()->after('plan');
            $table->unsignedInteger('student_limit_override')->nullable()->after('enabled_modules');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['enabled_modules', 'student_limit_override']);
        });
    }
};
