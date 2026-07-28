<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guardian / parent (the reference system sm_parents). Persists across academic years
 * (not year-scoped) — one guardian, many children. Linked to a role=parent user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('father_name')->nullable();
            $table->string('father_mobile')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_photo')->nullable();

            $table->string('mother_name')->nullable();
            $table->string('mother_mobile')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_photo')->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_mobile')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_relation')->nullable(); // relation to student
            $table->string('guardian_photo')->nullable();
            $table->text('guardian_address')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
