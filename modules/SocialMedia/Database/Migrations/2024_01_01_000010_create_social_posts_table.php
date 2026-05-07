<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, facebook, both
            $table->string('content_type')->default('post'); // post, story, reel
            $table->text('caption')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable(); // image, video
            $table->string('status')->default('draft'); // draft, planlandi, yayinlandi, hata
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('publish_payload')->nullable();
            $table->json('meta_response')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
