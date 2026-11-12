<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('navigation_groups') || !Schema::hasTable('pages')) {
            return;
        }

        $groups = DB::table('pages')->select('main', DB::raw('MIN(main_order) as sort_order'))->groupBy('main')->orderBy('sort_order')->get();
        DB::table('navigation_groups')->whereNotIn('name', $groups->pluck('main')->all())->delete();
        foreach ($groups as $index => $group) {
            if (DB::table('navigation_groups')->where('name', $group->main)->exists()) {
                DB::table('navigation_groups')->where('name', $group->main)->update(['sort_order' => $index + 1, 'updated_at' => now()]);
            } else {
                DB::table('navigation_groups')->insert(['name' => $group->main, 'sort_order' => $index + 1, 'source' => 'pages table', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Registry cleanup is intentionally not reversed.
    }
};
