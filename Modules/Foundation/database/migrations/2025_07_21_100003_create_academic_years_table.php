<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Academic year = the scoping key for the whole system (the reference system's
 * sm_academic_years, which doubled as "session"). Exactly one is_active row.
 * `template_id` = the year this one was cloned from (the reference system parent_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('title');            // e.g. "2025-2026"
            $table->string('year');             // e.g. "2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->foreignId('template_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
