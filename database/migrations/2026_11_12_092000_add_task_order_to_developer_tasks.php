<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('developer_tasks')) {
            return;
        }

        if (!Schema::hasColumn('developer_tasks', 'task_order')) {
            Schema::table('developer_tasks', function (Blueprint $table) {
                $table->unsignedInteger('task_order')->nullable()->after('status');
            });
        }

        // Existing tasks keep their current creation order. New tasks can then
        // be inserted anywhere without making the order field nullable.
        DB::statement('UPDATE developer_tasks SET task_order = id WHERE task_order IS NULL');
        DB::statement('ALTER TABLE developer_tasks MODIFY task_order INT UNSIGNED NOT NULL');

        $indexes = DB::select("SHOW INDEX FROM developer_tasks WHERE Key_name = 'developer_tasks_task_order_unique'");
        if (!$indexes) {
            Schema::table('developer_tasks', function (Blueprint $table) {
                $table->unique('task_order', 'developer_tasks_task_order_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('developer_tasks')) {
            return;
        }

        try {
            Schema::table('developer_tasks', function (Blueprint $table) {
                $table->dropUnique('developer_tasks_task_order_unique');
            });
        } catch (\Throwable $e) {
            // Keep rollback safe when the index was already removed manually.
        }

        if (Schema::hasColumn('developer_tasks', 'task_order')) {
            Schema::table('developer_tasks', function (Blueprint $table) {
                $table->dropColumn('task_order');
            });
        }
    }
};
