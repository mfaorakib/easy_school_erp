<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single collection against a fee assignment. Multiple rows = partial payments.
 * Each row records what was collected plus the fine and discount applied AT THAT
 * time, so a receipt is reproducible and the running balance is auditable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 40)->nullable()->unique(); // printable receipt number (admin-configurable pattern)
            $table->foreignId('fee_assignment_id')->constrained('fee_assignments')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);    // principal paid this time
            $table->decimal('fine', 12, 2)->default(0);      // fine collected this time
            $table->decimal('discount', 12, 2)->default(0);  // discount applied this time
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->date('paid_on');
            $table->string('method')->default('cash');       // cash | bank | mobile | ...
            $table->string('reference')->nullable();         // txn/receipt ref
            $table->string('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('fee_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
