<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notice board entry. `audiences` = JSON list of role slugs it is visible to
 * (['all'] or e.g. ['student','parent']) — the reference's per-role visibility
 * flags, normalized. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('notice_date');
            $table->date('publish_date')->nullable();
            $table->json('audiences')->nullable();       // ['all'] | ['student','parent',...]
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
