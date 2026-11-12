<?php

use App\Support\PortalPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $entries = [
            ['main' => 'Students', 'page' => 'Change of Course', 'link' => '/change-of-course/admin', 'actions' => 'view,update,approve,export'],
            ['main' => 'System Settings', 'page' => 'Users', 'link' => '/users', 'actions' => 'view,create,update,delete,upload'],
            ['main' => 'System Settings', 'page' => 'User Accounts', 'link' => '/users2', 'actions' => 'view,create,update,delete,upload'],
            ['main' => 'System Settings', 'page' => 'General Settings', 'link' => '/settings/general', 'actions' => 'view,update,export'],
            ['main' => 'Human Resource', 'page' => 'Reference Data', 'link' => '/reference-data', 'actions' => 'view,create,update,delete,upload,export'],
        ];

        $groupOrders = DB::table('pages')->select('main', DB::raw('MIN(main_order) AS main_order'))->groupBy('main')->pluck('main_order', 'main');
        $nextOrder = (int) (DB::table('pages')->max('main_order') ?? 0) + 1;

        foreach ($entries as $entry) {
            if (DB::table('pages')->whereIn('link', PortalPermission::linkCandidates($entry['link']))->exists()) {
                continue;
            }

            $mainOrder = (int) ($groupOrders[$entry['main']] ?? $nextOrder++);
            DB::table('pages')->insert([
                'page' => $entry['page'],
                'link' => PortalPermission::normalizeLink($entry['link']),
                'type' => 'Admin',
                'main_order' => $mainOrder,
                'sub_order' => (int) DB::table('pages')->where('main', $entry['main'])->max('sub_order') + 1,
                'main' => $entry['main'],
                'action' => $entry['actions'],
                'status' => '1',
                'user' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Keep catalogue records on rollback to avoid invalidating assignments.
    }
};
