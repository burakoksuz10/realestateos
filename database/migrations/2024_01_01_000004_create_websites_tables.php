<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Websites
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->unique();
            $table->string('theme')->default('modern');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color')->default('#0ea5e9');
            $table->string('secondary_color')->default('#64748b');
            $table->string('font_family')->default('Inter');
            $table->json('settings')->nullable();
            $table->json('seo_settings')->nullable();
            $table->json('social_links')->nullable();
            $table->json('contact_info')->nullable();
            $table->string('analytics_id')->nullable();
            $table->string('gtm_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->text('header_scripts')->nullable();
            $table->text('footer_scripts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pages
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->json('title');
            $table->string('slug');
            $table->json('content')->nullable();
            $table->string('template')->default('default');
            $table->json('blocks')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['website_id', 'slug']);
        });

        // Forms
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('fields');
            $table->json('settings')->nullable();
            $table->text('success_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('notification_emails')->nullable();
            $table->boolean('create_lead')->default(true);
            $table->foreignId('assign_to')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Form Submissions
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('page_url')->nullable();
            $table->timestamps();
        });

        // Visitor Tracking
        Schema::create('visitor_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_id');
            $table->string('session_id');
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->json('event_data')->nullable();
            $table->string('page_url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();
            
            $table->index(['website_id', 'visitor_id']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_tracking');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('websites');
    }
};
