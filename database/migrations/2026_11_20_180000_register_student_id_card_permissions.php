<?php

use App\Support\PortalPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        $now = now();
        $link = '/student id card';
        $actions = ['view', 'idcard', 'update'];
        $existing = DB::table('pages')
            ->whereIn('link', PortalPermission::linkCandidates($link))
            ->orderBy('id')
            ->first();

        if ($existing) {
            $current = collect(explode(',', (string) ($existing->action ?? '')))
                ->map(fn ($action) => PortalPermission::normalizeAction($action))
                ->filter();
            $merged = $current->merge($actions)->unique()->implode(',');
            DB::table('pages')->where('id', $existing->id)->update([
                'action' => $merged,
                'status' => '1',
                'updated_at' => $now,
            ]);
        } else {
            $main = 'Students';
            $mainOrder = (int) (DB::table('pages')->where('main', $main)->min('main_order')
                ?: ((int) DB::table('pages')->max('main_order') + 1));
            $subOrder = (int) DB::table('pages')->where('main', $main)->max('sub_order') + 1;

            DB::table('pages')->insert([
                'main' => $main,
                'main_order' => $mainOrder,
                'page' => 'Student ID Card',
                'link' => $link,
                'sub_order' => $subOrder,
                'type' => 'Admin',
                'action' => implode(',', $actions),
                'status' => '1',
                'user' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('navigation_actions')) {
            DB::table('navigation_actions')->updateOrInsert(
                ['name' => 'idcard'],
                [
                    'label' => 'ID card issue and download',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        PortalPermission::flush();
    }

    public function down(): void
    {
        // Keep the catalogue row and any staff assignments on rollback.
    }
};
