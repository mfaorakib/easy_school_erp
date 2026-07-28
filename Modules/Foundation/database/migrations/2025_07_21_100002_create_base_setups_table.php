<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base setups = the values inside a base group (Male/Female under Gender,
 * A+/B+ under Blood group, ...). the reference system: sm_base_setups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_group_id')->constrained('base_groups')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['base_group_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_setups');
    }
};
