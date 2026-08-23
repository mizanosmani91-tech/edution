<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * fee_installment_plans — একটা বড় অঙ্কের ফি (যেমন ভর্তি ফি, পরীক্ষার ফি)
 * কয়েক কিস্তিতে ভাগ করে নেওয়ার প্ল্যান। প্ল্যান তৈরি করলেই সমান-ভাগে
 * ভাগ হওয়া কিস্তিগুলো আলাদা আলাদা fee_collections সারি হিসেবে তৈরি হয়ে
 * যায় (installment_plan_id দিয়ে লিংক করা) — তাই বকেয়া তালিকা, অভিভাবক
 * পোর্টাল, SMS রিমাইন্ডার — সব বিদ্যমান ফিচারই স্বয়ংক্রিয়ভাবে কিস্তি
 * সাপোর্ট করবে, আলাদা কিছু বদলাতে হবে না।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_installment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('fee_type');
            $table->decimal('total_amount', 12, 2);
            $table->unsignedTinyInteger('installments_count');
            $table->string('start_month'); // 'YYYY-MM'
            $table->text('note')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('fee_collections', function (Blueprint $table) {
            $table->foreignUuid('installment_plan_id')->nullable()->after('institution_id')->constrained('fee_installment_plans')->nullOnDelete();
            $table->unsignedTinyInteger('installment_number')->nullable()->after('installment_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('installment_plan_id');
            $table->dropColumn('installment_number');
        });

        Schema::dropIfExists('fee_installment_plans');
    }
};
