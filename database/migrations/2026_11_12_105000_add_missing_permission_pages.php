<?php

use App\Support\PortalPermission;
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

        $now = now();
        $entries = [
            ['main' => 'Academic Records', 'page' => 'Course Structure', 'link' => '/course structure', 'actions' => 'view,create,update,delete,upload'],
            ['main' => 'Academic Records', 'page' => 'Attendance', 'link' => '/attendance', 'actions' => 'view,create,update,delete,export'],
            ['main' => 'Academic Records', 'page' => 'Pending Results', 'link' => '/pending-results', 'actions' => 'view,update,export'],
        ];

        foreach ($entries as $entry) {
            if (DB::table('pages')->whereIn('link', PortalPermission::linkCandidates($entry['link']))->exists()) {
                continue;
            }

            $mainOrder = (int) (DB::table('pages')->where('main', $entry['main'])->min('main_order')
                ?? (DB::table('pages')->max('main_order') + 1));
            DB::table('pages')->insert([
                'main' => $entry['main'],
                'main_order' => $mainOrder,
                'page' => $entry['page'],
                'link' => PortalPermission::normalizeLink($entry['link']),
                'sub_order' => (int) DB::table('pages')->where('main', $entry['main'])->max('sub_order') + 1,
                'action' => $entry['actions'],
                'type' => 'Admin',
                'status' => '1',
                'user' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Keep catalogue entries on rollback so assigned access is not lost.
    }
};
