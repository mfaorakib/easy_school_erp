<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A visitor logbook entry. out_time null = still on premises. Year-scoped. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->string('purpose')->nullable();
            $table->string('to_meet')->nullable();       // person/department being visited
            $table->string('id_card')->nullable();
            $table->unsignedInteger('no_of_person')->default(1);
            $table->date('visit_date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
