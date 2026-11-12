<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Single source of truth for staff/admin page and action permissions.
 *
 * The legacy database stores actions as comma-separated values on `rolls`.
 * This class keeps that format compatible while making resolution consistent,
 * cached, and reusable by middleware, views, and permission administration.
 */
final class PortalPermission
{
    private const CACHE_SECONDS = 60;
    private const CACHE_VERSION_KEY = 'portal.permissions.version';

    private const ACTION_LABELS = [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'upload' => 'Upload',
        'approve' => 'Approve',
        'export' => 'Export',
        'restore' => 'Restore',
        'verify' => 'Verify',
        'cancel' => 'Cancel',
        'assign' => 'Assign',
        'flag' => 'Flag',
        'unflag' => 'Unflag',
        'idcard' => 'ID card',
        'getpdf' => 'PDF download',
        'status' => 'Change status',
        'password' => 'Password reset',
        'faculty' => 'Faculty filter',
        'department' => 'Department filter',
        'program' => 'Programme filter',
        'level' => 'Level filter',
        'session' => 'Session filter',
        'gender' => 'Gender filter',
        'marital' => 'Marital status filter',
        'state' => 'State filter',
        'lga' => 'LGA filter',
        'phone' => 'Phone filter',
        'jamb' => 'JAMB filter',
        'id' => 'ID filter',
        'profile_status' => 'Profile status filter',
        'active_student' => 'Active students metric',
        'active_staff' => 'Active staff metric',
        'paid_student' => 'Paid students metric',
        'unpaid_student' => 'Unpaid students metric',
        'paid_hostel_pin' => 'Paid hostel pins metric',
        'paid_hostel' => 'Paid hostel metric',
        'paid_certificate' => 'Paid certificate metric',
        'paid_transcript' => 'Paid transcript metric',
        'suspended' => 'Suspended metric',
        'nysc' => 'NYSC metric',
        'exemption' => 'Exemption metric',
        'alumni' => 'Alumni metric',
    ];

    private const ROUTE_ALIASES = [
        'fees-due' => '/fees due',
        'fees-due/school-summary' => '/fees due',
        'fees-due/school-records' => '/fees due',
        'fees-due/ug-summary' => '/fees due',
        'admin/remita-split-report' => '/admin/remita-split-report',
        'admin/remita-finance-live' => '/admin/remita-finance-live',
        'admin/make-payment' => '/admin/make-payment',
        'admin/receipts' => '/admin/receipts',
        'admin/student-results' => '/admin/student-results',
        'admin/hostel-permit' => '/admin/hostel-permit',
        'change hall' => '/hostel recipients',
        'change bed' => '/hostel recipients',
        'revoke' => '/hostel recipients',
        'hostel-payment' => '/hostel recipients',
        'admin/applicant' => '/admin-applicant',
        'admin/admit-applicant' => '/admin-applicant',
        'admin/cleared-applicant' => '/admin-applicant',
        'admin/reject-applicant' => '/admin-applicant',
        'admin/reject-clearing-applicant' => '/admin-applicant',
        'admin/face-search' => '/students list',
        'admin/move-to-alumni' => '/alumni',
        'admin/restore-alumni' => '/alumni',
        'admin/update-passwords-progress' => '/staff',
        'reset-staff-password' => '/staff',
        'staff-password' => '/staff',
        'reset-pass' => '/reset-passwords',
        'staff-record' => '/staff',
        'staff id card' => '/staff',
        'staff-record/download-cv' => '/staff',
        'staff/download-template' => '/staff',
        'staff/reset-password-progress' => '/staff',
        'staff/reset-password-stream' => '/staff',
        'reset-staff-password' => '/staff',
        'get-departments' => '/staff',
        'get-programs' => '/staff',
        'upload-degree' => '/staff',
        'update-staff' => '/staff',
        'attendance-page' => '/attendance',
        'attendance-getDate' => '/attendance',
        'committee-meeting-members' => '/committee meetings',
        'course system allocation' => '/course system allocation',
        'course-system-results' => '/course system allocation',
        'create-course-system-allocation' => '/course system allocation',
        'delete-course-system-allocation' => '/course system allocation',
        'course structure' => '/course structure',
        'create-course-structure' => '/course structure',
        'update-course-structure' => '/course structure',
        'delete-course-structure' => '/course structure',
        'upload-course-structure' => '/course structure',
        'pending-results' => '/pending-results',
        'upload-payment' => '/fees due',
        'export-users' => '/registration',
        'export-courses' => '/course',
        'course ajax' => '/course',
        'assign-courses' => '/student course registration',
        'update-student-level' => '/students list',
        'update-med' => '/results',
        'delete-results' => '/results',
        'comment-pending-result' => '/results',
        'initiate corrigenda' => '/corrigenda',
        'update corrigenda' => '/corrigenda',
        'corrigenda' => '/corrigenda',
        'delete-result' => '/approve results',
        'update-result-status' => '/approve results',
        'update-mark' => '/approve results',
        'approve-department-results' => '/approve results',
        'process-batch' => '/approve results',
        'process-next-batch' => '/approve results',
        'generate-status' => '/approve results',
        'admin-applicant' => '/admin-applicant',
        'admin/siwes' => '/admin/siwes',
        'inter-university-transfer/admin' => '/inter-university-transfer/admin',
        'inter-university-transfer/show' => '/inter-university-transfer/admin',
        'change-of-course/admin' => '/change-of-course/admin',
        'change-of-course/show' => '/change-of-course/admin',
        'change-of-course/bulk-edit' => '/change-of-course/admin',
        'change-of-course/bulk-update' => '/change-of-course/admin',
        'change-of-course/new-' => '/change-of-course/admin',
        'change-of-course/current-' => '/change-of-course/admin',
        'change-of-course/registrar-action' => '/change-of-course/admin',
        'change-of-course/vc-action' => '/change-of-course/admin',
        'change-of-course/admission-letter' => '/change-of-course/admin',
        'change-of-course/downloads' => '/change-of-course/admin',
        'change-of-course/resubmit' => '/change-of-course/admin',
        'change-of-course/bulk-download' => '/change-of-course/admin',
        'change-of-course/get-selected-applications' => '/change-of-course/admin',
        'change-of-course/track-download' => '/change-of-course/admin',
        'change-of-course/export' => '/change-of-course/admin',
        'recruitment' => '/recruitment',
        'recruitment/management' => '/recruitment/management',
        'recruitment/access' => '/recruitment/access',
        'developer-tasks' => '/developer-tasks',
        'certificate' => '/certificate',
        'affiliated-schools' => '/affiliated-schools',
        'alumni' => '/alumni',
        'staff' => '/staff',
        'student details' => '/student details',
        'student-results' => '/admin/student-results',
        'audit-logs' => '/audit-logs',
        'users' => '/users',
        'users2' => '/users2',
        'settings/general' => '/settings/general',
        'settings/update' => '/settings/general',
        'settings/update-single' => '/settings/general',
        'settings/security' => '/settings/general',
        'settings/reset' => '/settings/general',
        'settings/export' => '/settings/general',
        'reference-data' => '/reference-data',
        'offline-payments' => '/fees due',
        'upload-excel' => '/users',
        'assign-upload-excel' => '/users2',
        'admin/bulk-verify' => '/fees due',
        'pg-admission' => '/pg-admission',
        'program/sync-duration' => '/program',
        'results' => '/results',
        'election-results' => '/election results',
        'election live' => '/election live',
        'program-course-registration' => '/program course registration',
        'sync pages' => '/pages',
        'sync page-actions' => '/pages',
        'create page-action' => '/pages',
    ];

