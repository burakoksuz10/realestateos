<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('developer_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('slogan')->nullable();
            $table->json('description')->nullable();
            $table->string('status')->default('planning');
            $table->string('type')->default('residential');
            $table->string('country')->default('Türkiye');
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('neighborhood')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('total_units')->default(0);
            $table->integer('available_units')->default(0);
            $table->integer('sold_units')->default(0);
            $table->integer('total_blocks')->nullable();
            $table->integer('total_floors')->nullable();
            $table->decimal('land_area', 12, 2)->nullable();
            $table->decimal('construction_area', 12, 2)->nullable();
            $table->decimal('min_price', 15, 2)->nullable();
            $table->decimal('max_price', 15, 2)->nullable();
            $table->string('price_currency')->default('TRY');
            $table->json('payment_plans')->nullable();
            $table->json('features')->nullable();
            $table->json('amenities')->nullable();
            $table->json('unit_types')->nullable();
            $table->date('start_date')->nullable();
            $table->date('estimated_completion')->nullable();
            $table->date('actual_completion')->nullable();
            $table->date('sales_start_date')->nullable();
            $table->string('website_url')->nullable();
            $table->string('brochure_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['city', 'status']);
        });

        // Listings
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_no')->unique();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('type');
            $table->string('category');
            $table->string('status')->default('draft');
            $table->string('listing_type');
            $table->decimal('price', 15, 2);
            $table->string('price_currency')->default('TRY');
            $table->decimal('price_per_sqm', 12, 2)->nullable();
            $table->decimal('original_price', 15, 2)->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->string('commission_type')->default('percentage');
            $table->string('country')->default('Türkiye');
            $table->string('city');
            $table->string('district');
            $table->string('neighborhood')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('location_description')->nullable();
            $table->decimal('gross_sqm', 10, 2)->nullable();
            $table->decimal('net_sqm', 10, 2)->nullable();
            $table->decimal('land_sqm', 12, 2)->nullable();
            $table->integer('room_count')->nullable();
            $table->integer('living_room_count')->nullable();
            $table->integer('bathroom_count')->nullable();
            $table->integer('floor_number')->nullable();
            $table->integer('total_floors')->nullable();
            $table->integer('building_age')->nullable();
            $table->string('heating_type')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('facade')->nullable();
            $table->boolean('is_furnished')->default(false);
            $table->string('furniture_status')->nullable();
            $table->json('features')->nullable();
            $table->json('features_text')->nullable();
            $table->json('amenities')->nullable();
            $table->json('nearby_places')->nullable();
            $table->string('deed_status')->nullable();
            $table->string('deed_type')->nullable();
            $table->string('zoning_status')->nullable();
            $table->string('usage_status')->nullable();
            $table->boolean('is_in_site')->default(false);
            $table->string('site_name')->nullable();
            $table->decimal('dues_amount', 10, 2)->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('favorite_count')->default(0);
            $table->integer('inquiry_count')->default(0);
            $table->integer('quality_score')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->date('available_from')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('rented_at')->nullable();
            $table->string('authorization_type')->nullable();
            $table->date('authorization_start')->nullable();
            $table->date('authorization_end')->nullable();
            $table->boolean('portal_sync_enabled')->default(false);
            $table->json('portal_ids')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('ai_description')->nullable();
            $table->json('ai_valuation')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'listing_type']);
            $table->index(['city', 'district']);
            $table->index(['type', 'category']);
            $table->index('agent_id');
        });

        // Listing Versions
        Schema::create('listing_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('version_number');
            $table->json('data');
            $table->json('changes')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // Portal Sync Logs
        Schema::create('portal_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('portal');
            $table->string('action');
            $table->string('status');
            $table->string('portal_listing_id')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['listing_id', 'portal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_sync_logs');
        Schema::dropIfExists('listing_versions');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('projects');
    }
};
