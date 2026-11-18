<?php

namespace App\Http\Controllers;

use App\Exports\ResultsExport;
use App\Imports\ResultImport;
use App\Support\StreamingXlsxExporter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function Laravel\Prompts\table;

class ResultsController extends Controller
{
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace('create ', '', $contents);
        $contents = str_replace('upload ', '', $contents);
        $contents = str_replace('download ', '', $contents);
        $contents = str_replace('update ', '', $contents);
        $contents = str_replace('delete ', '', $contents);
        $this->page = $contents;
        $this->table = str_replace(' ', '_', $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $isStaff = session('accType') === 'Staff';
        $resultsSession = \App\Http\Controllers\SystemSettingsController::getResultsSession();

        $filters = [
            'faculty' => trim((string) $req->query('faculty', '')),
            'department' => trim((string) $req->query('department', '')),
            'program' => trim((string) $req->query('program', '')),
            'code' => trim((string) $req->query('code', '')),
            'session' => trim((string) $req->query('session', '')),
            'semester' => trim((string) $req->query('semester', '')),
            'level' => trim((string) $req->query('level', '')),
            'approve' => trim((string) $req->query('approve', '')),
            'username' => trim((string) $req->query('username', '')),
        ];
        $filters = array_map(static fn ($value) => $value === 'all' ? '' : $value, $filters);
        $hasFilters = collect($filters)->contains(static fn ($value) => $value !== '');

        $perPage = (int) $req->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

        $query = DB::table('results as r')
            ->select([
                'r.id', 'r.username', 'r.code', 'r.ca', 'r.exam', 'r.total',
                'r.grade', 'r.session', 'r.semester', 'r.approve', 'r.lecturer',
                'r.corrigenda', 'r.program', 'r.department', 'r.unit', 'r.point',
                'r.remark',
            ]);

        if ($filters['faculty'] !== '') {
            $query->join('program as filter_program', 'filter_program.code', '=', 'r.program')
                ->where('filter_program.faculty', $filters['faculty']);
        }

        foreach (['department', 'program', 'code', 'session', 'semester', 'level', 'approve'] as $column) {
            if ($filters[$column] !== '') {
                $query->where('r.' . $column, $filters[$column]);
            }
        }
        if ($filters['username'] !== '') {
            $query->where('r.username', 'like', '%' . $filters['username'] . '%');
        }

        if ($isStaff) {
            $assignedCourses = DB::table('course_allocation')
                ->where(['username' => session('username'), 'type' => 'MAIN'])
                ->pluck('course');
            if ($assignedCourses->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('r.code', $assignedCourses);
            }
        }

        // Do not load the entire results table when the page is first opened.
        // A filter is required before rows are read, and all filtered results
        // are loaded with a bounded, server-side paginator.
        if (!$hasFilters) {
            $query->whereRaw('1 = 0');
        }
        $data['data'] = $query
            ->orderByDesc('r.id')
            ->paginate($perPage)
            ->withQueryString();

        $data['faculty'] = Cache::remember('results.filter.faculties', 600, static fn () => DB::table('faculty')
            ->where('status', '1')
            ->select('code', 'title')
            ->orderBy('title', 'ASC')
            ->get());
        $data['session'] = Cache::remember('results.filter.sessions', 600, static fn () => DB::table('session')
            ->select('title')
            ->orderBy('title', 'ASC')
            ->get());
        $data['resultLevels'] = Cache::remember('results.filter.levels', 600, static fn () => DB::table('course')
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->select('level')
            ->distinct()
            ->orderByRaw("CASE WHEN level REGEXP '^[0-9]+$' THEN CAST(level AS UNSIGNED) ELSE 999999 END")
            ->orderBy('level', 'ASC')
            ->pluck('level'));
        $data['filterDepartments'] = $filters['faculty'] !== ''
            ? DB::table('department')->where('faculty', $filters['faculty'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['filterPrograms'] = $filters['department'] !== ''
            ? DB::table('program')->where('department', $filters['department'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['selectedCourse'] = $filters['code'] !== ''
            ? DB::table('course')->where('code', $filters['code'])->select('code', 'title', 'unit')->first()
            : null;
        $data['lecturerCourses'] = DB::table('course_allocation')
            ->where(['username' => session('username'), 'type' => 'MAIN'])
            ->select('course')
            ->groupBy('course')
            ->orderBy('course', 'ASC')
            ->get();
        // The delete dialog only needs courses assigned to the lecturer. Do
        // not scan the million-row results table just to build this list.
        $data['myUploads'] = DB::table('course_allocation')
            ->where(['username' => session('username'), 'type' => 'MAIN'])
            ->select('course as code')
            ->groupBy('course')
            ->orderBy('course', 'ASC')
            ->get();
        $data['sessions'] = $resultsSession;
        $data['filters'] = $filters;
        $data['hasResultFilters'] = $hasFilters;
        $data['perPage'] = $perPage;
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    /**
     * Download final approved results for administrators.
     *
     * All filters are optional. The export intentionally uses only VC-approved
     * records so an academic workbook can never include a draft or an
     * intermediate approval stage.
     */
    public function exportExcel(Request $req)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            abort(403, 'Only administrators can export results.');
        }

        $filters = $req->validate([
            'faculty' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:30'],
            'session' => ['nullable', 'string', 'max:30'],
            'semester' => ['nullable', 'string', 'max:30'],
        ]);

        foreach ($filters as $key => $value) {
            if ($value === 'all') {
                $filters[$key] = null;
            }
        }

        // The approved result set can be very large (hundreds of thousands of
        // rows). Generate the XLSX package directly from the database cursor so
        // PhpSpreadsheet does not retain every cell in the PHP-FPM worker.
        @set_time_limit(0);

        $export = new ResultsExport($filters);
        $xlsxPath = StreamingXlsxExporter::create(
            $export->generator(),
            $export->headings(),
            static fn ($row): array => $export->map($row)
        );

        return response()->download(
            $xlsxPath,
            'approved-results-' . now()->format('Y-m-d_His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function pendingResults(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        abort_unless(\App\Support\PendingResultAccess::canView((string) $req->code, (string) $req->session), 403,
            'You do not have access to review this course for the selected session.');

        // Keep the course-specific review link from approve-results scoped to
        // the lecturer who actually owns the course allocation.  The
        // middleware performs the same check, but this controller guard also
        // protects the endpoint if it is called through another route or
        // middleware configuration.
        if (session('accType') === 'Staff' && $req->query('approve') === 'system') {
            $courseCode = trim((string) $req->query('code', ''));
            $assignedToCourse = $courseCode !== '' && DB::table('course_allocation')
                ->whereRaw('UPPER(TRIM(username)) = ?', [strtoupper(trim((string) session('username')))])
                ->whereRaw('UPPER(TRIM(type)) = ?', ['MAIN'])
                ->whereRaw('UPPER(TRIM(course)) = ?', [strtoupper($courseCode)])
                ->where(function ($query) use ($req) {
                    $query->whereNull('session')->orWhere('session', '')->orWhere('session', $req->query('session'));
                })
                ->exists();

            if (!$assignedToCourse) {
                abort(403, 'You are not the main lecturer assigned to this course for the selected session.');
            }
        }

        $req->validate(['code' => 'required|string|max:50', 'session' => 'required|string|max:15', 'semester' => 'required|string|max:25', 'approve' => 'nullable|in:system,lecturer,hod,dean,cs,vc']);
        $session = $req->session;
            $rs = DB::table('results')
                ->select(
                    'username',
                    'ca',
                    'code',
                    'exam',
                    'total',
                    'grade',
                    'approve'
                )
                ->where([
                    'session' => $req->session,
                    'semester' => $req->semester,
                    'code' => $req->code,
                ]);
            $data['results'] = $rs->get();
            $result = $rs->pluck('username');
            $data['data'] = DB::table('student_course_registration')
                ->select('username', 'comment', 'code', 'id')
                ->where(['session' => $req->session, 'semester' => $req->semester, 'code' => $req->code])
                ->whereNotIn('username', $result)
                ->get();
            $data['pendingIds'] = DB::table('student_course_registration')
                ->select('id')
                ->where(['session' => $req->session, 'semester' => $req->semester, 'code' => $req->code])
                ->whereNotIn('username', $result)
                ->pluck('id');
            // The course review always uses the selected batch, including when a CSRF token is in the URL.
        $data['faculty'] = DB::table('faculty')
            ->where(['status' => '1'])
            ->select('code', 'title')
            ->orderBy('title', 'ASC')
            ->get();
        $data['session'] = DB::table('session')
            ->select('title')
            ->orderBy('title', 'ASC')
            ->get();
        $data['lecturerCourses'] = DB::table('course_allocation')
            ->where(['username' => session('username')])
            ->select('course')
            ->orderBy('course', 'ASC')
            ->get();
        $data['lecturer'] = DB::table('course_allocation')
            ->where(['username' => session('username'), 'course' => $req->code])
            ->select('username')
            ->value('username');
        $data['sessions'] = $session;
        $data['session'] = $req->session;
        $data['semester'] = $req->semester;
        $data['code'] = $req->code;
        $data['page'] = 'pending results';
        $data['title'] = 'Pending Results';
        $data['sessions'] = $req->session;
        $data['approvalStage'] = (string) $req->query('approve', optional($data['results']->first())->approve);
        $context = ['code' => $req->code, 'session' => $req->session, 'semester' => $req->semester, 'approve' => $data['approvalStage']];
        $reviewer = app(ApproveResultsController::class);
        $data['canSubmitReview'] = $reviewer->canSubmitApprovalStage($context);
        $data['canRejectReview'] = $reviewer->canSubmitApprovalStage($context, 'update');
        $data['canCommentOmissions'] = \App\Support\PendingResultAccess::canComment((string) $req->code, (string) $req->session, (string) $req->semester);
        return view('main', $data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()
            ->back()
            ->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $code = $datas['code'];
        $id = $datas['username'];
        $idd = $datas['id'];
        $ca = (int) $datas['ca'];
        $exam = (int) $datas['exam'];
        $getYear = explode('/', $id);
        $getYear = '20' . $getYear[0];
        // if ($exam > 70) {
        //     $exam = 70;
        // }
        // if ($ca > 30) {
        //     $ca = 30;
        // }

        $std_program = DB::table('students')
            ->where(['username' => $id])
            ->select('program', 'structure_id')
            ->first();
        if (!$std_program || !$std_program->structure_id) {
            return redirect()->back()->with('error', 'The student has no curriculum structure assigned, so this result cannot be graded safely.');
        }
        $grading = DB::table('program_course_registration')
            ->where(['code' => $code, 'program' => $std_program?->program, 'structure_id' => $std_program?->structure_id])
            ->select('grading')
            ->value('grading');
        $data = DB::table('results')
            ->where(['id' => $idd])
            ->select('id', 'program')
            ->get();
        foreach ($data as $row) {
            $year = $id[0] . $id[1];
            $total = (int) $ca + (int) $exam;

            if ($total > 100) {
                $total = 100;
            }
            $unit = DB::table('course')
                ->where(['code' => $code])
                ->value('unit');
            $unit = (int) $unit;
            $remark = 'PASS';

            $gradings = DB::table('grading_system')
                ->where(function ($query) use ($getYear) {
                    $query
                        ->where(function ($innerQuery) use ($getYear) {
                            $innerQuery
                                ->where('to', '!=', 'current')
                                ->where('from', '<=', $getYear)
                                ->where('to', '>=', $getYear);
                        })
                        ->orWhere(function ($innerQuery) use ($getYear) {
                            $innerQuery
                                ->where('to', '=', 'current')
                                ->where('from', '<=', $getYear);
                        });
                })
                ->where('min_score', '<=', $total)
                ->where('max_score', '>=', $total)
                ->where('name', $grading)
                ->get();
            // dd($gradings);

            foreach ($gradings as $gs) {
                $grade = $gs->grade;
                $point = $unit * $gs->point;
                $remark = $gs->remark;
                $records['ca'] = $ca;
                $records['exam'] = $exam;
                $records['total'] = $total;
                $records['grade'] = $grade;
                $records['remark'] = $remark;
                $records['ugp'] = $point;
                $records['updated_at'] = NOW();
                // print_r($records['grade']);
                // die;
                if ($point > 0) {
                    $records['point'] = $point / $unit;
                } else {
                    $records['point'] = $point;
                }
                DB::table('results')
                    ->where(['id' => $idd])
                    ->update($records);
            }
        }
        return redirect()
            ->back()
            ->with('success', 'Record Updated!!!');
    }

    public function updateMed(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $datas = $req->all();
        $records['grade'] = 'F';
        $records['remark'] = 'FC';
        $records['ugp'] = 0;
        $records['point'] = 0;
        $records['updated_at'] = NOW();
        DB::table('results')->where(['id' => $datas['id']])->update($records);
        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function pending(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        // Older forms send the selected IDs as a JSON string; accept both formats.
        if ($req->has('ids') && is_string($req->input('ids'))) {
            $req->merge(['ids' => json_decode($req->input('ids'), true)]);
        }
        $validated = $req->validate([
            'id' => 'required_without:ids|nullable|integer|min:1',
            'ids' => 'required_without:id|nullable|array|min:1|max:5000',
            'ids.*' => 'required|integer|min:1|distinct',
            'comment' => 'required|string|max:2000',
        ], ['comment.required' => 'Please explain why the result is missing.']);
        $ids = $validated['ids'] ?? [$validated['id']];
        $context = DB::transaction(function () use ($ids, $validated) {
            $records = DB::table('student_course_registration')->whereIn('id', $ids)->lockForUpdate()->get();
            abort_unless($records->count() === count($ids), 422, 'Some selected registrations no longer exist. Refresh the course review and try again.');
            $groups = $records->groupBy(fn ($row) => $row->code . '|' . $row->session . '|' . $row->semester);
            abort_unless($groups->count() === 1, 422, 'Select students from one course, session and semester at a time.');
            $first = $records->first();
            $batch = DB::table('results')->where(['code' => $first->code, 'session' => $first->session, 'semester' => $first->semester]);
            $results = (clone $batch)->lockForUpdate()->get(['username', 'approve']);
            abort_unless(\App\Support\PendingResultAccess::canComment($first->code, $first->session, $first->semester), 403,
                'Only the main lecturer assigned to this course can comment on missing results before submission.');
            abort_if($results->contains(fn ($row) => $row->approve !== 'system'), 409,
                'This course has already moved to approval. Omission comments can no longer be changed.');
            abort_if($results->pluck('username')->intersect($records->pluck('username'))->isNotEmpty(), 409,
                'A result has now been uploaded for a selected student. Refresh the course review before commenting.');
            DB::table('student_course_registration')->whereIn('id', $ids)->update([
                'comment' => trim($validated['comment']), 'grade' => 'F', 'status' => 'FAILED',
            ]);
            return ['code' => $first->code, 'session' => $first->session, 'semester' => $first->semester, 'approve' => 'system'];
        });

        return redirect('/pending-results?' . http_build_query($context))
            ->with('success', count($ids) . ' omission comment(s) saved.');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)
            ->where('id', $req->id)
            ->delete();

        return redirect()
            ->back()
            ->with('success', 'Record Deleted!!!');
    }

    public function deleteResults(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table('results')
            ->where(['lecturer' => session('username'), 'code' => $req->code, 'semester' => $req->semester, 'session' => $req->session, 'approve' => 'system'])
            ->delete();

        return redirect()
            ->back()
            ->with('success', 'Record Deleted!!!');
    }

    /**
     * Dedicated result-upload workspace. Keeping this separate from the
     * results table makes the upload context clear and gives us a place to
     * return a row-by-row processing report.
     */
    public function uploadPage(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        return view('main', $this->uploadPageData([
            'page' => 'result upload',
            'title' => 'Upload Course Results',
        ]));
    }

    public function upload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:15360',
            'course' => 'required|string|max:50',
            'session' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => 'required|string|max:25',
            'per' => 'required|in:70,30,60,40,100',
        ], [
            'file.required' => 'Choose the Excel result spreadsheet before processing.',
            'file.file' => 'The selected upload is not a valid file. Choose the Excel spreadsheet again.',
            'file.mimes' => 'Upload the official Excel result template in .xlsx or .xls format.',
            'file.max' => 'The spreadsheet is too large. Please keep it below 15 MB or split the upload into smaller files.',
            'course.required' => 'Select the course whose results you are uploading.',
            'session.required' => 'Select the academic session for these results.',
            'session.regex' => 'Select a valid academic session, for example 2025/2026.',
            'semester.required' => 'Select the semester for these results.',
            'per.required' => 'Select the score format used in the spreadsheet.',
            'per.in' => 'Choose one of the available CA and examination score formats.',
        ]);

        $course = (string) $request->input('course');
        if (session('accType') === 'Staff') {
            $assigned = DB::table('course_allocation')
                ->where([
                    'username' => session('username'),
                    'course' => $course,
                    'type' => 'MAIN',
                ])
                ->exists();
            if (!$assigned) {
                return redirect()->route('results.upload.page')
                    ->withErrors(['course' => 'This course is not assigned to your account. Select one of your assigned courses or contact the Course Coordinator.'])
                    ->withInput();
            }
        }

        if (!DB::table('course')->where('code', $course)->exists()) {
            return redirect()->route('results.upload.page')
                ->withErrors(['course' => 'The selected course is not available. Return to the course list and select a valid course.'])
                ->withInput();
        }

        try {
            $import = new ResultImport(
                null,
                null,
                null,
                $course,
                $request->input('per'),
                $request->input('semester'),
                $request->input('session')
            );
            Excel::import($import, $request->file('file'));
            $report = $import->report();

            return view('main', $this->uploadPageData([
                'page' => 'result upload',
                'title' => 'Upload Course Results',
                'uploadReport' => $report,
                'uploadContext' => [
                    'course' => $course,
                    'session' => $request->input('session'),
                    'semester' => $request->input('semester'),
                    'per' => $request->input('per'),
                    'filename' => $request->file('file')->getClientOriginalName(),
                ],
            ]));
        } catch (\Throwable $exception) {
            report($exception);
            return view('main', $this->uploadPageData([
                'page' => 'result upload',
                'title' => 'Upload Course Results',
                'uploadError' => 'We could not read this spreadsheet. Download the official template, keep the column headings unchanged, save it as an Excel file (.xlsx or .xls), and upload it again. If the problem continues, contact Academic Records.',
            ]));
        }
    }

    private function uploadPageData(array $extra = []): array
    {
        $currentSession = \App\Http\Controllers\SystemSettingsController::getResultsSession();
        $isStaff = session('accType') === 'Staff';

        if ($isStaff) {
            $courses = DB::table('course_allocation as allocation')
                ->leftJoin('course', 'course.code', '=', 'allocation.course')
                ->where('allocation.username', session('username'))
                ->where('allocation.type', 'MAIN')
                ->select(
                    'allocation.course as code',
                    'course.title',
                    'course.unit'
                )
                ->groupBy('allocation.course', 'course.title', 'course.unit')
                ->orderBy('allocation.course')
                ->get();
        } else {
            $courses = DB::table('course')
                ->select('code', 'title', 'unit')
                ->orderBy('code')
                ->get();
        }

        return array_merge([
            'lecturerCourses' => $courses,
            'sessions' => DB::table('session')->select('title')->orderByDesc('title')->get(),
            'currentSession' => $currentSession,
            'isStaffUploader' => $isStaff,
        ], $extra);
    }

    public function uploadss(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads');  // Save the file temporarily

            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'message' => 'File uploaded successfully. Processing will start shortly.',
            ]);
        }

