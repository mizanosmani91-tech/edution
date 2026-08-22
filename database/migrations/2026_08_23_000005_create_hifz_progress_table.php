<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * হিফজ/কুরআন মুখস্থের অগ্রগতি ট্র্যাকিং — মাদ্রাসায় প্রচলিত তিন ধরনের
 * দৈনিক পাঠের হিসাব: সবক (আজকের নতুন পড়া), সবকি (সাম্প্রতিক সবক রিভিশন),
 * মঞ্জিল/দোর (পুরাতন সবকের ঘূর্ণায়মান রিভিশন)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hifz_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('student_id');
            $table->uuid('teacher_id')->nullable(); // হিফজ শিক্ষক/মুরাব্বি
            $table->date('date');

            // সবক — আজকের নতুন পড়া
            $table->string('sabak_para')->nullable(); // যেমন: পারা ৫
            $table->string('sabak_range')->nullable(); // যেমন: পৃষ্ঠা ১০-১২
            $table->string('sabak_quality')->nullable(); // excellent / good / weak

            // সবকি — সাম্প্রতিক সবক রিভিশন
            $table->string('sabqi_range')->nullable();
            $table->string('sabqi_quality')->nullable();

            // মঞ্জিল/দোর — পুরাতন সবক রিভিশন
            $table->string('manzil_range')->nullable();
            $table->string('manzil_quality')->nullable();

            $table->text('remarks')->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->timestamps();

            $table->index('institution_id');
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hifz_progress');
    }
};
