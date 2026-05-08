<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('url')->unique();
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('category')->default('genel');
            $table->string('sentiment')->default('neutral');
            $table->json('tags')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('published_at');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
