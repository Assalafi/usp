<?php

namespace App\Support;

use App\Http\Controllers\SystemSettingsController;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Listing and navigation state only. Decisions remain in the approval controller. */
final class ResultApprovalWorkspace
{
    public const STAGES = [
        'system' => 'Lecturer review', 'lecturer' => 'Department review',
        'hod' => 'Faculty review', 'dean' => 'Course System review',
        'cs' => 'Final approval', 'vc' => 'Approved',
    ];

    public static function stateKey(): string
    {
        return 'result_approval_view.' . sha1((string) session('username') . '|' . session('appointment') . '|' . session('role'));
    }

    public static function returnUrl(array $fallback = []): string
    {
        $filters = session(self::stateKey(), $fallback);
        return url('/approve results') . ($filters ? '?' . http_build_query($filters) : '');
    }

    public static function isFinalReviewer(): bool
    {
        return session('accType') === 'Admin'
            || (session('accType') === 'Staff' && PortalPermission::hasRole('VC'));
    }

    public static function canDecideDepartment(string $decision): bool
    {
        if (session('accType') === 'Admin') return true;
        if (!self::isFinalReviewer() || !PortalPermission::hasPageAccess('/approve results')) return false;
        $actions = PortalPermission::actionsForLink('/approve results');
        return !$actions || (bool) array_intersect($actions, ['*', 'approve', $decision === 'Rejected' ? 'update' : 'create']);
    }

