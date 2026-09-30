<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_search_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32);
            $table->string('record_key', 191);
            $table->string('photo_path', 500)->nullable();
            $table->char('photo_hash', 64)->nullable();
            $table->longText('embedding')->nullable();
            $table->longText('metadata')->nullable();
            $table->decimal('quality', 6, 4)->nullable();
            $table->string('status', 20)->default('ready');
            $table->text('error_message')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'record_key']);
            $table->index(['source', 'status']);
            $table->index('photo_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_search_embeddings');
    }
};
