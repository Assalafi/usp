<?php

namespace App\Http\Controllers;

use App\Services\FinanceSummaryAggregationService;
use App\Exports\UnpaidStudentsExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceLiveSummaryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorise();
        $sessions = DB::table('session')->select('title', 'status')->whereNotNull('title')->where('title', '<>', '')->orderByDesc('title')->get();
        $selectedSession = \App\Support\FinanceAcademicSession::resolve($request->query('session'));
        // Render immediately from the local ledger. Remote systems are pulled
        // by the first background sync so a slow source cannot delay the page.
        return view('main', [
            'page' => 'remita finance live',
            'title' => 'Live Finance Summary',
            'financeSessions' => $sessions,
            'selectedFinanceSession' => $selectedSession,
        ]);
    }

    public function data(Request $request)
    {
        $this->authorise();
        $selectedSession = \App\Support\FinanceAcademicSession::resolve($request->query('session'));
        $request->validate(['session' => 'nullable|string|max:20']);
        $payload = Cache::remember('finance_live_react_v1:' . md5($selectedSession), 8, fn () => $this->payload(true, $selectedSession));
        return response()->json($payload)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function authorise(): void
    {
        if (! session()->has('log') || ! in_array(session('accType'), ['Admin', 'Staff'], true)) {
            abort(403, 'You do not have permission to view live finance summaries.');
        }
    }

    private function payload(bool $includeRemote = true, ?string $selectedSession = null): array
    {
        $selectedSession = \App\Support\FinanceAcademicSession::resolve($selectedSession);
        $sessionFilter = $selectedSession;
        $now = now();
        $from = null;
        $to = null;

        $surcharge = 'COALESCE(CAST(invoices.surcharge_amount AS DECIMAL(15,2)), 0)';
        $base = 'COALESCE(CAST(invoices.base_amount AS DECIMAL(15,2)), GREATEST(CAST(invoices.amount AS DECIMAL(15,2)) - ' . $surcharge . ', 0))';
        $gross = $base;
        $query = DB::table('invoices')->where('invoices.status', 'Paid')
            ->whereRaw("UPPER(COALESCE(invoices.description, '')) NOT LIKE ?", ['%REFUND%']);
        if ($sessionFilter) {
            $query->where('invoices.session', $sessionFilter);
        }

        $summary = (clone $query)->selectRaw('COUNT(*) AS payment_count, COALESCE(SUM(' . $gross . '),0) AS gross_amount')->first();
        $statusRows = (clone $query)->select('invoices.status')->selectRaw('COUNT(*) AS payment_count, COALESCE(SUM(' . $gross . '),0) AS gross_amount')->groupBy('invoices.status')->get();
        $serviceRows = (clone $query)->select('invoices.description')->selectRaw('COUNT(*) AS payment_count, COALESCE(SUM(' . $gross . '),0) AS gross_amount')->groupBy('invoices.description')->orderByDesc('gross_amount')->get();
        $trendRows = collect();
        $lastLocal = (clone $query)->orderByDesc('invoices.created_at')->value('invoices.created_at');

        $localStatus = $this->normaliseStatuses($statusRows->map(fn ($row) => [
            'status' => (string) ($row->status ?: 'unknown'),
            'payment_count' => (int) $row->payment_count,
            'gross_amount' => (float) $row->gross_amount,
        ])->all());
        $localTotals = $this->statusTotals($localStatus);
        $localSource = [
            'source' => 'ug', 'label' => 'Undergraduate', 'status' => 'ok', 'error' => null,
            'count' => (int) ($summary->payment_count ?? 0),
            'gross_amount' => (float) ($summary->gross_amount ?? 0),
            'paid_amount' => $localTotals['paid_amount'], 'paid_count' => $localTotals['paid_count'],
            'pending_amount' => $localTotals['pending_amount'], 'pending_count' => $localTotals['pending_count'],
            'cancelled_amount' => $localTotals['cancelled_amount'], 'cancelled_count' => $localTotals['cancelled_count'],
            'last_payment_at' => $lastLocal ? Carbon::parse($lastLocal)->toIso8601String() : null,
            'services' => $serviceRows->map(fn ($row) => [
                'label' => (string) ($row->description ?: 'Payment'),
                'invoice_count' => (int) $row->payment_count,
                'gross_amount' => (float) $row->gross_amount,
            ])->values()->all(),
        ];

        $schoolFeesSession = SystemSettingsController::getSchoolFeesSession();
        $debtSession = $sessionFilter ?: $schoolFeesSession;
        $debtLevels = Cache::remember('live_finance_ug_debt_levels_' . md5($debtSession), 20, fn () => UnpaidStudentsExport::summaryByLevel($debtSession));
        $debt = [
            'students' => (int) collect($debtLevels)->sum('students'),
            'outstanding_amount' => (float) collect($debtLevels)->sum('outstanding_amount'),
        ];

        $remote = $includeRemote
            ? app(FinanceSummaryAggregationService::class)->collect($from, $to, $sessionFilter)
            : ['summary' => ['invoice_count' => 0, 'gross_amount' => 0], 'status_summary' => [], 'service_summary' => [], 'trend' => [], 'sources' => collect((array) config('services.finance_summary.sources', []))->map(fn ($source, $key) => ['source' => (string) $key, 'label' => (string) ($source['label'] ?? ucfirst((string) $key)), 'status' => 'syncing', 'error' => 'Waiting for the first source sync.'])->values()->all()];
        $status = $this->normaliseStatuses(array_merge($localStatus, (array) ($remote['status_summary'] ?? [])));
        $services = $this->mergeServices($serviceRows->map(fn ($row) => [
            'label' => (string) ($row->description ?: 'Payment'),
            'payment_count' => (int) $row->payment_count,
            'gross_amount' => (float) $row->gross_amount,
        ])->all(), (array) ($remote['service_summary'] ?? []));
        $trend = $this->mergeTrend($trendRows->map(fn ($row) => [
            'bucket' => (string) $row->bucket,
            'payment_count' => (int) $row->payment_count,
            'gross_amount' => (float) $row->gross_amount,
        ])->all(), (array) ($remote['trend'] ?? []));

        $remoteSources = collect((array) ($remote['sources'] ?? []))->map(function ($source) {
            return [
                'source' => (string) ($source['source'] ?? 'source'),
                'label' => (string) ($source['label'] ?? 'External system'),
                'status' => (string) ($source['status'] ?? 'unavailable'),
                'error' => $source['error'] ?? null,
                'count' => (int) ($source['count'] ?? 0),
                'gross_amount' => (float) ($source['gross_amount'] ?? 0),
                'paid_amount' => (float) ($source['paid_amount'] ?? 0), 'paid_count' => (int) ($source['paid_count'] ?? 0),
                'pending_amount' => (float) ($source['pending_amount'] ?? 0), 'pending_count' => (int) ($source['pending_count'] ?? 0),
                'cancelled_amount' => (float) ($source['cancelled_amount'] ?? 0), 'cancelled_count' => (int) ($source['cancelled_count'] ?? 0),
                'last_payment_at' => $source['last_payment_at'] ?? null,
                'services' => (array) ($source['services'] ?? []),
            ];
        })->values()->all();
        $systems = array_merge([$localSource], $remoteSources);

        $statusTotals = $this->statusTotals($status);
        $lastPayment = collect($systems)->pluck('last_payment_at')->filter()->sortDesc()->first();
        $remoteGross = (float) (($remote['summary']['gross_amount'] ?? 0));

        return [
            'success' => true,
            'status' => 'live',
            'updated_at' => now()->toIso8601String(),
            'period' => ['label' => $sessionFilter ? 'Session ' . $sessionFilter : 'All sessions', 'from' => null, 'to' => null, 'session' => $selectedSession],
            'totals' => [
                'gross_amount' => (float) ($summary->gross_amount ?? 0) + $remoteGross,
                'payment_count' => (int) ($summary->payment_count ?? 0) + (int) ($remote['summary']['invoice_count'] ?? 0),
                'paid_amount' => $statusTotals['paid_amount'], 'paid_count' => $statusTotals['paid_count'],
                'ug_debt_amount' => (float) ($debt['outstanding_amount'] ?? 0),
                'ug_debt_students' => (int) ($debt['students'] ?? 0),
                'last_payment_at' => $lastPayment,
            ],
            'debt' => ['session' => $debtSession, 'amount' => (float) ($debt['outstanding_amount'] ?? 0), 'students' => (int) ($debt['students'] ?? 0)],
            'debt_levels' => $debtLevels,
            'systems' => $systems,
            'status_summary' => $status,
            'service_summary' => $services,
            'trend' => $trend,
        ];
    }

    private function normaliseStatuses(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $raw = strtolower(trim((string) ($row['status'] ?? 'unknown')));
            $label = in_array($raw, ['paid', 'successful', 'success'], true) ? 'Paid' : (in_array($raw, ['pending', 'processing'], true) ? 'Pending' : (in_array($raw, ['failed', 'cancelled', 'canceled', 'rejected'], true) ? 'Failed / cancelled' : 'Other'));
            $groups[$label]['status'] = $label;
            $groups[$label]['payment_count'] = ($groups[$label]['payment_count'] ?? 0) + (int) ($row['payment_count'] ?? $row['invoice_count'] ?? 0);
            $groups[$label]['gross_amount'] = ($groups[$label]['gross_amount'] ?? 0) + (float) ($row['gross_amount'] ?? 0);
        }
        return collect($groups)->values()->sortByDesc('gross_amount')->values()->all();
    }

    private function statusTotals(array $rows): array
    {
        $totals = ['paid_amount' => 0.0, 'paid_count' => 0, 'pending_amount' => 0.0, 'pending_count' => 0, 'cancelled_amount' => 0.0, 'cancelled_count' => 0];
        foreach ($rows as $row) {
            $key = match ((string) ($row['status'] ?? '')) { 'Paid' => 'paid', 'Pending' => 'pending', 'Failed / cancelled' => 'cancelled', default => null };
            if ($key) { $totals[$key . '_amount'] += (float) ($row['gross_amount'] ?? 0); $totals[$key . '_count'] += (int) ($row['payment_count'] ?? 0); }
        }
        return $totals;
    }

    private function mergeServices(array $local, array $remote): array
    {
        $groups = [];
        foreach (array_merge($local, $remote) as $row) {
            $key = trim((string) ($row['label'] ?? 'Payment')) ?: 'Payment';
            $groups[$key] = ['label' => $key, 'payment_count' => ($groups[$key]['payment_count'] ?? 0) + (int) ($row['payment_count'] ?? $row['invoice_count'] ?? 0), 'gross_amount' => ($groups[$key]['gross_amount'] ?? 0) + (float) ($row['gross_amount'] ?? 0)];
        }
        return collect($groups)->sortByDesc('gross_amount')->take(8)->values()->all();
    }

    private function mergeTrend(array $local, array $remote): array
    {
        $groups = [];
        foreach (array_merge($local, $remote) as $row) {
            $key = (string) ($row['bucket'] ?? '');
            if ($key === '') continue;
            $groups[$key] = ['bucket' => $key, 'payment_count' => ($groups[$key]['payment_count'] ?? 0) + (int) ($row['payment_count'] ?? $row['invoice_count'] ?? 0), 'gross_amount' => ($groups[$key]['gross_amount'] ?? 0) + (float) ($row['gross_amount'] ?? 0)];
        }
        return collect($groups)->sortBy('bucket')->values()->all();
    }
}
