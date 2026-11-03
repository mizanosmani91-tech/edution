<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ক্লাস ডায়েরি — শিক্ষক প্রতিদিন প্রতিটা ক্লাসে কী পড়ালেন (ক্লাসওয়ার্ক),
 * কী বাড়ির কাজ দিলেন এবং অভিভাবকের জন্য কোনো মন্তব্য থাকলে তা লিখে রাখেন।
 * অভিভাবক পোর্টালে নিজের সন্তানের ক্লাস/সেকশনের ডায়েরি দেখা যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_diary_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('class_id');
            $table->uuid('section_id')->nullable(); // null = শ্রেণির সব সেকশন
            $table->uuid('subject_id')->nullable();
            $table->uuid('teacher_id')->nullable();
            $table->date('entry_date');
            $table->string('topic', 200);
            $table->text('classwork')->nullable();
            $table->text('homework')->nullable();
            $table->text('remarks')->nullable(); // অভিভাবকের জন্য মন্তব্য
            $table->timestamps();

            $table->index(['institution_id', 'class_id', 'entry_date']);
            $table->index(['institution_id', 'teacher_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_diary_entries');
    }
};
