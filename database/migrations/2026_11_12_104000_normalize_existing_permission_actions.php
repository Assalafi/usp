<?php

use App\Support\PortalPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pages', 'rolls'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'action')) {
                continue;
            }

            DB::table($table)
                ->select('id', 'action')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $normalized = PortalPermission::normalizeActionList($row->action);
                        if ($normalized !== (string) $row->action) {
                            DB::table($table)->where('id', $row->id)->update(['action' => $normalized]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Action normalization is intentionally not reversed. The catalogue
        // accepts the canonical format and existing access is preserved.
    }
};
