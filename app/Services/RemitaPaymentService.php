<?php

namespace App\Services;

use App\Http\Controllers\SystemSettingsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Builds and submits Remita payment-initialisation requests.
 *
 * The amount configured for a service is the base charge. When split payments
 * are enabled for that service, its service-specific surcharge is added to the
 * total and the surcharge is distributed across the configured beneficiaries.
 * The base charge remains with the primary beneficiary.
 */
class RemitaPaymentService
{
    public static function prepare(
        string $serviceKey,
        $baseAmount,
        string $orderId,
        array $payer,
        string $description,
        array $customFields = []
    ): array {
        $baseKobo = self::toKobo($baseAmount);
        if ($baseKobo < 0) {
            throw new \InvalidArgumentException('Payment amount cannot be negative.');
        }

        $serviceTypeId = SystemSettingsController::getRemitaServiceType($serviceKey, '');
        if ($serviceTypeId === '') {
            throw new \InvalidArgumentException('No Remita service type is configured for this payment.');
        }

        $catalog = SystemSettingsController::getRemitaServiceCatalog();
        $service = collect($catalog)->firstWhere('key', $serviceKey) ?: [];
        if ($service !== [] && array_key_exists('active', $service) && !$service['active']) {
            throw new \InvalidArgumentException('This payment service is currently unavailable.');
        }
        $splitSettings = SystemSettingsController::getRemitaSplitSettings();
        $splitEnabled = (bool) ($splitSettings['enabled'] ?? false)
            && (bool) ($service['split_enabled'] ?? false);
        $surchargeKobo = $splitEnabled
            ? self::toKobo($service['surcharge'] ?? 0)
            : 0;

        $totalKobo = $baseKobo + $surchargeKobo;
        $payload = [
            'serviceTypeId' => (string) $serviceTypeId,
            'amount' => self::formatKobo($totalKobo),
            'orderId' => $orderId,
            'payerName' => self::cleanText($payer['name'] ?? 'STUDENT'),
            'payerEmail' => self::cleanText($payer['email'] ?? ''),
            'payerPhone' => self::cleanPhone($payer['phone'] ?? ''),
            'description' => self::cleanText($description),
        ];

        $lineItems = [];
        if ($splitEnabled && $surchargeKobo > 0) {
            $lineItems = self::buildLineItems($baseKobo, $surchargeKobo, $splitSettings['beneficiaries'] ?? []);
            $payload['lineItems'] = $lineItems;
        }
        if ($customFields !== []) {
            $payload['customFields'] = array_values($customFields);
        }

        $merchantId = (string) config('services.remita.merchant_id');
        $apiKey = (string) config('services.remita.api_key');
        $amountForHash = self::formatKobo($totalKobo);
        $hash = hash('sha512', $merchantId . $serviceTypeId . $orderId . $amountForHash . $apiKey);

        return [
            'payload' => $payload,
            'hash' => $hash,
            'service_type_id' => (string) $serviceTypeId,
            'base_amount' => self::fromKobo($baseKobo),
            'surcharge_amount' => self::fromKobo($surchargeKobo),
            'total_amount' => self::fromKobo($totalKobo),
            'split_enabled' => $splitEnabled && $surchargeKobo > 0,
            'line_items' => $lineItems,
            'line_item_summary' => array_map(function (array $item): array {
                $account = (string) ($item['beneficiaryAccount'] ?? '');
                return array_merge($item, [
                    'beneficiaryAccount' => $account === '' ? '' : str_repeat('*', max(0, strlen($account) - 4)) . substr($account, -4),
                ]);
            }, $lineItems),
        ];
    }

    /**
     * Submit a prepared payment request and parse JSON/JSONP responses.
     */
    public static function createReference(array $prepared, string $context = 'UG Remita payment'): array
    {
        $configuredBaseUrl = trim((string) SystemSettingsController::getRemitaBaseUrl());
        $merchantId = trim((string) config('services.remita.merchant_id'));
        $apiKey = trim((string) config('services.remita.api_key'));
        if ($merchantId === '' || $apiKey === '') {
            return [
                'success' => false,
                'message' => 'Remita credentials are not configured. Please contact the accounts office.',
                'http_code' => 0,
                'response' => [],
            ];
        }
        if (!filter_var($configuredBaseUrl, FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($configuredBaseUrl, PHP_URL_SCHEME)), ['https', 'http'], true)) {
            return [
                'success' => false,
                'message' => 'The Remita gateway URL is not configured correctly. Please contact the accounts office.',
                'http_code' => 0,
                'response' => [],
            ];
        }

