<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sourceRows = DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['sp5179'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', '0');
            })
            ->orderBy('id')
            ->get();

        foreach ($sourceRows as $source) {
            $existing = DB::table('rolls')
                ->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])
                ->where('link', $source->link)
                ->where('faculty', $source->faculty)
                ->where('department', $source->department)
                ->where('program', $source->program)
                ->where('level', $source->level)
                ->first();

            $sourceActions = collect(explode(',', (string) $source->action))
                ->map(fn ($action) => strtolower(trim((string) $action)))
                ->filter()
                ->unique();

            if ($existing) {
                $existingActions = collect(explode(',', (string) $existing->action))
                    ->map(fn ($action) => strtolower(trim((string) $action)))
                    ->filter()
                    ->unique();

                DB::table('rolls')->where('id', $existing->id)->update([
                    'action' => $existingActions->merge($sourceActions)->unique()->values()->implode(','),
                    'status' => $existing->status ?: ($source->status ?: '1'),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $payload = (array) $source;
            unset($payload['id']);
            $payload['username'] = 'VC';
            $payload['user'] = 'system';
            $payload['created_at'] = now();
            $payload['updated_at'] = now();
            DB::table('rolls')->insert($payload);
        }
    }

    public function down(): void
    {
        // Remove only copied rows, leaving the three pre-existing VC grants
        // and any later VC assignments untouched.
        $sourceLinks = DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['sp5179'])
            ->pluck('link');

        DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])
            ->whereIn('link', $sourceLinks)
            ->where('user', 'system')
            ->delete();
    }
};
