<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base groups = categories for the generic lookup table (Gender, Religion,
 * Blood group, ...). the reference system: sm_base_groups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // e.g. "Gender"
            $table->string('slug')->unique();  // e.g. "gender" (stable key for code)
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_groups');
    }
};
