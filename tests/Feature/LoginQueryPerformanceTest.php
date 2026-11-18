<?php

namespace Tests\Feature;

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginQueryPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'login_test', 'database.connections.login_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'cache.default' => 'array']);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Cache::put('system_settings', ['post_utme_session' => '2025/2026', 'system_session' => '2025/2026']);
        foreach (['users', 'students', 'applicants', 'invoices', 'staff'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->increments('id');
                $columns = [
                    'users' => 'username password accType gender level program faculty status',
                    'students' => 'user_id username jamb_no session_of_entry level_of_entry id_no program faculty level state_origin lga_origin update_profile duration father_phone level_flag profile_status profile_submission_session profile_level_session',
                    'applicants' => 'user_id',
                    'invoices' => 'username serviceTypeId session status description',
                    'staff' => 'username appointment unit faculty department program',
                ];
                foreach (explode(' ', $columns[$name]) as $column) {
                    $table->string($column)->nullable();
                }
            });
        }
        Schema::create('session', function ($table) { $table->string('title'); $table->string('status'); });
        Schema::create('semester', function ($table) { $table->string('semester'); $table->string('status'); });
        Schema::create('course_structure', function ($table) { $table->increments('id'); $table->string('from_session'); $table->string('to_session'); });
        DB::table('session')->insert(['title' => '2025/2026', 'status' => '1']);
        DB::table('semester')->insert(['semester' => 'SECOND', 'status' => '1']);
        DB::table('users')->insert(['id' => 5068, 'username' => 'TEST/ACCOUNT', 'password' => Hash::make('test-password'), 'accType' => 'Student', 'status' => '1']);
    }

    private function login($password = 'test-password')
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $request = Request::create('/auth', 'POST', ['email' => 'TEST/ACCOUNT', 'password' => $password]);
        $request->setLaravelSession(session()->driver());
        return (new LoginController())->auth($request);
    }

    /** @dataProvider studentCases */
    public function test_student_login_preserves_payment_and_profile_gates($entry, $profile, $paid, $destination): void
    {
        DB::table('students')->insert([
            'user_id' => '5068', 'username' => 'TEST/ACCOUNT', 'session_of_entry' => $entry,
            'id_no' => 'TEST/ACCOUNT', 'level' => '200', 'profile_status' => $profile,
            'profile_submission_session' => '2025/2026', 'profile_level_session' => '2025/2026',
        ]);
        if ($paid) {
            DB::table('invoices')->insert(['username' => '5068', 'serviceTypeId' => '365039916', 'session' => '2025/2026', 'status' => 'Paid']);
        }
        $this->assertSame(url($destination), $this->login()->getTargetUrl());
        $this->assertSame('5068', session('id'));
        $this->assertSame($destination === '/dash', session()->has('log'));
        $queries = collect(DB::getQueryLog());
        $this->assertCount(1, $queries->filter(function ($query) { return strpos($query['query'], 'from "users"') !== false; }));
        foreach ($queries as $query) {
            if (strpos($query['query'], 'where "user_id"') !== false || strpos($query['query'], 'from "invoices"') !== false) {
                $this->assertContains('5068', $query['bindings']);
                $this->assertNotContains(5068, $query['bindings']);
            }
        }
    }

    public function studentCases(): array
    {
        return [
            'new paid' => ['2025/2026', 'submitted', true, '/dash'],
            'new unpaid' => ['2025/2026', 'submitted', false, '/school-fees'],
            'new draft' => ['2025/2026', 'draft', false, '/dash'],
            'returning submitted' => ['2021/2022', 'submitted', false, '/dash'],
            'returning draft' => ['2021/2022', 'draft', false, '/dash'],
        ];
    }

    public function test_bad_password_does_not_create_a_login_session(): void
    {
        $this->login('incorrect');
        $this->assertFalse(session()->has('log'));
        $this->assertSame('Wrong ID Or Password', session('error'));
    }

    public function test_unknown_account_returns_a_readable_error(): void
    {
        DB::table('users')->delete();
        $this->login();
        $this->assertFalse(session()->has('log'));
        $this->assertSame('Wrong ID Or Password', session('error'));
    }

    public function test_staff_login_keeps_their_appointment(): void
    {
        DB::table('users')->update(['accType' => 'Staff']);
        DB::table('staff')->insert(['username' => 'TEST/ACCOUNT', 'appointment' => 'HOD', 'department' => 'CSC']);
        $this->assertSame(url('/dash'), $this->login()->getTargetUrl());
        $this->assertSame('HOD', session('appointment'));
        $this->assertSame('CSC', session('department'));
    }

    public function test_applicant_payment_lookup_binds_text_ids(): void
    {
        DB::table('users')->update(['accType' => 'Applicant']);
        DB::table('applicants')->insert(['user_id' => '5068']);
        $this->assertSame(url('/applicant-fees'), $this->login()->getTargetUrl());
        foreach (DB::getQueryLog() as $query) {
            if (strpos($query['query'], 'from "applicants"') !== false || strpos($query['query'], 'from "invoices"') !== false) {
                $this->assertContains('5068', $query['bindings']);
                $this->assertNotContains(5068, $query['bindings']);
            }
        }
    }

    public function test_paid_applicant_continues_to_application(): void
    {
        DB::table('users')->update(['accType' => 'Applicant']);
        DB::table('applicants')->insert(['user_id' => '5068']);
        DB::table('invoices')->insert(['username' => '5068', 'description' => 'POST UTME', 'session' => '2025/2026', 'status' => 'Paid']);
        $this->assertSame(url('/application'), $this->login()->getTargetUrl());
        $this->assertTrue(session()->has('log'));
    }

    public function test_login_error_is_visible_without_javascript(): void
    {
        session()->flash('error', 'Wrong ID Or Password');
        $html = view('login', ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertStringContainsString('role="alert">Wrong ID Or Password</div>', $html);
        $this->assertStringContainsString('id="portal-login-submit"', $html);
    }

    private function returningStudentLogin(): void
    {
        DB::table('students')->insert(['user_id' => '5068', 'username' => 'TEST/ACCOUNT', 'id_no' => '0', 'session_of_entry' => '2021/2022']);
        $this->login();
    }

    public function test_dashboard_uses_same_academic_session_as_login_not_payment_settings(): void
    {
        Cache::put('system_settings', ['system_session' => '2026/2027']);
        $this->returningStudentLogin();
        $route = app('router')->getRoutes()->match(Request::create('/dash'));
        $response = $route->run();
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertSame('dashboard', $response->getData()['page']);
    }

    public function test_dashboard_requires_a_new_login_after_active_academic_session_changes(): void
    {
        $this->returningStudentLogin();
        DB::table('session')->update(['title' => '2026/2027']);
        $route = app('router')->getRoutes()->match(Request::create('/dash'));
        $response = $route->run();
        $this->assertSame(url('/logout'), $response->getTargetUrl());
        $this->assertStringContainsString('academic session has changed', session('error'));
    }
}