        return response()->json(['error' => 'File not found or other error occurred'], 400);
    }

    public function processBatch(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'start' => 'required|integer',
            'length' => 'required|integer',
            'faculty' => 'required|string',
            'department' => 'required|string',
            'program' => 'required|string',
            'course' => 'required|string',
            'per' => 'required|string',
            'semester' => 'required|string',
            'session' => 'required|string',
        ]);

        $filePath = $request->file_path;
        $start = $request->start;
        $length = $request->length;

        // Load only the specified rows
        $rows = Excel::toArray(null, storage_path('app/' . $filePath));
        $data = array_slice($rows[0], $start, $length);

        // Import the sliced data
        $import = new ResultImport(
            $request->faculty,
            $request->department,
            $request->program,
            $request->course,
            $request->per,
            $request->semester,
            $request->session
        );
        $import->collection(collect($data));

        return response()->json(['success' => true, 'message' => 'Batch processed successfully.']);
    }

    public function getProgress()
    {
        $progress = Cache::get('import_progress', 0);
        return response()->json(['progress' => $progress]);
    }

    public function corrigenda(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $session = \App\Http\Controllers\SystemSettingsController::getResultsSession();
        if ($req->has('_token')) {
            $data['data'] = DB::table('corrigenda')
                ->select(
                    'corrigenda.*',
                    'results.username AS username',
                    'results.code AS code',
                    'results.ca AS old_ca',
                    'results.exam AS old_exam'
                )
                ->leftJoin('results', function ($join) {
                    $join->on('results.id', '=', 'corrigenda.result_id');
                })
                ->where('corrigenda.department', $req->department)
                ->where('corrigenda.session', $req->session)
                ->get();
        } else {
            $data['data'] = DB::table('corrigenda')
                ->select(
                    'corrigenda.*',
                    'results.username AS username',
                    'results.code AS code',
                    'results.ca AS old_ca',
                    'results.exam AS old_exam'
                )
                ->leftJoin('results', function ($join) {
                    $join->on('results.id', '=', 'corrigenda.result_id');
                })
                ->where('results.code', 'code')
                ->get();
        }
        $data['faculty'] = DB::table('faculty')
            ->where(['status' => '1'])
            ->select('code', 'title')
            ->orderBy('title', 'ASC')
            ->get();
        $data['session'] = DB::table('session')
            ->select('title')
            ->orderBy('title', 'ASC')
            ->get();
        $data['lecturerCourses'] = DB::table('course_allocation')
            ->where(['username' => session('username')])
            ->select('course')
            ->orderBy('course', 'ASC')
            ->get();

        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        if (session('accType') == 'Staff' && session('appointment') != 'DEAN' && session('appointment') != 'VC' && session('unit') != 'COURSE SYSTEM') {
            $data['sessions'] = $session;
            if (session('appointment') == 'HOD') {
                $lecturerCourses = DB::table('course')->where(['department' => session('department')])->select('code')->pluck('code');
            } else {
                $lecturerCourses = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->pluck('course');
            }

            $data['data'] = DB::table('corrigenda')
                ->select(
                    'corrigenda.*',
                    'results.username AS username',
                    'results.code AS code',
                    'results.ca AS old_ca',
                    'results.exam AS old_exam'
                )
                ->leftJoin('results', function ($join) {
                    $join->on('results.id', '=', 'corrigenda.result_id');
                })
                ->whereIn('results.code', $lecturerCourses)
                ->get();
        }
        $data['sessions'] = $session;
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function initiateCorrigenda(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data = DB::table('results')
            ->where('id', $req->id)
            ->get();
        foreach ($data as $row) {
            $ca = $row->ca;
            $exam = $row->exam;
            $department = DB::table('course')->where('code', $row->code)->select('department')->value('department');
            DB::table('corrigenda')->insert([
                'result_id' => $row->id,
                'ca' => 0,
                'exam' => 0,
                'program' => $row->program,
                'department' => $department,
                'created_at' => now(),
                'updated_at' => now(),
                'session' => session('system_session'),
                'semester' => session('system_semester'),
                'user' => session('username'),
                'lecturer' => $row->lecturer,
            ]);
            DB::table('results')
                ->where('id', $row->id)
                ->update(['corrigenda' => 1]);
        }
        return redirect()
            ->back()
            ->with('success', 'Done!!!');
    }

    public function updateCorrigenda(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $data = DB::table('corrigenda')->where(['id' => $req->id])->select('approve')->limit(1)->get();
        // dd($datas);
        foreach ($data as $row) {
            if ($row->approve == 'system') {
                $code = $datas['code'];
                $username = $datas['username'];
                $idd = $datas['id'];
                $ca = (int) $datas['ca'];
                $exam = (int) $datas['exam'];
                $getYear = explode('/', $username);
                $getYear = '20' . $getYear[0];
                if ($exam > 70) {
                    $exam = 70;
                }
                if ($ca > 30) {
                    $ca = 30;
                }

                $std_program = DB::table('students')
                    ->where(['username' => $username])
                    ->select('program', 'structure_id')
                    ->first();
                if (!$std_program || !$std_program->structure_id) {
                    return redirect()->back()->with('error', 'The student has no curriculum structure assigned, so this correction cannot be graded safely.');
                }
                $grading = DB::table('program_course_registration')
                    ->where(['code' => $code, 'program' => $std_program?->program, 'structure_id' => $std_program?->structure_id])
                    ->select('grading')
                    ->value('grading');
                $data = DB::table('corrigenda')
                    ->where(['id' => $idd])
                    ->select('id', 'program')
                    ->get();
                foreach ($data as $row) {
                    $total = (int) $ca + (int) $exam;
                    $unit = DB::table('course')
                        ->where(['code' => $code])
                        ->value('unit');
                    $unit = (int) $unit;
                    $remark = 'PASS';

                    $gradings = DB::table('grading_system')
                        ->where(function ($query) use ($getYear) {
                            $query
                                ->where(function ($innerQuery) use ($getYear) {
                                    $innerQuery
                                        ->where('to', '!=', 'current')
                                        ->where('from', '<=', $getYear)
                                        ->where('to', '>=', $getYear);
                                })
                                ->orWhere(function ($innerQuery) use ($getYear) {
                                    $innerQuery
                                        ->where('to', '=', 'current')
                                        ->where('from', '<=', $getYear);
                                });
                        })
                        ->where('min_score', '<=', $total)
                        ->where('max_score', '>=', $total)
                        ->where('name', $grading)
                        ->get();
                    // dd($gradings);

                    foreach ($gradings as $gs) {
                        $grade = $gs->grade;
                        $point = $unit * $gs->point;
                        $remark = $gs->remark;
                        $records['ca'] = $ca;
                        $records['exam'] = $exam;
                        $records['total'] = $total;
                        $records['grade'] = $grade;
                        $records['remark'] = $remark;
                        $records['point'] = $gs->point;
                        $records['ugp'] = $point;
                        $records['updated_at'] = NOW();
                    }
                }
                $status = 'Submitted to Department';
                $records['approve'] = 'lecturer';
                $records['lecturer'] = session('username');
            } else if ($row->approve == 'lecturer') {
                $status = 'Submitted to Faculty';
                $records['approve'] = 'hod';
                $records['hod'] = session('username');
            } else if ($row->approve == 'hod') {
                $status = 'Submitted to Course System';
                $records['approve'] = 'dean';
                $records['dean'] = session('username');
            } else if ($row->approve == 'dean') {
                $status = 'Submitted to Senate';
                $records['approve'] = 'cs';
                $records['cs'] = session('username');
            } else if ($row->approve == 'cs') {
                $status = 'Approved!!!';
                $records['approve'] = 'vc';
                $records['vc'] = session('username');
                $data = DB::table('corrigenda')->where(['id' => $req->id])->select('point', 'ugp', 'total', 'grade', 'session', 'semester', 'remark')->get();
                foreach ($data as $row) {
                    $record['total'] = $row->total;
                    $record['grade'] = $row->grade;
                    $record['status'] = $row->remark;
                    $record['point'] = $row->point;
                    $record['ugp'] = $row->ugp;
                    DB::table('student_course_registration')->where(['code' => $req->code, 'session' => $row->session, 'semester' => $row->semester, 'username' => $req->username])->update($record);

                    unset($record['status']);
                    $record['remark'] = $row->remark;
                    DB::table('results')->where(['id' => $req->result_id])->update($record);
                }
            } else {
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        }
        DB::table('corrigenda')->where(['id' => $req->id])->update($records);
        return redirect()->back()->with('success', 'Results ' . $status);
    }
}
