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
        $actions = [
            'view',
            'import',
            'update_picture',
            'delete',
            'move',
            'restore',
            'bulk_restore',
            'template',
        ];

        $mergeActions = static function (?string $value) use ($actions): string {
            $current = collect(explode(',', (string) $value))
                ->map(fn ($action) => PortalPermission::normalizeAction($action))
                ->filter();

            return $current->merge($actions)->unique()->implode(',');
        };

        $links = PortalPermission::linkCandidates('/alumni');

        DB::table('pages')->whereIn('link', $links)->get()->each(function ($page) use ($mergeActions, $now): void {
            DB::table('pages')->where('id', $page->id)->update([
                'action' => $mergeActions($page->action),
                'status' => '1',
                'updated_at' => $now,
            ]);
        });

        // Existing staff who already have Alumni access receive the explicit
        // action grants once. Future assignments inherit them from `pages`.
        DB::table('rolls')->whereIn('link', $links)->get()->each(function ($roll) use ($mergeActions, $now): void {
            DB::table('rolls')->where('id', $roll->id)->update([
                'action' => $mergeActions($roll->action),
                'updated_at' => $now,
            ]);
        });

        if (Schema::hasTable('navigation_actions')) {
            $labels = [
                'import' => 'Import alumni records',
                'update_picture' => 'Update alumni picture',
                'move' => 'Move students to alumni',
                'bulk_restore' => 'Bulk restore alumni',
                'template' => 'Download alumni template',
            ];

            foreach ($labels as $name => $label) {
                DB::table('navigation_actions')->updateOrInsert(
                    ['name' => $name],
                    [
                        'label' => $label,
                        'created_by' => 'system',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        PortalPermission::flush();
    }

    public function down(): void
    {
        // Keep permission grants intact on rollback; removing action tokens
        // could unexpectedly revoke an administrator's assignment.
    }
};
