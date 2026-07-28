<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module on/off registry — replaces the reference system's moduleStatusCheck()
 * (purchase code + SaaS plan). Here: a plain enabled flag per module.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('module')->unique();  // "Fees", "ExamPlan", "Inventory", ...
            $table->string('label');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
