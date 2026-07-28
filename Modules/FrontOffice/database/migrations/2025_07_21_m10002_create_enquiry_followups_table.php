<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A follow-up contact recorded against an admission enquiry. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_enquiry_id')->constrained('admission_enquiries')->cascadeOnDelete();
            $table->date('follow_up_date');
            $table->date('next_follow_up_date')->nullable();
            $table->text('response')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_followups');
    }
};
