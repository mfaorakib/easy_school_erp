<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Which rooms a seat plan uses, and the order they fill in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_plan_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_seat_plan_id')->constrained('exam_seat_plans')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['exam_seat_plan_id', 'classroom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_plan_rooms');
    }
};
