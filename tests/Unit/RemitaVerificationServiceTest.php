<?php

namespace Tests\Unit;

use App\Services\RemitaVerificationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use PHPUnit\Framework\TestCase;

class RemitaVerificationServiceTest extends TestCase
{
    private function invoice(): object
    {
        return (object) ['rrr' => '171397395152', 'orderId' => '93057294558', 'amount' => '15135.00', 'serviceTypeId' => '767540443'];
    }

    private function paid(array $changes = []): array
    {
        return array_merge(['status' => '00', 'rrr' => '171397395152', 'orderId' => '93057294558', 'amount' => 15135, 'serviceTypeId' => '767540443'], $changes);
    }

    private function check(Factory $http, ?object $invoice = null): array
    {
        return (new RemitaVerificationService($http))->verify($invoice ?? $this->invoice(), 'https://gateway.example/', 'merchant', 'test-key');
    }

    public function test_rrr_404_falls_back_using_order_hash_and_checks_identity(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push('404 page not found', 404)->push($this->paid());
        $this->assertSame('paid', $this->check($http)['state']);
        $http->assertSentCount(2);
        $hash = hash('sha512', '93057294558test-keymerchant');
        $http->assertSent(function ($request) use ($hash) {
            return substr($request->url(), -strlen('/orderstatus.reg')) === '/orderstatus.reg'
                && strpos($request->url(), '/93057294558/' . $hash . '/') !== false
                && $request->hasHeader('Authorization', 'remitaConsumerKey=merchant,remitaConsumerToken=' . $hash);
        });
    }

    public function test_transient_failures_retry_and_recover(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push('', 404)->push('', 503)->push($this->paid());
        $this->assertSame('paid', $this->check($http)['state']);
        $http->assertSentCount(3);
    }

    public function test_pending_response_is_preserved_when_alternative_is_unavailable(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push(['status' => '021', 'RRR' => '171397395152'])->push('', 404);
        $this->assertSame('pending', $this->check($http)['state']);
        $http->assertSentCount(2);
    }

    public function test_alternate_endpoint_can_confirm_after_pending_response(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push(['status' => '021'])->push($this->paid(['status' => '01']));
        $this->assertSame('paid', $this->check($http)['state']);
    }

    public function test_complete_outage_is_bounded_and_never_interpreted_as_pending_or_paid(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push('', 404)->push('<html>error</html>', 200)->push('', 429)->push('', 500);
        $this->assertSame('unavailable', $this->check($http)['state']);
        $http->assertSentCount(4);
    }

    public function test_network_timeout_is_an_unavailable_check(): void
    {
        $http = new Factory();
        $attempts = 0;
        $http->fake(function () use (&$attempts) {
            $attempts++;
            throw new ConnectionException('Simulated timeout');
        });
        $this->assertSame('unavailable', $this->check($http)['state']);
        $this->assertSame(4, $attempts);
    }

    /** @dataProvider mismatchProvider */
    public function test_mismatched_success_is_never_accepted(array $changes, string $reason): void
    {
        $http = new Factory();
        $http->fakeSequence()->push($this->paid($changes));
        $result = $this->check($http);
        $this->assertSame('review', $result['state']);
        $this->assertSame($reason, $result['reason']);
    }

    public function mismatchProvider(): array
    {
        return [
            [['rrr' => '999999999999'], 'reference_mismatch'],
            [['orderId' => 'other-order'], 'reference_mismatch'],
            [['amount' => 15134.99], 'amount_mismatch'],
            [['amount' => null], 'amount_mismatch'],
            [['serviceTypeId' => 'other-service'], 'service_mismatch'],
        ];
    }

    public function test_success_without_reference_is_not_accepted(): void
    {
        $http = new Factory();
        $payload = $this->paid();
        unset($payload['rrr']);
        $http->fakeSequence()->push($payload)->push($payload)->push($payload)->push($payload);
        $this->assertSame('unavailable', $this->check($http)['state']);
    }

    public function test_missing_order_id_retries_only_rrr(): void
    {
        $http = new Factory();
        $http->fakeSequence()->push('', 404)->push($this->paid());
        $invoice = $this->invoice();
        $invoice->orderId = null;
        $this->assertSame('paid', $this->check($http, $invoice)['state']);
        $http->assertSentCount(2);
    }
}
