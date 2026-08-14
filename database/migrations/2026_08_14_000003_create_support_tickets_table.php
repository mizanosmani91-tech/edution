<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * support_tickets — প্রতিষ্ঠান থেকে superadmin-কে পাঠানো সাপোর্ট অনুরোধ।
 * institution_id আছে, কিন্তু superadmin-কে সব প্রতিষ্ঠানের টিকেট একসাথে
 * দেখতে হয় বলে BelongsToTenant (fail-closed) ব্যবহার হয়নি —
 * InstitutionPayment মডেলের মতোই কাস্টম tenant-or-superadmin scope।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->enum('priority', ['low', 'med', 'high'])->default('med');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->timestamps();

            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
