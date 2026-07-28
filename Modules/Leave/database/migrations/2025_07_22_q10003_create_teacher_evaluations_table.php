<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A teacher evaluation. `criteria` is a JSON list of {name, score} rows; the
 * average is stored on total_score. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('term')->nullable();
            $table->date('evaluation_date');
            $table->json('criteria')->nullable();
            $table->decimal('total_score', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluations');
    }
};
