<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A room in a dormitory with a bed capacity and cost (global). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dormitory_id')->constrained('dormitories')->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->string('room_no');
            $table->unsignedInteger('capacity')->default(1);   // number of beds
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('dormitory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_rooms');
    }
};
