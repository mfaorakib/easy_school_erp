<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One row per staff member per date. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->char('status', 1)->default('P');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['staff_id', 'date'], 'staff_attendance_unique');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
