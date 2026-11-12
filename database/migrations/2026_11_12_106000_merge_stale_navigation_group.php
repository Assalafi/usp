<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        DB::transaction(function () {
            DB::table('pages')->where('main', 'Human Resourse')->update(['main' => 'Human Resource']);
            if (Schema::hasTable('rolls')) {
                DB::table('rolls')->where('main', 'Human Resourse')->update(['main' => 'Human Resource']);
            }

            if (Schema::hasTable('navigation_groups')) {
                $stale = DB::table('navigation_groups')->where('name', 'Human Resourse')->first();
                $current = DB::table('navigation_groups')->where('name', 'Human Resource')->first();
                if ($stale && $current) {
                    DB::table('navigation_groups')->where('id', $stale->id)->delete();
                } elseif ($stale) {
                    DB::table('navigation_groups')->where('id', $stale->id)->update([
                        'name' => 'Human Resource',
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // The correction is intentionally retained; reverting it would
        // recreate a duplicate navigation group.
    }
};
