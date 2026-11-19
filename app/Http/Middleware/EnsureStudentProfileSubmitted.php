<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Support\ReturningStudentFeeBalance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureStudentProfileSubmitted
{
    /**
     * The student-record update deadline has expired. Students who have not
     * submitted profile are restricted to the notice page
     * and logout. This middleware is intentionally central so every existing
     * and future web route receives the same protection.
     */
    public function handle(Request $request, Closure $next)
    {
        $isStudentAccount = strcasecmp(trim((string) session('accType')), 'student') === 0;
        // A few legacy login records have an empty/non-standard account type,
        // but still carry a valid student session.  Resolve that identity from
        // the student table so the fee lock cannot be bypassed by a stale role
        // value in the session.
        if (!$isStudentAccount && session()->has('id') && session()->has('id_number')) {
            $isStudentAccount = Student::where('user_id', (string) session('id'))
                ->where('username', (string) session('id_number'))
                ->exists();
        }
        if (!$isStudentAccount || !session()->has('log')) {
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
            'session_of_entry',
            'nin',
            'nin_update_version',
        ]);
        // A submitted profile remains valid when the academic session changes.
        // The profile update is a completion check, not a yearly re-submission
        // requirement. NIN verification has its own independent cycle below.
        $isSubmitted = $student
            && $student->profile_status === 'submitted'
            && !empty($student->profile_submitted_at);
        if ($isSubmitted) {
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

            $entrySession = trim((string) ($student->session_of_entry ?? ''));
            // Always compare against the canonical setting.  The value kept in
            // the login session can be stale when the administrator opens a
            // new academic session while students remain signed in.
            $activeSession = trim((string) \App\Http\Controllers\SystemSettingsController::getSystemSession());
            $feeBalance = ($entrySession !== '' && $entrySession !== $activeSession)
                ? ReturningStudentFeeBalance::forUser(session('id'))
                : null;
            if ($feeBalance && $feeBalance['is_returning'] && $feeBalance['balance'] > 0
                && !$this->isAllowedWhilePaymentRestricted($request)) {
                $message = 'Please pay your full outstanding programme-fee balance for '
                    . $feeBalance['session'] . ' before using the portal for the new academic session.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => $message,
                        'redirect' => url('/student-fee-clearance'),
                    ], 423);
                }

                return redirect('/student-fee-clearance')->with('error', $message);
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
            $message = 'Please complete and submit your student profile before using portal services.';
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

    /**
     * Returning students may still view payment history, create/complete or
     * verify an invoice, download a receipt, and sign out while fee recovery
     * is active. Everything else is redirected to the payment page.
     */
    private function isAllowedWhilePaymentRestricted(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return $path === 'student-fee-clearance'
            || $path === 'verify'
            || $request->is('invoices/*')
            || $request->is('verify/*')
            || $request->is('print-receipt/*')
            || $request->is('print invoice/*')
            || $request->is('cancel-student-payment/*')
            || $path === 'logout';
    }
}
