<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('developer_tasks') || !Schema::hasColumn('developer_tasks', 'task_order')) {
            return;
        }

        $offset = 1000000;
        DB::statement('UPDATE developer_tasks SET task_order = task_order + ?', [$offset]);
        $ids = DB::table('developer_tasks')->orderBy('task_order')->pluck('id');
        foreach ($ids as $index => $id) {
            DB::table('developer_tasks')->where('id', $id)->update(['task_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        // Order values are intentionally not reverted; they are a live queue.
    }
};
