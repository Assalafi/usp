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

        $student = Student::where('user_id', session('id'))->first(['profile_status', 'profile_submitted_at', 'profile_submission_session', 'profile_level_session']);
        $currentSession = DB::table('session')->where('status', '1')->value('title');
        $isSubmittedForCurrentSession = $student
            && $student->profile_status === 'submitted'
            && !empty($student->profile_submitted_at)
            && (string) $student->profile_submission_session === (string) $currentSession
            && (string) $student->profile_level_session === (string) $currentSession;
        if ($isSubmittedForCurrentSession) {
            return $next($request);
        }

        $message = 'Your student portal access is restricted because the student record update deadline has expired.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message, 'redirect' => url('/profile-update-expired')], 423);
        }

        return redirect('/profile-update-expired');
    }

    private function isAllowedWhileRestricted(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return $path === 'profile-update-expired'
            || $path === 'logout';
    }
}
