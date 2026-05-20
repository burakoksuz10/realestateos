<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->text('openai_key')->nullable(); // encrypted via Eloquent cast
            $table->string('openai_organization')->nullable();
            $table->string('preferred_model', 60)->default('gpt-4o');
            $table->json('features_enabled')->nullable(); // {valuation:true, copilot:true, content:true, ...}
            $table->json('custom_prompts')->nullable();
            $table->timestamps();

            $table->unique('office_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
