<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_agent_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->unique()->constrained('offices')->cascadeOnDelete();

            $table->boolean('is_active')->default(false);

            // ElevenLabs Agent referansları (dashboard'dan kopyalanır)
            $table->string('elevenlabs_agent_id')->nullable();
            $table->string('default_voice_id')->nullable();

            // 4 routing modu
            $table->string('routing_mode', 30)->default('listing_owner_first');
            // listing_owner_first | secretary_only | listing_owner_only | callback_only

            // Transfer hedefleri
            $table->string('secretary_phone')->nullable();
            $table->string('default_agent_phone')->nullable(); // moda göre fallback

            // Sıraya alma süresi (saniye) — owner_first modunda owner'ı kaç saniye bekleyeceğiz
            $table->unsignedInteger('ring_timeout_seconds')->default(15);

            // Mesai saatleri — basit ilk versiyon: weekday_start, weekday_end, weekend_active
            $table->time('business_hours_start')->default('09:00');
            $table->time('business_hours_end')->default('19:00');
            $table->boolean('weekend_active')->default(false);
            $table->string('timezone', 60)->default('Europe/Istanbul');

            // Karakter / dil
            $table->text('system_prompt')->nullable(); // varsayılan template config'ten gelir
            $table->string('greeting_template')->default('Merhaba, {{office.name}}\'a hoş geldiniz. Size nasıl yardımcı olabilirim?');
            $table->string('language', 5)->default('tr');

            // Telegram kanalı — agent burada brifing/özet atar
            $table->string('telegram_office_channel')->nullable(); // chat_id

            // Yapılandırma extra
            $table->json('settings')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_agent_configs');
    }
};
