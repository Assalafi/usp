<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        $exists = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$name]);
        if (! $exists) {
            Schema::table($table, function ($tableBlueprint) use ($columns, $name): void {
                $tableBlueprint->index($columns, $name);
            });
        }
    }

    public function up(): void
    {
        if (! Schema::hasTable('results')) {
            return;
        }

        // Approved-result exports always start with the approval stage. The
        // remaining columns make session/semester/level filtered exports use
        // the same index instead of scanning the entire legacy table.
        $this->addIndexIfMissing(
            'results',
            ['approve', 'session', 'semester', 'level', 'department', 'program'],
            'results_export_approval_filters_idx'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('results')) {
            return;
        }

        $exists = DB::select('SHOW INDEX FROM `results` WHERE Key_name = ?', ['results_export_approval_filters_idx']);
        if ($exists) {
            Schema::table('results', function ($tableBlueprint): void {
                $tableBlueprint->dropIndex('results_export_approval_filters_idx');
            });
        }
    }
};
