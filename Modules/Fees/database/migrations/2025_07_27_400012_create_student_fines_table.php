<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An ad-hoc, one-off fine against a student for a disciplinary/other reason —
 * deliberately NOT part of the structured Fee Group/Type/Master/Assignment
 * hierarchy. Posts into Accounting as income once collected, mirroring
 * FeePayment's own (source_module, source_id) idempotent posting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_no', 40)->nullable()->unique();
            $table->string('reason');
            $table->decimal('amount', 10, 2);
            $table->date('fine_date');
            $table->boolean('is_collected')->default(false);
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('imposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'is_collected']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fines');
    }
};
