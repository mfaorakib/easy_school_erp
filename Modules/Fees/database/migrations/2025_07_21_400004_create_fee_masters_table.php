<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A chargeable fee line = group + type + amount + due date (+ optional late fine),
 * optionally scoped to a class. Year-scoped. Assigned to students via
 * fee_assignments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_group_id')->constrained('fee_groups')->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete(); // null = any class
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('fine_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('fine_value', 12, 2)->default(0); // % or fixed depending on fine_type
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_masters');
    }
};
