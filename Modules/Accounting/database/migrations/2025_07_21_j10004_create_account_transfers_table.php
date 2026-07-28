<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A money transfer between two bank accounts. Year-scoped.
 *
 * The two bank-account FKs use restrictOnDelete (not cascade): a bank account
 * with real transfer history must never be deletable — cascading would
 * silently destroy that history and corrupt other accounts' derived
 * balances. The model already has an `is_active` toggle for "retire this
 * without losing history"; deletion is only ever for a never-used mistake
 * entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('to_bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('purpose')->nullable();
            $table->date('transfer_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
    }
};
