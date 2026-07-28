<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A generated payslip for one staff member for one month (period = 'YYYY-MM').
 * Amounts are a snapshot at generation time (the line items live in payslip_items).
 * Year-scoped. One payslip per (staff, period).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('salary_template_id')->nullable()->constrained('salary_templates')->nullOnDelete();
            $table->string('period', 7); // YYYY-MM
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('gross_earnings', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->decimal('advance_deduction', 14, 2)->default(0);
            $table->enum('status', ['generated', 'paid'])->default('generated');
            $table->string('payment_method', 30)->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->date('paid_on')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['staff_id', 'period']);
            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
