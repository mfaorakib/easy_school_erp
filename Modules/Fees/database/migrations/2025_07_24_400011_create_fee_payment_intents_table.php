<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tracked online-checkout attempt. Created the moment a guardian clicks
 * "Pay Now"; the gateway redirect/callback resolves it to completed/failed.
 * On success it creates exactly one real `fee_payments` row via the same
 * FeeService::collect() the admin manual-collection flow uses — so a guardian
 * paying online and a cashier recording cash both settle through one path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->foreignId('fee_assignment_id')->constrained('fee_assignments')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending | completed | failed
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_payload')->nullable();
            $table->string('failure_reason')->nullable();
            $table->foreignId('fee_payment_id')->nullable()->constrained('fee_payments')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payment_intents');
    }
};
