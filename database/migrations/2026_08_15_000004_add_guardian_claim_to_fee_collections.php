<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * অভিভাবক পোর্টাল থেকে "আমি পেমেন্ট করেছি" জমা দেওয়ার জন্য আলাদা কলাম —
 * সরাসরি amount_paid/status বদলায় না (এডমিনের ভেরিফিকেশন ছাড়া কেউ নিজে
 * নিজের ফি "পরিশোধ হয়েছে" বলে দাবি করে দিতে পারবে না — জালিয়াতি ঠেকাতে)।
 * এডমিন কনফার্ম করলে তখনই আসল amount_paid/status আপডেট হবে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->decimal('guardian_claimed_amount', 10, 2)->nullable()->after('status');
            $table->string('guardian_claimed_method')->nullable()->after('guardian_claimed_amount'); // bkash/nagad/bank_transfer/cash
            $table->string('guardian_claimed_ref')->nullable()->after('guardian_claimed_method');
            $table->text('guardian_claim_note')->nullable()->after('guardian_claimed_ref');
            $table->timestamp('guardian_claimed_at')->nullable()->after('guardian_claim_note');
            $table->string('guardian_claim_status')->nullable()->after('guardian_claimed_at'); // pending / confirmed / rejected
        });
    }

    public function down(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->dropColumn([
                'guardian_claimed_amount',
                'guardian_claimed_method',
                'guardian_claimed_ref',
                'guardian_claim_note',
                'guardian_claimed_at',
                'guardian_claim_status',
            ]);
        });
    }
};
