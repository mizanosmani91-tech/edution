<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * অডিট লগ — ফি, নম্বর, বেতন ইত্যাদি সংবেদনশীল রেকর্ডে কে কখন কী বদলাল।
 * শুধু যোগ হয় (append-only): RLS ও মডেল দুই জায়গাতেই আপডেট/মোছা আটকানো।
 * ইউজারের নাম/রোল snapshot হিসেবে রাখা, যাতে ইউজার মুছে গেলেও ইতিহাস থাকে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role', 30)->nullable();
            $table->string('event', 20); // created | updated | deleted
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->string('auditable_label')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['institution_id', 'created_at']);
            $table->index(['institution_id', 'auditable_type', 'auditable_id']);
            $table->index(['institution_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