    public function filters(Request $request): array
    {
        $appointment = strtoupper(trim((string) session('appointment')));
        $defaultStage = self::isFinalReviewer()
            ? 'cs'
            : (['HOD' => 'lecturer', 'DEAN' => 'hod', 'COURSE SYSTEM' => 'dean'][$appointment] ?? 'system');
        $defaults = ['session' => self::isFinalReviewer() ? 'all' : SystemSettingsController::getResultsSession(),
            'faculty' => '', 'department' => '', 'program' => '', 'semester' => '', 'stage' => $defaultStage, 'search' => '', 'page' => 1];
        $values = $request->validate([
            'session' => ['nullable', 'string', 'max:20'], 'faculty' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:50'], 'program' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:25'], 'stage' => ['nullable', 'in:system,lecturer,hod,dean,cs,vc'],
            'search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $filters = array_merge($defaults, $values);
        foreach ($filters as $key => $value) {
            $filters[$key] = $key === 'page' ? max(1, (int) $value) : trim((string) $value);
        }
        $filters['session'] = $filters['session'] ?: $defaults['session'];
        if (!array_key_exists('stage', $values) && $filters['stage'] === '') {
            $filters['stage'] = $defaultStage;
        }
        session([self::stateKey() => $filters]);
        return $filters;
    }

    public function data(array $filters): array
    {
        $final = self::isFinalReviewer();
        $appointment = strtoupper(trim((string) session('appointment')));
        $reviewStage = $final ? 'cs' : (['HOD' => 'lecturer', 'DEAN' => 'hod', 'COURSE SYSTEM' => 'dean'][$appointment] ?? 'system');
        $query = DB::table('results as r');
        $username = strtoupper(trim((string) session('username')));
        // Follow the same academic boundaries used by the course review page.
        if (!$final && !in_array($appointment, ['COURSE SYSTEM'], true)) {
            if (in_array($appointment, ['HOD', 'DEAN'], true)) {
                $field = $appointment === 'HOD' ? 'department' : 'faculty';
                $entity = (string) session($field);
                $query->where(function ($scope) use ($field, $entity, $username) {
                    $scope->whereExists(function ($course) use ($field, $entity) {
                        $course->selectRaw('1')->from('course as owned_course')->whereColumn('owned_course.code', 'r.code')
                            ->where('owned_course.' . $field, $entity);
                    })->orWhereExists($this->allocation($username));
                });
            } else {
                // Preserve the lecturer listing: assigned courses only.
                $query->whereExists($this->allocation($username));
            }
        }
        if ($filters['session'] !== 'all') $query->where('r.session', $filters['session']);
        if ($filters['semester'] !== '') $query->where('r.semester', $filters['semester']);
        if ($filters['program'] !== '') $query->where('r.program', $filters['program']);
        if ($filters['faculty'] !== '' || $filters['department'] !== '') {
            $query->whereExists(function ($programs) use ($filters) {
                $programs->selectRaw('1')->from('program as placement')->whereColumn('placement.code', 'r.program');
                if ($filters['faculty'] !== '') $programs->where('placement.faculty', $filters['faculty']);
                if ($filters['department'] !== '') $programs->where('placement.department', $filters['department']);
            });
        }
        if ($filters['search'] !== '') {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($search) use ($term) {
                $search->where('r.code', 'like', $term)->orWhereExists(function ($courses) use ($term) {
                    $courses->selectRaw('1')->from('course as search_course')->whereColumn('search_course.code', 'r.code')->where('search_course.title', 'like', $term);
                });
            });
        }
        $listingQuery = clone $query;
        if ($filters['stage'] !== '') $listingQuery->where('r.approve', $filters['stage']);
        // Approval is managed per course batch. Do not aggregate result rows
        // or students here: a student count is both expensive and irrelevant
        // to the approval decision. The grouped keys below are the complete
        // batch identity used by the page and its counters.
        $groups = $listingQuery->select('r.code', 'r.session', 'r.semester', 'r.approve', 'r.lecturer', 'r.program')
            ->groupBy('r.code', 'r.session', 'r.semester', 'r.approve', 'r.lecturer', 'r.program');

        // The default VC view used to execute this million-row grouping three
        // times (cards, pagination count, and department queue). Keep one
        // short-lived snapshot and paginate it in memory. A new request after
        // an approval gets fresh data within 15 seconds, while a page render
        // never repeats the expensive aggregation.
        $canSnapshot = $filters['faculty'] === ''
            && $filters['department'] === '' && $filters['program'] === ''
            && $filters['semester'] === '' && $filters['search'] === '';
        $cacheKey = 'result_approval_batches:' . sha1(json_encode([
            session('username'), session('appointment'), session('department'), session('faculty'), $final, $filters,
        ]));
        $allBatches = $canSnapshot
            ? Cache::remember($cacheKey, now()->addSeconds(15), fn () => DB::query()->fromSub($groups, 'batches')->get())
            : null;
        if ($allBatches) {
            $visible = $allBatches;
            $visible = $visible->sort(function ($left, $right) use ($reviewStage) {
                $rank = static fn ($row) => $row->approve === $reviewStage ? 0 : ($row->approve === 'vc' ? 2 : 1);
                return [$rank($left), -strtotime((string) $left->session), $left->code, $left->semester, $left->approve, $left->lecturer]
                    <=> [$rank($right), -strtotime((string) $right->session), $right->code, $right->semester, $right->approve, $right->lecturer];
            })->values();
            $total = $visible->count();
            $page = max(1, (int) $filters['page']);
            $rows = new LengthAwarePaginator($visible->forPage($page, 25)->values(), $total, 25, $page, [
                'path' => url('/approve results'), 'query' => array_diff_key($filters, ['page' => true]),
            ]);
        } else {
            $visible = null;
            $listing = DB::query()->fromSub($groups, 'batches');
            if ($filters['stage'] !== '') $listing->where('approve', $filters['stage']);
            $listing->orderByRaw('CASE WHEN approve = ? THEN 0 WHEN approve = ? THEN 2 ELSE 1 END', [$reviewStage, 'vc'])
                ->orderByDesc('session')->orderBy('code')->orderBy('semester')->orderBy('approve')->orderBy('lecturer');
            $rows = $listing->paginate(25, ['*'], 'page', $filters['page'])->appends(array_diff_key($filters, ['page' => true]));
        }
        $countKey = 'result_approval_counts:' . sha1(json_encode([
            session('username'), session('appointment'), session('department'), session('faculty'), $final,
            $filters['session'], $filters['faculty'], $filters['department'], $filters['program'], $filters['semester'], $filters['search'],
        ]));
        $counts = $allBatches
            ? $allBatches->groupBy('approve')->map->count()
            : Cache::remember($countKey, now()->addSeconds(15), function () use ($groups) {
                // Count already-grouped course batches instead of scanning
                // the full results table with a concatenated DISTINCT key.
                return DB::query()->fromSub($groups, 'batches')
                    ->select('approve')->selectRaw('COUNT(*) AS total')
                    ->groupBy('approve')->pluck('total', 'approve');
            });
        $this->attachBatchMetadata($rows);
        $titles = DB::table('course')->whereIn('code', $rows->getCollection()->pluck('code'))->pluck('title', 'code');

        $departments = Cache::remember('result_approval_departments', now()->addMinutes(10), fn () => DB::table('department')->select('code', 'title', 'faculty')->orderBy('title')->get());
        $programs = Cache::remember('result_approval_programs', now()->addMinutes(10), fn () => DB::table('program')->select('code', 'title', 'faculty', 'department')->orderBy('title')->get());
        $pendingDepartments = collect();
        if ($final) {
            // Department decisions cover the entire department for the displayed
            // session, regardless of the course list's programme/search filters.
            if ($allBatches && $filters['session'] === 'all' && $filters['faculty'] === '' && $filters['department'] === '') {
                $pendingBatches = $allBatches->where('approve', 'cs')
                    ->unique(fn ($batch) => implode('|', [$batch->program, $batch->code, $batch->session, $batch->semester]));
            } else {
                $pendingKey = 'result_approval_pending_departments:' . sha1(json_encode([$filters['faculty'], $filters['department']]));
                $pendingBatches = Cache::remember($pendingKey, now()->addSeconds(15), function () use ($filters) {
                    return DB::table('results as pending')->where('pending.approve', 'cs')
                        ->when($filters['faculty'] !== '' || $filters['department'] !== '', function ($pending) use ($filters) {
                            $pending->whereExists(function ($placement) use ($filters) {
                                $placement->selectRaw('1')->from('program as pending_program')->whereColumn('pending_program.code', 'pending.program');
                                if ($filters['faculty'] !== '') $placement->where('pending_program.faculty', $filters['faculty']);
                                if ($filters['department'] !== '') $placement->where('pending_program.department', $filters['department']);
                            });
                        })->select('pending.program', 'pending.code', 'pending.session', 'pending.semester')
                        ->groupBy('pending.program', 'pending.code', 'pending.session', 'pending.semester')->get();
                });
            }
            $totals = $pendingBatches->groupBy('program')->map(fn ($batch, $program) => (object) ['program' => $program, 'total' => $batch->count()])->values();
            $placements = $programs->keyBy('code');
            foreach ($totals as $total) {
                $placement = $placements->get($total->program);
                if (!$placement) continue;
                if ($filters['faculty'] !== '' && $placement->faculty !== $filters['faculty']) continue;
                if ($filters['department'] !== '' && $placement->department !== $filters['department']) continue;
                $code = $placement->department;
                $pendingDepartments[$code] = ($pendingDepartments[$code] ?? 0) + (int) $total->total;
            }
            $pendingDepartments = $departments->filter(fn ($d) => $pendingDepartments->has($d->code))
                ->map(fn ($d) => (object) ['code' => $d->code, 'title' => $d->title, 'total' => $pendingDepartments[$d->code]])->values();
        }
        return compact('filters', 'rows', 'counts', 'titles', 'departments', 'programs', 'pendingDepartments', 'final', 'reviewStage') + [
            'faculties' => Cache::remember('result_approval_faculties', now()->addMinutes(10), fn () => DB::table('faculty')->select('code', 'title')->orderBy('title')->get()),
            'academicSessions' => Cache::remember('result_approval_sessions', now()->addMinutes(10), fn () => DB::table('session')->orderByDesc('title')->pluck('title')),
            'stageLabels' => self::STAGES,
            'canApproveDepartment' => self::canDecideDepartment('Approved'),
            'canRejectDepartment' => self::canDecideDepartment('Rejected'),
        ];
    }

