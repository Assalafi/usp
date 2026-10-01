<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureStudentProfileSubmitted
{
    /**
     * Student routes are protected until the current profile is submitted.
     * The editor, photo preview, submission endpoint and logout remain usable
     * so a student can complete the required information without being locked
     * out of the portal.
     */
    public function handle(Request $request, Closure $next)
    {
        if (session('accType') !== 'Student' || !session()->has('log')) {
            return $next($request);
        }

        if ($this->isAllowedWhileIncomplete($request)) {
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

        $message = 'Complete and submit your student profile before using other portal services.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message, 'redirect' => url('/profile?mode=edit&required=1')], 423);
        }

        return redirect('/profile?mode=edit&required=1')->with('warning', $message);
    }

    private function isAllowedWhileIncomplete(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return $path === 'profile'
            || $path === 'update-profile'
            || $path === 'submit-profile'
            || $path === 'profile/photo/preview'
            || $path === 'logout';
    }
}
