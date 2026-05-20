<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('drip_campaigns')->cascadeOnDelete();

            $table->unsignedInteger('order')->default(0);

            // Tipler:
            //   send_message → config: { channel, body_template, subject?, attachments? }
            //   wait         → config: { hours? minutes? days? }
            //   branch       → config: { condition: 'lead_status_in', value: [...], on_true_step_id, on_false_step_id }
            //   create_task  → config: { subject, description?, due_in_hours?, assigned_to? }
            //   ai_action    → config: { feature: 'follow_up_plan'|'lead_score'|... }
            $table->string('type', 30);
            $table->json('config');

            $table->string('label')->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_steps');
    }
};
