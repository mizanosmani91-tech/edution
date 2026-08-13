<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * staff_attendances — শিক্ষক/স্টাফদের দৈনিক হাজিরা (চেক-ইন/চেক-আউট সময়সহ)।
 * students এর attendances টেবিল থেকে আলাদা রাখা হয়েছে কারণ staff attendance এ
 * check_in/check_out টাইমস্ট্যাম্প দরকার (কর্মঘণ্টা হিসাবের জন্য), যা student
 * attendance এ প্রযোজ্য না।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('present'); // present / late / absent / leave
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignUuid('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'date']);
            $table->index(['institution_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
