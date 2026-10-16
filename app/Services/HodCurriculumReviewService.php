<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Keeps the Head of Department curriculum-review trail independent from the
 * course rows themselves. A missing row deliberately means "Pending review",
 * which lets existing HODs enter the new workflow without a data migration.
 */
class HodCurriculumReviewService
{
    public static function forHod(string $username, int $structureId): ?object
    {
        return DB::table('hod_curriculum_reviews')
            ->where('hod_username', $username)
            ->where('structure_id', $structureId)
            ->first();
    }

    public static function submit(string $username, string $department, int $structureId): void
    {
        $now = now();

        DB::table('hod_curriculum_reviews')->updateOrInsert(
            ['hod_username' => $username, 'structure_id' => $structureId],
            [
                'department' => $department,
                'status' => 'submitted',
                'submitted_at' => $now,
                'submitted_by' => $username,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * A mapping edit reopens the review for every HOD responsible for the
     * affected department. Existing submissions remain in the audit trail,
     * but their status is reset so a fresh confirmation is required.
     */
    public static function invalidateMapping(int $structureId, string $department, string $action, ?string $actor = null): void
    {
        $department = strtoupper(trim($department));
        if ($department === '') {
            return;
        }

        $hods = DB::table('staff')
            ->whereRaw('UPPER(appointment) = ?', ['HOD'])
            ->where('status', '1')
            ->whereRaw('UPPER(department) = ?', [$department])
            ->select('username', 'department')
            ->get();

        self::markPending($hods, $structureId, $action, $actor, $department);
    }

    /**
     * A structure-level change can affect multiple departments. Resolve the
     * departments currently using the structure, then reopen each HOD review.
     */
    public static function invalidateStructure(int $structureId, string $action, ?string $actor = null): void
    {
        $departments = DB::table('program_course_registration as pcr')
            ->join('program as p', 'p.code', '=', 'pcr.program')
            ->where('pcr.structure_id', $structureId)
            ->whereNotNull('p.department')
            ->distinct()
            ->pluck('p.department')
            ->map(fn ($department) => strtoupper(trim((string) $department)))
            ->filter()
            ->values();

        if ($departments->isEmpty()) {
            // If a structure has not received mappings yet, still prepare
            // active HODs for it; the admin tracker will show them as pending.
            $hods = DB::table('staff')
                ->whereRaw('UPPER(appointment) = ?', ['HOD'])
                ->where('status', '1')
                ->select('username', 'department')
                ->get();
            self::markPending($hods, $structureId, $action, $actor);
            return;
        }

        $hods = DB::table('staff')
            ->whereRaw('UPPER(appointment) = ?', ['HOD'])
            ->where('status', '1')
            ->whereIn(DB::raw('UPPER(department)'), $departments->all())
            ->select('username', 'department')
            ->get();

        self::markPending($hods, $structureId, $action, $actor);
    }

    private static function markPending($hods, int $structureId, string $action, ?string $actor = null, ?string $department = null): void
    {
        $now = now();
        foreach ($hods as $hod) {
            $hodDepartment = strtoupper(trim((string) ($department ?: $hod->department)));
            if ($hodDepartment === '') {
                continue;
            }

            DB::table('hod_curriculum_reviews')->updateOrInsert(
                ['hod_username' => (string) $hod->username, 'structure_id' => $structureId],
                [
                    'department' => $hodDepartment,
                    'status' => 'pending',
                    'submitted_at' => null,
                    'submitted_by' => null,
                    'last_changed_at' => $now,
                    'last_changed_by' => $actor,
                    'last_change_action' => $action,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
