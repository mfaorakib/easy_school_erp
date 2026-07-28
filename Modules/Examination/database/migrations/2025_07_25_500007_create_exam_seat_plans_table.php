<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One seating plan per exam (regenerating replaces its rooms/assignments,
 * doesn't create a second plan). `mix_classes` interleaves different
 * class/section groups seat-by-seat (anti-cheating); `seats_per_bench`
 * controls how many students share one bench (1 or 2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->unique()->constrained('exams')->cascadeOnDelete();
            $table->unsignedTinyInteger('seats_per_bench')->default(1);
            $table->boolean('mix_classes')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_plans');
    }
};
