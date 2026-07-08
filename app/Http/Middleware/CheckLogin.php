<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLogin
{
    /**
     * Routes that should be accessible without login.
     */
    protected $except = [
        // Login & Auth
        '/',
        'auth',
        'Apply Hostel',
        'forgot password',
        'forgot',
        'update pass',
        'update password',
        'account validation',
        'account-validation',
        'reset-pass',
        'validate H-Pin',
        'V-H-Pin',
        'staff-password',

        // Public views
        'pdff',
        'pdf-view',
        'lecture-timetable/*',
        'exam-timetable/*',
        'ca-timetable/*',
        'response',

        // Applicant portal (pre-login)
        'application',
        'applicant-dashboard',
        'applicant-fees',
        'invoices-applicant-fees',
        'submit-application',
        'application/*',
        'download-admission-letter',

        // Payment verification callbacks
        'verify',
        'verify/*',
        'change-of-course/verify',
        'inter-university-transfer/verify',
        'invoices/*',

        // Public registration
        'inter-university-transfer/register',
        'recruitment',
        'recruitment/data',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (!session()->has('log') || empty(session('username'))) {
            return redirect('/')->with('error', 'Please login to access this page.');
        }

        return $next($request);
    }

    protected function shouldPassThrough(Request $request)
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