    public static function actionLabels(): array
    {
        return self::ACTION_LABELS;
    }

    public static function knownActions(): array
    {
        return array_keys(self::ACTION_LABELS);
    }

    public static function normalizeAction(?string $action): string
    {
        $action = trim((string) $action);
        if ($action === '') {
            return '';
        }

        $action = preg_replace('/([a-z])([A-Z])/', '$1_$2', $action);
        $action = strtolower((string) $action);
        $action = preg_replace('/[^a-z0-9]+/', '_', $action);
        return trim((string) $action, '_');
    }

    public static function normalizeActionList($value): string
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($action) => self::normalizeAction($action))
            ->filter()
            ->unique()
            ->values()
            ->implode(',');
    }

    public static function normalizeLink(?string $link): string
    {
        $link = trim(urldecode((string) $link));
        $link = preg_replace('#/+#', '/', $link);
        $link = '/' . ltrim((string) $link, '/');
        return strtolower(rtrim($link, '/')) ?: '/';
    }

    public static function linkCandidates(?string $link): array
    {
        $normalized = self::normalizeLink($link);
        $withoutSlash = ltrim($normalized, '/');
        $encoded = '/' . str_replace(' ', '%20', $withoutSlash);
        return array_values(array_unique([$normalized, $encoded, '/' . $withoutSlash]));
    }

    public static function currentIdentities(): array
    {
        return collect([
            session('username'),
            session('appointment'),
        ])->map(fn ($value) => trim((string) $value))->filter()->unique()->values()->all();
    }

    public static function flush(): void
    {
        $version = (int) Cache::get(self::CACHE_VERSION_KEY, 1);
        Cache::forever(self::CACHE_VERSION_KEY, $version + 1);
    }

    private static function cacheVersion(): int
    {
        if (! Cache::has(self::CACHE_VERSION_KEY)) {
            Cache::forever(self::CACHE_VERSION_KEY, 1);
        }
        return (int) Cache::get(self::CACHE_VERSION_KEY, 1);
    }

    public static function rowsForLink(?string $link): Collection
    {
        if (session('accType') === 'Admin' || !session()->has('log')) {
            return collect();
        }

        $key = 'portal.permissions.rows.v' . self::cacheVersion() . '.' . sha1(implode('|', [session('username'), session('appointment'), self::normalizeLink($link)]));
        return Cache::remember($key, self::CACHE_SECONDS, function () use ($link) {
            $identities = self::currentIdentities();
            if (!$identities) {
                return collect();
            }

            return DB::table('rolls')
                ->whereIn('username', $identities)
                ->where(function ($query) use ($link) {
                    $query->whereIn('link', self::linkCandidates($link));
                })
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', '!=', '0');
                })
                ->orderBy('id')
                ->get();
        });
    }

    public static function actionsForLink(?string $link): array
    {
        return self::rowsForLink($link)
            ->flatMap(function ($row) {
                return array_map([self::class, 'normalizeAction'], explode(',', (string) $row->action));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function hasPageAccess(?string $link): bool
    {
        return session('accType') === 'Admin' || self::rowsForLink($link)->isNotEmpty();
    }

    public static function allows(?string $link, ?string $action): bool
    {
        if (session('accType') === 'Admin') {
            return true;
        }

        $rows = self::rowsForLink($link);
        if ($rows->isEmpty()) {
            return false;
        }

        $requested = self::normalizeAction($action);
        if ($requested === '' || $requested === 'view') {
            // Legacy assignments pre-date the explicit view action. Any row
            // that grants a non-empty action list already grants page access.
            return true;
        }

        $actions = self::actionsForLink($link);
        return in_array($requested, $actions, true) || in_array('*', $actions, true);
    }

    public static function actionClass(string $action): string
    {
        return Str::camel(self::normalizeAction($action)) . 'Action';
    }

    public static function pageExists(?string $link): bool
    {
        $key = 'portal.permissions.page.v' . self::cacheVersion() . '.' . sha1(self::normalizeLink($link));
        return (bool) Cache::remember($key, self::CACHE_SECONDS, function () use ($link) {
            return DB::table('pages')->whereIn('link', self::linkCandidates($link))->exists();
        });
    }

    public static function resolve(Request $request): ?array
    {
        $path = trim(urldecode($request->path()), '/');
        $name = strtolower((string) optional($request->route())->getName());
        $uri = strtolower((string) optional($request->route())->uri());

        if ($path === '') {
            return null;
        }

        $link = self::linkForPath($path);
        $action = self::actionForPath($path, $name, $uri, $request->method());

        return ['link' => self::normalizeLink($link), 'action' => $action];
    }

    private static function linkForPath(string $path): string
    {
        if (str_starts_with($path, 'change-of-course/')) {
            return '/change-of-course/admin';
        }
        if (str_starts_with($path, 'inter-university-transfer/admin') || str_starts_with($path, 'inter-university-transfer/show')) {
            return '/inter-university-transfer/admin';
        }

        foreach (self::ROUTE_ALIASES as $prefix => $link) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $link;
            }
        }

        if (preg_match('/^(create|update|delete|upload)\s+(.+)$/i', $path, $match)) {
            return '/' . trim($match[2]);
        }

        foreach (['abs', 'obs', 'available bed space', 'online bed space', 'hostel recipients', 'manage hostel'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return '/' . $prefix;
            }
        }

        $first = explode('/', $path)[0];
        return '/' . $first;
    }

    private static function actionForPath(string $path, string $name, string $uri, string $method): string
    {
        if (preg_match('/^(create|update|delete|upload)\s+/', $path, $match)) {
            return strtolower($match[1]);
        }

        $download = str_contains($path, 'export')
            || str_contains($path, 'download')
            || str_contains($path, '/pdf')
            || str_contains($path, 'print-');
        if ($download || str_ends_with($name, '.export') || str_contains($name, 'download')) {
            return 'export';
        }

        if (str_contains($path, 'restore-alumni')) {
            return 'restore';
        }

        if (str_contains($path, 'move-to-alumni')) {
            return 'update';
        }

        if (str_contains($path, '/data') || str_contains($path, 'search') || str_contains($path, 'get-')) {
            return 'view';
        }

        if (str_contains($path, 'ajax')) {
            return 'view';
        }

        if (str_contains($path, 'upload') || str_contains($path, 'import')) {
            return 'upload';
        }

        if (str_contains($path, 'verify') || str_contains($path, 'cancel') || str_contains($path, 'restore') || str_contains($path, 'assign') || str_contains($path, 'flag') || str_contains($path, 'unflag') || str_contains($path, 'approve') || str_contains($path, 'reject') || str_contains($path, 'review') || str_contains($path, 'submit') || str_contains($path, 'sync') || str_contains($path, 'reset') || str_contains($path, 'action') || str_contains($name, '.store') || str_contains($name, '.update') || str_contains($name, '.delete')) {
            return 'update';
        }

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return 'update';
        }

        return 'view';
    }
}
