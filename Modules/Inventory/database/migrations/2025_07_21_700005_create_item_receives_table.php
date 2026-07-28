<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Stock-in: a quantity of an item received into a store (from a supplier). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_receives', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 40)->nullable()->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_store_id')->nullable()->constrained('item_stores')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->date('received_on');
            $table->string('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_id', 'item_store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_receives');
    }
};
