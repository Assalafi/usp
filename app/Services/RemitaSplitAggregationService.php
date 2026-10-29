<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class RemitaSplitAggregationService
{
    public function collect(?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $result = [
            'sources' => [],
            'rows' => [],
            'summary' => ['invoice_count' => 0, 'gross_amount' => 0.0, 'base_amount' => 0.0, 'surcharge_amount' => 0.0],
            'status_summary' => [],
            'service_summary' => [],
            'allocation_summary' => [],
            'trend' => [],
            'paid' => 0.0,
            'pending' => 0.0,
            'cancelled' => 0.0,
        ];

        if (! config('services.remita_aggregate.enabled', true)) {
            return $result;
        }

        // Faculty/department/programme and sponsor filters only exist in the
        // undergraduate schema. Do not silently add unfiltered PG/SBRS/
        // Institute money to a narrowly filtered UG report.
        $ugOnlyFilters = ['service', 'faculty', 'department', 'program', 'level', 'session', 'sponsor', 'student_id'];
        $hasUgOnlyFilter = collect($ugOnlyFilters)->contains(static fn (string $key): bool => trim((string) ($filters[$key] ?? '')) !== '');
        if ($hasUgOnlyFilter) {
            foreach ((array) config('services.remita_aggregate.sources', []) as $key => $source) {
                $result['sources'][] = [
                    'source' => (string) $key,
                    'label' => (string) ($source['label'] ?? ucfirst((string) $key)),
                    'status' => 'filtered',
                    'count' => 0,
                    'gross_amount' => 0.0,
                    'error' => 'Excluded because the selected academic filter is only available in Undergraduate records.',
                ];
            }

            return $result;
        }

        $query = array_filter([
            'date_from' => $from?->toDateString(),
            'date_to' => $to?->toDateString(),
            'status' => trim((string) ($filters['status'] ?? '')),
            'rrr' => trim((string) ($filters['rrr'] ?? '')),
            'limit' => 250,
        ], static fn ($value) => $value !== null && $value !== '');

        foreach ((array) config('services.remita_aggregate.sources', []) as $key => $source) {
            $label = (string) ($source['label'] ?? ucfirst((string) $key));
            $url = trim((string) ($source['url'] ?? ''));
            $apiKey = trim((string) ($source['api_key'] ?? ''));
            $meta = ['source' => (string) $key, 'label' => $label, 'status' => 'unavailable', 'count' => 0, 'gross_amount' => 0.0, 'error' => null];

            if ($url === '' || $apiKey === '') {
                $meta['error'] = 'API endpoint or key is not configured.';
                $result['sources'][] = $meta;
                continue;
            }

            try {
                $response = Http::timeout(10)
                    ->withOptions(['connect_timeout' => 4])
                    ->retry(1, 200)
                    ->withHeaders(['Accept' => 'application/json', 'X-API-Key' => $apiKey])
                    ->get($url, $query);

                if (! $response->successful() || ! $response->json('success')) {
                    $meta['error'] = 'The source API returned HTTP ' . $response->status() . '.';
                    $result['sources'][] = $meta;
                    continue;
                }

                $payload = (array) $response->json();
                $summary = (array) ($payload['summary'] ?? []);
                $meta['status'] = 'ok';
                $meta['count'] = (int) ($summary['invoice_count'] ?? 0);
                $meta['gross_amount'] = (float) ($summary['gross_amount'] ?? 0);
                $result['sources'][] = $meta;
                $this->addSummary($result['summary'], $summary);
                $this->mergeGrouped($result['status_summary'], (array) ($payload['status_summary'] ?? []), 'status', 'gross_amount');
                $this->mergeGrouped($result['service_summary'], (array) ($payload['service_summary'] ?? []), 'label', 'gross_amount');
                $this->mergeGrouped($result['allocation_summary'], (array) ($payload['allocation_summary'] ?? []), 'beneficiary_name', 'allocated_amount');
                $this->mergeGrouped($result['trend'], (array) ($payload['trend'] ?? []), 'bucket', 'gross_amount');

                foreach ((array) ($payload['rows'] ?? []) as $row) {
                    $row['source'] = (string) $key;
                    $row['source_label'] = $label;
                    $result['rows'][] = $row;
                }
            } catch (\Throwable $exception) {
                $meta['error'] = 'Unable to reach the source API.';
                $result['sources'][] = $meta;
            }
        }

        foreach ($result['status_summary'] as $row) {
            $status = strtolower((string) ($row['status'] ?? ''));
            if (in_array($status, ['paid', 'successful', 'success'], true)) $result['paid'] += (float) ($row['gross_amount'] ?? 0);
            elseif (in_array($status, ['pending', 'processing'], true)) $result['pending'] += (float) ($row['gross_amount'] ?? 0);
            elseif (in_array($status, ['cancelled', 'canceled', 'failed', 'rejected'], true)) $result['cancelled'] += (float) ($row['gross_amount'] ?? 0);
        }

        usort($result['rows'], static fn ($left, $right) => strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? '')));
        return $result;
    }

    private function addSummary(array &$target, array $source): void
    {
        foreach (['invoice_count', 'gross_amount', 'base_amount', 'surcharge_amount'] as $key) {
            $target[$key] += (float) ($source[$key] ?? 0);
        }
        $target['invoice_count'] = (int) $target['invoice_count'];
    }

    private function mergeGrouped(array &$target, array $rows, string $keyField, string $amountField): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = (string) ($row[$keyField] ?? 'Unknown');
            $index = null;
            foreach ($target as $candidateIndex => $candidate) {
                if ((string) ($candidate[$keyField] ?? '') === $key) {
                    $index = $candidateIndex;
                    break;
                }
            }

            if ($index === null) {
                $target[] = array_merge($row, [
                    $keyField => $key,
                    'invoice_count' => (int) ($row['invoice_count'] ?? 0),
                    $amountField => (float) ($row[$amountField] ?? 0),
                    'surcharge_amount' => (float) ($row['surcharge_amount'] ?? 0),
                ]);
                continue;
            }

            $target[$index]['invoice_count'] = (int) ($target[$index]['invoice_count'] ?? 0) + (int) ($row['invoice_count'] ?? 0);
            $target[$index][$amountField] = (float) ($target[$index][$amountField] ?? 0) + (float) ($row[$amountField] ?? 0);
            $target[$index]['surcharge_amount'] = (float) ($target[$index]['surcharge_amount'] ?? 0) + (float) ($row['surcharge_amount'] ?? 0);
        }
    }
}
