<?php

namespace Tests\Feature;

use App\Http\Controllers\ApproveResultsController;
use App\Http\Middleware\AuditLog;
use App\Support\PortalPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ResultApprovalAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'approval_test', 'database.connections.approval_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'cache.default' => 'array']);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Cache::flush();
        $this->withoutMiddleware(AuditLog::class);
        foreach ([
            'course_allocation' => ['username', 'course', 'type', 'session'],
            'results' => ['username', 'code', 'session', 'semester', 'approve', 'lecturer', 'department', 'program', 'hod', 'dean', 'cs', 'vc', 'comment', 'ca', 'exam', 'total', 'grade', 'remark', 'unit', 'point', 'ugp'],
            'student_course_registration' => ['username', 'code', 'session', 'semester', 'comment', 'grade', 'status', 'total', 'unit', 'point', 'ugp'],
            'course' => ['code', 'title', 'department', 'faculty'],
            'department' => ['code', 'title', 'faculty'],
            'program' => ['code', 'title', 'department', 'faculty'],
            'faculty' => ['code', 'title'],
            'session' => ['title', 'status'],
            'system_settings' => ['key', 'value'],
            'pages' => ['link'],
            'rolls' => ['username', 'link', 'action', 'status'],
        ] as $name => $columns) {
            Schema::create($name, function ($table) use ($columns) {
                $table->increments('id');
                foreach ($columns as $column) $table->string($column)->nullable();
                $table->timestamps();
            });
        }
        DB::table('pages')->insert([['link' => '/results'], ['link' => '/approve results']]);
        DB::table('course')->insert(['code' => 'CSC408', 'department' => 'CSC', 'faculty' => 'PS']);
        DB::table('course_allocation')->insert(['username' => 'SP100', 'course' => 'CSC408', 'session' => '2025/2026', 'type' => 'MAIN']);
        DB::table('results')->insert($this->context() + ['username' => '22/01/01/0001', 'lecturer' => 'SP100', 'department' => 'CSC']);
        foreach ([1, 2] as $id) DB::table('student_course_registration')->insert(['id' => $id, 'username' => '22/01/01/000' . ($id + 1), 'code' => 'CSC408', 'session' => '2025/2026', 'semester' => 'SECOND', 'comment' => 'No Comment', 'status' => 'awaiting']);
        $this->staff();
    }

    private function staff(string $username = 'SP100', string $appointment = 'LECTURER'): void
    {
        $this->withSession(['log' => true, 'id' => '1', 'username' => $username, 'accType' => 'Staff', 'appointment' => $appointment, 'department' => 'CSC', 'faculty' => 'PS']);
        PortalPermission::flush();
    }

    private function context(string $stage = 'system'): array
    {
        return ['code' => 'CSC408', 'session' => '2025/2026', 'semester' => 'SECOND', 'approve' => $stage];
    }

    private function comment(array $data = [])
    {
        return $this->post('/comment-pending-result', $data + ['id' => 1, 'comment' => 'Student did not attend the examination.'], ['Accept' => 'application/json']);
    }

    public function test_assigned_lecturer_can_comment_without_a_separate_results_page_grant()
    {
        $response = $this->comment();
        $response->assertRedirect('/pending-results?' . http_build_query($this->context()));
        $this->assertSame('Student did not attend the examination.', DB::table('student_course_registration')->where('id', 1)->value('comment'));
        $this->assertSame('No Comment', DB::table('student_course_registration')->where('id', 2)->value('comment'));
    }

    public function test_legacy_bulk_form_saves_comments_for_the_same_course()
    {
        $this->comment(['ids' => '[1,2]'])->assertRedirect();
        $this->assertSame(2, DB::table('student_course_registration')->where('status', 'FAILED')->count());
    }

    public function test_unassigned_staff_and_page_only_readers_cannot_comment()
    {
        $this->staff('SP999', 'HOD');
        DB::table('rolls')->insert(['username' => 'HOD', 'link' => '/approve results', 'action' => null]);
        $this->comment()->assertForbidden();
        $this->assertSame(0, DB::table('student_course_registration')->where('status', 'FAILED')->count());
    }

    public function test_other_session_or_assistant_allocation_does_not_authorise_comments()
    {
        DB::table('course_allocation')->update(['session' => '2024/2025']);
        $this->comment()->assertForbidden();
        DB::table('course_allocation')->update(['session' => '2025/2026', 'type' => 'ASSISTANT']);
        $this->comment()->assertForbidden();
    }

    public function test_bulk_comments_cannot_include_another_course()
    {
        DB::table('student_course_registration')->where('id', 2)->update(['code' => 'MLS326']);
        $this->comment(['ids' => [1, 2]])->assertStatus(422);
        $this->assertSame(2, DB::table('student_course_registration')->where('comment', 'No Comment')->count());
    }

    public function test_existing_results_and_progressed_batches_are_protected()
    {
        DB::table('results')->update(['username' => '22/01/01/0002']);
        $this->comment()->assertStatus(409);
        DB::table('results')->update(['username' => '22/01/01/0001', 'approve' => 'lecturer']);
        $this->comment()->assertForbidden();
    }

    public function test_student_cannot_post_comment_or_submit_approval()
    {
        $this->withSession(['accType' => 'Applicant']);
        $this->comment()->assertForbidden();
        $this->post('/create approve results', $this->context())->assertForbidden();
    }

    public function test_empty_comment_and_invalid_bulk_ids_are_explained()
    {
        $this->comment(['comment' => ''])->assertStatus(422)->assertJsonValidationErrors('comment');
        $this->comment(['ids' => '[1,"invalid"]'])->assertStatus(422)->assertJsonValidationErrors('ids.1');
    }

    public function test_assigned_lecturer_can_submit_draft_without_unrelated_page_permission()
    {
        $this->post('/create approve results', $this->context(), ['Accept' => 'application/json'])->assertStatus(422)->assertJsonValidationErrors('comment');
        $this->comment(['ids' => '[1,2]'])->assertRedirect();
        $this->post('/create approve results', $this->context())->assertRedirect();
        $this->assertSame('lecturer', DB::table('results')->value('approve'));
    }

    public function test_hod_submission_honours_department_and_explicit_action_grants()
    {
        $this->staff('SP200', 'HOD');
        DB::table('rolls')->insert(['username' => 'HOD', 'link' => '/approve results', 'action' => null]);
        DB::table('results')->update(['approve' => 'lecturer']);
        $this->withSession(['department' => 'MLS']);
        $this->post('/create approve results', $this->context('lecturer'))->assertForbidden();
        $this->withSession(['department' => 'CSC']);
        DB::table('rolls')->update(['action' => 'view']); PortalPermission::flush();
        $this->post('/create approve results', $this->context('lecturer'))->assertForbidden();
        DB::table('rolls')->update(['action' => null]); PortalPermission::flush();
        $this->post('/create approve results', $this->context('lecturer'))->assertRedirect();
        $this->assertSame('hod', DB::table('results')->value('approve'));
    }

    public function test_approval_roles_match_the_existing_stage_and_academic_scope()
    {
        foreach (['HOD' => 'lecturer', 'DEAN' => 'hod', 'COURSE SYSTEM' => 'dean', 'VC' => 'cs'] as $appointment => $stage) {
            $this->staff('SP200', $appointment);
            DB::table('results')->update(['approve' => $stage]);
            $controller = new ApproveResultsController(Request::create('/approve results'));
            $this->assertTrue($controller->canSubmitApprovalStage($this->context($stage)));
            $this->assertFalse($controller->canSubmitApprovalStage($this->context('system')));
        }
    }

    public function test_role_assignments_are_case_and_whitespace_insensitive()
    {
        $this->staff('SP200', ' vc ');
        DB::table('rolls')->insert([
            'username' => 'VC ',
            'link' => '/approve results',
            'action' => null,
            'status' => '1',
        ]);

        $this->assertTrue(PortalPermission::hasRole('VC'));
        $this->assertTrue(PortalPermission::hasPageAccess('/approve results'));
    }

    public function test_delete_permission_is_not_mistaken_for_update()
    {
        $resolved = PortalPermission::resolve(Request::create('/delete-result', 'POST'));
        $this->assertSame('/approve results', $resolved['link']);
        $this->assertSame('delete', $resolved['action']);
        DB::table('rolls')->insert(['username' => 'SP100', 'link' => '/approve results', 'action' => 'update']);
        $this->post('/delete-result', $this->context())->assertForbidden();
    }

    public function test_department_final_approval_is_not_available_to_other_offices()
    {
        $this->staff('SP200', 'HOD');
        DB::table('rolls')->insert(['username' => 'HOD', 'link' => '/approve results', 'action' => 'update']);
        $this->post('/approve-department-results', ['dept' => 'CSC', 'status' => 'Approved'])->assertForbidden();
        $this->get('/process-next-batch/CSC/Approved')->assertForbidden();
    }

    public function test_final_approval_updates_only_the_selected_semester_and_rejects_replay()
    {
        $this->staff('SP300', 'VC');
        DB::table('rolls')->insert(['username' => 'VC', 'link' => '/approve results', 'action' => null]);
        DB::table('results')->update(['approve' => 'cs', 'total' => '80', 'grade' => 'A', 'remark' => 'PASSED', 'unit' => '3', 'point' => '5', 'ugp' => '15']);
        DB::table('student_course_registration')->insert([
            ['username' => '22/01/01/0001', 'code' => 'CSC408', 'session' => '2025/2026', 'semester' => 'FIRST'],
            ['username' => '22/01/01/0001', 'code' => 'CSC408', 'session' => '2025/2026', 'semester' => 'SECOND'],
        ]);
        $this->post('/create approve results', $this->context('cs'))->assertRedirect();
        $this->assertSame('vc', DB::table('results')->value('approve'));
        $this->assertNull(DB::table('student_course_registration')->where('semester', 'FIRST')->value('grade'));
        $this->assertSame('A', DB::table('student_course_registration')->where('username', '22/01/01/0001')->where('semester', 'SECOND')->value('grade'));
        $this->post('/create approve results', $this->context('cs'))->assertStatus(409);
    }

    public function test_course_downloads_and_review_do_not_expose_other_department_results()
    {
        $this->staff('SP999', 'HOD');
        DB::table('rolls')->insert(['username' => 'HOD', 'link' => '/approve results', 'action' => null]);
        $this->withSession(['department' => 'MLS']);
        $this->get('/pending-results?' . http_build_query($this->context('lecturer')))->assertForbidden();
        $this->get('/print-result-pdf/CSC408/2025/2026')->assertForbidden();
        $this->get('/print-result-pdf2/CSC408/2025/2026/lecturer/SECOND')->assertForbidden();
        $this->post('/count-records', $this->context())->assertForbidden();
    }

    public function test_hod_rejection_preserves_the_reason_and_department_boundary()
    {
        $this->staff('SP200', 'HOD');
        DB::table('rolls')->insert(['username' => 'HOD', 'link' => '/approve results', 'action' => null]);
        DB::table('results')->update(['approve' => 'lecturer']);
        $payload = $this->context('lecturer') + ['comment' => 'Please review the examination marks.'];
        $this->withSession(['department' => 'MLS']);
        $this->post('/update approve results', $payload)->assertForbidden();
        $this->withSession(['department' => 'CSC']);
        $this->post('/update approve results', $payload)->assertRedirect();
        $this->assertSame('system', DB::table('results')->value('approve'));
        $this->assertSame($payload['comment'], DB::table('results')->value('comment'));
    }

    public function test_pending_review_template_renders_for_readers_without_comment_buttons()
    {
        $html = view('Admin.pending results', [
            'code' => 'CSC408', 'semester' => 'SECOND', 'session' => '2025/2026', 'sessions' => '2025/2026',
            'results' => DB::table('results')->get(), 'data' => DB::table('student_course_registration')->get(),
            'pendingIds' => collect([1,2]), 'lecturer' => 'SP100', 'approvalStage' => 'system',
            'canSubmitReview' => false, 'canRejectReview' => false, 'canCommentOmissions' => false,
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();
        $this->assertStringNotContainsString('data-bs-target="#commentAll"', $html);
        $this->assertStringNotContainsString('data-bs-target="#updateStudent', $html);
        $this->assertStringNotContainsString('@if', $html);
    }

    public function test_approval_templates_compile_without_blade_syntax_errors()
    {
        foreach (['Admin/approve results.blade.php', 'Admin/result-approval-workspace.blade.php', 'Admin/pending results.blade.php'] as $path) {
            $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/' . $path)));
            $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE));
        }
    }

    /** @dataProvider reviewerOffices */
    public function test_workspace_filters_survive_review_and_submission($office, $stage)
    {
        $this->staff('SP200', $office);
        DB::table('rolls')->insert(['username' => $office, 'link' => '/approve results', 'action' => null]);
        DB::table('results')->update(['approve' => $stage]);
        $workspace = new \App\Support\ResultApprovalWorkspace();
        $filters = $workspace->filters(Request::create('/approve results', 'GET', [
            'session' => '2025/2026', 'faculty' => 'PS', 'department' => 'CSC',
            'program' => 'CSC', 'semester' => 'SECOND', 'stage' => $stage, 'search' => 'CSC', 'page' => 2,
        ]));
        $return = \App\Support\ResultApprovalWorkspace::returnUrl();
        $this->assertStringContainsString('page=2', $return);
        $this->get('/approve%20results')->assertRedirect($return);
        $this->post('/create approve results', $this->context($stage))->assertRedirect($return);
        $this->assertSame($filters, session(\App\Support\ResultApprovalWorkspace::stateKey()));
        $this->staff('SP999', 'DEAN');
        $this->assertNull(session(\App\Support\ResultApprovalWorkspace::stateKey()));
    }

    public static function reviewerOffices(): array
    {
        return [['HOD', 'lecturer'], ['DEAN', 'hod'], ['COURSE SYSTEM', 'dean'], ['VC', 'cs']];
    }

    public function test_reset_and_out_of_range_page_keep_navigation_usable()
    {
        $this->staff('SP300', 'VC');
        DB::table('rolls')->insert(['username' => 'VC', 'link' => '/approve results', 'action' => null]);
        $workspace = new \App\Support\ResultApprovalWorkspace();
        $filters = $workspace->filters(Request::create('/approve results', 'GET', ['session' => 'all', 'page' => 9]));
        $this->get('/approve%20results?' . http_build_query($filters))->assertRedirect();
        $this->assertSame(1, session(\App\Support\ResultApprovalWorkspace::stateKey())['page']);
        $this->get('/approve%20results?reset=1')->assertRedirect('/approve results');
        $this->assertNull(session(\App\Support\ResultApprovalWorkspace::stateKey()));
    }

    public function test_workspace_groups_batches_and_renders_final_review_without_template_errors()
    {
        $this->staff('SP300', 'VC');
        DB::table('rolls')->insert(['username' => 'VC', 'link' => '/approve results', 'action' => null]);
        DB::table('faculty')->insert(['code' => 'PS', 'title' => 'Physical Sciences']);
        DB::table('department')->insert(['code' => 'CSC', 'title' => 'Computer Science', 'faculty' => 'PS']);
        DB::table('program')->insert(['code' => 'CSC', 'title' => 'Computer Science', 'department' => 'CSC', 'faculty' => 'PS']);
        DB::table('course')->update(['title' => 'Software Engineering']);
        DB::table('session')->insert([['title' => '2025/2026'], ['title' => '2024/2025']]);
        DB::table('results')->update(['approve' => 'cs', 'program' => 'CSC']);
        DB::table('results')->insert($this->context('cs') + ['username' => '22/01/01/0099', 'lecturer' => 'SP100', 'program' => 'CSC', 'updated_at' => '2026-11-20 12:00:00']);
        $workspace = new \App\Support\ResultApprovalWorkspace();
        $filters = $workspace->filters(Request::create('/approve results'));
        $this->assertSame('all', $filters['session']);
        $data = $workspace->data($filters);
        $this->assertSame(1, $data['rows']->total());
        $this->assertSame(2, (int) $data['rows']->first()->result_count);
        $this->assertSame(1, $data['pendingDepartments']->first()->total);
        $html = view('Admin.approve results', $data + ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertStringContainsString('Computer Science', $html);
        $this->assertStringContainsString('Software Engineering', $html);
        $this->assertStringNotContainsString('@php', $html);
        $this->assertStringNotContainsString('@endif', $html);
    }

    public function test_view_only_vc_cannot_make_department_decisions()
    {
        $this->staff('SP300', 'VC');
        DB::table('rolls')->insert(['username' => 'VC', 'link' => '/approve results', 'action' => 'view']);
        $this->assertFalse(\App\Support\ResultApprovalWorkspace::canDecideDepartment('Approved'));
        $this->post('/approve-department-results', ['dept' => 'CSC', 'status' => 'Approved'])->assertForbidden();
        $this->get('/process-next-batch/CSC/Approved')->assertForbidden();
    }

    public function test_hod_listing_cannot_include_another_department_unless_course_is_assigned()
    {
        $this->staff('SP200', 'HOD');
        DB::table('course')->insert(['code' => 'MLS326', 'title' => 'Laboratory Science', 'department' => 'MLS', 'faculty' => 'AHS']);
        DB::table('results')->insert(['username' => 'TEST2', 'code' => 'MLS326', 'session' => '2025/2026', 'semester' => 'SECOND', 'approve' => 'lecturer']);
        $workspace = new \App\Support\ResultApprovalWorkspace();
        $data = $workspace->data($workspace->filters(Request::create('/approve results', 'GET', ['session' => 'all', 'stage' => ''])));
        $this->assertSame(['CSC408'], $data['rows']->pluck('code')->all());
    }
}
