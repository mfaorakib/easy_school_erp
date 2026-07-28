<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A certificate template. `body` is HTML containing {{placeholders}} filled at
 * generation time. Not year-scoped — reusable configuration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('holder_type', ['student', 'staff', 'general'])->default('student');
            $table->string('document_prefix', 20)->nullable();
            $table->string('heading')->nullable();
            $table->longText('body')->nullable();
            $table->string('header_image_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('signature_left_path')->nullable();
            $table->string('signature_left_label')->nullable();
            $table->string('signature_right_path')->nullable();
            $table->string('signature_right_label')->nullable();
            $table->enum('orientation', ['portrait', 'landscape'])->default('landscape');
            $table->string('accent_color', 7)->default('#b9942f');
            $table->enum('font_family', ['serif', 'sans', 'elegant'])->default('serif');
            $table->enum('border_style', ['classic', 'simple', 'none'])->default('classic');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
