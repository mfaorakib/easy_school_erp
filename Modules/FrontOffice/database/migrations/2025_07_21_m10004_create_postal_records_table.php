<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A postal dispatch or receive record (one typed table for both).
 * `party` = the recipient (dispatch) or sender (receive). Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_records', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['dispatch', 'receive']);
            $table->string('title');
            $table->string('party')->nullable();          // to (dispatch) / from (receive)
            $table->string('reference_no')->nullable();
            $table->string('address')->nullable();
            $table->date('postal_date');
            $table->text('note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'postal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postal_records');
    }
};
