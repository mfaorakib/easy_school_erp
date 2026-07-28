<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A message posted to a group. Read tracking is per-member via members.last_read_at. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('message_type', 20)->default('text'); // text | file
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_messages');
    }
};
