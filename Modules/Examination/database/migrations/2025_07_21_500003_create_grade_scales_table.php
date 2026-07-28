<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grading scale: a mark range maps to a grade name + GPA point
 * (e.g. 80–100 → A+ → 5.00). Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // A+, A, B ...
            $table->decimal('gpa', 4, 2)->default(0);     // 5.00, 4.00 ...
            $table->decimal('mark_from', 6, 2)->default(0);
            $table->decimal('mark_upto', 6, 2)->default(100);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'mark_from', 'mark_upto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
