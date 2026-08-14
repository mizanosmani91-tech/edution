<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ভর্তি পাইপলাইন — অনলাইন ভর্তি ফর্ম (StudentAdmissionWizard) থেকে আলাদা;
 * এটা student তৈরি হওয়ার আগের স্তর (আবেদন → পরীক্ষা/ইন্টারভিউ → গৃহীত/অপেক্ষমাণ/বাতিল)।
 * গৃহীত হলে convert_to_student() এর মাধ্যমে আসল students টেবিলে যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->uuid('applying_class_id')->nullable();
            $table->string('previous_school')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('pending'); // pending/test_scheduled/shortlisted/waiting/accepted/rejected
            $table->date('test_date')->nullable();
            $table->time('test_time')->nullable();
            $table->decimal('test_score', 5, 2)->nullable();
            $table->text('interview_notes')->nullable();
            $table->uuid('converted_student_id')->nullable();
            $table->timestamps();

            $table->foreign('applying_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
