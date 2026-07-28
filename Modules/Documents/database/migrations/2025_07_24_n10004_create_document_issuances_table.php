<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per (template, holder) the FIRST time a numbered certificate
 * (document_prefix set) is generated for them — the document_no is created
 * once here and reused on every reprint, so an official document number
 * never changes underneath a student/staff member. Templates with no
 * document_prefix never create rows here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->string('holder_type', 20);
            $table->unsignedBigInteger('holder_id');
            $table->string('document_no');
            $table->date('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['certificate_template_id', 'holder_type', 'holder_id'], 'doc_issuance_holder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_issuances');
    }
};
