<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A logged complaint, tracked open → in_progress → resolved. Year-scoped. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_type_id')->nullable()->constrained('complaint_types')->nullOnDelete();
            $table->string('complainant_name');
            $table->string('phone', 40)->nullable();
            $table->string('source')->nullable();
            $table->date('complaint_date');
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('staff')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->string('attachment_path')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
