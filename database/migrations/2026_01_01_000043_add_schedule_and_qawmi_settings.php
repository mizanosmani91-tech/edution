<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * পরীক্ষার সময়সূচি (exam_date/start_time/end_time) + কওমি গ্রেডিং টগল
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->date('exam_date')->nullable()->after('pass_marks');
            $table->time('start_time')->nullable()->after('exam_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('room')->nullable()->after('end_time');
        });

        Schema::table('institution_settings', function (Blueprint $table) {
            $table->boolean('qawmi_grading')->default(false)->after('has_departments');
        });
    }

    public function down(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->dropColumn(['exam_date', 'start_time', 'end_time', 'room']);
        });

        Schema::table('institution_settings', function (Blueprint $table) {
            $table->dropColumn('qawmi_grading');
        });
    }
};
