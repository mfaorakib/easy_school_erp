<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A staff member's resignation request. NOT year-scoped — a resignation is a
 * one-time employment-lifecycle event, not something that repeats or resets
 * per academic year (unlike LeaveApplication, which this otherwise mirrors).
 * Approving one stamps Staff.leaving_date + is_active=false (see
 * ResignationService::review()) — the same fields the Documents module's
 * Experience Certificate already reads.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resignation_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->date('intended_last_day');
            $table->string('status', 20)->default('pending');
            $table->string('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignation_applications');
    }
};
