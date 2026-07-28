<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A content block on a page. `type` selects the renderer (hero, features, stats,
 * richtext, cta, testimonials, gallery); `data` holds the type-specific JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('cms_pages')->cascadeOnDelete();
            $table->string('type', 40);
            $table->unsignedInteger('position')->default(0);
            $table->json('data')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_blocks');
    }
};
