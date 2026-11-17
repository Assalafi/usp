<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;

/** Read-only gateway checks. Only the caller may settle an owned invoice. */
class RemitaVerificationService
{
    private $http;

    public function __construct(Factory $http)
    {
        $this->http = $http;
    }

    public function verify(object $invoice, string $baseUrl, string $merchantId, string $apiKey): array
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $merchantId = trim($merchantId);
        $apiKey = trim($apiKey);
        if ($merchantId === '' || $apiKey === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL)
            || parse_url($baseUrl, PHP_URL_SCHEME) !== 'https') {
            return ['state' => 'unavailable', 'reason' => 'configuration', 'attempts' => []];
        }

        $rrr = trim((string) $invoice->rrr);
        $orderId = trim((string) ($invoice->orderId ?? ''));
        $targets = [[$rrr, 'status.reg']];
        if ($orderId !== '') {
            $targets[] = [$orderId, 'orderstatus.reg'];
        }
        $observed = null;
        $attempts = [];

        // At most four requests (20 seconds total network timeout). A real
        // pending response survives an unavailable alternate endpoint.
        for ($round = 0; $round < 2; $round++) {
            if ($round > 0) {
                usleep(200000);
            }
            foreach ($targets as [$reference, $endpoint]) {
                $hash = hash('sha512', $reference . $apiKey . $merchantId);
                $url = $baseUrl . '/remita/exapp/api/v1/send/api/echannelsvc/'
                    . rawurlencode($merchantId) . '/' . rawurlencode($reference) . '/' . $hash . '/' . $endpoint;
                try {
                    $response = $this->http->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $hash,
                    ])->timeout(5)->withOptions(['connect_timeout' => 3, 'allow_redirects' => false])->get($url);
                } catch (ConnectionException $e) {
                    $attempts[] = ['endpoint' => $endpoint, 'http' => 0];
                    continue;
                }

                $attempts[] = ['endpoint' => $endpoint, 'http' => $response->status()];
                if ($response->status() !== 200) {
                    continue;
                }
                $payload = $response->json();
                if (!is_array($payload) || !is_scalar($payload['status'] ?? null)) {
                    continue;
                }
                $status = trim((string) $payload['status']);
                if (!in_array($status, ['00', '01', '021', '023'], true)) {
                    continue;
                }
                $returnedRrr = $payload['rrr'] ?? $payload['RRR'] ?? null;
                $returnedOrder = $payload['orderId'] ?? null;
                if (($returnedRrr !== null && (!is_scalar($returnedRrr) || trim((string) $returnedRrr) !== $rrr))
                    || ($returnedOrder !== null && $orderId !== ''
                        && (!is_scalar($returnedOrder) || trim((string) $returnedOrder) !== $orderId))) {
                    return ['state' => 'review', 'reason' => 'reference_mismatch', 'attempts' => $attempts];
                }

                if (in_array($status, ['00', '01'], true)) {
                    // Success must positively identify this RRR and match the
                    // original billed total, including any recorded surcharge.
                    if ($returnedRrr === null) {
                        continue;
                    }
                    if (!is_numeric($payload['amount'] ?? null)
                        || !is_numeric($invoice->amount ?? null)
                        || (float) $invoice->amount <= 0
                        || (int) round((float) $payload['amount'] * 100) !== (int) round((float) $invoice->amount * 100)) {
                        return ['state' => 'review', 'reason' => 'amount_mismatch', 'attempts' => $attempts];
                    }
                    if (isset($payload['serviceTypeId']) && !empty($invoice->serviceTypeId)
                        && (string) $payload['serviceTypeId'] !== (string) $invoice->serviceTypeId) {
                        return ['state' => 'review', 'reason' => 'service_mismatch', 'attempts' => $attempts];
                    }
                    return ['state' => 'paid', 'status' => $status, 'attempts' => $attempts];
                }

                if ($status === '021') {
                    $observed = ['state' => 'pending', 'status' => $status];
                } elseif ($observed === null) {
                    $observed = ['state' => 'unavailable', 'reason' => 'reference_not_resolved'];
                }
            }
            // Do not keep retrying a definitive pending response. The alternate
            // endpoint has already had a chance to report a newer settlement.
            if (($observed['state'] ?? null) === 'pending') {
                break;
            }
        }

        return array_merge($observed ?? ['state' => 'unavailable', 'reason' => 'gateway_unavailable'], ['attempts' => $attempts]);
    }
}
