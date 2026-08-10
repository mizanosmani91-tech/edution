<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * institution_payments — SaaS সাবস্ক্রিপশন পেমেন্ট (৳499/মাস, ম্যানুয়াল
 * bKash/Nagad/bank collection)। Institution admin জমা দেয়, superadmin
 * approve/reject করে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(499);
            $table->string('method'); // bkash / nagad / bank_transfer
            $table->string('transaction_ref');
            $table->string('for_month'); // 'YYYY-MM'
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_payments');
    }
};
