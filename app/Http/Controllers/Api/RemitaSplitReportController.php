<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Services\RemitaSplitAggregationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemitaSplitReportController
{
    public function summary(Request $request)
    {
        $from = $this->date($request->query('date_from'));
        $to = $this->date($request->query('date_to'));
        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $status = strtolower(trim((string) $request->query('status', '')));
        $query = DB::table('invoices')->where('invoices.split_enabled', 1);
        if ($from) {
            $query->where('invoices.created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $query->where('invoices.created_at', '<', $to->copy()->addDay()->startOfDay());
        }
        if ($status !== '') {
            $query->whereIn('invoices.status', $this->statuses($status));
        }
        if (($rrr = trim((string) $request->query('rrr', ''))) !== '') {
            $query->where(function ($nested) use ($rrr) {
                $nested->where('invoices.rrr', 'like', '%' . $rrr . '%')
                    ->orWhere('invoices.orderId', 'like', '%' . $rrr . '%');
            });
        }

        $gross = 'COALESCE(CAST(invoices.amount AS DECIMAL(15,2)), 0)';
        $base = 'COALESCE(CAST(invoices.base_amount AS DECIMAL(15,2)), GREATEST(CAST(invoices.amount AS DECIMAL(15,2)) - COALESCE(CAST(invoices.surcharge_amount AS DECIMAL(15,2)), 0), 0))';
        $surcharge = 'COALESCE(CAST(invoices.surcharge_amount AS DECIMAL(15,2)), GREATEST(CAST(invoices.amount AS DECIMAL(15,2)) - COALESCE(CAST(invoices.base_amount AS DECIMAL(15,2)), 0), 0))';

        $summary = (clone $query)->selectRaw(
            'COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount,
             COALESCE(SUM(' . $base . '), 0) AS base_amount,
             COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount'
        )->first();

        $statusSummary = (clone $query)
            ->select('invoices.status')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount')
            ->groupBy('invoices.status')
            ->orderByDesc('gross_amount')
            ->get();

        $serviceSummary = (clone $query)
            ->select('invoices.description')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount, COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount')
            ->groupBy('invoices.description')
            ->orderByDesc('gross_amount')
            ->limit(100)
            ->get();

        $allocationQuery = DB::table('invoice_split_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_split_items.invoice_id')
            ->where('invoices.split_enabled', 1);
        if ($from) {
            $allocationQuery->where('invoices.created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $allocationQuery->where('invoices.created_at', '<', $to->copy()->addDay()->startOfDay());
        }
        if ($status !== '') {
            $allocationQuery->whereIn('invoices.status', $this->statuses($status));
        }
        if ($rrr !== '') {
            $allocationQuery->where(function ($nested) use ($rrr) {
                $nested->where('invoices.rrr', 'like', '%' . $rrr . '%')
                    ->orWhere('invoices.orderId', 'like', '%' . $rrr . '%');
            });
        }
        $allocationSummary = $allocationQuery
            ->select('invoice_split_items.beneficiary_name')
            ->selectRaw('COUNT(DISTINCT invoice_split_items.invoice_id) AS invoice_count, COALESCE(SUM(invoice_split_items.beneficiary_amount), 0) AS allocated_amount')
            ->groupBy('invoice_split_items.beneficiary_name')
            ->orderByDesc('allocated_amount')
            ->get();

        $trend = (clone $query)
            ->selectRaw("DATE_FORMAT(invoices.created_at, '%Y-%m') AS bucket")
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount, COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $limit = min(250, max(1, (int) $request->query('limit', 100)));
        $rows = (clone $query)
            ->select([
                'invoices.id', 'invoices.rrr', 'invoices.orderId', 'invoices.description',
                'invoices.status', 'invoices.amount', 'invoices.base_amount',
                'invoices.surcharge_amount', 'invoices.created_at', 'invoices.username',
            ])
            ->selectRaw($gross . ' AS gross_amount')
            ->selectRaw($base . ' AS base_amount_normalized')
            ->selectRaw($surcharge . ' AS surcharge_amount_normalized')
            ->orderByDesc('invoices.created_at')
            ->orderByDesc('invoices.id')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (string) $row->id,
                'reference' => (string) ($row->rrr ?: $row->orderId ?: ''),
                'order_reference' => (string) ($row->orderId ?: ''),
                'student_id' => (string) ($row->username ?: ''),
                'description' => (string) ($row->description ?: 'Payment'),
                'status' => (string) ($row->status ?: 'unknown'),
                'gross_amount' => (float) $row->gross_amount,
                'base_amount' => (float) $row->base_amount_normalized,
                'surcharge_amount' => (float) $row->surcharge_amount_normalized,
                'created_at' => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
            ])->values();

        // Keep this endpoint useful to integrations as well as the admin
        // page: `summary` remains the UG source payload, while `overall`
        // combines it with every configured source API.
        $remote = app(RemitaSplitAggregationService::class)->collect($from, $to, [
            'status' => $status,
            'rrr' => $rrr,
        ]);
        $remoteSummary = (array) ($remote['summary'] ?? []);
        $overallSummary = [
            'invoice_count' => (int) ($summary->invoice_count ?? 0) + (int) ($remoteSummary['invoice_count'] ?? 0),
            'gross_amount' => (float) ($summary->gross_amount ?? 0) + (float) ($remoteSummary['gross_amount'] ?? 0),
            'base_amount' => (float) ($summary->base_amount ?? 0) + (float) ($remoteSummary['base_amount'] ?? 0),
            'surcharge_amount' => (float) ($summary->surcharge_amount ?? 0) + (float) ($remoteSummary['surcharge_amount'] ?? 0),
            'allocated_amount' => (float) $allocationSummary->sum('allocated_amount') + (float) collect($remote['allocation_summary'] ?? [])->sum('allocated_amount'),
        ];

        return response()->json([
            'success' => true,
            'source' => (string) config('services.remita_split_report.source', 'ug'),
            'label' => 'Undergraduate',
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'invoice_count' => (int) ($summary->invoice_count ?? 0),
                'gross_amount' => (float) ($summary->gross_amount ?? 0),
                'base_amount' => (float) ($summary->base_amount ?? 0),
                'surcharge_amount' => (float) ($summary->surcharge_amount ?? 0),
            ],
            'overall' => [
                'summary' => $overallSummary,
                'sources' => collect([
                    ['source' => 'ug', 'label' => 'Undergraduate', 'status' => 'ok', 'count' => (int) ($summary->invoice_count ?? 0), 'gross_amount' => (float) ($summary->gross_amount ?? 0), 'error' => null],
                ])->merge($remote['sources'] ?? [])->values(),
                'status_summary' => $statusSummary->map(fn ($row) => [
                    'status' => (string) ($row->status ?: 'unknown'),
                    'invoice_count' => (int) $row->invoice_count,
                    'gross_amount' => (float) $row->gross_amount,
                    'source' => 'ug',
                ])->merge($remote['status_summary'] ?? [])->values(),
                'service_summary' => $serviceSummary->map(fn ($row) => [
                    'label' => (string) ($row->description ?: 'Unnamed service'),
                    'invoice_count' => (int) $row->invoice_count,
                    'gross_amount' => (float) $row->gross_amount,
                    'surcharge_amount' => (float) $row->surcharge_amount,
                    'source' => 'ug',
                ])->merge($remote['service_summary'] ?? [])->values(),
                'allocation_summary' => $allocationSummary->map(fn ($row) => [
                    'beneficiary_name' => (string) ($row->beneficiary_name ?: 'Beneficiary'),
                    'invoice_count' => (int) $row->invoice_count,
                    'allocated_amount' => (float) $row->allocated_amount,
                    'source' => 'ug',
                ])->merge($remote['allocation_summary'] ?? [])->values(),
                'trend' => $trend->map(fn ($row) => [
                    'bucket' => (string) $row->bucket,
                    'invoice_count' => (int) $row->invoice_count,
                    'gross_amount' => (float) $row->gross_amount,
                    'surcharge_amount' => (float) $row->surcharge_amount,
                    'source' => 'ug',
                ])->merge($remote['trend'] ?? [])->values(),
                'rows' => collect($rows->all())->map(fn ($row) => array_merge($row, ['source' => 'ug', 'source_label' => 'Undergraduate']))->merge($remote['rows'] ?? [])->values(),
            ],
            'status_summary' => $statusSummary->map(fn ($row) => [
                'status' => (string) ($row->status ?: 'unknown'),
                'invoice_count' => (int) $row->invoice_count,
                'gross_amount' => (float) $row->gross_amount,
            ])->values(),
            'service_summary' => $serviceSummary->map(fn ($row) => [
                'label' => (string) ($row->description ?: 'Unnamed service'),
                'invoice_count' => (int) $row->invoice_count,
                'gross_amount' => (float) $row->gross_amount,
                'surcharge_amount' => (float) $row->surcharge_amount,
            ])->values(),
            'allocation_summary' => $allocationSummary->map(fn ($row) => [
                'beneficiary_name' => (string) ($row->beneficiary_name ?: 'Beneficiary'),
                'invoice_count' => (int) $row->invoice_count,
                'allocated_amount' => (float) $row->allocated_amount,
            ])->values(),
            'trend' => $trend->map(fn ($row) => [
                'bucket' => (string) $row->bucket,
                'invoice_count' => (int) $row->invoice_count,
                'gross_amount' => (float) $row->gross_amount,
                'surcharge_amount' => (float) $row->surcharge_amount,
            ])->values(),
            'rows' => $rows,
        ]);
    }

    private function date($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfDay() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function statuses(string $status): array
    {
        return match ($status) {
            'paid', 'successful', 'success' => ['paid', 'successful', 'success'],
            'pending', 'processing' => ['pending', 'processing'],
            'cancelled', 'canceled', 'failed', 'rejected' => ['cancelled', 'canceled', 'failed', 'rejected'],
            default => [$status],
        };
    }
}
