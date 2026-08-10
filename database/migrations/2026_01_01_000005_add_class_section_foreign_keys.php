<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * students.class_id/section_id এর FK এখন 000004 migration এই যোগ করা হয়েছে
 * (folded in), তাই এখানে শুধু exam_subjects.class_id এর FK — exam_subjects
 * টেবিল তৈরি হওয়ার পরে এই migration রান করান।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });
    }
};
