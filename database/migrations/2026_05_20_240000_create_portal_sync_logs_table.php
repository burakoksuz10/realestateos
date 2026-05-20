<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_sync_logs')) {
            return;
        }

        Schema::create('portal_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('portal', 32);                       // sahibinden, hepsiemlak, emlakjet, zingat
            $table->string('action', 32);                       // publish, update, delete, fetch_stats
            $table->string('status', 16);                       // pending, success, failed
            $table->string('portal_listing_id')->nullable();    // dış sistemdeki ID
            $table->string('portal_url')->nullable();           // dış sistemdeki ilan URL'i
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'portal']);
            $table->index(['portal', 'status']);
            $table->index('synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_sync_logs');
    }
};
