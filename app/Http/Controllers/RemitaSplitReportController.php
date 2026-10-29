<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Support\StreamingXlsxExporter;
use App\Services\RemitaSplitAggregationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemitaSplitReportController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('log') || !in_array(session('accType'), ['Admin', 'Staff'], true)) {
            return redirect('/')->with('error', 'You do not have permission to view finance reports.');
        }

        [$period, $dateFrom, $dateTo, $periodLabel] = $this->periodWindow($request);
        $filters = [
            // Paid charges are the useful default for finance reconciliation;
            // submitting an empty status explicitly still shows every status.
            'status' => trim((string) $request->get('status', 'Paid')),
            'service' => trim((string) $request->get('service', '')),
            'faculty' => trim((string) $request->get('faculty', '')),
            'department' => trim((string) $request->get('department', '')),
            'program' => trim((string) $request->get('program', '')),
            'level' => trim((string) $request->get('level', '')),
            'session' => trim((string) $request->get('session', '')),
            'sponsor' => trim((string) $request->get('sponsor', '')),
            'student_id' => trim((string) $request->get('student_id', '')),
            'rrr' => trim((string) $request->get('rrr', '')),
        ];

        $services = collect(SystemSettingsController::getRemitaServiceCatalog());
        $splitSettings = SystemSettingsController::getRemitaSplitSettings();
        $serviceFilters = $services->mapWithKeys(function ($service) {
            $key = (string) ($service['key'] ?? '');
            $patterns = [
                'school_fees' => ['SCHOOL FEES', '1000127'],
                'hostel' => ['HOSTEL'],
                'post_utme' => ['POST UTME'],
                'id_card' => ['ID CARD'],
                'change_of_course' => ['CHANGE OF COURSE'],
                'inter_university_transfer' => ['TRANSFER'],
            ];
            return [$key => [
                'service_type_id' => (string) ($service['service_type_id'] ?? ''),
                'description_patterns' => $patterns[$key] ?? [],
            ]];
        });

        $baseQuery = DB::table('invoices')->where('invoices.split_enabled', 1);
        $this->applyFilters($baseQuery, $filters, $serviceFilters, $dateFrom, $dateTo);
        // `amount` is the amount charged at Remita (base fee plus split
        // surcharge). Keep each component available so the overall report can
        // reconcile every portal consistently.
        $grossExpression = "COALESCE(CAST(amount AS DECIMAL(15,2)), 0)";
        $baseExpression = "COALESCE(CAST(base_amount AS DECIMAL(15,2)), GREATEST(CAST(amount AS DECIMAL(15,2)) - COALESCE(CAST(surcharge_amount AS DECIMAL(15,2)), 0), 0))";
        $surchargeExpression = "COALESCE(CAST(surcharge_amount AS DECIMAL(15,2)), GREATEST(CAST(amount AS DECIMAL(15,2)) - COALESCE(CAST(base_amount AS DECIMAL(15,2)), 0), 0))";
        $qualifiedGrossExpression = "COALESCE(CAST(invoices.amount AS DECIMAL(15,2)), 0)";
        $qualifiedBaseExpression = "COALESCE(CAST(invoices.base_amount AS DECIMAL(15,2)), GREATEST(CAST(invoices.amount AS DECIMAL(15,2)) - COALESCE(CAST(invoices.surcharge_amount AS DECIMAL(15,2)), 0), 0))";
        $qualifiedSurchargeExpression = "COALESCE(CAST(invoices.surcharge_amount AS DECIMAL(15,2)), GREATEST(CAST(invoices.amount AS DECIMAL(15,2)) - COALESCE(CAST(invoices.base_amount AS DECIMAL(15,2)), 0), 0))";

        $summary = (clone $baseQuery)->selectRaw(
            'COUNT(*) AS invoice_count,
             COALESCE(SUM('.$grossExpression.'), 0) AS gross_amount,
             COALESCE(SUM('.$baseExpression.'), 0) AS base_amount,
             COALESCE(SUM('.$surchargeExpression.'), 0) AS surcharge_amount'
        )->first();

        $statusSummary = (clone $baseQuery)
            ->select('status')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM('.$grossExpression.'), 0) AS gross_amount')
            ->groupBy('status')
            ->orderByDesc('gross_amount')
            ->get();

        $serviceSummary = (clone $baseQuery)
            ->select('description')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM('.$grossExpression.'), 0) AS gross_amount, COALESCE(SUM('.$surchargeExpression.'), 0) AS surcharge_amount')
            ->groupBy('description')
            ->orderByDesc('gross_amount')
            ->get();

        $allocationSummary = DB::table('invoice_split_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_split_items.invoice_id')
            ->where('invoices.split_enabled', 1);
        $this->applyFilters($allocationSummary, $filters, $serviceFilters, $dateFrom, $dateTo, 'invoices');
        $allocationSummary = $allocationSummary
            ->select('invoice_split_items.beneficiary_name')
            ->selectRaw('COUNT(DISTINCT invoice_split_items.invoice_id) AS invoice_count, COALESCE(SUM(invoice_split_items.beneficiary_amount), 0) AS allocated_amount')
            ->groupBy('invoice_split_items.beneficiary_name')
            ->orderByDesc('allocated_amount')
            ->get();

        $paid = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['paid', 'successful', 'success'], true))->sum('gross_amount');
        $pending = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['pending', 'processing'], true))->sum('gross_amount');
        $cancelled = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['cancelled', 'canceled', 'failed', 'rejected'], true))->sum('gross_amount');
        $allocatedTotal = (float) $allocationSummary->sum('allocated_amount');

        $groupExpression = $this->groupExpression($period);
        $trend = (clone $baseQuery)
            ->selectRaw($groupExpression . ' AS bucket')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM('.$grossExpression.'), 0) AS gross_amount, COALESCE(SUM('.$surchargeExpression.'), 0) AS surcharge_amount')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $rows = (clone $baseQuery)
            ->leftJoin('students', 'students.user_id', '=', 'invoices.username')
            ->leftJoin('faculty', 'faculty.code', '=', 'invoices.faculty')
            ->leftJoin('department', 'department.code', '=', 'invoices.department')
            ->leftJoin('program', 'program.code', '=', 'invoices.program')
            ->select([
                'invoices.id', 'invoices.rrr', 'invoices.orderId', 'invoices.description', 'invoices.status',
                'invoices.amount', 'invoices.base_amount', 'invoices.surcharge_amount', 'invoices.session',
                'invoices.created_at', 'invoices.username', 'invoices.faculty', 'invoices.department', 'invoices.program', 'invoices.level',
                'students.username AS matric_number', 'students.fullname',
                'faculty.title AS faculty_title', 'department.title AS department_title', 'program.title AS program_title',
            ])
            ->selectRaw($qualifiedGrossExpression.' AS gross_amount')
            ->orderByDesc('invoices.created_at')
            ->orderByDesc('invoices.id')
            ->paginate(25)
            ->withQueryString();
        $rows->getCollection()->transform(function ($row) {
            $row->amount = (float) ($row->gross_amount ?? $row->amount ?? 0);
            return $row;
        });

        // Pull the same normalized read-only shape from PG, SBRS and
        // Institute. A source outage never breaks the UG report; its status is
        // surfaced to the user and the available systems are still totaled.
        $ugSummary = $summary;
        $aggregation = app(RemitaSplitAggregationService::class)->collect($dateFrom, $dateTo, $filters);
        $remoteSummary = (array) ($aggregation['summary'] ?? []);
        $allocatedTotal += (float) collect($aggregation['allocation_summary'] ?? [])->sum('allocated_amount');
        $summary = (object) [
            'invoice_count' => (int) ($ugSummary->invoice_count ?? 0) + (int) ($remoteSummary['invoice_count'] ?? 0),
            'gross_amount' => (float) ($ugSummary->gross_amount ?? 0) + (float) ($remoteSummary['gross_amount'] ?? 0),
            'base_amount' => (float) ($ugSummary->base_amount ?? 0) + (float) ($remoteSummary['base_amount'] ?? 0),
            'surcharge_amount' => (float) ($ugSummary->surcharge_amount ?? 0) + (float) ($remoteSummary['surcharge_amount'] ?? 0),
        ];
        $paid += (float) ($aggregation['paid'] ?? 0);
        $pending += (float) ($aggregation['pending'] ?? 0);
        $cancelled += (float) ($aggregation['cancelled'] ?? 0);

        $mergeGroups = static function ($local, array $remote, string $localKey, string $remoteKey, string $amountKey): \Illuminate\Support\Collection {
            $groups = [];
            foreach ($local as $item) {
                $key = (string) ($item->{$localKey} ?? 'Unknown');
                $groups[$key] = [
                    $localKey => $key,
                    'invoice_count' => (int) ($item->invoice_count ?? 0),
                    $amountKey => (float) ($item->{$amountKey} ?? 0),
                    'surcharge_amount' => (float) ($item->surcharge_amount ?? 0),
                ];
            }
            foreach ($remote as $item) {
                $key = (string) ($item[$remoteKey] ?? 'Unknown');
                if (! isset($groups[$key])) $groups[$key] = [$localKey => $key, 'invoice_count' => 0, $amountKey => 0.0, 'surcharge_amount' => 0.0];
                $groups[$key]['invoice_count'] += (int) ($item['invoice_count'] ?? 0);
                $groups[$key][$amountKey] += (float) ($item[$amountKey] ?? 0);
                $groups[$key]['surcharge_amount'] += (float) ($item['surcharge_amount'] ?? 0);
            }
            return collect(array_values($groups))->map(fn ($item) => (object) $item)->sortByDesc($amountKey)->values();
        };
        $statusSummary = $mergeGroups($statusSummary, (array) ($aggregation['status_summary'] ?? []), 'status', 'status', 'gross_amount');
        $serviceSummary = $mergeGroups($serviceSummary, (array) ($aggregation['service_summary'] ?? []), 'description', 'label', 'gross_amount');
        $trend = $mergeGroups($trend, (array) ($aggregation['trend'] ?? []), 'bucket', 'bucket', 'gross_amount')->sortBy('bucket')->values();
        $externalRows = collect($aggregation['rows'] ?? []);
        $sourceBreakdown = collect([
            ['source' => 'ug', 'label' => 'Undergraduate', 'status' => 'ok', 'count' => (int) ($ugSummary->invoice_count ?? 0), 'gross_amount' => (float) ($ugSummary->gross_amount ?? 0), 'error' => null],
        ])->merge($aggregation['sources'] ?? [])->values();

        $faculties = DB::table('faculty')->where('status', 1)->select('code', 'title')->orderBy('title')->get();
        $departments = DB::table('department')->where('status', 1)->select('code', 'title', 'faculty')->orderBy('title')->get();
        $programs = DB::table('program')->where('status', 1)->select('code', 'title', 'department', 'faculty')->orderBy('title')->get();
        $sessions = DB::table('invoices')->whereNotNull('session')->where('session', '<>', '')->distinct()->orderByDesc('session')->pluck('session');

        return view('main', [
            'page' => 'remita split report',
            'title' => 'Remita Split Payments',
            'period' => $period,
            'periodLabel' => $periodLabel,
            'dateFrom' => $dateFrom?->toDateString(),
            'dateTo' => $dateTo?->toDateString(),
            'filters' => $filters,
            'services' => $services,
            'splitSettings' => $splitSettings,
            'beneficiaryShares' => collect($splitSettings['beneficiaries'] ?? [])->mapWithKeys(fn ($beneficiary) => [(string) ($beneficiary['label'] ?? '') => (float) ($beneficiary['percentage'] ?? 0)]),
            'summary' => $summary,
            'paid' => $paid,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'allocatedTotal' => $allocatedTotal,
            'statusSummary' => $statusSummary,
            'serviceSummary' => $serviceSummary,
            'allocationSummary' => $allocationSummary,
            'externalAllocationSummary' => collect($aggregation['allocation_summary'] ?? []),
            'externalRows' => $externalRows,
            'sourceBreakdown' => $sourceBreakdown,
            'remoteSources' => $aggregation['sources'] ?? [],
            'trend' => $trend,
            'rows' => $rows,
            'faculties' => $faculties,
            'departments' => $departments,
            'programs' => $programs,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Export every transaction matching the report filters, not only the
     * paginated rows visible on the current screen.
     */
    public function export(Request $request)
    {
        if (! session()->has('log') || ! in_array(session('accType'), ['Admin', 'Staff'], true)) {
            abort(403, 'You do not have permission to export finance reports.');
        }

        [$period, $dateFrom, $dateTo] = $this->periodWindow($request);
        $filters = [
            'status' => trim((string) $request->get('status', 'Paid')),
            'service' => trim((string) $request->get('service', '')),
            'faculty' => trim((string) $request->get('faculty', '')),
            'department' => trim((string) $request->get('department', '')),
            'program' => trim((string) $request->get('program', '')),
            'level' => trim((string) $request->get('level', '')),
            'session' => trim((string) $request->get('session', '')),
            'sponsor' => trim((string) $request->get('sponsor', '')),
            'student_id' => trim((string) $request->get('student_id', '')),
            'rrr' => trim((string) $request->get('rrr', '')),
        ];

        $services = collect(SystemSettingsController::getRemitaServiceCatalog());
        $serviceFilters = $services->mapWithKeys(function ($service) {
            $key = (string) ($service['key'] ?? '');
            $patterns = [
                'school_fees' => ['SCHOOL FEES', '1000127'],
                'hostel' => ['HOSTEL'],
                'post_utme' => ['POST UTME'],
                'id_card' => ['ID CARD'],
                'change_of_course' => ['CHANGE OF COURSE'],
                'inter_university_transfer' => ['TRANSFER'],
            ];

            return [$key => [
                'service_type_id' => (string) ($service['service_type_id'] ?? ''),
                'description_patterns' => $patterns[$key] ?? [],
            ]];
        });

        $query = DB::table('invoices')->where('invoices.split_enabled', 1);
        $this->applyFilters($query, $filters, $serviceFilters, $dateFrom, $dateTo);
        $qualifiedGrossExpression = "COALESCE(CAST(invoices.amount AS DECIMAL(15,2)), 0)";
        $rows = $query
            ->leftJoin('students', 'students.user_id', '=', 'invoices.username')
            ->leftJoin('faculty', 'faculty.code', '=', 'invoices.faculty')
            ->leftJoin('department', 'department.code', '=', 'invoices.department')
            ->leftJoin('program', 'program.code', '=', 'invoices.program')
            ->select([
                'invoices.id', 'invoices.rrr', 'invoices.orderId', 'invoices.description',
                'invoices.serviceTypeId', 'invoices.status', 'invoices.amount',
                'invoices.base_amount', 'invoices.surcharge_amount', 'invoices.session',
                'invoices.created_at', 'invoices.username', 'invoices.fees_type',
                'invoices.faculty', 'invoices.department', 'invoices.program', 'invoices.level',
                'students.username AS matric_number', 'students.fullname',
                'faculty.title AS faculty_title', 'department.title AS department_title',
                'program.title AS program_title',
            ])
            ->selectRaw($qualifiedGrossExpression . ' AS gross_amount')
            ->orderByDesc('invoices.created_at')
            ->orderByDesc('invoices.id')
            ->cursor();

        @set_time_limit(0);
        $xlsxPath = StreamingXlsxExporter::create(
            $rows,
            [
                'Invoice ID', 'RRR', 'Order Reference', 'Student ID', 'Student Name',
                'Service', 'Service Type ID', 'Faculty', 'Department', 'Programme',
                'Level', 'Academic Session', 'Sponsor', 'Base Amount', 'Service Charge',
                'Overall Payment', 'Status', 'Created At',
            ],
            static function ($row): array {
                $base = $row->base_amount !== null
                    ? (float) $row->base_amount
                    : max((float) $row->amount - (float) ($row->surcharge_amount ?? 0), 0);
                $gross = (float) ($row->gross_amount ?? $row->amount ?? 0);

                return [
                    $row->id,
                    $row->rrr ?: 'Pending RRR',
                    $row->orderId ?: '',
                    $row->matric_number ?: $row->username ?: '',
                    $row->fullname ?: 'Student',
                    $row->description ?: 'Payment',
                    $row->serviceTypeId ?: '',
                    $row->faculty_title ?: $row->faculty ?: '',
                    $row->department_title ?: $row->department ?: '',
                    $row->program_title ?: $row->program ?: '',
                    $row->level ?: '',
                    $row->session ?: '',
                    strtolower((string) $row->fees_type) === 'nelfund' ? 'NELFUND' : 'Self sponsored',
                    number_format($base, 2, '.', ''),
                    number_format((float) ($row->surcharge_amount ?? 0), 2, '.', ''),
                    number_format($gross, 2, '.', ''),
                    $row->status ?: 'Unknown',
                    $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '',
                ];
            }
        );

        return response()->download(
            $xlsxPath,
            'remita-split-transactions-' . $period . '-' . now()->format('Y-m-d_His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function periodWindow(Request $request): array
    {
        $period = strtolower((string) $request->get('period', 'monthly'));
        $now = Carbon::now('Africa/Lagos')->startOfDay();
        $from = null;
        $to = $now->copy();
        $label = 'All time';

        if ($period === 'custom') {
            $from = $this->safeDate($request->get('date_from'));
            $to = $this->safeDate($request->get('date_to')) ?: $now->copy();
            if ($from && $from->gt($to)) {
                [$from, $to] = [$to, $from];
            }
            $label = $from ? $from->format('d M Y') . ' – ' . $to->format('d M Y') : 'Custom dates';
            return [$period, $from, $to, $label];
        }

        $quickRanges = [
            'today' => [
                'from' => $now->copy()->startOfDay(),
                'to' => $now->copy()->endOfDay(),
                'label' => 'Today',
            ],
            'yesterday' => [
                'from' => $now->copy()->subDay()->startOfDay(),
                'to' => $now->copy()->subDay()->endOfDay(),
                'label' => 'Yesterday',
            ],
            'this_week' => [
                'from' => $now->copy()->startOfWeek(),
                'to' => $now->copy()->endOfWeek(),
                'label' => 'This week',
            ],
            'this_month' => [
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth(),
                'label' => 'This month',
            ],
            'this_quarter' => [
                'from' => $now->copy()->startOfQuarter(),
                'to' => $now->copy()->endOfQuarter(),
                'label' => 'This quarter',
            ],
            'this_year' => [
                'from' => $now->copy()->startOfYear(),
                'to' => $now->copy()->endOfYear(),
                'label' => 'This year',
            ],
            'last_week' => [
                'from' => $now->copy()->subWeek()->startOfWeek(),
                'to' => $now->copy()->subWeek()->endOfWeek(),
                'label' => 'Last week',
            ],
            'last_month' => [
                'from' => $now->copy()->subMonthNoOverflow()->startOfMonth(),
                'to' => $now->copy()->subMonthNoOverflow()->endOfMonth(),
                'label' => 'Last month',
            ],
            'last_quarter' => [
                'from' => $now->copy()->subQuarter()->startOfQuarter(),
                'to' => $now->copy()->subQuarter()->endOfQuarter(),
                'label' => 'Last quarter',
            ],
            'last_year' => [
                'from' => $now->copy()->subYear()->startOfYear(),
                'to' => $now->copy()->subYear()->endOfYear(),
                'label' => 'Last year',
            ],
        ];
        if (isset($quickRanges[$period])) {
            $range = $quickRanges[$period];
            $label = $range['label'] . ' · ' . $range['from']->format('d M Y') . ' – ' . $range['to']->format('d M Y');
            return [$period, $range['from'], $range['to'], $label];
        }

        if ($period === 'monthly') {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
            return [$period, $from, $to, 'This month · ' . $from->format('d M Y') . ' – ' . $to->format('d M Y')];
        }

        $windows = ['daily' => 29, 'weekly' => 83, 'monthly' => 364, 'quarterly' => 730, 'yearly' => 1825];
        if (isset($windows[$period])) {
            $from = $now->copy()->subDays($windows[$period]);
            $label = match ($period) {
                'daily' => 'Last 30 days',
                'weekly' => 'Last 12 weeks',
                'monthly' => 'Last 12 months',
                'quarterly' => 'Last 8 quarters',
                'yearly' => 'Last 5 years',
                default => 'All time',
            };
        } else {
            $period = 'all';
            $to = null;
        }
        return [$period, $from, $to, $label];
    }

    private function safeDate($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function applyFilters($query, array $filters, $serviceFilters, ?Carbon $from, ?Carbon $to, string $table = 'invoices'): void
    {
        $prefix = $table . '.';
        if ($from) {
            $query->where($prefix . 'created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to) {
            $query->where($prefix . 'created_at', '<', $to->copy()->addDay()->startOfDay());
        }
        foreach (['status', 'faculty', 'department', 'program', 'level', 'session'] as $field) {
            if ($filters[$field] !== '') {
                $query->where($prefix . $field, $filters[$field]);
            }
        }
        if ($filters['service'] !== '') {
            $service = $serviceFilters[(string) $filters['service']] ?? null;
            $serviceId = $service['service_type_id'] ?? $filters['service'];
            $query->where($prefix . 'serviceTypeId', $serviceId);
            if (!empty($service['description_patterns'])) {
                $query->where(function ($nested) use ($prefix, $service) {
                    foreach ($service['description_patterns'] as $index => $pattern) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $nested->{$method}($prefix . 'description', 'like', '%' . $pattern . '%');
                    }
                });
            }
        }
        if ($filters['sponsor'] !== '') {
            $filters['sponsor'] === 'nelfund'
                ? $query->where($prefix . 'fees_type', 'nelfund')
                : $query->where(function ($nested) use ($prefix) {
                    $nested->where($prefix . 'fees_type', '<>', 'nelfund')->orWhereNull($prefix . 'fees_type');
                });
        }
        if ($filters['rrr'] !== '') {
            $query->where(function ($nested) use ($prefix, $filters) {
                $nested->where($prefix . 'rrr', 'like', '%' . $filters['rrr'] . '%')
                    ->orWhere($prefix . 'orderId', 'like', '%' . $filters['rrr'] . '%');
            });
        }
        if ($filters['student_id'] !== '') {
            $term = '%' . $filters['student_id'] . '%';
            $query->where(function ($nested) use ($prefix, $term) {
                $nested->where($prefix . 'username', 'like', $term)
                    ->orWhere($prefix . 'name', 'like', $term)
                    ->orWhereIn($prefix . 'username', DB::table('students')->select('user_id')->where('username', 'like', $term));
            });
        }
    }

    private function groupExpression(string $period): string
    {
        return match ($period) {
            'daily' => "DATE_FORMAT(invoices.created_at, '%Y-%m-%d')",
            'weekly' => "CONCAT(YEAR(invoices.created_at), '-W', LPAD(WEEK(invoices.created_at, 3), 2, '0'))",
            'monthly' => "DATE_FORMAT(invoices.created_at, '%Y-%m')",
            'quarterly' => "CONCAT(YEAR(invoices.created_at), '-Q', QUARTER(invoices.created_at))",
            'today', 'yesterday', 'this_week', 'last_week', 'last_month' => "DATE_FORMAT(invoices.created_at, '%Y-%m-%d')",
            'this_month', 'this_quarter', 'this_year', 'last_quarter', 'last_year' => "DATE_FORMAT(invoices.created_at, '%Y-%m')",
            'yearly', 'all' => "DATE_FORMAT(invoices.created_at, '%Y')",
            default => "DATE_FORMAT(invoices.created_at, '%Y-%m')",
        };
    }
}
