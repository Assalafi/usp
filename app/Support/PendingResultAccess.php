<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Omission comments belong to the assigned main lecturer's draft course only. */
final class PendingResultAccess
{
    public static function canView(string $code, string $session): bool
    {
        if (session('accType') === 'Admin') return true;
        if (session('accType') !== 'Staff') return false;
        $assigned = DB::table('course_allocation')
            ->whereRaw('UPPER(TRIM(username)) = ?', [strtoupper(trim((string) session('username')))])
            ->whereRaw('UPPER(TRIM(course)) = ?', [strtoupper(trim($code))])
            ->where(function ($query) use ($session) {
                $query->whereNull('session')->orWhere('session', '')->orWhere('session', $session);
            })->exists();
        if ($assigned) return true;
        if (!PortalPermission::hasPageAccess('/approve results')) return false;
        $course = DB::table('course')->where('code', $code)->first();
        $appointment = strtoupper(trim((string) session('appointment')));
        if ($appointment === 'HOD') return $course && strtoupper(trim((string) $course->department)) === strtoupper(trim((string) session('department')));
        if ($appointment === 'DEAN') return $course && strtoupper(trim((string) $course->faculty)) === strtoupper(trim((string) session('faculty')));
        return true; // Retain read access explicitly granted on Approve Results.
    }

    public static function canComment(string $code, string $session, string $semester): bool
    {
        if (session('accType') === 'Admin') {
            $batch = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester]);
            return (clone $batch)->where('approve', 'system')->exists()
                && !(clone $batch)->where(function ($query) { $query->whereNull('approve')->orWhere('approve', '!=', 'system'); })->exists();
        }
        if (session('accType') !== 'Staff' || !$code || !$session || !$semester) return false;
        $actions = PortalPermission::actionsForLink('/approve results');
        if ($actions && !array_intersect($actions, ['*', 'update', 'approve', 'create'])) return false;
        $username = strtoupper(trim((string) session('username')));
        $allocated = DB::table('course_allocation')
            ->whereRaw('UPPER(TRIM(username)) = ?', [$username])
            ->whereRaw('UPPER(TRIM(course)) = ?', [strtoupper(trim($code))])
            ->whereRaw('UPPER(TRIM(type)) = ?', ['MAIN'])
            ->where(function ($query) use ($session) {
                $query->whereNull('session')->orWhere('session', '')->orWhere('session', $session);
            })->exists();
        if (!$allocated) return false;
        $batch = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester]);
        if (!(clone $batch)->exists()) return false;
        // Both conditions already govern lecturer submission. Check every row,
        // not merely the first result, before changing an omission comment.
        return !(clone $batch)->where(function ($query) use ($username) {
            $query->whereNull('approve')->orWhere('approve', '!=', 'system')
                ->orWhereNull('lecturer')->orWhereRaw('UPPER(TRIM(lecturer)) != ?', [$username]);
        })->exists();
    }
}
