<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A designable ID-card template for students or staff. `fields` (JSON) lists which
 * optional rows to print (roll, blood_group, phone, address, dob, session).
 * Not year-scoped — a template is reusable configuration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('holder_type', ['student', 'staff'])->default('student');
            $table->string('title')->nullable();                 // heading; defaults to school name
            $table->string('background_color', 20)->default('#4f46e5');
            $table->string('text_color', 20)->default('#ffffff');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('signature_label')->nullable();
            $table->json('fields')->nullable();
            $table->string('footer_text')->nullable();
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
