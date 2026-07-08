<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleAccess
{
    /**
     * Pages that should be accessible without role assignment (public admin pages).
     */
    protected $except = [
        '',
        'dashboard',
        'staff-profile',
        'profile',
        'dash',
        'auth',
        'forgot password',
        'forgot',
    ];

    /**
     * Pages accessible via course allocation (independent of rolls).
     */
    protected $courseAllocationPages = [
        'results',
        'approve results',
        'my-lecture-timetable',
        'exam timetable',
        'course allocation',
        'corrigenda',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Skip check for Admin users
        if (session('accType') == 'Admin') {
            return $next($request);
        }

        // Skip check for public pages
        $page = trim($request->path(), '/');

        // Strip dynamic ID parameters for certain routes
        $page = preg_replace('/\/\d+$/', '', $page); // Remove trailing /123
        $page = preg_replace('/\/[a-f0-9-]{36}$/', '', $page); // Remove trailing UUID

        if (in_array($page, $this->except)) {
            return $next($request);
        }

        // Admin-only pages - block non-admins
        $adminOnlyPages = ['rolls', 'pages', 'users', 'users2'];
        if (in_array($page, $adminOnlyPages)) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        // Skip check for non-admin routes (applicant, student, etc.)
        if (session('accType') != 'Staff') {
            return $next($request);
        }

        // Check if page is accessible via course allocation
        $encodedPage = str_replace(' ', '%20', $page);
        if (in_array($page, $this->courseAllocationPages) || in_array($encodedPage, $this->courseAllocationPages)) {
            $hasCourseAllocation = \Illuminate\Support\Facades\DB::table('course_allocation')
                ->where('username', session('username'))
                ->exists();
            if ($hasCourseAllocation) {
                return $next($request);
            }
        }

        // Check if user has this page assigned in rolls table
        // First check if this page exists in rolls for ANY user
        $pageExistsInRolls = \Illuminate\Support\Facades\DB::table('rolls')
            ->where(function ($q) use ($page) {
                $q->where('link', '/' . $page)
                  ->orWhere('link', '/' . str_replace(' ', '%20', $page));
            })
            ->exists();

        // If page doesn't exist in rolls at all, allow access
        if (!$pageExistsInRolls) {
            return $next($request);
        }

        // Page exists in rolls, so check if user has access
        $hasAccess = \Illuminate\Support\Facades\DB::table('rolls')
            ->where(function ($q) {
                $q->where('username', session('username'))
                  ->orWhere('username', session('appointment'));
            })
            ->where(function ($q) use ($page) {
                $q->where('link', '/' . $page)
                  ->orWhere('link', '/' . str_replace(' ', '%20', $page));
            })
            ->exists();

        if (!$hasAccess) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }
}
