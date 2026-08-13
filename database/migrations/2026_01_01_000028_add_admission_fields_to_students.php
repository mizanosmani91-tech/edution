<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('birth_reg_no')->nullable()->after('gender');
            $table->string('blood_group')->nullable()->after('birth_reg_no');
            $table->string('religion')->nullable()->after('blood_group');
            $table->string('nationality')->default('বাংলাদেশী')->after('religion');
            $table->string('admission_type')->default('new')->after('nationality'); // new / transfer
            $table->string('previous_school')->nullable()->after('admission_type');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['name_en','gender','birth_reg_no','blood_group','religion','nationality','admission_type','previous_school']);
        });
    }
};
