<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A behaviour incident recorded against a student. `points` snapshots the
 * type's point at record time so later edits to the type don't rewrite history.
 * Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behaviour_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('behaviour_type_id')->constrained('behaviour_types')->cascadeOnDelete();
            $table->date('incident_date');
            $table->string('comment')->nullable();
            $table->decimal('points', 6, 2)->default(0); // snapshot of the type's point
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behaviour_records');
    }
};
