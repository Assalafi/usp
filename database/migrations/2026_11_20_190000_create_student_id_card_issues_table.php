<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_id_card_issues')) {
            return;
        }

        Schema::create('student_id_card_issues', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('username', 80)->nullable();
            $table->string('session', 30)->nullable();
            $table->text('description');
            $table->string('status', 20)->default('open');
            $table->unsignedBigInteger('reported_by')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status'], 'student_id_card_issue_student_status_idx');
            $table->index(['session', 'status'], 'student_id_card_issue_session_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_id_card_issues');
    }
};
