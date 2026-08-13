<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('gender')->nullable()->after('name_en');
            $table->string('nid')->nullable()->after('gender');
            $table->text('address')->nullable()->after('nid');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('education')->nullable()->after('emergency_contact');
            $table->string('passing_institution')->nullable()->after('education');
            $table->string('employee_type')->default('permanent')->after('designation'); // permanent/temporary/parttime
            $table->unsignedInteger('experience_years')->nullable()->after('employee_type');
            $table->string('previous_workplace')->nullable()->after('experience_years');
            $table->decimal('base_salary', 10, 2)->nullable()->after('previous_workplace');
            $table->decimal('house_rent', 10, 2)->nullable()->after('base_salary');
            $table->decimal('medical_allowance', 10, 2)->nullable()->after('house_rent');
            $table->string('bank_name')->nullable()->after('medical_allowance');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('bank_account')->nullable()->after('bank_branch');
            $table->string('mobile_banking')->nullable()->after('bank_account');
            $table->json('subjects_taught')->nullable()->after('mobile_banking'); // Subject id array
            $table->json('assigned_classes')->nullable()->after('subjects_taught'); // SchoolClass id array
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'name_en','gender','nid','address','emergency_contact','education',
                'passing_institution','employee_type','experience_years','previous_workplace',
                'base_salary','house_rent','medical_allowance','bank_name','bank_branch',
                'bank_account','mobile_banking','subjects_taught','assigned_classes',
            ]);
        });
    }
};
