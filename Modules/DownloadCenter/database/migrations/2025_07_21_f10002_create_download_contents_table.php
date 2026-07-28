<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A downloadable document. Targeted by `audiences` (JSON role slugs, ['all'] or
 * roles) and optionally a class/section. Holds either an uploaded file or an
 * external link. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->nullable()->constrained('content_types')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->json('audiences')->nullable();       // ['all'] | ['student','parent',...]
            $table->string('file_path')->nullable();      // stored upload
            $table->string('external_url')->nullable();   // or an external link
            $table->date('publish_date')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_contents');
    }
};
