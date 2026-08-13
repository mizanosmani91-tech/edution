<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * notices — নোটিশ বোর্ড। audience একটা JSON array (guardian/teacher/student/all
 * থেকে একাধিক বেছে নেওয়া যায়), category দিয়ে ফিল্টার/ব্যাজ কালার ঠিক হয়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('category')->default('general'); // academic / finance / event / general / urgent
            $table->json('audience')->nullable(); // ["guardian","teacher","student"] বা null মানে সকলে
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamp('publish_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
