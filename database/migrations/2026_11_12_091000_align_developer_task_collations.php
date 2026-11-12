<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('developer_tasks')) {
            DB::statement("ALTER TABLE developer_tasks MODIFY assigned_to VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
            DB::statement("ALTER TABLE developer_tasks MODIFY created_by VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
        }
        if (Schema::hasTable('developer_task_updates')) {
            DB::statement("ALTER TABLE developer_task_updates MODIFY user_username VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL");
            DB::statement("ALTER TABLE developer_task_updates MODIFY user_name VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
        }
        if (Schema::hasTable('developer_task_files')) {
            DB::statement("ALTER TABLE developer_task_files MODIFY uploaded_by VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('developer_tasks')) {
            DB::statement("ALTER TABLE developer_tasks MODIFY assigned_to VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
            DB::statement("ALTER TABLE developer_tasks MODIFY created_by VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        }
        if (Schema::hasTable('developer_task_updates')) {
            DB::statement("ALTER TABLE developer_task_updates MODIFY user_username VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
            DB::statement("ALTER TABLE developer_task_updates MODIFY user_name VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        }
        if (Schema::hasTable('developer_task_files')) {
            DB::statement("ALTER TABLE developer_task_files MODIFY uploaded_by VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        }
    }
};
