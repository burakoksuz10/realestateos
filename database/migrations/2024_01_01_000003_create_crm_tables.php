<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('individual');
            $table->string('status')->default('active');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_title')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('tax_office')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Türkiye');
            $table->string('preferred_contact_method')->nullable();
            $table->string('preferred_contact_time')->nullable();
            $table->string('language')->default('tr');
            $table->json('property_preferences')->nullable();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('budget_currency')->default('TRY');
            $table->json('preferred_locations')->nullable();
            $table->string('source')->nullable();
            $table->string('source_detail')->nullable();
            $table->foreignId('referral_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->boolean('kvkk_consent')->default(false);
            $table->timestamp('kvkk_consent_date')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consent_date')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['email', 'phone']);
            $table->index('assigned_to');
        });

        // Pipelines
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pipeline Stages
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#6b7280');
            $table->integer('order')->default(0);
            $table->integer('probability')->default(50);
            $table->boolean('is_won_stage')->default(false);
            $table->boolean('is_lost_stage')->default(false);
            $table->json('auto_actions')->nullable();
            $table->json('required_fields')->nullable();
            $table->timestamps();
        });

        // Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pipeline_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->integer('score')->default(0);
            $table->string('source')->nullable();
            $table->string('source_detail')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('landing_page')->nullable();
            $table->string('referrer_url')->nullable();
            $table->string('interest_type')->nullable();
            $table->string('property_type')->nullable();
            $table->string('property_category')->nullable();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('budget_currency')->default('TRY');
            $table->json('preferred_locations')->nullable();
            $table->integer('room_requirement')->nullable();
            $table->decimal('size_min', 10, 2)->nullable();
            $table->decimal('size_max', 10, 2)->nullable();
            $table->text('requirements_notes')->nullable();
            $table->string('urgency')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->boolean('is_qualified')->default(false);
            $table->text('qualification_notes')->nullable();
            $table->string('disqualification_reason')->nullable();
            $table->integer('ai_score')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->json('intent_signals')->nullable();
            $table->json('behavior_data')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'score']);
            $table->index('assigned_to');
            $table->index('source');
        });

        // Deals
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pipeline_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('open');
            $table->string('deal_type')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('currency')->default('TRY');
            $table->integer('probability')->default(50);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->string('commission_type')->default('percentage');
            $table->decimal('commission_amount', 15, 2)->nullable();
            $table->json('commission_split')->nullable();
            $table->boolean('is_partner_deal')->default(false);
            $table->foreignId('partner_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('partner_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('partner_commission_split')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->boolean('contract_signed')->default(false);
            $table->timestamp('contract_signed_at')->nullable();
            $table->boolean('deposit_received')->default(false);
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->timestamp('deposit_received_at')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('won_reason')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'stage_id']);
            $table->index('assigned_to');
        });

        // Activities
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('outcome')->nullable();
            $table->integer('call_duration')->nullable();
            $table->string('call_recording_url')->nullable();
            $table->text('call_transcript')->nullable();
            $table->string('call_sentiment')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('attendees')->nullable();
            $table->string('email_message_id')->nullable();
            $table->string('email_thread_id')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_next_actions')->nullable();
            $table->string('ai_sentiment')->nullable();
            $table->string('ai_intent')->nullable();
            $table->boolean('is_automated')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'created_at']);
            $table->index('user_id');
        });

        // Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->string('result')->nullable();
            $table->text('result_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['assigned_to', 'status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('contacts');
    }
};
