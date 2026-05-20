<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');                        // Lead / Deal / Contact / Listing
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');                               // kullanıcı tarafı isim
            $table->string('original_name');                        // dosya adı
            $table->string('file_path');                            // local disk relative path
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('category', 64)->nullable();             // contract, identity, deed, valuation, other
            $table->text('notes')->nullable();
            $table->boolean('is_confidential')->default(true);      // KVKK için varsayılan kapalı
            $table->softDeletes();
            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id', 'category']);
            $table->index('office_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
