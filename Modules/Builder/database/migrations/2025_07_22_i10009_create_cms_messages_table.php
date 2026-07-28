<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Submissions captured by the builder's public Contact / Newsletter sections.
 * `type` = contact | subscribe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('contact');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_messages');
    }
};