        $baseUrl = rtrim($configuredBaseUrl, '/')
            . '/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit';
        $payload = $prepared['payload'] ?? [];
        $hash = (string) ($prepared['hash'] ?? '');
        if ($hash === '' || empty($payload['serviceTypeId']) || empty($payload['orderId']) || empty($payload['amount'])) {
            return [
                'success' => false,
                'message' => 'The payment request is incomplete. Please try again.',
                'http_code' => 0,
                'response' => [],
            ];
        }
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encodedPayload === false) {
            return [
                'success' => false,
                'message' => 'The payment request could not be prepared. Please try again.',
                'http_code' => 0,
                'response' => [],
            ];
        }
        $rawResponse = false;
        $curlError = null;
        $httpCode = 0;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $curl = curl_init($baseUrl);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 12,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encodedPayload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $hash,
                ],
            ]);
            $rawResponse = curl_exec($curl);
            $curlError = curl_error($curl) ?: null;
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($rawResponse !== false && $httpCode >= 200 && $httpCode < 500 && $httpCode !== 429) {
                break;
            }
            if ($attempt < 3) {
                usleep($attempt * 500000);
            }
        }

        $rawBody = is_string($rawResponse) ? trim($rawResponse) : '';
        $decoded = self::decodeResponse($rawBody);
        $rrr = $decoded['RRR'] ?? $decoded['rrr'] ?? null;

        Log::info($context . ' generation', [
            'service_type_id' => $prepared['service_type_id'] ?? null,
            'order_id' => $payload['orderId'] ?? null,
            'amount' => $prepared['total_amount'] ?? null,
            'base_amount' => $prepared['base_amount'] ?? null,
            'surcharge_amount' => $prepared['surcharge_amount'] ?? null,
            'split_enabled' => $prepared['split_enabled'] ?? false,
            'http_status' => $httpCode,
            'curl_error' => $curlError,
            'gateway_status' => $decoded['status'] ?? null,
            'gateway_status_code' => $decoded['statuscode'] ?? null,
        ]);

        if (!$rrr) {
            $message = $decoded['responseMsg']
                ?? $decoded['statusMessage']
                ?? $decoded['message']
                ?? ($curlError ?: (($httpCode >= 500 || $httpCode === 429)
                    ? 'Remita is temporarily busy. Please try again in a moment.'
                    : 'Remita did not return a payment reference.'));

            return [
                'success' => false,
                'message' => (string) $message,
                'http_code' => $httpCode,
                'response' => $decoded,
            ];
        }

        return [
            'success' => true,
            'rrr' => (string) $rrr,
            'http_code' => $httpCode,
            'response' => $decoded,
        ];
    }

    /**
     * Persist an invoice and its Remita allocation atomically.
     *
     * The invoice remains the source of truth for the amount/status while the
     * child rows make every beneficiary allocation auditable and reportable.
     * Only masked account numbers are stored; the full account is never written
     * to the application database.
     */
    public static function storeInvoice(array $invoiceData, array $prepared): int
    {
        $invoiceData['base_amount'] = $prepared['base_amount'] ?? ($invoiceData['base_amount'] ?? null);
        $invoiceData['surcharge_amount'] = $prepared['surcharge_amount'] ?? ($invoiceData['surcharge_amount'] ?? 0);
        $invoiceData['split_enabled'] = !empty($prepared['split_enabled']) ? 1 : (int) ($invoiceData['split_enabled'] ?? 0);
        $invoiceData['split_details'] = ($prepared['line_item_summary'] ?? []) !== []
            ? json_encode($prepared['line_item_summary'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : ($invoiceData['split_details'] ?? null);

        return (int) DB::transaction(function () use ($invoiceData, $prepared) {
            $invoiceId = (int) DB::table('invoices')->insertGetId($invoiceData);

            if (!empty($prepared['line_items']) && !Schema::hasTable('invoice_split_items')) {
                throw new \RuntimeException('The split-payment audit table is not available. Run the database migrations before enabling split payments.');
            }

            if (!empty($prepared['line_items'])) {
                $now = now();
                $rows = [];
                foreach ($prepared['line_items'] as $item) {
                    $account = preg_replace('/\D+/', '', (string) ($item['beneficiaryAccount'] ?? ''));
                    $amount = (float) ($item['beneficiaryAmount'] ?? 0);
                    $rows[] = [
                        'invoice_id' => $invoiceId,
                        'line_item_id' => (string) ($item['lineItemsId'] ?? 'beneficiary'),
                        'beneficiary_name' => (string) ($item['beneficiaryName'] ?? 'Beneficiary'),
                        'beneficiary_account' => $account === '' ? null : str_repeat('*', max(0, strlen($account) - 4)) . substr($account, -4),
                        'bank_code' => (string) ($item['bankCode'] ?? ''),
                        'beneficiary_amount' => $amount,
                        'is_primary' => ($item['lineItemsId'] ?? '') === 'beneficiary1' ? 1 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($rows !== []) {
                    DB::table('invoice_split_items')->insert($rows);
                }
            }

            return $invoiceId;
        });
    }

    private static function buildLineItems(int $baseKobo, int $surchargeKobo, array $beneficiaries): array
    {
        if (count($beneficiaries) !== 3) {
            throw new \InvalidArgumentException('Exactly three split beneficiaries are required.');
        }

        $percentageTotal = 0.0;
        foreach ($beneficiaries as $beneficiary) {
            $percentageTotal += (float) ($beneficiary['percentage'] ?? 0);
            if (trim((string) ($beneficiary['account_number'] ?? '')) === ''
                || trim((string) ($beneficiary['bank_code'] ?? '')) === '') {
                throw new \InvalidArgumentException('Every split beneficiary must have an account number and bank code.');
            }
        }
        if (abs($percentageTotal - 100) > 0.001) {
            throw new \InvalidArgumentException('Split beneficiary percentages must total 100%.');
        }

        $shares = array_map(
            static fn (array $beneficiary): int => (int) floor($surchargeKobo * ((float) $beneficiary['percentage'] / 100)),
            array_values($beneficiaries)
        );
        $shares[0] += $surchargeKobo - array_sum($shares);
        $lineItems = [];
        foreach (array_values($beneficiaries) as $index => $beneficiary) {
            $share = $shares[$index];
            $beneficiaryAmount = $share + ($index === 0 ? $baseKobo : 0);
            if ($beneficiaryAmount <= 0) {
                continue;
            }
            $lineItems[] = [
                'lineItemsId' => 'beneficiary' . ($index + 1),
                'beneficiaryName' => self::cleanText($beneficiary['label'] ?? ('Beneficiary ' . ($index + 1))),
                'beneficiaryAccount' => preg_replace('/\D+/', '', (string) $beneficiary['account_number']),
                'bankCode' => preg_replace('/\D+/', '', (string) $beneficiary['bank_code']),
                'beneficiaryAmount' => self::formatKobo($beneficiaryAmount),
                'deductFeeFrom' => $index === 0 ? '1' : '0',
            ];
        }

        $lineTotal = array_sum(array_map(fn ($item) => self::toKobo($item['beneficiaryAmount']), $lineItems));
        if ($lineTotal !== $baseKobo + $surchargeKobo) {
            throw new \InvalidArgumentException('Split amounts could not be reconciled with the invoice total.');
        }

        return $lineItems;
    }

    private static function decodeResponse(string $body): array
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/\{.*\}/s', $body, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    private static function toKobo($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private static function fromKobo(int $amount): float
    {
        return round($amount / 100, 2);
    }

    private static function formatKobo(int $amount): string
    {
        return rtrim(rtrim(number_format($amount / 100, 2, '.', ''), '0'), '.') ?: '0';
    }

    private static function cleanText($value): string
    {
        return trim(preg_replace('/[^\x20-\x7E]/', '', (string) $value)) ?: '';
    }

    private static function cleanPhone($value): string
    {
        return trim(preg_replace('/[^0-9+]/', '', (string) $value));
    }
}
