<?php

namespace Tests\Feature;

use App\Http\Middleware\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentProfileSaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'profile_test', 'database.connections.profile_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'cache.default' => 'array']);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutMiddleware(AuditLog::class);
        Storage::fake('public');
        Cache::put('system_settings', ['system_session' => '2025/2026', 'student_profile_update_enabled' => '1']);
        Schema::create('session', function ($table) { $table->string('title'); $table->string('status'); });
        DB::table('session')->insert(['title' => '2025/2026', 'status' => '1']);
        Schema::create('students', function ($table) {
            $table->increments('id');
            foreach (explode(' ', 'user_id username last_name first_name other_name fullname mode_of_entry jamb_no gender date_of_birth place_of_birth country state_origin lga_origin marital_status maiden_name religion nin health_status physical_challenge hobbies blood_group room hall games genotype highest_qualification home_address home_phone home_email contact_address contact_phone contact_email bank_name bank_code account_number sort_code kin_name kin_address kin_phone kin_email sponsor_type sponsor_name sponsor_address sponsor_phone sponsor_email mother_name mother_address mother_phone mother_email father_name father_address father_phone father_email update_profile level picture signiture profile_photo_updated_at profile_status profile_submitted_at profile_last_updated_at profile_submission_session profile_level_session') as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        foreach (['student_documents', 'document_uploads'] as $name) {
            Schema::create($name, function ($table) {
                $table->increments('id');
                foreach (['user_id', 'student_id', 'doc_type', 'file_path', 'original_name', 'mime_type', 'size', 'uploaded_at'] as $column) $table->string($column)->nullable();
                $table->unique(['user_id', 'doc_type']);
                $table->timestamps();
            });
        }
        Schema::create('siwes', function ($table) {
            $table->string('username');
            foreach (['bank_name', 'bank_code', 'account_number', 'sort_code'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        DB::table('students')->insert(['id' => 1, 'user_id' => 'test-user', 'username' => '22/01/01/0001', 'level' => '200', 'profile_status' => 'draft']);
        $this->withSession(['log' => true, 'id' => 'test-user', 'username' => '22/01/01/0001', 'accType' => 'Student']);
    }

    private function draft(array $extra = [])
    {
        return $this->post('/update-profile', array_merge(['id' => '1', 'profile_action' => 'save', 'surname' => 'Test', 'first_name' => 'Student'], $extra), ['Accept' => 'application/json']);
    }

    public function test_save_progress_uploads_a_document_without_requiring_completed_profile()
    {
        $this->draft(['ssce_result' => UploadedFile::fake()->image('ssce.jpeg'), 'document_upload_attempt' => '1'])
            ->assertOk()->assertJsonPath('redirect', url('/profile?mode=edit&required=1'));
        $document = DB::table('student_documents')->first();
        $this->assertSame('ssce_result', $document->doc_type);
        Storage::disk('public')->assertExists($document->file_path);
        $this->assertSame('draft', DB::table('students')->value('profile_status'));
        $this->assertNull(DB::table('students')->value('profile_submitted_at'));
    }

    public function test_supplied_document_can_be_saved_when_available_locally()
    {
        $sample = 'C:/Users/Abubakar/Downloads/ssce1.jpeg';
        if (!is_file($sample)) $this->markTestSkipped('Local user sample is not present.');
        $this->draft(['ssce_result' => new UploadedFile($sample, 'ssce1.jpeg', 'image/jpeg', null, true)]) ->assertOk();
        $path = DB::table('student_documents')->value('file_path');
        $this->assertSame(hash_file('sha256', $sample), hash('sha256', Storage::disk('public')->get($path)));
    }

    public function test_invalid_and_missing_uploads_return_visible_validation_errors()
    {
        $this->draft(['ssce_result' => UploadedFile::fake()->create('result.pdf', 401, 'application/pdf')])->assertStatus(422)->assertJsonValidationErrors('ssce_result');
        $this->draft(['document_upload_attempt' => '1'])->assertStatus(422)->assertJsonValidationErrors('documents');
        $this->assertSame(0, DB::table('student_documents')->count());
    }

    public function test_final_submission_requires_signature_and_all_documents()
    {
        $data = $this->completeFields();
        $this->draft($data)->assertStatus(422)->assertJsonValidationErrors('documents');
        $this->assertSame('draft', DB::table('students')->value('profile_status'));
    }

    public function test_completed_profile_submits_only_after_files_are_saved()
    {
        // Admissions settings and old login sessions must not change the student-profile session.
        Cache::put('system_settings', ['system_session' => '2026/2027', 'student_profile_update_enabled' => '1', 'student_nin_verification_enabled' => '0']);
        $this->withSession(['system_session' => '2024/2025']);
        Storage::disk('public')->put('picture/previews/test.jpg', 'prepared-photo');
        Cache::put('student.photo-preview.test-token', ['student_id' => 1, 'user_id' => 'test-user', 'source' => 'upload', 'path' => 'picture/previews/test.jpg']);
        $data = $this->completeFields() + ['processed_photo_token' => 'test-token', 'signiture' => UploadedFile::fake()->image('signature.png')];
        foreach (['jamb_result', 'ssce_result', 'birth_certificate', 'nin_document', 'jamb_admission', 'indigine'] as $type) $data[$type] = UploadedFile::fake()->image($type . '.jpg');
        $this->draft($data)->assertOk();
        $student = DB::table('students')->first();
        $this->assertSame('submitted', $student->profile_status);
        $this->assertSame('2025/2026', $student->profile_submission_session);
        $this->assertSame('2025/2026', $student->profile_level_session);
        $this->assertSame('200', $student->level);
        Storage::disk('public')->assertExists('signature/' . $student->signiture);
        Storage::disk('public')->assertExists('picture/' . $student->picture);
        $this->assertSame(6, DB::table('student_documents')->count());
        $this->assertFalse(Cache::has('student.photo-preview.test-token'));
        $this->get('/profile?mode=edit&required=1')->assertRedirect('/profile');
        $access = (new \App\Http\Middleware\EnsureStudentProfileSubmitted())->handle(\Illuminate\Http\Request::create('/dash'), fn () => response('allowed'));
        $this->assertSame('allowed', $access->getContent());
        $this->draft()->assertForbidden();
    }

    public function test_storage_failure_rolls_back_profile_and_preserves_previous_document()
    {
        Storage::disk('public')->put('old.jpg', 'old-document');
        DB::table('student_documents')->insert(['user_id' => 'test-user', 'student_id' => '1', 'doc_type' => 'ssce_result', 'file_path' => 'old.jpg']);
        // Fail after the replacement file is written, when updating its database record.
        DB::statement("CREATE TRIGGER fail_document BEFORE UPDATE ON student_documents BEGIN SELECT RAISE(ABORT, 'Test write failure'); END");
        $this->draft(['ssce_result' => UploadedFile::fake()->image('new.jpg')])->assertStatus(500)->assertJsonStructure(['message']);
        $this->assertNull(DB::table('students')->value('first_name'));
        $this->assertSame('old.jpg', DB::table('student_documents')->value('file_path'));
        Storage::disk('public')->assertExists('old.jpg');
        $this->assertSame([], Storage::disk('public')->allFiles('student-documents'));
    }

    public function test_student_cannot_change_level_or_another_students_profile()
    {
        $this->draft(['level' => '300'])->assertStatus(422)->assertJsonValidationErrors('level');
        $this->draft(['id' => '2'])->assertNotFound();
    }

    public function test_expired_deadline_blocks_writes_and_the_required_editor()
    {
        Cache::put('system_settings', ['student_profile_update_enabled' => '2']);
        $this->draft()->assertForbidden();
        $this->get('/profile?mode=edit&required=1')->assertRedirect('/profile-update-expired');
    }

    public function test_a_profile_submitted_in_a_previous_session_remains_valid()
    {
        DB::table('students')->where('id', 1)->update([
            'profile_status' => 'submitted',
            'profile_submitted_at' => '2025-10-01 12:00:00',
            'profile_submission_session' => '2025/2026',
            'profile_level_session' => '2025/2026',
        ]);
        DB::table('session')->update(['title' => '2026/2027']);
        Cache::put('system_settings', ['student_profile_update_enabled' => '2', 'student_nin_verification_enabled' => '0']);

        $access = (new \App\Http\Middleware\EnsureStudentProfileSubmitted())->handle(
            \Illuminate\Http\Request::create('/dash'),
            fn () => response('allowed')
        );

        $this->assertSame('allowed', $access->getContent());
    }

    public function test_native_form_errors_return_directly_to_required_editor()
    {
        $this->post('/update-profile', ['id' => '1', 'profile_action' => 'save', 'home_email' => 'invalid'])
            ->assertRedirect('/profile?mode=edit&required=1')->assertSessionHasErrors('home_email');
    }

    public function test_profile_editor_renders_upload_controls_and_save_feedback()
    {
        Schema::create('locals', function ($table) { $table->string('state_id'); $table->string('local_name'); });
        foreach (['program', 'faculty', 'department'] as $name) {
            Schema::create($name, function ($table) { $table->string('code'); $table->string('title'); });
        }
        $this->app['request']->query->set('mode', 'edit');
        $html = view('Student.profile', ['errors' => new \Illuminate\Support\ViewErrorBag(), 'username' => '22/01/01/0001'])->render();
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//form[@id="myform"]//input[@name="ssce_result"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="myform"]//input[@name="signiture"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="myform"]//*[@id="profile-save-feedback"]')->length);
        $this->assertStringNotContainsString('@if', $html);
        if (getenv('PROFILE_RENDER_PATH')) file_put_contents(getenv('PROFILE_RENDER_PATH'), $html);
    }

    private function completeFields(): array
    {
        $fields = ['profile_action' => 'submit', 'jamb_no' => 'TEST123', 'surname' => 'Test', 'first_name' => 'Student', 'gender' => 'Female', 'date_of_birth' => '2000-01-01', 'country' => 'Ghana', 'nin' => '12345678901', 'mode_of_entry' => 'UTME', 'bank_name' => 'Test Bank', 'bank_code' => '001', 'account_number' => '1234567890'];
        foreach (explode(' ', 'place_of_birth state_origin lga_origin marital_status maiden_name religion home_address home_phone contact_address contact_phone kin_name kin_address kin_phone sponsor_type sponsor_name sponsor_address sponsor_phone mother_name mother_address mother_phone father_name father_address father_phone') as $field) $fields[$field] = 'Test';
        $fields['home_email'] = $fields['kin_email'] = 'test@example.org';
        return $fields;
    }
}
