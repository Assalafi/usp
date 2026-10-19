<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            'status' => trim((string) $request->get('status', '')),
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

        $summary = (clone $baseQuery)->selectRaw(
            'COUNT(*) AS invoice_count,
             COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) AS gross_amount,
             COALESCE(SUM(COALESCE(base_amount, CAST(amount AS DECIMAL(15,2)) - COALESCE(surcharge_amount, 0))), 0) AS base_amount,
             COALESCE(SUM(COALESCE(surcharge_amount, 0)), 0) AS surcharge_amount'
        )->first();

        $statusSummary = (clone $baseQuery)
            ->select('status')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) AS gross_amount')
            ->groupBy('status')
            ->orderByDesc('gross_amount')
            ->get();

        $serviceSummary = (clone $baseQuery)
            ->select('description')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) AS gross_amount, COALESCE(SUM(surcharge_amount), 0) AS surcharge_amount')
            ->groupBy('description')
            ->orderByDesc('gross_amount')
            ->get();

        $allocationSummary = DB::table('invoice_split_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_split_items.invoice_id')
            ->where('invoices.split_enabled', 1);
        $this->applyFilters($allocationSummary, $filters, $serviceFilters, $dateFrom, $dateTo, 'invoices');
        $allocationSummary = $allocationSummary
            ->select('invoice_split_items.beneficiary_name', 'invoice_split_items.is_primary')
            ->selectRaw('COUNT(DISTINCT invoice_split_items.invoice_id) AS invoice_count, COALESCE(SUM(invoice_split_items.beneficiary_amount), 0) AS allocated_amount')
            ->groupBy('invoice_split_items.beneficiary_name', 'invoice_split_items.is_primary')
            ->orderByDesc('allocated_amount')
            ->get();

        $paid = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['paid', 'successful', 'success'], true))->sum('gross_amount');
        $pending = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['pending', 'processing'], true))->sum('gross_amount');
        $cancelled = $statusSummary->filter(fn ($row) => in_array(strtolower((string) $row->status), ['cancelled', 'canceled', 'failed', 'rejected'], true))->sum('gross_amount');
        $allocatedTotal = (float) $allocationSummary->sum('allocated_amount');

        $groupExpression = $this->groupExpression($period);
        $trend = (clone $baseQuery)
            ->selectRaw($groupExpression . ' AS bucket')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) AS gross_amount, COALESCE(SUM(surcharge_amount), 0) AS surcharge_amount')
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
            ->orderByDesc('invoices.created_at')
            ->orderByDesc('invoices.id')
            ->paginate(25)
            ->withQueryString();

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
            'trend' => $trend,
            'rows' => $rows,
            'faculties' => $faculties,
            'departments' => $departments,
            'programs' => $programs,
            'sessions' => $sessions,
        ]);
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
