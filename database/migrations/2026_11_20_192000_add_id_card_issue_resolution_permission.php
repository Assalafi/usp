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
        $page = DB::table('pages')
            ->whereIn('link', PortalPermission::linkCandidates('/student id card'))
            ->orderBy('id')->first();
        if ($page) {
            $actions = collect(explode(',', (string) ($page->action ?? '')))
                ->merge(['resolve_issue'])
                ->map(fn ($action) => PortalPermission::normalizeAction($action))
                ->filter()->unique()->implode(',');
            DB::table('pages')->where('id', $page->id)->update(['action' => $actions, 'updated_at' => now()]);
        }
        if (Schema::hasTable('navigation_actions')) {
            DB::table('navigation_actions')->updateOrInsert(
                ['name' => 'resolve_issue'],
                ['label' => 'Resolve an ID-card issue', 'created_at' => now(), 'updated_at' => now()]
            );
        }
        PortalPermission::flush();
    }

    public function down(): void
    {
        // Keep permission assignments intact when rolling back.
    }
};
