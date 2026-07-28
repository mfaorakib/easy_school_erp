<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A row's mere existence means "this sidebar item (section/group/link key)
 * is hidden for this role" — presence-based, same minimalist style as
 * sidebar_group_assignments. Only the Admin role is exposed via the UI
 * today, but `role` is a plain string so nothing stops extending this to
 * other roles later without a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_hidden_items', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('item_key');
            $table->timestamps();

            $table->unique(['role', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_hidden_items');
    }
};
