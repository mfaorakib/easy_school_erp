<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A behaviour/conduct definition with a point value: positive = reward,
 * negative = penalty (e.g. "Helping others" +5, "Late" -2). Global.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behaviour_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('point', 6, 2)->default(0); // may be negative
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behaviour_types');
    }
};
