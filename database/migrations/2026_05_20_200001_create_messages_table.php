<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('direction', 10); // in, out
            $table->string('channel', 30);
            $table->string('external_id', 191)->nullable()->index(); // provider message id

            $table->text('body')->nullable();
            $table->json('attachments')->nullable(); // [{type, url, name, mime}]

            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // AI alanları
            $table->text('ai_summary')->nullable();
            $table->string('ai_sentiment', 20)->nullable(); // positive, neutral, negative
            $table->string('ai_intent', 60)->nullable();

            $table->string('status', 20)->default('received');
            // out: queued, sent, delivered, read, failed
            // in:  received, read

            $table->timestamp('read_at')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
