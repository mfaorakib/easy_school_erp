<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-created EXTRA umbrella sections, beyond the 10 built into
 * App\Support\SidebarNav::defaultSections(). The 10 built-in ones are never
 * stored here (they're plain PHP) — this table only ever holds custom ones,
 * so "is this section deletable" is simply "does a row exist here for it."
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label', 100);
            // No DB-level default: a literal multi-byte emoji embedded directly
            // in a CREATE TABLE ... DEFAULT clause isn't reliably preserved
            // through MySQL's DDL text pipeline in this environment (verified:
            // normal parameter-bound INSERTs of the same emoji round-trip
            // perfectly, only the raw DDL-literal default got mangled to '?').
            // SidebarManagerController::storeSection() always supplies a real
            // icon anyway ('📁' as an application-layer fallback), so this
            // column never actually needs a DB default to fall back on.
            $table->string('icon', 20)->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_sections');
    }
};
