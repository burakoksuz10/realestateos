<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // kanal: telegram, whatsapp, sms, email, instagram_dm, facebook_messenger
            $table->string('channel', 30);
            // dış sistem thread kimliği — telegram chat_id, whatsapp from-number, email Thread-Id, vs.
            $table->string('channel_thread_id', 191)->nullable();

            $table->string('subject')->nullable();
            $table->string('status', 20)->default('open'); // open, archived, closed
            $table->unsignedInteger('unread_count')->default(0);

            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 500)->nullable();
            $table->string('last_message_direction', 10)->nullable(); // in, out

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel', 'channel_thread_id']);
            $table->index(['office_id', 'status', 'last_message_at']);
            $table->index(['assigned_to', 'status']);
            $table->index('contact_id');
            $table->index('lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
