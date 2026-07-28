<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A menu entry. Links either to a CMS page (page_id) or an arbitrary url.
 * parent_id allows one level of dropdown nesting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('cms_menus')->cascadeOnDelete();
            $table->string('label');
            $table->string('url')->nullable();
            $table->foreignId('page_id')->nullable()->constrained('cms_pages')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cms_menu_items')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('open_new_tab')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_menu_items');
    }
};
