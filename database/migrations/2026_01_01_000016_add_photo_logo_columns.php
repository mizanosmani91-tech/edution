<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('address');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('status');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('joining_date');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', fn (Blueprint $t) => $t->dropColumn('logo_path'));
        Schema::table('students', fn (Blueprint $t) => $t->dropColumn('photo_path'));
        Schema::table('teachers', fn (Blueprint $t) => $t->dropColumn('photo_path'));
    }
};
