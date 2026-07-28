<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overrides which umbrella section a sub-group (e.g. "fees", "wallet") lives
 * under — one row per sub-group that's been moved away from its DEFAULT
 * section (the one it's grouped under in SidebarNav::defaultSections()).
 * A sub-group with NO row here just uses its default placement, so this
 * table starts empty and the sidebar is byte-for-byte unchanged until an
 * admin actually moves something. `section_key` can point at either one of
 * the 10 built-in keys or a custom sidebar_sections.key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('item_key')->unique();
            $table->string('section_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_group_assignments');
    }
};
