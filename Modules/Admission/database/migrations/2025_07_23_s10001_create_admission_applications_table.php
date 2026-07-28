<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A public admission application — submitted by a prospective student/guardian,
 * pending staff review. Confirming one creates a real AcademicCore Student
 * (see AdmissionService::confirm()); the application row is kept for the audit
 * trail either way (confirmed or rejected).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 20)->unique();

            $table->string('first_name', 70);
            $table->string('last_name', 70)->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('base_setups')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->foreignId('desired_class_id')->constrained('classes')->cascadeOnDelete();

            $table->string('guardian_name', 120)->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->string('guardian_mobile', 30)->nullable();
            $table->string('guardian_email', 120)->nullable();

            $table->text('present_address')->nullable();
            $table->string('previous_school', 150)->nullable();
            $table->string('photo_path')->nullable();

            $table->string('status', 20)->default('pending'); // pending | confirmed | rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('unique_id', 40)->nullable();

            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
