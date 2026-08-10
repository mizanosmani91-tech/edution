<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_collections', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('fee_type');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('payment_method', ['bkash', 'nagad', 'bank_transfer', 'cash']);
            $table->string('transaction_ref')->nullable();
            $table->string('due_month');
            $table->timestamp('paid_at')->nullable();
            $table->foreignUuid('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['paid', 'partial', 'due', 'overdue'])->default('due');
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['student_id', 'due_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_collections');
    }
};
