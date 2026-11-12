<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureStudentProfileSubmitted
{
    /**
     * The student-record update deadline has expired. Students who have not
     * submitted the current session's update are restricted to the notice page
     * and logout. This middleware is intentionally central so every existing
     * and future web route receives the same protection.
     */
    public function handle(Request $request, Closure $next)
    {
        if (session('accType') !== 'Student' || !session()->has('log')) {
            return $next($request);
        }

        if ($this->isAllowedWhileRestricted($request)) {
            return $next($request);
        }

        $student = Student::where('user_id', session('id'))->first([
            'profile_status',
            'profile_submitted_at',
            'profile_submission_session',
            'profile_level_session',
            'nin',
            'nin_update_version',
        ]);
        $currentSession = DB::table('session')->where('status', '1')->value('title');
        $isSubmittedForCurrentSession = $student
            && $student->profile_status === 'submitted'
            && !empty($student->profile_submitted_at)
            && (string) $student->profile_submission_session === (string) $currentSession
            && (string) $student->profile_level_session === (string) $currentSession;
        if ($isSubmittedForCurrentSession) {
            $ninEnabledValue = \App\Http\Controllers\SystemSettingsController::get('student_nin_verification_enabled', '1');
            $ninEnabled = $ninEnabledValue === null || (string) $ninEnabledValue !== '0';
            if (!$ninEnabled) {
                return $next($request);
            }

            $ninCycle = max(1, (int) \App\Http\Controllers\SystemSettingsController::get('student_nin_update_version', 1));
            $ninIsConfirmed = preg_match('/^\d{11}$/', trim((string) $student->nin))
                && (int) ($student->nin_update_version ?? 0) >= $ninCycle;

            if (!$ninIsConfirmed) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Please confirm your National Identification Number before using the student portal.',
                        'redirect' => url('/nin-update'),
                    ], 423);
                }

                return redirect('/nin-update');
            }

            return $next($request);
        }

        // Administrators can pause the profile-update requirement from
        // General Settings without changing students' stored records.
        $profileMode = (int) \App\Http\Controllers\SystemSettingsController::get('student_profile_update_enabled', '1');
        $profileMode = in_array($profileMode, [0, 1, 2], true) ? $profileMode : 1;
        if ($profileMode === 0) {
            return $next($request);
        }

        if ($profileMode === 1) {
            $redirect = url('/profile?mode=edit&required=1');
            $message = 'Please complete and submit your current student profile update before using portal services.';
        } else {
            $redirect = url('/profile-update-expired');
            $message = 'Your student portal access is restricted because the student record update deadline has expired.';
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message, 'redirect' => $redirect], 423);
        }

        return redirect($redirect);
    }

    private function isAllowedWhileRestricted(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if ($path === 'profile' && $request->query('mode') === 'edit' && $request->query('required') === '1') {
            return true;
        }

        return $path === 'profile-update-expired'
            || $path === 'nin-update'
            || $path === 'update-profile'
            || $path === 'submit-profile'
            || $path === 'profile/photo/preview'
            || $path === 'logout';
    }
}
