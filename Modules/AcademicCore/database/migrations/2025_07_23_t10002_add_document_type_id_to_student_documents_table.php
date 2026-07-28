<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Links a student document to the (optional) catalog entry it fulfills — nullable so ad-hoc admin uploads without a formal type still work. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('student_id')
                ->constrained('document_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_type_id');
        });
    }
};
