<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

/**
 * Read-only institution finance summary. This service deliberately has no
 * split-payment or beneficiary logic: every figure is based on the base
 * payment amount returned by each system.
 */
class FinanceSummaryAggregationService
{
    public function collect(?Carbon $from, ?Carbon $to, ?string $session = null): array
    {
        $result = [
            'sources' => [],
            'summary' => ['invoice_count' => 0, 'gross_amount' => 0.0],
            'status_summary' => [],
            'service_summary' => [],
            'trend' => [],
        ];

        // Queue all schools on one HTTP handler before waiting for any response.
        // Failures are handled per school, so a timeout never hides healthy schools.
        $sources = (array) config('services.finance_summary.sources', []);
        $pool = new Pool(Http::getFacadeRoot());
        foreach ($sources as $key => $source) {
            $url = trim((string) ($source['url'] ?? ''));
            $apiKey = trim((string) ($source['api_key'] ?? ''));
            if ($url === '' || $apiKey === '') continue;
            try {
                $pool->as((string) $key)->timeout(6)->withOptions(['connect_timeout' => 2])
                    ->withHeaders(['Accept' => 'application/json', 'X-API-Key' => $apiKey])
                    ->get($url, array_filter([
                        'date_from' => $from?->toDateString(), 'date_to' => $to?->toDateString(),
                        'status' => 'Paid', 'scope' => 'base', 'limit' => 250, 'session' => $session,
                    ], static fn ($value) => $value !== null && $value !== ''));
            } catch (\Throwable $exception) {
                // The missing or rejected promise is reported below as unavailable.
            }
        }
        $requests = $pool->getRequests();

        foreach ($sources as $key => $source) {
            $label = (string) ($source['label'] ?? ucfirst((string) $key));
            $url = trim((string) ($source['url'] ?? ''));
            $apiKey = trim((string) ($source['api_key'] ?? ''));
            $meta = [
                'source' => (string) $key, 'label' => $label, 'status' => 'unavailable', 'error' => null,
                'count' => 0, 'gross_amount' => 0.0, 'paid_amount' => 0.0, 'paid_count' => 0,
                'pending_amount' => 0.0, 'pending_count' => 0, 'cancelled_amount' => 0.0,
                'cancelled_count' => 0, 'last_payment_at' => null, 'services' => [],
            ];
            if ($url === '' || $apiKey === '') {
                $meta['error'] = 'Finance source is not configured.';
                $result['sources'][] = $meta;
                continue;
            }

            try {
                $response = isset($requests[$key]) ? $requests[$key]->getPromise()?->wait() : null;
                if (! $response instanceof \Illuminate\Http\Client\Response) {
                    throw new \RuntimeException('School response unavailable.');
                }

                if (! $response->successful() || ! $response->json('success')) {
                    $meta['error'] = 'Finance source returned HTTP ' . $response->status() . '.';
                    $result['sources'][] = $meta;
                    continue;
                }
                $payload = (array) $response->json();
                $summary = (array) ($payload['summary'] ?? []);
                $meta['status'] = 'ok';
                $meta['count'] = (int) ($summary['invoice_count'] ?? 0);
                $meta['gross_amount'] = (float) ($summary['gross_amount'] ?? 0);
                $meta['last_payment_at'] = collect((array) ($payload['rows'] ?? []))->pluck('created_at')->filter()->sortDesc()->first();
                $meta['services'] = collect((array) ($payload['service_summary'] ?? []))->map(fn ($row) => [
                    'label' => (string) ($row['label'] ?? 'Payment'),
                    'invoice_count' => (int) ($row['invoice_count'] ?? 0),
                    'gross_amount' => (float) ($row['gross_amount'] ?? 0),
                ])->values()->all();
                foreach ((array) ($payload['status_summary'] ?? []) as $row) {
                    $status = strtolower((string) ($row['status'] ?? ''));
                    $amount = (float) ($row['gross_amount'] ?? 0);
                    $count = (int) ($row['invoice_count'] ?? 0);
                    if (in_array($status, ['paid', 'successful', 'success'], true)) { $meta['paid_amount'] += $amount; $meta['paid_count'] += $count; }
                    elseif (in_array($status, ['pending', 'processing'], true)) { $meta['pending_amount'] += $amount; $meta['pending_count'] += $count; }
                    elseif (in_array($status, ['failed', 'cancelled', 'canceled', 'rejected'], true)) { $meta['cancelled_amount'] += $amount; $meta['cancelled_count'] += $count; }
                }
                $result['summary']['invoice_count'] += $meta['count'];
                $result['summary']['gross_amount'] += $meta['gross_amount'];
                $result['sources'][] = $meta;
                $this->merge($result['status_summary'], (array) ($payload['status_summary'] ?? []), 'status');
                $this->merge($result['service_summary'], (array) ($payload['service_summary'] ?? []), 'label');
                $this->merge($result['trend'], (array) ($payload['trend'] ?? []), 'bucket');
            } catch (\Throwable $exception) {
                $meta['error'] = 'Unable to reach finance source.';
                $result['sources'][] = $meta;
            }
        }

        return $result;
    }

    private function merge(array &$target, array $rows, string $keyField): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) continue;
            $key = (string) ($row[$keyField] ?? 'Unknown');
            $found = null;
            foreach ($target as $index => $item) if ((string) ($item[$keyField] ?? '') === $key) { $found = $index; break; }
            if ($found === null) {
                $target[] = [$keyField => $key, 'invoice_count' => (int) ($row['invoice_count'] ?? 0), 'gross_amount' => (float) ($row['gross_amount'] ?? 0)];
                continue;
            }
            $target[$found]['invoice_count'] += (int) ($row['invoice_count'] ?? 0);
            $target[$found]['gross_amount'] += (float) ($row['gross_amount'] ?? 0);
        }
    }
}
