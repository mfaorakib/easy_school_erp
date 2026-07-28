<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A named salary grade: a basic pay plus earning/deduction components. Year-scoped. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_templates');
    }
};
