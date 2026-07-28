<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One row per student per date (per subject when subject-wise). Roster is drawn
 * from the live enrollment (student_records). Upserted when a day is re-saved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete(); // null = daily
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('date');
            $table->char('status', 1)->default('P'); // P/L/A/H/O
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'section_id', 'date']);
        });

        // Functional UNIQUE index so daily rows (subject_id NULL) are genuinely
        // unique per student+date. A plain UNIQUE(student_id,date,subject_id)
        // does NOT enforce this in InnoDB because a UNIQUE index permits many
        // NULLs, so concurrent daily saves could both INSERT and duplicate.
        // COALESCE(subject_id, 0) is evaluated only inside the index expression;
        // the stored subject_id stays NULL (FK untouched), while NULL rows now
        // collide with each other and distinct real subject_ids still differ.
        DB::statement('CREATE UNIQUE INDEX student_attendances_daily_unique ON student_attendances (student_id, date, (COALESCE(subject_id, 0)))');
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
