<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug', 80)->nullable()->index();
            $table->text('description')->nullable();

            // Tetikleyici: lead_created, manual, cold_lead, deal_stage, custom
            $table->string('trigger', 30)->default('manual');
            // Tetikleyici için ekstra parametreler (örn. cold_lead için inactivity_days)
            $table->json('trigger_config')->nullable();

            // Filtre — sadece şu lead'ler enroll olsun (status, source, score, vs.)
            $table->json('audience_filter')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // sistem onboarding gibi

            $table->unsignedInteger('enrollments_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['office_id', 'is_active']);
            $table->index(['trigger', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_campaigns');
    }
};
