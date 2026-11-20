<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_id_card_generations')) {
            return;
        }

        Schema::create('student_id_card_generations', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('username', 80)->nullable();
            $table->string('session', 30);
            $table->string('source', 30)->default('id_card');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session'], 'student_id_card_generation_unique');
            $table->index(['session', 'source'], 'student_id_card_generation_session_source_idx');
            $table->index(['username', 'session'], 'student_id_card_generation_username_session_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_id_card_generations');
    }
};
