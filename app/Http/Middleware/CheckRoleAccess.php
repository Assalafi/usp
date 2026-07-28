<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleAccess
{
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
        if (session('accType') == 'Admin') {
            return $next($request);
        }

        $page = trim($request->path(), '/');
        $page = urldecode($page);
        $page = preg_replace('/\/\d+$/', '', $page);
        $page = preg_replace('/\/[a-f0-9-]{36}$/', '', $page);

        if (in_array($page, $this->except)) {
            return $next($request);
        }

        $adminOnlyPages = ['rolls', 'pages', 'users', 'users2'];
        if (in_array($page, $adminOnlyPages)) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        // Skip check for non-Staff users (Students, Applicants, etc.)
        if (session('accType') != 'Staff') {
            return $next($request);
        }

        // Check course allocation
        $encodedPage = str_replace(' ', '%20', $page);
        if (in_array($page, $this->courseAllocationPages) || in_array($encodedPage, $this->courseAllocationPages)) {
            $hasCourseAllocation = \Illuminate\Support\Facades\DB::table('course_allocation')
                ->where('username', session('username'))
                ->exists();
            if ($hasCourseAllocation) {
                return $next($request);
            }
        }

        // Check if page exists in rolls table
        $pageExistsInRolls = \Illuminate\Support\Facades\DB::table('rolls')
            ->where(function ($q) use ($page, $encodedPage) {
                $q->where('link', '/' . $page)
                  ->orWhere('link', '/' . $encodedPage);
            })
            ->exists();

        // If page exists in rolls, check if this user has access
        if ($pageExistsInRolls) {
            $hasAccess = \Illuminate\Support\Facades\DB::table('rolls')
                ->where(function ($q) {
                    $q->where('username', session('username'))
                      ->orWhere('username', session('appointment'));
                })
                ->where(function ($q) use ($page, $encodedPage) {
                    $q->where('link', '/' . $page)
                      ->orWhere('link', '/' . $encodedPage);
                })
                ->exists();

            if (!$hasAccess) {
                return redirect('/')->with('error', 'You do not have access to this page.');
            }

            return $next($request);
        }

        // Page not in rolls — check if it's a known system page
        // Known admin pages that require roll assignment
        $protectedPages = [
            'faculty', 'department', 'program', 'semester', 'session',
            'halls', 'hall allocation', 'lecture timetable', 'exam timetable',
            'ca timetable', 'fees due', 'fees type', 'fees master list',
            'students list', 'student id card', 'course allocation',
            'course material', 'attendance', 'assignment', 'student exit',
            'status', 'results', 'approve results', 'program course registration',
            'student course registration', 'summary of graduation',
            'press release', 'computation record', 'transcript',
            'school fees', 'hostel fees', 'staff',
            'election settings', 'election positions', 'election candidates',
            'election votes', 'election general', 'election faculty',
            'election hostel', 'election lga', 'manage fixed assets',
            'fixed assets', 'fixed assets depreciation', 'fixed assets analysis',
            'fixed assets disposal', 'grading system', 'committee',
            'committee role', 'committee membership', 'committee meetings',
            'sub committee', 'session history',
            'available bed space', 'online bed space', 'hostel recipients',
            'manage hostel', 'pins', 'bed space reservations',
        ];

        if (in_array($page, $protectedPages) || in_array($encodedPage, $protectedPages)) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }
}
