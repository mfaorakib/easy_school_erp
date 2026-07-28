<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Stock-out by sale (to a student/staff user). Reduces stock like an issue. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_sells', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 40)->nullable()->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_store_id')->nullable()->constrained('item_stores')->nullOnDelete();
            $table->foreignId('sold_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name', 150)->nullable();
            $table->string('buyer_contact', 40)->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->date('sold_on');
            $table->string('note')->nullable();
            $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_sells');
    }
};
