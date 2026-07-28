<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock-out: a quantity of an item issued to a user. Returnable — a returned
 * issue no longer counts against stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_store_id')->nullable()->constrained('item_stores')->nullOnDelete();
            $table->foreignId('issued_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->date('issue_date');
            $table->date('return_date')->nullable();
            $table->boolean('is_returned')->default(false);
            $table->string('note')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_id', 'is_returned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_issues');
    }
};
