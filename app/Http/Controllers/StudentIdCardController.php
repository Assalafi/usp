<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\PortalPermission;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class StudentIdCardController extends Controller
{
    protected string $page = "student id card";
    protected string $table = "student_id_card";
    protected string $title = "STUDENT ID CARD";
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = (string) $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = 'student id card';
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['idCardFaculties'] = DB::table('faculty')
            ->where('status', '1')
            ->select('code', 'title')
            ->orderBy('title')
            ->get();
        $data['idCardSessions'] = DB::table('session')
            ->select('title', 'status')
            ->orderByDesc('title')
            ->get();
        $data['idCardCurrentSession'] = DB::table('session')->where('status', '1')->value('title');
        $data['page'] = $this->page;
        $data['title'] = 'STUDENT ID CARD';

        return view('main', $data);
    }

    /** Return the live, payment-backed ID-card queue for the management page. */
    public function data(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        try {
            // Keep the summary independent of the active table tab. The tab
            // controls which rows are listed; the cards must always describe
            // every eligible record for the selected academic filters.
            $baseRecords = $this->eligibleRecordsQuery($request, false);
            $records = clone $baseRecords;
            $status = (string) $request->input('status', 'awaiting');
            if ($status === 'awaiting') {
                $records->whereNull('g.generated_at');
            } elseif ($status === 'generated') {
                $records->whereNotNull('g.generated_at');
            } elseif ($status === 'issues') {
                $records->whereNotNull('oi.issue_id');
            }
            $queueVersion = (int) Cache::get('student-id-card:queue-version', 1);
            $filterKey = collect($request->query())->except(['page', 'per_page', 'status'])->merge(['queue_version' => $queueVersion])->sortKeys()->toJson();
            $summaryRow = Cache::remember('student-id-card:summary:' . md5($filterKey), now()->addSeconds(60), function () use ($baseRecords) {
                return (clone $baseRecords)
                    ->select([])
                    ->selectRaw("COUNT(*) as total, SUM(CASE WHEN eligible.source = 'id_card' THEN 1 ELSE 0 END) as paid_count, SUM(CASE WHEN eligible.source = 'school_fee' THEN 1 ELSE 0 END) as covered_count, SUM(CASE WHEN g.generated_at IS NOT NULL THEN 1 ELSE 0 END) as generated_count")
                    ->first();
            });
            $issueCount = (clone $baseRecords)->whereNotNull('oi.issue_id')->count();
            $total = (int) ($summaryRow->total ?? 0);
            $generated = (int) ($summaryRow->generated_count ?? 0);
            $paid = (int) ($summaryRow->paid_count ?? 0);
            $covered = (int) ($summaryRow->covered_count ?? 0);

            $perPage = max(10, min(100, (int) $request->input('per_page', 25)));
            $page = max(1, (int) $request->input('page', 1));
            $rows = Cache::remember('student-id-card:rows:' . md5($filterKey . ':' . $status . ':' . $page . ':' . $perPage), now()->addSeconds(60), function () use ($records, $page, $perPage) {
                return (clone $records)
                    ->orderByRaw('generation_generated_at IS NULL DESC')
                    ->orderByDesc('paid_at')
                    ->orderBy('fullname')
                    ->forPage($page, $perPage)
                    ->get();
            });

            $payload = $rows->map(function ($row) {
                return [
                    'student_id' => (int) $row->student_id,
                    'session' => (string) $row->session,
                    'username' => (string) ($row->username ?: 'Not assigned'),
                    'fullname' => (string) ($row->fullname ?: 'Student'),
                    'faculty' => (string) ($row->faculty_title ?: ($row->faculty ?: 'Not recorded')),
                    'department' => (string) ($row->department_title ?: ($row->department ?: 'Not recorded')),
                    'program' => (string) ($row->program_title ?: ($row->program ?: 'Not recorded')),
                    'level' => (string) ($row->level ?: 'Not recorded'),
                    'phone' => (string) ($row->contact_phone ?: $row->home_phone ?: 'Not recorded'),
                    'source' => $row->source === 'school_fee' ? 'Current-session school-fee coverage' : 'ID-card payment',
                    'source_key' => $row->source,
                    'paid_at' => $row->paid_at ? (string) $row->paid_at : null,
                    'payment_id' => $row->payment_id ? (int) $row->payment_id : null,
                    'generated_at' => $row->generation_generated_at ? (string) $row->generation_generated_at : null,
                    'generated' => (bool) $row->generation_generated_at,
                    'has_issue' => (bool) $row->issue_id,
                    'issue_description' => $row->issue_description ? (string) $row->issue_description : null,
                    'media_ready' => $this->studentMediaReady($row),
                    'missing_media' => $this->missingStudentMedia($row),
                    'preview_url' => url('/student-id-card/preview/' . (int) $row->student_id),
                    'card_url' => url('/id card/' . (int) $row->student_id),
                ];
            })->values();

            return response()->json([
                'rows' => $payload,
                'summary' => [
                    'total' => $total,
                    'generated' => $generated,
                    'awaiting' => max(0, $total - $generated),
                    'paid' => $paid,
                    'school_fee_covered' => $covered,
                    'issues' => $issueCount,
                ],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Student ID-card queue failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'The ID-card list could not be loaded. Please try again.'], 500);
        }
    }

    public function options(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        $departments = DB::table('department')
            ->when($request->filled('faculty') && $request->faculty !== 'all', fn ($q) => $q->where('faculty', $request->faculty))
            ->select('code', 'title')->orderBy('title')->get();
        $programs = DB::table('program')
            ->when($request->filled('faculty') && $request->faculty !== 'all', fn ($q) => $q->where('faculty', $request->faculty))
            ->when($request->filled('department') && $request->department !== 'all', fn ($q) => $q->where('department', $request->department))
            ->select('code', 'title')->orderBy('title')->get();

        return response()->json(['departments' => $departments, 'programs' => $programs]);
    }

    public function preview(int $id)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        $student = Student::query()->whereKey($id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student record was not found.'], 404);
        }

        $openIssue = DB::table('student_id_card_issues')
            ->where('student_id', $student->id)
            ->where('status', 'open')
            ->latest('id')
            ->first(['id', 'session', 'description', 'created_at']);
        $media = $this->studentMedia($student);

        return response()->json([
            'student' => [
                'id' => (int) $student->id,
                'name' => $student->fullname ?: 'Student',
                'username' => $student->username ?: $student->id_no ?: 'Not assigned',
                'faculty' => DB::table('faculty')->where('code', $student->faculty)->value('title') ?: ($student->faculty ?: 'Not recorded'),
                'department' => DB::table('department')->where('code', $student->department)->value('title') ?: ($student->department ?: 'Not recorded'),
                'program' => DB::table('program')->where('code', $student->program)->value('title') ?: ($student->program ?: 'Not recorded'),
                'level' => $student->level ?: 'Not recorded',
                'phone' => $student->contact_phone ?: $student->home_phone ?: 'Not recorded',
                'state' => $student->state_origin ?: 'Not recorded',
                'nationality' => $student->country ?: 'Not recorded',
                'emergency_contact' => $student->kin_name ?: 'Not recorded',
                'emergency_phone' => $student->kin_phone ?: 'Not recorded',
                'picture_url' => $student->picture
                    ? asset('storage/picture/' . ltrim((string) $student->picture, '/'))
                    : asset('storage/picture/default.jpg'),
                'default_picture_url' => asset('storage/picture/default.jpg'),
                'signature_url' => $student->signiture
                    ? asset('storage/signature/' . ltrim((string) $student->signiture, '/'))
                    : ($student->passport_sign
                        ? asset('storage/passport_sign/' . ltrim((string) $student->passport_sign, '/'))
                        : null),
                'media_ready' => $media['ready'],
                'missing_media' => $media['missing'],
                'issue' => $openIssue ? [
                    'id' => (int) $openIssue->id,
                    'session' => $openIssue->session,
                    'description' => $openIssue->description,
                    'created_at' => (string) $openIssue->created_at,
                ] : null,
            ],
        ]);
    }

    /** Update the official photo and/or signature from the details view. */
    public function updateMedia(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }
        abort_unless($this->allowsAction('media'), 403, 'You do not have permission to update student ID-card documents.');

        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'picture' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'signiture' => ['nullable', 'file', 'image', 'mimes:png,jpeg,jpg', 'max:2048'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        if (!$request->hasFile('picture') && !$request->hasFile('signiture')) {
            return response()->json(['message' => 'Choose a photo, a signature, or both before saving.'], 422);
        }

        $student = Student::query()->find((int) $request->student_id);
        if (!$student) {
            return response()->json(['message' => 'Student record was not found.'], 404);
        }

        try {
            DB::transaction(function () use ($request, $student): void {
                $updates = [];
                if ($request->hasFile('picture')) {
                    $name = 'processed/' . $student->id . '_' . Str::uuid() . '.' . strtolower($request->file('picture')->getClientOriginalExtension());
                    Storage::disk('public')->makeDirectory('picture/processed');
                    if (!$request->file('picture')->storeAs('picture', $name, 'public')) {
                        throw new \RuntimeException('The student photo could not be saved.');
                    }
                    $updates['picture'] = $name;
                    $updates['profile_photo_updated_at'] = now();
                }
                if ($request->hasFile('signiture')) {
                    $name = $student->id . '_' . Str::uuid() . '.' . strtolower($request->file('signiture')->getClientOriginalExtension());
                    if (!$request->file('signiture')->storeAs('signature', $name, 'public')) {
                        throw new \RuntimeException('The student signature could not be saved.');
                    }
                    $updates['signiture'] = $name;
                }
                Student::whereKey($student->id)->update($updates);
            });
            $this->bumpQueueVersion();
            return response()->json(['message' => 'Student document(s) updated successfully.']);
        } catch (\Throwable $e) {
            Log::error('Student ID-card media update failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'The document update could not be saved. Please try again.'], 422);
        }
    }

    /** Record a review issue against a student ID-card record. */
    public function markIssue(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }
        abort_unless($this->allowsAction('issue'), 403, 'You do not have permission to record ID-card issues.');
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'session' => ['nullable', 'string', 'max:30'],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $student = Student::query()->find((int) $request->student_id);
        if (!$student) {
            return response()->json(['message' => 'Student record was not found.'], 404);
        }
        DB::table('student_id_card_issues')->insert([
            'student_id' => $student->id,
            'username' => $student->username,
            'session' => $request->input('session') ?: DB::table('session')->where('status', '1')->value('title'),
            'description' => trim((string) $request->input('description')),
            'status' => 'open',
            'reported_by' => session('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->bumpQueueVersion();
        return response()->json(['message' => 'The ID-card issue was recorded.']);
    }

    /** Close an issue after the correction has been reviewed. */
    public function resolveIssue(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }
        abort_unless($this->allowsAction('resolve_issue'), 403, 'You do not have permission to resolve ID-card issues.');
        $validator = Validator::make($request->all(), ['issue_id' => ['required', 'integer']]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $updated = DB::table('student_id_card_issues')
            ->where('id', (int) $request->issue_id)
            ->where('status', 'open')
            ->update(['status' => 'resolved', 'resolved_by' => session('id'), 'resolved_at' => now(), 'updated_at' => now()]);
        if (!$updated) {
            return response()->json(['message' => 'The issue is already resolved or could not be found.'], 404);
        }
        $this->bumpQueueVersion();
        return response()->json(['message' => 'The ID-card issue was resolved.']);
    }

    /** Return open ID-card issues for the dedicated issues tab. */
    public function issues(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }
        $query = DB::table('student_id_card_issues as i')
            ->join('students as s', 's.id', '=', 'i.student_id')
            ->leftJoin('faculty as f', 'f.code', '=', 's.faculty')
            ->leftJoin('department as d', 'd.code', '=', 's.department')
            ->leftJoin('program as p', 'p.code', '=', 's.program')
            ->where('i.status', 'open')
            ->when($request->filled('session') && $request->session !== 'all', fn ($q) => $q->where('i.session', $request->session))
            ->when($request->filled('faculty') && $request->faculty !== 'all', fn ($q) => $q->where('s.faculty', $request->faculty))
            ->when($request->filled('department') && $request->department !== 'all', fn ($q) => $q->where('s.department', $request->department))
            ->when($request->filled('program') && $request->program !== 'all', fn ($q) => $q->where('s.program', $request->program))
            ->when($request->filled('level') && $request->level !== 'all', fn ($q) => $q->where('s.level', $request->level));
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
                $q->where('s.fullname', 'like', $like)->orWhere('s.username', 'like', $like);
            });
        }
        $perPage = max(10, min(100, (int) $request->input('per_page', 25)));
        $rows = $query->select('i.id as issue_id', 'i.session', 'i.description', 'i.created_at as issue_created_at', 's.id as student_id', 's.username', 's.fullname', 'f.title as faculty', 'd.title as department', 'p.title as program', 's.level')
            ->orderByDesc('i.created_at')->paginate($perPage);
        return response()->json($rows);
    }

    public function generate(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'session' => ['required', 'string', 'max:30'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $student = Student::query()->whereKey((int) $request->student_id)->lockForUpdate()->first();
                if (!$student) {
                    throw new \RuntimeException('Student record was not found.');
                }

                $media = $this->studentMedia($student);
                if (!$media['ready']) {
                    throw new \RuntimeException('Generation is blocked until the student has both a profile photo and a signature. Missing: ' . implode(' and ', $media['missing']) . '.');
                }
                if (DB::table('student_id_card_issues')->where('student_id', $student->id)->where('status', 'open')->exists()) {
                    throw new \RuntimeException('Generation is blocked because this student has an open ID-card issue. Review and resolve the issue first.');
                }

                $session = (string) $request->session;
                $payment = DB::table('invoices')->where('username', $student->user_id)
                    ->where('description', 'ID CARDS')->where('status', 'Paid')->where('session', $session)
                    ->orderByDesc('updated_at')->orderByDesc('id')->first();
                $source = $payment ? 'id_card' : null;
                $currentSession = DB::table('session')->where('status', '1')->value('title');
                if (!$payment
                    && $currentSession
                    && $session === (string) $currentSession
                    && (string) $student->session_of_entry === (string) $currentSession
                    && (int) $student->school_fee === 1) {
                    $source = 'school_fee';
                }
                if (!$source) {
                    throw new \RuntimeException('This student has no confirmed ID-card payment or current-session school-fee coverage.');
                }

                $existing = DB::table('student_id_card_generations')
                    ->where('student_id', $student->id)->where('session', $session)->first();
                $values = [
                    'username' => $student->username,
                    'source' => $source,
                    'invoice_id' => $payment?->id,
                    'generated_by' => session('id'),
                    'generated_at' => now(),
                    'updated_at' => now(),
                ];
                if ($existing) {
                    DB::table('student_id_card_generations')->where('id', $existing->id)->update($values);
                } else {
                    DB::table('student_id_card_generations')->insert(array_merge($values, [
                        'student_id' => $student->id,
                        'session' => $session,
                        'created_at' => now(),
                    ]));
                }

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->fullname ?: 'Student',
                    'username' => $student->username ?: $student->id_no,
                    'session' => $session,
                    'card_url' => url('/id card/' . $student->id),
                ];
            });

            $this->bumpQueueVersion();
            return response()->json(['message' => 'ID card marked as generated.', 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage() ?: 'The card could not be marked as generated.'], 422);
        }
    }

    public function generateBulk(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        $items = collect($request->input('items', []))->filter(fn ($item) => is_array($item))->take(100)->values();
        if ($items->isEmpty()) {
            return response()->json(['message' => 'Select at least one awaiting card.'], 422);
        }

        $done = 0;
        $failed = [];
        $results = [];
        foreach ($items as $item) {
            try {
                $response = $this->generate(new Request([
                    'student_id' => $item['student_id'] ?? null,
                    'session' => $item['session'] ?? null,
                ]));
                if ($response->getStatusCode() >= 400) {
                    $body = $response->getData(true);
                    $failed[] = $body['message'] ?? 'The card could not be marked as generated.';
                } else {
                    $done++;
                    $body = $response->getData(true);
                    if (!empty($body['result'])) {
                        $results[] = $body['result'];
                    }
                }
            } catch (\Throwable $e) {
                $failed[] = $e->getMessage();
            }
        }

        return response()->json([
            'message' => $done . ' card(s) marked as generated.',
            'done' => $done,
            'failed' => $failed,
            'results' => $results,
        ]);
    }

    public function reset(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'session' => ['required', 'string', 'max:30'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        DB::table('student_id_card_generations')
            ->where('student_id', (int) $request->student_id)
            ->where('session', (string) $request->session)
            ->delete();
        $this->bumpQueueVersion();
        return response()->json(['message' => 'The card was returned to the awaiting list.']);
    }

    private function bumpQueueVersion(): void
    {
        Cache::forever('student-id-card:queue-version', (int) Cache::get('student-id-card:queue-version', 1) + 1);
    }

    private function allowsAction(string $action): bool
    {
        return strtoupper((string) session('accType')) === 'ADMIN'
            || PortalPermission::allows('/student id card', $action);
    }

    private function studentMedia($student): array
    {
        $disk = Storage::disk('public');
        $picture = !empty($student->picture) && ($disk->exists('picture/' . ltrim((string) $student->picture, '/'))
            || file_exists(public_path('storage/picture/' . ltrim((string) $student->picture, '/'))));
        $signatureName = $student->signiture ?: $student->passport_sign;
        $signatureDir = $student->signiture ? 'signature/' : 'passport_sign/';
        $signature = !empty($signatureName) && ($disk->exists($signatureDir . ltrim((string) $signatureName, '/'))
            || file_exists(public_path('storage/' . $signatureDir . ltrim((string) $signatureName, '/'))));
        $missing = [];
        if (!$picture) {
            $missing[] = 'profile photo';
        }
        if (!$signature) {
            $missing[] = 'signature';
        }
        return ['ready' => $picture && $signature, 'missing' => $missing];
    }

    private function studentMediaReady($row): bool
    {
        $disk = Storage::disk('public');
        $picture = !empty($row->picture) && $disk->exists('picture/' . ltrim((string) $row->picture, '/'));
        $signatureName = $row->signiture ?: $row->passport_sign;
        $signatureDir = $row->signiture ? 'signature/' : 'passport_sign/';
        $signature = !empty($signatureName) && $disk->exists($signatureDir . ltrim((string) $signatureName, '/'));
        return $picture && $signature;
    }

    private function missingStudentMedia($row): array
    {
        $missing = [];
        if (!$this->studentMediaReady($row)) {
            $disk = Storage::disk('public');
            if (empty($row->picture) || !$disk->exists('picture/' . ltrim((string) $row->picture, '/'))) {
                $missing[] = 'profile photo';
            }
            $signatureName = $row->signiture ?: $row->passport_sign;
            $signatureDir = $row->signiture ? 'signature/' : 'passport_sign/';
            if (empty($signatureName) || !$disk->exists($signatureDir . ltrim((string) $signatureName, '/'))) {
                $missing[] = 'signature';
            }
        }
        return $missing;
    }

    private function eligibleRecordsQuery(Request $request, bool $applyStatus = true)
    {
        $session = (string) $request->input('session', '');
        $selectedSession = $session !== '' && $session !== 'all' ? $session : null;
        // School-fee coverage identifies new students only in the active
        // academic session. It must never make an older-session student
        // eligible without a confirmed ID-card payment.
        $currentSession = (string) (DB::table('session')->where('status', '1')->value('title') ?: '');

        $paid = DB::table('invoices as i')
            ->join('students as s', function ($join) {
                // Legacy invoices and student accounts use different utf8mb4
                // collations. Normalize both sides so the queue works on the
                // production database without requiring a risky table rebuild.
                $join->on(
                    DB::raw('CONVERT(s.user_id USING utf8mb4) COLLATE utf8mb4_general_ci'),
                    '=',
                    DB::raw('i.username')
                );
            })
            ->leftJoin('faculty as f', 'f.code', '=', 's.faculty')
            ->leftJoin('department as d', 'd.code', '=', 's.department')
            ->leftJoin('program as p', 'p.code', '=', 's.program')
            ->where('i.description', 'ID CARDS')
            ->where('i.status', 'Paid')
            ->when($selectedSession, fn ($q) => $q->where('i.session', $selectedSession))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('invoices as newer')
                    ->whereColumn('newer.username', 'i.username')
                    ->whereColumn('newer.session', 'i.session')
                    ->where('newer.description', 'ID CARDS')
                    ->where('newer.status', 'Paid')
                    ->whereColumn('newer.id', '>', 'i.id');
            })
            ->selectRaw("s.id as student_id, s.username as username, s.fullname as fullname, s.faculty as faculty, s.department as department, s.program as program, s.level as level, s.contact_phone as contact_phone, s.home_phone as home_phone, s.picture as picture, s.signiture as signiture, s.passport_sign as passport_sign, f.title as faculty_title, d.title as department_title, p.title as program_title, i.session as session, 'id_card' as source, i.id as payment_id, i.updated_at as paid_at");

        $coverageQuery = function (string $entrySession, array $paidUsernames = []) {
            $query = DB::table('students as s')
                ->leftJoin('faculty as f', 'f.code', '=', 's.faculty')
                ->leftJoin('department as d', 'd.code', '=', 's.department')
                ->leftJoin('program as p', 'p.code', '=', 's.program')
                ->where('s.status', 1)
                ->where('s.school_fee', 1)
                ->where('s.session_of_entry', $entrySession)
                ->selectRaw("s.id as student_id, s.username as username, s.fullname as fullname, s.faculty as faculty, s.department as department, s.program as program, s.level as level, s.contact_phone as contact_phone, s.home_phone as home_phone, s.picture as picture, s.signiture as signiture, s.passport_sign as passport_sign, f.title as faculty_title, d.title as department_title, p.title as program_title, s.session_of_entry as session, 'school_fee' as source, NULL as payment_id, NULL as paid_at");
            if ($paidUsernames) {
                $query->whereNotIn('s.user_id', $paidUsernames);
            }
            return $query;
        };

        if ($selectedSession) {
            // The default/current-session view is the hot path. Resolve the
            // small set of paid accounts first, then let the indexed student
            // query exclude them without a cross-collation correlated scan.
            $paidUsernames = DB::table('invoices')
                ->where('description', 'ID CARDS')->where('status', 'Paid')->where('session', $selectedSession)
                ->whereNotNull('username')->distinct()->pluck('username')->all();
            $covered = $selectedSession === $currentSession
                ? $coverageQuery($currentSession, $paidUsernames)
                : $coverageQuery('__no_current_session_coverage__', ['__no_match__']);
        } else {
            // The all-session view still gets coverage only for new students
            // in the current session. Paid ID-card records remain available
            // from every session.
            if ($currentSession !== '') {
                $paidUsernames = DB::table('invoices')
                    ->where('description', 'ID CARDS')->where('status', 'Paid')->where('session', $currentSession)
                    ->whereNotNull('username')->distinct()->pluck('username')->all();
                $covered = $coverageQuery($currentSession, $paidUsernames);
            } else {
                $covered = $coverageQuery('__no_current_session_coverage__', ['__no_match__']);
            }
        }

        $union = $paid->unionAll($covered);
        $records = DB::query()->fromSub($union, 'eligible')
            ->leftJoin('student_id_card_generations as g', function ($join) {
                $join->on('g.student_id', '=', 'eligible.student_id')->on(
                    DB::raw('g.session COLLATE utf8mb4_general_ci'),
                    '=',
                    DB::raw('eligible.session COLLATE utf8mb4_general_ci')
                );
            })
            ->leftJoinSub(
                DB::table('student_id_card_issues')
                    ->where('status', 'open')
                    ->select('student_id')
                    ->selectRaw('MAX(id) as issue_id')
                    ->groupBy('student_id'),
                'oi',
                'oi.student_id', '=', 'eligible.student_id'
            )
            ->select('eligible.*', 'g.generated_at as generation_generated_at')
            ->addSelect(DB::raw("(SELECT issue_latest.description FROM student_id_card_issues AS issue_latest WHERE issue_latest.student_id = eligible.student_id AND issue_latest.status = 'open' ORDER BY issue_latest.id DESC LIMIT 1) AS issue_description"));

        if ($selectedSession) {
            $records->where('eligible.session', $selectedSession);
        }
        foreach (['faculty', 'department', 'program', 'level'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '' && $value !== 'all') {
                $records->where('eligible.' . $field, $value);
            }
        }
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $records->where(function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
                $q->where('eligible.fullname', 'like', $like)->orWhere('eligible.username', 'like', $like);
            });
        }
        if ($applyStatus) {
            $status = (string) $request->input('status', 'all');
            if ($status === 'awaiting') {
                $records->whereNull('g.generated_at');
            } elseif ($status === 'generated') {
                $records->whereNotNull('g.generated_at');
            }
        }

        return $records;
    }

    /**
     * Generate a print-ready, two-sided CR80 student ID card.
     *
     * Each side is a portrait ID-1 page: 53.98mm wide by 85.6mm tall.
     */
    public function download($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $student = Student::query()->where('id', $id)->firstOrFail();

            $isPostgraduate = Str::contains(strtoupper((string) $student->username), 'PG')
                || Str::contains(strtoupper((string) $student->faculty), '.PG');

            // Keep the established postgraduate card unchanged. The new
            // Dompdf CR80 design is intentionally for undergraduate cards only.
            if ($isPostgraduate) {
                return view('pdf/pg id card', ['id' => $id]);
            }

            $facultyTitle = DB::table('faculty')->where('code', $student->faculty)->value('title')
                ?: ($student->faculty ?: 'Not recorded');
            $programRecord = DB::table('program')
                ->where('code', $student->program)
                ->first(['title', 'duration']);
            $programTitle = $programRecord?->title ?: ($student->program ?: 'Not recorded');

            $photoPath = $this->firstExistingPath([
                $student->picture ? public_path('storage/picture/' . $student->picture) : null,
                $student->picture ? storage_path('app/public/picture/' . $student->picture) : null,
                $student->passport_pic ? public_path('storage/passport_pic/' . $student->passport_pic) : null,
                public_path('card/default.jpg'),
            ]);

            $signaturePath = $this->firstExistingPath([
                $student->signiture ? public_path('storage/signature/' . $student->signiture) : null,
                $student->signiture ? storage_path('app/public/signature/' . $student->signiture) : null,
                $student->passport_sign ? public_path('storage/passport_sign/' . $student->passport_sign) : null,
            ]);

            $logoPath = $this->firstExistingPath([
                public_path('uploads/logo-transparent.png'),
                public_path('uploads/logo.png'),
            ]);
            $registrarSignaturePath = $this->firstExistingPath([
                public_path('card/registrar sign.png'),
            ]);

            $matric = trim((string) ($student->username ?: $student->jamb_no ?: 'Not assigned'));
            $fullName = trim((string) ($student->fullname ?: 'Student'));
            $issueDate = $this->formatCardDate($student->issue_date, now()->format('d M Y'));
            $expiryDate = $this->expiryDateForProgramme(
                $student->issue_date,
                $programRecord?->duration,
                $student->expire_date
            );
            $qrData = implode("\n", array_filter([
                $matric,
                $fullName,
                $programTitle,
            ]));

            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), storage_path()],
                'tempDir' => storage_path('app/dompdf'),
                'fontCache' => storage_path('fonts'),
                'fontDir' => storage_path('fonts'),
            ]);
            $dompdf = new Dompdf($options);
            $html = View::make('pdf.student-id-card', [
                'student' => $student,
                'facultyTitle' => $facultyTitle,
                'programTitle' => $programTitle,
                'matric' => $matric,
                'fullName' => $fullName,
                'issueDate' => $issueDate,
                'expiryDate' => $expiryDate,
                'photo' => $this->cardImage($photoPath, 21, 22.5),
                'signature' => $this->cardImage($signaturePath, 19, 6.5),
                'registrarSignature' => $this->cardImage($registrarSignaturePath, 19, 6.5),
                'logo' => $this->cardImage($logoPath, 9, 11),
                'qrData' => $this->makeQrDataUri($qrData),
                'nameText' => $this->fitCardText($dompdf, $fullName, 47, 2, 8.6, 6),
                'matricText' => $this->fitCardText($dompdf, $matric, 47, 1, 7.4, 4.8),
                'programText' => $this->fitCardText($dompdf, $programTitle, 47, 2, 6.6, 5.2),
                'facultyText' => $this->fitCardText($dompdf, $facultyTitle, 47, 2, 6, 5),
                'stateText' => $this->fitCardText($dompdf, $student->state_origin ?: 'Not recorded', 22, 1, 6, 4.5),
                'nationalityText' => $this->fitCardText($dompdf, $student->country ?: 'Not recorded', 22, 1, 6, 4.5),
                'kinText' => $this->fitCardText($dompdf, $student->kin_name ?: 'Not recorded', 47, 2, 6.2, 5),
                'kinPhoneText' => $this->fitCardText($dompdf, $student->kin_phone ?: 'Not recorded', 47, 1, 6.8, 5),
            ])->render();

            $dompdf->loadHtml($html, 'UTF-8');
            // ISO ID-1, portrait. Values are PDF points (72 points per inch).
            $dompdf->setPaper([0, 0, 153.014173, 242.645669], 'portrait');
            $dompdf->render();

            $safeName = Str::slug($matric ?: 'student');
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="student-id-card-' . $safeName . '.pdf"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            Log::error('Student ID card PDF generation failed', [
                'student_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'The student ID card could not be generated right now.');
        }
    }

    private function firstExistingPath(array $paths): string
    {
        foreach ($paths as $path) {
            if ($path && is_file($path) && is_readable($path) && @getimagesize($path)) {
                return $path;
            }
        }

        return '';
    }

    /** Explicit dimensions preserve image proportions in Dompdf (no object-fit). */
    private function cardImage(string $path, float $boxWidth, float $boxHeight): array
    {
        $size = $path !== '' ? @getimagesize($path) : false;
        if (!$size || !$size[0] || !$size[1]) {
            return ['src' => '', 'width' => 0, 'height' => 0, 'left' => 0, 'top' => 0];
        }

        $scale = min($boxWidth / $size[0], $boxHeight / $size[1]);
        $width = $size[0] * $scale;
        $height = $size[1] * $scale;

        return [
            'src' => $this->toDataUri($path),
            'width' => round($width, 3),
            'height' => round($height, 3),
            'left' => round(($boxWidth - $width) / 2, 3),
            'top' => round(($boxHeight - $height) / 2, 3),
        ];
    }

    /** Fit complete values to reserved lines using the actual PDF font metrics. */
    private function fitCardText(Dompdf $pdf, string $text, float $widthMm, int $maxLines, float $maximum, float $minimum): array
    {
        $text = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $text)));
        $metrics = $pdf->getFontMetrics();
        $font = $metrics->getFont('DejaVu Sans', 'bold');
        $width = $widthMm * 72 / 25.4;

        for ($size = $maximum; $size >= $minimum - 0.01; $size -= 0.2) {
            $lines = [];
            $line = '';
            foreach (explode(' ', $text) as $word) {
                $candidate = $line === '' ? $word : $line . ' ' . $word;
                if ($line !== '' && $metrics->getTextWidth($candidate, $font, $size) > $width) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $candidate;
                }
            }
            $lines[] = $line;
            $widest = max(array_map(fn ($value) => $metrics->getTextWidth($value, $font, $size), $lines));
            if (count($lines) <= $maxLines && $widest <= $width) {
                return ['text' => implode("\n", $lines), 'size' => round($size, 2)];
            }
        }

        // Do not silently clip or omit unusually long official record values.
        throw new \RuntimeException('An ID-card field is too long to print legibly. Please review the student record.');
    }

    private function toDataUri(string $path): string
    {
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : null;
        $mime = $mime ?: ('image/' . strtolower(pathinfo($path, PATHINFO_EXTENSION)));

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private function makeQrDataUri(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                'scale' => 5,
                'imageBase64' => true,
                'quietzoneSize' => 4,
            ]);

            return (new \chillerlan\QRCode\QRCode($options))->render($value);
        } catch (\Throwable $e) {
            Log::warning('Student ID card QR generation failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function formatCardDate($value, string $fallback): string
    {
        if (!$value) {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /** Calculate an undergraduate card expiry from the programme duration. */
    private function expiryDateForProgramme($issueValue, $duration, $storedExpiry): string
    {
        $years = (int) $duration;
        if ($years <= 0 && is_string($duration) && preg_match('/\d+/', $duration, $matches)) {
            $years = (int) $matches[0];
        }

        if ($years > 0) {
            try {
                return \Carbon\Carbon::parse($issueValue ?: now())
                    ->addYears($years)
                    ->format('d M Y');
            } catch (\Throwable $e) {
                // Fall back to the stored date below when an old issue date is invalid.
            }
        }

        return $this->formatCardDate($storedExpiry, now()->copy()->addYears(5)->format('d M Y'));
    }
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
