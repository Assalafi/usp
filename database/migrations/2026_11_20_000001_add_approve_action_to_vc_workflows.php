<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $links = [
        '/change-of-course/admin',
        '/inter-university-transfer/admin',
    ];

    public function up(): void
    {
        // These two workflows already belong to the VC role.  Older rows
        // were created before `approve` became an explicit action, so they
        // contained CRUD actions only and blocked the VC action endpoints.
        DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])
            ->whereIn('link', $this->links)
            ->orderBy('id')
            ->get(['id', 'action'])
            ->each(function ($row): void {
                $actions = collect(explode(',', (string) $row->action))
                    ->map(fn ($action) => strtolower(trim((string) $action)))
                    ->filter()
                    ->unique()
                    ->values();

                if (!$actions->contains('approve')) {
                    $actions->push('approve');
                    DB::table('rolls')->where('id', $row->id)->update([
                        'action' => $actions->implode(','),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])
            ->whereIn('link', $this->links)
            ->get(['id', 'action'])
            ->each(function ($row): void {
                $actions = collect(explode(',', (string) $row->action))
                    ->map(fn ($action) => strtolower(trim((string) $action)))
                    ->filter(fn ($action) => $action !== 'approve')
                    ->unique()
                    ->values();
                DB::table('rolls')->where('id', $row->id)->update([
                    'action' => $actions->implode(','),
                    'updated_at' => now(),
                ]);
            });
    }
};
