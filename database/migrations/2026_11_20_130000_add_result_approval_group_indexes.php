<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const GROUP_INDEX = 'results_approval_group_idx';
    private const DEPARTMENT_INDEX = 'results_approval_department_idx';

    public function up(): void
    {
        if (!Schema::hasTable('results')) return;
        $names = collect(DB::select('SHOW INDEX FROM `results`'))->pluck('Key_name')->unique();
        Schema::table('results', function (Blueprint $table) use ($names): void {
            if (!$names->contains(self::GROUP_INDEX)) {
                $table->index(['approve', 'code', 'session', 'semester', 'lecturer', 'program'], self::GROUP_INDEX);
            }
            if (!$names->contains(self::DEPARTMENT_INDEX)) {
                $table->index(['approve', 'program', 'code', 'session', 'semester'], self::DEPARTMENT_INDEX);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('results')) return;
        $names = collect(DB::select('SHOW INDEX FROM `results`'))->pluck('Key_name')->unique();
        Schema::table('results', function (Blueprint $table) use ($names): void {
            if ($names->contains(self::GROUP_INDEX)) $table->dropIndex(self::GROUP_INDEX);
            if ($names->contains(self::DEPARTMENT_INDEX)) $table->dropIndex(self::DEPARTMENT_INDEX);
        });
    }
};
