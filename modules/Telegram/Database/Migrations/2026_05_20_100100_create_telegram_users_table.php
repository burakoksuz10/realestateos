<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('telegram_chat_id')->nullable()->index();
            $table->string('telegram_user_id')->nullable();
            $table->string('telegram_username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('language_code', 8)->nullable();
            $table->string('pairing_code', 16)->nullable()->unique();
            $table->timestamp('pairing_expires_at')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};
