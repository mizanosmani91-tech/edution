<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ইনভেন্টরি/অ্যাসেট ম্যানেজমেন্ট — লাইব্রেরির বই ছাড়া বাকি সব সম্পদ
 * (ফার্নিচার, ল্যাব যন্ত্রপাতি, ইলেকট্রনিক্স, স্পোর্টস সরঞ্জাম ইত্যাদি)।
 * BookIssue এর মতোই ইস্যু/রিটার্ন প্যাটার্ন ব্যবহার করা হয়েছে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('asset_tag')->nullable();
            $table->unsignedInteger('quantity_total')->default(1);
            $table->unsignedInteger('quantity_available')->default(1);
            $table->string('unit')->default('পিস');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('condition')->default('good'); // good / fair / damaged / lost
            $table->string('location')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('institution_id');
            $table->index('category');
        });

        Schema::create('inventory_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('item_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('issued_to'); // কার কাছে/কোন রুমে গেল (ফ্রি টেক্সট)
            $table->uuid('issued_by')->nullable();
            $table->date('issued_at');
            $table->date('expected_return_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->string('status')->default('issued'); // issued / returned / lost
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_issues');
        Schema::dropIfExists('inventory_items');
    }
};
