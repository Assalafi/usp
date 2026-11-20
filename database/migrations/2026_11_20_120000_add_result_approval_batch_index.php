<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'results_approval_batch_idx';

    public function up(): void
    {
        if (!Schema::hasTable('results')) {
            return;
        }

        $exists = DB::select('SHOW INDEX FROM `results` WHERE Key_name = ?', [self::INDEX]);
        if ($exists) {
            return;
        }

        Schema::table('results', function (Blueprint $table): void {
            $table->index(
                ['approve', 'session', 'semester', 'code', 'lecturer', 'program'],
                self::INDEX
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('results')) {
            return;
        }

        $exists = DB::select('SHOW INDEX FROM `results` WHERE Key_name = ?', [self::INDEX]);
        if ($exists) {
            Schema::table('results', function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }
    }
};