    private function allocation(string $username): \Closure
    {
        return function ($allocation) use ($username) {
            $allocation->selectRaw('1')->from('course_allocation as assigned')
                ->whereRaw('UPPER(TRIM(assigned.username)) = ?', [$username])
                ->whereRaw('UPPER(TRIM(assigned.course)) = UPPER(TRIM(r.code))')
                ->where(function ($session) {
                    $session->whereNull('assigned.session')->orWhere('assigned.session', '')->orWhereColumn('assigned.session', 'r.session');
                });
        };
    }

    private function attachBatchMetadata(LengthAwarePaginator $rows): void
    {
        $items = $rows->getCollection();
        if ($items->isEmpty()) return;
        $metadata = DB::table('results')->where(function ($query) use ($items) {
            foreach ($items as $item) {
                $query->orWhere(function ($batch) use ($item) {
                    $batch->where('code', $item->code)->where('session', $item->session)
                        ->where('semester', $item->semester)->where('approve', $item->approve)
                        ->where('lecturer', $item->lecturer);
                });
            }
        })->select('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at', 'comment')
            ->orderByDesc('updated_at')->get()->groupBy(fn ($item) => implode('|', [$item->code, $item->session, $item->semester, $item->approve, $item->lecturer]));
        $items->transform(function ($item) use ($metadata) {
            $key = implode('|', [$item->code, $item->session, $item->semester, $item->approve, $item->lecturer]);
            $latest = $metadata->get($key, collect())->first();
            $item->updated_at = $latest->updated_at ?? null;
            $item->comment = $latest->comment ?? null;
            return $item;
        });
    }

    private function batchCountExpression(string $prefix): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "COUNT(DISTINCT COALESCE({$prefix}.code,'') || '|' || COALESCE({$prefix}.session,'') || '|' || COALESCE({$prefix}.semester,'') || '|' || COALESCE({$prefix}.lecturer,'') || '|' || COALESCE({$prefix}.program,''))";
        }
        return "COUNT(DISTINCT CONCAT_WS('|', COALESCE({$prefix}.code,''), COALESCE({$prefix}.session,''), COALESCE({$prefix}.semester,''), COALESCE({$prefix}.lecturer,''), COALESCE({$prefix}.program,'')))";
    }
}
