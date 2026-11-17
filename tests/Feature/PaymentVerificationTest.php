<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoicesController;
use App\Services\RemitaVerificationService;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    private $http;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'verification_test',
            'database.connections.verification_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array',
            'cache.stores.file' => ['driver' => 'array'],
            'services.remita.merchant_id' => 'merchant',
            'services.remita.api_key' => 'test-key',
        ]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Cache::put('system_settings', ['remita_mode' => 'live', 'remita_live_url' => 'https://gateway.example']);
        Schema::create('invoices', function ($table) {
            $table->increments('id');
            foreach (['username', 'rrr', 'orderId', 'serviceTypeId', 'status', 'rrr_status', 'description'] as $column) {
                $table->string($column)->nullable();
            }
            $table->decimal('amount', 12, 2);
            $table->integer('hostel_id')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('students', function ($table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('username');
        });
        Schema::create('hostel', function ($table) {
            $table->increments('id');
            $table->string('occupant');
            $table->string('hostel_payment')->nullable();
        });
        DB::table('invoices')->insert([
            'id' => 1, 'username' => '54822', 'rrr' => '171397395152', 'orderId' => '93057294558',
            'amount' => 15135, 'serviceTypeId' => '767540443', 'status' => 'Pending', 'rrr_status' => '021',
            'description' => 'HOSTEL-MAINTENANCE/FEES', 'hostel_id' => 1,
        ]);
        DB::table('students')->insert(['user_id' => '54822', 'username' => 'TEST/STUDENT']);
        DB::table('hostel')->insert([
            ['id' => 1, 'occupant' => 'TEST/STUDENT', 'hostel_payment' => '0'],
            ['id' => 2, 'occupant' => 'TEST/STUDENT', 'hostel_payment' => '0'],
        ]);
        $this->http = new Factory();
        $this->app->instance(RemitaVerificationService::class, new RemitaVerificationService($this->http));
        session()->put(['log' => '1', 'id' => '54822', 'accType' => 'Student']);
    }

    private function verify()
    {
        $request = Request::create('/verify', 'GET', ['rrr' => '171397395152']);
        $request->setLaravelSession(session()->driver());
        return (new InvoicesController($request))->verify($request);
    }

    private function successfulPayload(): array
    {
        return ['status' => '00', 'rrr' => '171397395152', 'orderId' => '93057294558', 'amount' => 15135];
    }

    public function test_verified_payment_settles_once_and_only_its_allocated_bed(): void
    {
        $this->http->fakeSequence()->push('', 404)->push($this->successfulPayload());
        session()->flash('error', 'Old HTTP 404 message');
        $this->verify();
        $this->assertSame('Paid', DB::table('invoices')->value('status'));
        $this->assertSame('1', DB::table('hostel')->where('id', 1)->value('hostel_payment'));
        $this->assertSame('0', DB::table('hostel')->where('id', 2)->value('hostel_payment'));
        $this->assertFalse(session()->has('error'));
        $this->verify();
        $this->http->assertSentCount(2);
        $this->assertStringContainsString('already verified', session('success'));
    }

    public function test_outage_keeps_invoice_pending_and_reuses_brief_result(): void
    {
        $this->http->fakeSequence()->push('', 404)->push('', 404)->push('', 404)->push('', 404);
        $this->verify();
        $this->verify();
        $this->http->assertSentCount(4);
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        $this->assertSame('0', DB::table('hostel')->where('id', 1)->value('hostel_payment'));
        $this->assertFalse(session()->has('error'));
        $this->assertStringContainsString('could not obtain confirmation', session('info'));
    }

    public function test_another_students_rrr_is_never_verified(): void
    {
        session()->put('id', 'different-owner');
        $this->http->fake();
        $this->verify();
        $this->http->assertNothingSent();
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        $this->assertStringContainsString('not found on your account', session('error'));
    }

    public function test_overlapping_check_returns_notice_without_gateway_call(): void
    {
        $lock = Cache::store('file')->lock('remita-verify-1', 45);
        $this->assertTrue($lock->get());
        $this->http->fake();
        try {
            $this->verify();
            $this->http->assertNothingSent();
            $this->assertStringContainsString('already being checked', session('info'));
        } finally {
            $lock->release();
        }
    }

    public function test_benefit_failure_rolls_back_payment_and_bed_together(): void
    {
        $this->http->fakeSequence()->push($this->successfulPayload());
        DB::listen(function ($event) {
            if (strpos($event->sql, 'update "hostel"') === 0) {
                throw new \RuntimeException('Simulated settlement failure');
            }
        });
        $this->verify();
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        $this->assertSame('0', DB::table('hostel')->where('id', 1)->value('hostel_payment'));
        $this->assertStringContainsString('could not complete', session('error'));
    }

    public function test_wrong_amount_cannot_settle_invoice_or_bed(): void
    {
        $payload = $this->successfulPayload();
        $payload['amount'] = 100;
        $this->http->fakeSequence()->push($payload);
        $this->verify();
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        $this->assertSame('0', DB::table('hostel')->where('id', 1)->value('hostel_payment'));
        $this->assertStringContainsString('do not match', session('error'));
    }

    public function test_webhook_confirmation_wins_over_a_pending_gateway_response(): void
    {
        $http = $this->http;
        $http->fake(function () use ($http) {
            DB::table('invoices')->where('id', 1)->update(['status' => 'Paid']);
            return $http->response(['status' => '021']);
        });
        $this->verify();
        $this->assertSame('Paid', DB::table('invoices')->value('status'));
        $this->assertStringContainsString('already verified', session('success'));
        $this->assertFalse(session()->has('info'));
    }

    public function test_invoice_edited_during_check_is_not_settled(): void
    {
        $http = $this->http;
        $http->fake(function () use ($http) {
            DB::table('invoices')->where('id', 1)->update(['amount' => 20000]);
            return $http->response($this->successfulPayload());
        });
        $this->verify();
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        $this->assertStringContainsString('changed while it was being checked', session('info'));
    }

    public function test_pending_cache_expires_to_allow_later_confirmation(): void
    {
        $this->http->fakeSequence()->push(['status' => '021'])->push('', 404)->push($this->successfulPayload());
        $this->verify();
        $this->assertSame('Pending', DB::table('invoices')->value('status'));
        try {
            \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(11));
            $this->verify();
            $this->assertSame('Paid', DB::table('invoices')->value('status'));
            $this->http->assertSentCount(3);
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }
}
