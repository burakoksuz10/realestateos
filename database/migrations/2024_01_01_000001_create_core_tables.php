<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenants
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color')->default('#0ea5e9');
            $table->string('secondary_color')->default('#64748b');
            $table->json('settings')->nullable();
            $table->json('features')->nullable();
            $table->string('subscription_plan')->default('trial');
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Regions
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Offices
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Türkiye');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_headquarters')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add office_id and team_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('office_id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('title')->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('title');
            $table->boolean('is_active')->default(true)->after('bio');
            $table->json('settings')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('settings');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes();
        });

        // Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('resource');
            $table->string('resource_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->integer('response_code')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['resource', 'resource_id']);
        });

        // Custom Notifications
        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('sent_via')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_notifications');
        Schema::dropIfExists('audit_logs');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropForeign(['team_id']);
            $table->dropColumn(['office_id', 'team_id', 'phone', 'avatar', 'title', 'bio', 'is_active', 'settings', 'last_login_at', 'last_login_ip', 'deleted_at']);
        });
        
        Schema::dropIfExists('teams');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('tenants');
    }
};
