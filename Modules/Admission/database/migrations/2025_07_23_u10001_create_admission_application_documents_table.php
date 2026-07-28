<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents uploaded with a PENDING public application — staged here, not on
 * the student, until the application is actually confirmed (see
 * AdmissionService::confirm(), which copies these into student_documents).
 * A rejected/abandoned application's uploads never touch a real student.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_application_documents');
    }
};
