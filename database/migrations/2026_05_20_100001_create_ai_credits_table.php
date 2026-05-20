<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->unsignedInteger('monthly_quota')->default(500);
            $table->unsignedInteger('used_this_month')->default(0);
            $table->unsignedInteger('extra_credits')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();

            $table->unique('office_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_credits');
    }
};
