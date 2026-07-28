<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single income or expense entry (one typed table replaces add_incomes /
 * add_expenses). `bank_account_id` null means cash. Year-scoped.
 *
 * `source_module`/`source_id` trace an auto-posted transaction back to the
 * module/record that created it (a Fee payment, a Payslip, an Inventory
 * sell/receive) — lets AccountingService::postFromSource() check "has this
 * already been posted?" before writing, so a source event can never be
 * double-counted even if it somehow runs twice. Manually-entered transactions
 * leave both columns null; MySQL's unique index never treats two NULLs as a
 * duplicate, so any number of manual entries can coexist alongside the
 * auto-posted ones.
 *
 * `parent_transaction_id` lets a correction (partial product return, fee
 * refund, ...) be posted as a NEW transaction linked back to the one it
 * corrects, instead of ever editing or deleting history. It is
 * nullable/nullOnDelete since a correction must never block the original from
 * later being soft-deleted.
 *
 * `account_head_id` uses restrictOnDelete (not cascade): a head with real
 * transaction history must never be deletable — cascading would silently
 * destroy that history and corrupt other accounts' derived balances. The
 * model already has an `is_active` toggle for "retire this without losing
 * history"; deletion is only ever for a never-used mistake entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('account_head_id')->constrained('account_heads')->restrictOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('source_module')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->boolean('is_adjustment')->default(false);
            $table->text('adjustment_reason')->nullable();
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('payment_method', 30)->default('cash'); // cash | bank | cheque | card | mobile
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['source_module', 'source_id'], 'acc_txn_source_unique');
            $table->index(['type', 'transaction_date', 'academic_year_id'], 'acc_txn_type_date_year_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
