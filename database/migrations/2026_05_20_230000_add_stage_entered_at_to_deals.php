<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->timestamp('stage_entered_at')->nullable()->after('stage_id');
            $table->index(['status', 'stage_entered_at'], 'deals_status_stage_entered_idx');
        });

        // Backfill existing open deals: use created_at as the best guess for when they entered their current stage
        DB::statement('UPDATE deals SET stage_entered_at = created_at WHERE stage_entered_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_status_stage_entered_idx');
            $table->dropColumn('stage_entered_at');
        });
    }
};
