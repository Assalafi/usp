<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('developer_tasks')) {
            Schema::create('developer_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title', 180);
                $table->longText('description');
                $table->string('priority', 20)->default('medium');
                $table->string('status', 30)->default('assigned');
                $table->string('assigned_to', 120)->nullable();
                $table->string('created_by', 120)->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->string('confirmed_by', 120)->nullable();
                $table->longText('confirmation_note')->nullable();
                $table->timestamps();

                $table->index(['status', 'priority'], 'developer_tasks_status_priority');
                $table->index('assigned_to', 'developer_tasks_assigned_to');
                $table->index('created_by', 'developer_tasks_created_by');
            });
        }

        if (!Schema::hasTable('developer_task_updates')) {
            Schema::create('developer_task_updates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('developer_tasks')->cascadeOnDelete();
                $table->string('user_username', 120);
                $table->string('user_name', 180)->nullable();
                $table->string('type', 30)->default('progress');
                $table->string('status_to', 30)->nullable();
                $table->longText('body');
                $table->timestamps();

                $table->index(['task_id', 'created_at'], 'developer_task_updates_task_created');
            });
        }

        if (!Schema::hasTable('developer_task_files')) {
            Schema::create('developer_task_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('developer_tasks')->cascadeOnDelete();
                $table->foreignId('update_id')->nullable()->constrained('developer_task_updates')->nullOnDelete();
                $table->string('uploaded_by', 120);
                $table->string('original_name', 255);
                $table->string('path', 500);
                $table->string('mime_type', 150)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at'], 'developer_task_files_task_created');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_task_files');
        Schema::dropIfExists('developer_task_updates');
        Schema::dropIfExists('developer_tasks');
    }
};
