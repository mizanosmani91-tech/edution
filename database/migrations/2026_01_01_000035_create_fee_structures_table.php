<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * fee_structures — কোন শ্রেণির জন্য কোন ধরনের ফি কত টাকা, কত ঘনঘন
 * ধার্য হবে সেটার টেমপ্লেট। class_id nullable = সব শ্রেণির জন্য প্রযোজ্য।
 * ফি সংগ্রহের সময় FeeCollection রেকর্ড এখান থেকে auto-suggest করা যাবে
 * (এই মুহূর্তে সেই wiring করা হয়নি, শুধু স্ট্রাকচার সংজ্ঞায়িত করার UI)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('fee_type');
            $table->decimal('amount', 10, 2);
            $table->string('frequency')->default('monthly'); // monthly / termly / yearly / one_time
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
