<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * বাজেট পরিকল্পনা — প্রতি মাসে প্রতিটা খরচের ক্যাটাগরির জন্য একটা লক্ষ্য/সীমা
 * বেঁধে দেওয়া, তারপর existing Expense রেকর্ডের সাথে তুলনা করে over/under
 * বাজেট দেখানো (Expense.category এর সাথে ম্যাচ করেই হিসাব হয়)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->string('category');
            $table->string('period_month'); // 'YYYY-MM'
            $table->decimal('planned_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'category', 'period_month']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
