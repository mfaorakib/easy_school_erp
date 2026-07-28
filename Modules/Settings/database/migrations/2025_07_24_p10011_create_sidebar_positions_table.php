<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores ONLY a display order for the admin sidebar — never a route or URL.
 * parent_key = '' means "top-level section ordering"; parent_key = '<section
 * key>' means "ordering of that section's own sub-groups". Using '' instead
 * of NULL for "no parent" deliberately avoids MySQL's NULL-uniqueness quirk
 * (a unique index never treats two NULLs as equal, which would silently let
 * duplicate top-level rows slip through).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_positions', function (Blueprint $table) {
            $table->id();
            $table->string('parent_key')->default('');
            $table->string('item_key');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['parent_key', 'item_key'], 'sidebar_pos_parent_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_positions');
    }
};
