<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]);
        if (!$exists) {
            Schema::table($table, function (Blueprint $blueprint) use ($name, $columns): void {
                $blueprint->index($columns, $name);
            });
        }
    }

    public function up(): void
    {
        $this->addIndexIfMissing('invoices', 'invoices_id_card_queue_idx', ['description', 'status', 'session', 'username']);
        if (Schema::hasTable('students')) {
            $exists = DB::select("SHOW INDEX FROM `students` WHERE Key_name = ?", ['students_id_card_coverage_idx']);
            if (!$exists) {
                // These legacy text columns require bounded prefixes in a
                // composite index; the values used by the queue are short.
                DB::statement('ALTER TABLE `students` ADD INDEX `students_id_card_coverage_idx` (`session_of_entry`(30), `school_fee`, `status`(20), `user_id`)');
            }
        }
    }

    public function down(): void
    {
        foreach ([
            ['invoices', 'invoices_id_card_queue_idx'],
            ['students', 'students_id_card_coverage_idx'],
        ] as [$table, $name]) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]);
            if ($exists) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            }
        }
    }
};
