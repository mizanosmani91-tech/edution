<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * app_notifications — সাধারণ Laravel notifications টেবিলের বদলে কাস্টম,
 * কারণ multi-tenant filtering (institution_id) দরকার যেটা built-in
 * notifications টেবিলে সহজে যোগ করা যায় না morphs structure এর কারণে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // fee_due / attendance_absent / exam_published / leave_request
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable(); // ক্লিক করলে কোথায় যাবে (relative path)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
