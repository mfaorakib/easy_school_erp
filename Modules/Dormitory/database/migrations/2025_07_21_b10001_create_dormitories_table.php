<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A dormitory / hostel building (global). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('boys');   // boys | girls | mixed
            $table->string('address')->nullable();
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitories');
    }
};
