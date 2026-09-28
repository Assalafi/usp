<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_documents')) {
            return;
        }

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->string('user_id', 255);
            $table->string('doc_type', 100);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'doc_type'], 'student_documents_user_type_unique');
            $table->index(['student_id', 'doc_type'], 'student_documents_student_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
