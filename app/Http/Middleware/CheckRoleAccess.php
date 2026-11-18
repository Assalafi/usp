<?php

namespace App\Http\Middleware;

use App\Support\PortalPermission;
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
        'results/upload',
        'approve results',
        'my-lecture-timetable',
        'exam timetable',
        'course allocation',
        'corrigenda',
    ];

    public function handle(Request $request, Closure $next)
    {
        $accountType = strtolower(trim((string) session('accType')));

        if ($accountType === 'admin') {
            return $next($request);
        }

        $page = trim($request->path(), '/');
        $page = urldecode($page);
        $page = preg_replace('/\/\d+$/', '', $page);
        $page = preg_replace('/\/[a-f0-9-]{36}$/', '', $page);
        // OBS uses AJAX sub-routes, but they share the same permission as the
        // secret online-bed-space management page.
        if (in_array($page, ['obs/data', 'obs/flag', 'obs/unflag'], true)) {
            $page = 'obs';
        }
        if (in_array($page, ['abs/data', 'abs/assign', 'abs/unflag'], true)) {
            $page = 'abs';
        }
        // The Fees Due school overview is an asynchronous child request of
        // the same page and must use the existing Fees Due permission.
        if (in_array($page, ['fees-due/school-summary', 'fees-due/school-records', 'fees-due/ug-summary'], true)) {
            $page = 'fees due';
        }
        if ($page === 'admin/remita-finance-live/data') {
            $page = 'admin/remita-finance-live';
        }

        if (in_array($page, $this->except)) {
            return $next($request);
        }

        $adminOnlyPages = ['rolls', 'pages', 'users', 'users2'];
        if (in_array($page, $adminOnlyPages)) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        // The applicant dashboard uses the historical admin-applicant PDF
        // URL. The controller verifies record ownership before rendering it.
        if ($accountType === 'applicant'
            && preg_match('#^admin/applicant/[^/]+/pdf$#', $page)) {
            return $next($request);
        }

        // Student/applicant accounts can use their self-service pages, but
        // must never be able to call an administrative action directly.
        if (!in_array($accountType, ['staff', 'admin'], true)
            && $this->isManagementEndpoint($page)) {
            abort(403, 'This action is available to authorised staff only.');
        }

        // Skip staff-roll checks for non-Staff users (Students, Applicants,
        // etc.) after the explicit management guard above.
        if ($accountType !== 'staff') {
            return $next($request);
        }

        // Every Head of Department may review the programme-course register.
        // Editing is authorised separately by the controller and the academic
        // settings, so a HOD is never locked out simply because editing is off.
        if (strtoupper((string) session('appointment')) === 'HOD'
            && in_array($page, ['program course registration', 'program-course-registration/preview', 'program-course-registration/submit-review'], true)) {
            return $next($request);
        }

        // Developers use the same portal login as staff. Their task board and
        // task actions are explicitly scoped by the task controller.
        if ($accountType === 'staff'
            && in_array(strtoupper(trim((string) session('appointment'))), ['DEVELOPER', 'DEVELOPMENT', 'IT', 'ICT'], true)
            && (strpos($page, 'developer-tasks') === 0 || strpos($page, 'developer tasks') === 0)) {
            return $next($request);
        }

        // Omission comments are authorised against their stored course/session
        // and MAIN allocation inside ResultsController::pending. Checking the
        // unrelated Results catalogue permission here used to redirect the
        // very lecturer permitted to review and submit this draft course.
        if ($page === 'comment-pending-result') {
            return $next($request);
        }

        // The same MAIN lecturer who can review and comment on this draft
        // course can submit it. The controller rechecks ownership and stage.
        if ($page === 'create approve results' && $request->input('approve') === 'system'
            && \App\Support\PendingResultAccess::canComment(
                (string) $request->input('code'), (string) $request->input('session'), (string) $request->input('semester'))) {
            return $next($request);
        }

        // Course reviews and their downloads use the same read-only scope.
        // Action ownership is checked separately by the submission handlers.
        if ($page === 'pending-results') {
            abort_unless(\App\Support\PendingResultAccess::canView(
                (string) $request->query('code'), (string) $request->query('session')), 403,
                'You do not have access to review this course for the selected session.');
            return $next($request);
        }

        // Check course allocation
        $encodedPage = str_replace(' ', '%20', $page);
        if (in_array($page, $this->courseAllocationPages) || in_array($encodedPage, $this->courseAllocationPages)) {
            $allocationQuery = \Illuminate\Support\Facades\DB::table('course_allocation')
                ->where('username', session('username'));
            if ($page === 'results/upload') {
                $allocationQuery->where('type', 'MAIN');
            }
            $hasCourseAllocation = $allocationQuery->exists();
            if ($hasCourseAllocation) {
                return $next($request);
            }
        }

        // Resolve every catalogue-backed endpoint to one page and one action.
        // This covers nested routes, AJAX endpoints and downloads without
        // duplicating permission queries in individual controllers.
        $permission = PortalPermission::resolve($request);
        if ($permission && PortalPermission::pageExists($permission['link'])) {
            if (!PortalPermission::hasPageAccess($permission['link'])) {
                if (in_array($page, ['pending-results', 'create approve results', 'update approve results', 'update-mark', 'delete-result', 'update-result-status', 'approve-department-results', 'generate-status'], true)
                    || str_starts_with($page, 'process-next-batch/')) {
                    abort(403, 'You do not have access to this result approval action. Please contact the portal administrator.');
                }
                return redirect('/')->with('error', 'You do not have access to this page.');
            }

            if (!PortalPermission::allows($permission['link'], $permission['action'])) {
                abort(403, 'You do not have permission to perform this action.');
            }

            return $next($request);
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
            'ca timetable', 'fees due', 'remita-split-report', 'admin/remita-split-report', 'remita-finance-live', 'admin/remita-finance-live', 'fees type', 'fees master list',
            'students list', 'student id card', 'course allocation',
            'course material', 'attendance', 'assignment', 'student exit',
            'status', 'results', 'results/upload', 'approve results', 'program course registration',
            'student course registration', 'summary of graduation',
            'press release', 'computation record', 'transcript',
            'school fees', 'hostel fees', 'staff',
            'election settings', 'election positions', 'election candidates',
            'election votes', 'election general', 'election faculty',
            'election hostel', 'election lga', 'manage fixed assets',
            'election results', 'election-results', 'election-results/export', 'election-results/pdf', 'election-results/certificates', 'election-results/certificate',
            'fixed assets', 'fixed assets depreciation', 'fixed assets analysis',
            'fixed assets disposal', 'grading system', 'committee',
            'committee role', 'committee membership', 'committee meetings',
            'sub committee', 'session history',
            'available bed space', 'online bed space', 'hostel recipients', 'hostel bulk revoke', 'abs', 'obs',
            'manage hostel', 'pins', 'bed space reservations', 'developer-tasks', 'developer tasks',
        ];

        if (in_array($page, $protectedPages) || in_array($encodedPage, $protectedPages)) {
            return redirect('/')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }

    private function isManagementEndpoint(string $page): bool
    {
        if (str_starts_with($page, 'admin/')
            || str_starts_with($page, 'admin-')
            || str_starts_with($page, 'create ')
            || str_starts_with($page, 'update ')
            || str_starts_with($page, 'delete ')
            || str_starts_with($page, 'upload ')
            || str_starts_with($page, 'sync ')) {
            return true;
        }

        return in_array($page, [
            'alumni',
            'import-alumni',
            'upload-student-to-alumni',
            'upload-student-ids-to-alumni',
            'sms',
            'sms/create',
            'sms/send',
            'sms/sent',
            'sms/non-academic-staff',
            'sms/reset-progress',
            'sms/reset-and-notify-stream',
            'sms/resend-all-progress',
            'sms/resend-all-stream',
            'sms/resend',
            'course',
            'course ajax',
            'course structure',
            'course system allocation',
            'course-system-results',
            'faculty',
            'department',
            'program',
            'semester',
            'session',
            'halls',
            'hall allocation',
            'lecture timetable',
            'exam timetable',
            'ca timetable',
            'fees due',
            'fees type',
            'fees master list',
            'students list',
            'student id card',
            'course allocation',
            'course material',
            'status',
            'results',
            'approve results',
            'program course registration',
            'summary of graduation',
            'press release',
            'computation record',
            'transcript',
            'school fees',
            'hostel fees',
            'election settings',
            'election positions',
            'election candidates',
            'election votes',
            'election results',
            'election-results',
            'grading system',
            'staff',
            'staff-record',
            'staff-record-update',
            'staff id card',
            'staff-record/download-cv',
            'staff/download-template',
            'staff-password',
            'reset-staff-password',
            'reset-pass',
            'reference-data',
            'audit-logs',
            'pages',
            'rolls',
            'settings',
            'pins',
            'assign bed',
            'delete bed',
            'create-hostel',
            'print-permit',
            'change hall',
            'change bed',
            'revoke',
            'hostel-payment',
            'hostel-invoice',
            'refresh-student',
            'upload-payment',
            'export-users',
            'export-courses',
            'assign-courses',
            'update-student-level',
            'pending-results',
            'update-med',
            'delete-results',
            'comment-pending-result',
            'initiate corrigenda',
            'update corrigenda',
            'corrigenda',
            'delete-result',
            'update-result-status',
            'process-batch',
            'update-mark',
            'approve-department-results',
            'process-next-batch',
            'generate-status',
            'recruitment',
            'certificate',
            'affiliated-schools',
            'pg-admission',
            'election live',
        ], true);
    }
}
