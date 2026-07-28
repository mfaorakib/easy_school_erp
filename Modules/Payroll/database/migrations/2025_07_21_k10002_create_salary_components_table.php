<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An earning or deduction line of a salary template. calc_type: `fixed` uses
 * `value` directly; `percent` uses `value`% of the template's basic salary.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_template_id')->constrained('salary_templates')->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('type', ['earning', 'deduction']);
            $table->enum('calc_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
