<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Previous-year unpaid balance carried into a new academic year as an opening
 * due. `academic_year_id` = the year the balance is carried INTO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_carry_forwards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid', 12, 2)->default(0);
            $table->foreignId('from_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_carry_forwards');
    }
};
