<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A category for money movement. `type` splits income heads from expense heads
 * (one typed table replaces the reference's separate income_heads / expense_heads
 * / chart_of_accounts). Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->enum('type', ['income', 'expense']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_heads');
    }
};
