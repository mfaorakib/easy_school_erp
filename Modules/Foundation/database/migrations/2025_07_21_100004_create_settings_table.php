<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normalized key/value settings — replaces the reference system's ~100-column
 * sm_general_settings. Grouped for the settings UI; typed for casting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general'); // general | localization | branding | mail | ...
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');   // string | bool | int | json
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
