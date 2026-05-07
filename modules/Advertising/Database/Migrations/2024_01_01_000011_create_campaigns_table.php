<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('hedef')->default('randevu'); // randevu, mesaj, tanitim, etkilesim, trafik
            $table->string('durum')->default('PAUSED'); // ACTIVE, PAUSED
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('city')->nullable();
            $table->string('external_id')->nullable();
            $table->json('latest_ai_analysis')->nullable();
            $table->integer('health_score')->default(0);
            $table->timestamps();
        });

        Schema::create('campaign_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->date('tarih');
            $table->decimal('harcama', 12, 2)->default(0);
            $table->integer('gosterim')->default(0);
            $table->integer('tiklama')->default(0);
            $table->integer('erisme')->default(0);
            $table->integer('lead')->default(0);
            $table->integer('mesaj')->default(0);
            $table->integer('donusum')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_metrics');
        Schema::dropIfExists('campaigns');
    }
};
