<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Group membership. role: 'admin' can post in admin_only groups and manage
 * members; 'member' otherwise. last_read_at drives the per-member unread count
 * (messages after it, not sent by the member, are unread).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // admin | member
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_members');
    }
};
