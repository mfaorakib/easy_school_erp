<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A staff member's request to draw part of a future salary early. NOT
 * year-scoped — recovery can span across an academic-year boundary.
 * `recovered_amount` accumulates as PayrollService::markPaid() deducts
 * against it from later payslips; `amount - recovered_amount` = outstanding.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('recovered_amount', 14, 2)->default(0);
            $table->date('requested_at');
            $table->string('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
