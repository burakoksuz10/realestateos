<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('drip_campaigns')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('enrolled_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // active, paused, completed, cancelled, failed
            $table->string('status', 20)->default('active');

            $table->foreignId('current_step_id')->nullable()->constrained('drip_steps')->nullOnDelete();
            $table->unsignedInteger('steps_completed')->default(0);
            $table->unsignedInteger('messages_sent')->default(0);

            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('last_error')->nullable();
            $table->json('meta')->nullable(); // istisnalar, branch sonuçları, vs.

            $table->timestamps();

            $table->index(['status', 'next_run_at']);
            $table->index(['campaign_id', 'status']);
            $table->index(['lead_id']);
            $table->index(['contact_id']);
            $table->unique(['campaign_id', 'lead_id']); // bir lead aynı campaign'e iki kere enroll olmasın
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_enrollments');
    }
};
