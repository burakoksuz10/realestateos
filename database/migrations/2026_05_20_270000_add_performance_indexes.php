<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Faz 7 performans iyileştirmesi — yoğun WHERE/ORDER BY kolonları için
 * eksik index'ler.
 *
 * Audit'te tespit edilen yerler:
 *  - Lead::ai_score (copilot filtering)
 *  - Deal::stage_entered_at (deals:stalled command)
 *  - Listing::office_id (office isolation queries)
 *  - Activity::user_id (analytics service)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!$this->hasIndex('leads', 'leads_ai_score_idx')) {
                    $table->index('ai_score', 'leads_ai_score_idx');
                }
                if (!$this->hasIndex('leads', 'leads_office_status_idx')) {
                    $table->index(['office_id', 'status'], 'leads_office_status_idx');
                }
                if (!$this->hasIndex('leads', 'leads_last_activity_idx')) {
                    $table->index('last_activity_at', 'leads_last_activity_idx');
                }
            });
        }

        if (Schema::hasTable('deals')) {
            Schema::table('deals', function (Blueprint $table) {
                if (!$this->hasIndex('deals', 'deals_office_status_idx')) {
                    $table->index(['office_id', 'status'], 'deals_office_status_idx');
                }
                if (!$this->hasIndex('deals', 'deals_assigned_status_idx')) {
                    $table->index(['assigned_to', 'status'], 'deals_assigned_status_idx');
                }
            });
        }

        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                if (!$this->hasIndex('listings', 'listings_office_status_idx')) {
                    $table->index(['office_id', 'status'], 'listings_office_status_idx');
                }
            });
        }

        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                if (!$this->hasIndex('activities', 'activities_user_type_idx')) {
                    $table->index(['user_id', 'type'], 'activities_user_type_idx');
                }
                if (!$this->hasIndex('activities', 'activities_user_created_idx')) {
                    $table->index(['user_id', 'created_at'], 'activities_user_created_idx');
                }
            });
        }

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!$this->hasIndex('conversations', 'conversations_office_status_unread_idx')) {
                    $table->index(['office_id', 'status', 'unread_count'], 'conversations_office_status_unread_idx');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_ai_score_idx');
            $table->dropIndex('leads_office_status_idx');
            $table->dropIndex('leads_last_activity_idx');
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_office_status_idx');
            $table->dropIndex('deals_assigned_status_idx');
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_office_status_idx');
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_user_type_idx');
            $table->dropIndex('activities_user_created_idx');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_office_status_unread_idx');
        });
    }

    /**
     * Idempotent guard — index zaten varsa atla.
     * Hem MySQL hem SQLite için çalışan generic bir kontrol.
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $result = Schema::getConnection()->select("PRAGMA index_list({$table})");
            foreach ($result as $row) {
                if (($row->name ?? '') === $indexName) return true;
            }
            return false;
        }

        // MySQL/MariaDB
        $result = Schema::getConnection()->select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($result);
    }
};
