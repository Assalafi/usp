<?php

use App\Support\PortalPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pages') || !Schema::hasTable('rolls')) {
            return;
        }

        $now = now();
        $labels = PortalPermission::actionLabels();

        // Register every action already used by the application, plus the
        // standard actions used by the route resolver. Existing assignments
        // are never removed.
        $tokens = collect(PortalPermission::knownActions())
            ->merge(DB::table('pages')->pluck('action'))
            ->merge(DB::table('rolls')->pluck('action'))
            ->flatMap(fn ($value) => array_map([PortalPermission::class, 'normalizeAction'], explode(',', (string) $value)))
            ->filter()
            ->unique()
            ->values();

        if (Schema::hasTable('navigation_actions')) {
            foreach ($tokens as $token) {
                DB::table('navigation_actions')->updateOrInsert(
                    ['name' => $token],
                    [
                        'label' => $labels[$token] ?? ucfirst(str_replace('_', ' ', $token)),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        // The original catalogue treated a non-empty action list as page
        // access. Add the explicit view action without changing any existing
        // action, preserving every current grant while making the model clear.
        foreach (['pages', 'rolls'] as $table) {
            DB::table($table)->select('id', 'action')->orderBy('id')->chunkById(500, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $actions = collect(explode(',', (string) $row->action))
                        ->map(fn ($value) => trim((string) $value))
                        ->filter()
                        ->values();
                    if ($actions->isEmpty() || $actions->map([PortalPermission::class, 'normalizeAction'])->contains('view')) {
                        continue;
                    }
                    $actions->prepend('view');
                    DB::table($table)->where('id', $row->id)->update(['action' => $actions->unique()->implode(',')]);
                }
            });
        }

        // Route-backed catalogue entries that were added after the original
        // Pages screen was created. They are inserted only when missing, so
        // existing page names, group order and role assignments stay intact.
        $catalogue = [
            ['main' => 'Fees Collection', 'page' => 'Admin Receipts', 'link' => '/admin/receipts', 'actions' => 'view,export'],
            ['main' => 'Fees Collection', 'page' => 'Remita Split Report', 'link' => '/admin/remita-split-report', 'actions' => 'view,export'],
            ['main' => 'Fees Collection', 'page' => 'Live Finance Summary', 'link' => '/admin/remita-finance-live', 'actions' => 'view'],
            ['main' => 'Fees Collection', 'page' => 'Admin Payment Desk', 'link' => '/admin/make-payment', 'actions' => 'view,create,update,verify,cancel'],
            ['main' => 'Students', 'page' => 'Student Details', 'link' => '/student details', 'actions' => 'view,update,export'],
            ['main' => 'Students', 'page' => 'Student Results', 'link' => '/admin/student-results', 'actions' => 'view,export'],
            ['main' => 'Students', 'page' => 'SIWES Records', 'link' => '/admin/siwes', 'actions' => 'view,export'],
            ['main' => 'Students', 'page' => 'Reset Passwords', 'link' => '/reset-passwords', 'actions' => 'view,update'],
            ['main' => 'Admission', 'page' => 'Applicant Details', 'link' => '/admin-applicant', 'actions' => 'view,update,export,approve,reject'],
            ['main' => 'Admission', 'page' => 'JAMB Admitted', 'link' => '/jamb-admitted', 'actions' => 'view,create,update,upload'],
            ['main' => 'Admission', 'page' => 'PG Admission', 'link' => '/pg-admission', 'actions' => 'view,update,approve,export'],
            ['main' => 'Human Resource', 'page' => 'Recruitment', 'link' => '/recruitment', 'actions' => 'view,create,update,delete,upload,export'],
            ['main' => 'Human Resource', 'page' => 'Recruitment Management', 'link' => '/recruitment/management', 'actions' => 'view,update'],
            ['main' => 'Human Resource', 'page' => 'Recruitment Access', 'link' => '/recruitment/access', 'actions' => 'view,create,update,delete'],
            ['main' => 'Human Resource', 'page' => 'Staff Record Update', 'link' => '/staff-record-update', 'actions' => 'view,update'],
            ['main' => 'Bursary', 'page' => 'Certificate Records', 'link' => '/certificate', 'actions' => 'view,create,update,delete,upload,export'],
            ['main' => 'Bursary', 'page' => 'Affiliated Schools', 'link' => '/affiliated-schools', 'actions' => 'view,create,update,delete,upload,export'],
            ['main' => 'Hostel', 'page' => 'Hostel Permit', 'link' => '/admin/hostel-permit', 'actions' => 'export'],
            ['main' => 'Alumni', 'page' => 'Alumni Management', 'link' => '/alumni', 'actions' => 'view,create,update,delete,upload,restore,export'],
            ['main' => 'E - Voting', 'page' => 'Election Results', 'link' => '/election results', 'actions' => 'view,export'],
            ['main' => 'E - Voting', 'page' => 'Election Live Dashboard', 'link' => '/election live', 'actions' => 'view'],
            ['main' => 'Students', 'page' => 'Inter-University Transfer', 'link' => '/inter-university-transfer/admin', 'actions' => 'view,update,approve,export'],
            ['main' => 'Students', 'page' => 'Change of Course', 'link' => '/change-of-course/admin', 'actions' => 'view,update,approve,export'],
            ['main' => 'System Settings', 'page' => 'Developer Tasks', 'link' => '/developer-tasks', 'actions' => 'view,create,update,delete,upload,review'],
        ];

        $groupOrders = DB::table('pages')->select('main', DB::raw('MIN(main_order) AS main_order'))->groupBy('main')->pluck('main_order', 'main');
        $nextIdOrder = (int) (DB::table('pages')->max('main_order') ?? 0) + 1;

        foreach ($catalogue as $entry) {
            $exists = DB::table('pages')->whereIn('link', PortalPermission::linkCandidates($entry['link']))->exists();
            if ($exists) {
                continue;
            }

            $mainOrder = (int) ($groupOrders[$entry['main']] ?? $nextIdOrder++);
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
        // Catalogue records are intentionally retained on rollback because
        // administrators may have assigned roles to them after deployment.
    }
};
