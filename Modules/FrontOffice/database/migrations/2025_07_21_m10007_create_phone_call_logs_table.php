<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A phone call log entry (incoming/outgoing). Year-scoped. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40);
            $table->enum('call_type', ['incoming', 'outgoing'])->default('incoming');
            $table->date('call_date');
            $table->string('call_duration', 40)->nullable();
            $table->text('description')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['call_type', 'call_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_call_logs');
    }
};
