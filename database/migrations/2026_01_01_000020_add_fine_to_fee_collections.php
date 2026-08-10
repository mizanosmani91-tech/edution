<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->decimal('fine_amount', 8, 2)->default(0)->after('amount_due');
            $table->string('fine_reason')->nullable()->after('fine_amount');
        });
    }

    public function down(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->dropColumn(['fine_amount', 'fine_reason']);
        });
    }
};
