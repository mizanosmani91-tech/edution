<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * নতুন বিলিং মডেল: postpaid (ছাত্রসংখ্যা-ভিত্তিক মাসিক টায়ার) + prepaid
 * (ছাত্র প্রতি ৫ টাকা, আগে থেকে ব্যালেন্স লোড করা লাগবে)।
 *
 * ⚠️ পুরনো ফ্ল্যাট basic/standard/premium প্ল্যান বাদ দেওয়া হয়নি, বরং
 * মডিফাই — সব বিদ্যমান প্রতিষ্ঠানকে ডিফল্ট postpaid এ move করা হচ্ছে
 * (নিচে data-migration অংশ দেখুন), plan কলাম আর প্রাইসিং এ ব্যবহার হবে না,
 * শুধু legacy ডেটা হিসেবে থেকে যাবে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('billing_type')->default('postpaid')->after('plan'); // postpaid | prepaid
            $table->decimal('prepaid_balance', 12, 2)->default(0)->after('billing_type');
            $table->string('billing_last_charged_month', 7)->nullable()->after('prepaid_balance'); // 'YYYY-MM'
            $table->date('billing_due_at')->nullable()->after('billing_last_charged_month');
            $table->date('billing_grace_ends_at')->nullable()->after('billing_due_at');
            $table->boolean('billing_suspended')->default(false)->after('billing_grace_ends_at');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // topup | deduction | adjustment
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->text('note')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('institution_payments', function (Blueprint $table) {
            $table->string('purpose')->default('subscription')->after('for_month'); // subscription | wallet_topup
        });

        // ⚠️ ডেটা-মাইগ্রেশন: বিদ্যমান সব প্রতিষ্ঠান ডিফল্ট postpaid এ,
        // billing_due_at এই মাসের ১ তারিখ (নতুন সাইকেল শুরু)।
        DB::table('institutions')->whereNull('billing_due_at')->update([
            'billing_type' => 'postpaid',
            'billing_due_at' => now()->startOfMonth()->toDateString(),
        ]);
    }

    public function down(): void
    {
        Schema::table('institution_payments', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });

        Schema::dropIfExists('wallet_transactions');

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'billing_type',
                'prepaid_balance',
                'billing_last_charged_month',
                'billing_due_at',
                'billing_grace_ends_at',
                'billing_suspended',
            ]);
        });
    }
};
