<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A topic under a lesson, marked complete/incomplete as it is taught. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_complete')->default(false);
            $table->date('completed_on')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_topics');
    }
};
