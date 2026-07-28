<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A book loan. Open while return_date is null; late returns accrue a fine
 * (days overdue × per-day rate) recorded here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('library_member_id')->constrained('library_members')->cascadeOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine', 10, 2)->default(0);
            $table->boolean('fine_collected')->default(false);
            $table->timestamp('fine_collected_at')->nullable();
            $table->boolean('is_returned')->default(false);
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['library_member_id', 'is_returned']);
            $table->index(['book_id', 'is_returned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
