<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Discounts granted on a specific fee assignment (many-to-many). Also an
 * auditable adjustment record: why the discount/waiver was given, and who
 * approved it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_assignment_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_assignment_id')->constrained('fee_assignments')->cascadeOnDelete();
            $table->foreignId('fee_discount_id')->constrained('fee_discounts')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['fee_assignment_id', 'fee_discount_id'], 'fee_assignment_discount_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_assignment_discount');
    }
};
