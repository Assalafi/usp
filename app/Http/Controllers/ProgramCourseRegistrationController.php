<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\CourseStructure;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProgramCourseRegistrationController extends Controller
{
    //
    //

    private $page;
    private $table;
    private $title;
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = $contents;
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $isHod = $this->isHod();
        $table = $this->table;
        $query = DB::table($table . ' as pcr')
            ->leftJoin('course as c', 'c.code', '=', 'pcr.code')
            ->leftJoin('program as p', 'p.code', '=', 'pcr.program')
            ->leftJoin('department as d', 'd.code', '=', 'p.department')
            ->leftJoin('faculty as f', 'f.code', '=', 'd.faculty')
            ->leftJoin('course_structure as cs', 'cs.id', '=', 'pcr.structure_id')
            ->select(
                'pcr.*',
                'c.title as course_title',
                'c.status as course_status',
                'p.title as program_title',
                'p.department as program_department',
                'd.title as department_title',
                'f.title as faculty_title',
                'cs.name as structure_name',
                'cs.from_session as structure_from_session',
                'cs.to_session as structure_to_session',
                'cs.status as structure_status'
            )
            ->whereNotNull('pcr.structure_id');

        // A HOD owns the complete academic register for the department. This
        // intentionally includes every programme in that department.
        if ($isHod && session('department')) {
            $query->where('p.department', session('department'));
        } elseif (session('appointment') === 'DEAN' && session('faculty')) {
            $query->where('f.code', session('faculty'));
        }

        $filterMap = [
            'faculty' => 'f.code',
            'department' => 'p.department',
            'program' => 'pcr.program',
            'level' => 'pcr.level',
            'semester' => 'pcr.semester',
            'structure_id' => 'pcr.structure_id',
            'type' => 'pcr.type',
            'grading' => 'pcr.grading',
        ];

        foreach ($filterMap as $input => $column) {
            $value = $req->input($input);
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        if ($req->filled('q')) {
            $search = trim((string) $req->input('q'));
            $query->where(function ($builder) use ($search) {
                $builder->where('pcr.code', 'like', '%' . $search . '%')
                    ->orWhere('c.title', 'like', '%' . $search . '%')
                    ->orWhere('p.title', 'like', '%' . $search . '%');
            });
        }

        $hasFilters = collect(array_keys($filterMap))
            ->contains(fn ($key) => $req->filled($key)) || $req->filled('q');

        // Keep the first visit light while allowing a structure/program filter to show its full mapping.
        if (!$hasFilters && !$isHod) {
            $query->where('pcr.status', '3')->limit(100);
        }

        $data['data'] = $query
            ->orderBy('pcr.level', 'ASC')
            ->orderBy('pcr.semester', 'ASC')
            ->orderBy('pcr.code', 'ASC')
            ->get();

        if(session('appointment') == 'HOD' || session('appointment') == 'DEAN'){
            $data['faculty'] = DB::table('faculty')->where(['code' => session('faculty')])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }else{
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }

        $data['grading'] = DB::table('grading_system')->select('name')->groupBy('name')->orderBy('name', 'ASC')->get();
        $data['semester'] = DB::table('semester')->select('semester')->orderBy('semester', 'ASC')->get();
        $data['courseStructure'] = CourseStructure::query()
            ->orderByDesc('status')
            ->orderByDesc('from_session')
            ->orderBy('name')
            ->get();
        $data['courseCatalog'] = DB::table('course')
            ->select('code', 'title', 'unit', 'level', 'semester', 'type')
            ->whereIn('status', [1, 3])
            ->orderBy('code')
            ->get();
        $data['selectedFilters'] = $req->only(array_merge(array_keys($filterMap), ['q']));
        $data['isHod'] = $isHod;
        $data['canManage'] = !$isHod || $this->hodCanManage();
        $data['hodDepartment'] = $isHod ? DB::table('department')->where('code', session('department'))->first(['code', 'title', 'faculty']) : null;
        $data['hodPrograms'] = $isHod
            ? DB::table('program')->where('department', session('department'))->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if ($this->isHod() && !$this->hodCanManage()) {
            return redirect()->back()->with('error', 'You have view-only access to programme courses. Please contact the main administrator if changes are needed.');
        }
        $validated = $req->validate([
            'faculty' => ['required', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:20'],
            'program' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'max:10000'],
            'structure_id' => ['required', 'integer', 'exists:course_structure,id'],
            'elective_map' => ['nullable', 'string', 'max:10000'],
            'de_map' => ['nullable', 'string', 'max:10000'],
        ]);

        $program = strtoupper(trim($validated['program']));
        $faculty = strtoupper(trim($validated['faculty']));
        $department = strtoupper(trim($validated['department']));
        $structureId = (int) $validated['structure_id'];

        if ($this->isHod()) {
            // Never trust a department or faculty posted by a browser.
            $department = strtoupper((string) session('department'));
            $faculty = strtoupper((string) session('faculty'));
        }
        $codes = collect(preg_split('/[\s,;]+/', strtoupper($validated['code']), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($code) => preg_replace('/[^A-Z0-9.\/-]/', '', trim($code)))
            ->filter()
            ->unique()
            ->values();

        $programExists = DB::table('program')->where('code', $program)->exists();
        if (!$programExists) {
            return redirect()->back()->withInput()->with('error', 'The selected programme could not be found. Please choose it from the programme list.');
        }

        $programRow = DB::table('program')->where('code', $program)->first(['department']);
        $departmentRow = DB::table('department')->where('code', $department)->first(['faculty']);
        if (!$programRow || strtoupper((string) $programRow->department) !== $department || !$departmentRow || strtoupper((string) $departmentRow->faculty) !== $faculty) {
            return redirect()->back()->withInput()->with('error', 'The faculty, department and programme do not match. Please select them in sequence.');
        }

        if ($codes->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Enter at least one course code from the master course catalogue.');
        }

        $courses = DB::table('course')->whereIn('code', $codes->all())->get()->keyBy(fn ($course) => strtoupper((string) $course->code));
        $missing = $codes->diff($courses->keys())->values();
        if ($missing->isNotEmpty()) {
            return redirect()->back()->withInput()->with('error', 'These course codes were not found: ' . $missing->implode(', ') . '. Nothing was changed.');
        }

        $electiveGroups = [];
        if ($req->filled('elective_map')) {
            $decoded = json_decode((string) $req->input('elective_map'), true);
            if (!is_array($decoded)) {
                return redirect()->back()->withInput()->with('error', 'The elective groups could not be read. Please select the group beside each elective course and try again.');
            }
            foreach ($decoded as $code => $group) {
                $normalisedCode = strtoupper(trim((string) $code));
                if (!isset($courses[$normalisedCode])) {
                    continue;
                }
                if (filter_var($group, FILTER_VALIDATE_INT) === false || (int) $group < 1 || (int) $group > 20) {
                    return redirect()->back()->withInput()->with('error', 'Every elective course must be linked to a valid elective group from 1 to 20.');
                }
                $electiveGroups[$normalisedCode] = (int) $group;
            }
        }

        foreach ($courses as $course) {
            if (strtoupper((string) ($course->type ?: 'CORE')) === 'ELECTIVE' && !isset($electiveGroups[strtoupper((string) $course->code)])) {
                return redirect()->back()->withInput()->with('error', 'Select an elective group for ' . $course->code . ' before saving. Courses in the same elective group are linked together for student registration.');
            }
        }

        $directEntryCourses = [];
        if ($req->filled('de_map')) {
            $decoded = json_decode((string) $req->input('de_map'), true);
            if (!is_array($decoded)) {
                return redirect()->back()->withInput()->with('error', 'The direct-entry course selections could not be read. Please review the course list and try again.');
            }
            foreach ($decoded as $code => $value) {
                $normalisedCode = strtoupper(trim((string) $code));
                if (!isset($courses[$normalisedCode])) {
                    continue;
                }
                if (!in_array((int) $value, [0, 1], true)) {
                    return redirect()->back()->withInput()->with('error', 'Direct-entry course selections must be marked Yes or No.');
                }
                if ((int) $value === 1 && (string) ($courses[$normalisedCode]->level ?? '') !== '100') {
                    return redirect()->back()->withInput()->with('error', $normalisedCode . ' is not a 100-level course. Mark direct-entry foundation only on 100-level courses.');
                }
                $directEntryCourses[$normalisedCode] = (int) $value;
            }
        }

        try {
            DB::transaction(function () use ($courses, $electiveGroups, $directEntryCourses, $program, $structureId) {
                foreach ($courses as $row) {
                    $type = strtoupper((string) ($row->type ?: 'CORE'));
                    DB::table($this->table)->updateOrInsert(
                        ['program' => $program, 'code' => $row->code, 'structure_id' => $structureId],
                        [
                            'unit' => $row->unit,
                            'semester' => $row->semester,
                            'level' => $row->level,
                            'type' => $type,
                            'elective' => $type === 'ELECTIVE' ? $electiveGroups[strtoupper((string) $row->code)] : 0,
                            'de' => $directEntryCourses[strtoupper((string) $row->code)] ?? 0,
                            'updated_at' => now(),
                        ]
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'The curriculum could not be saved. Please try again.');
        }

        return redirect()->back()->with('success', $courses->count() . ' course mapping(s) saved successfully.');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if ($this->isHod() && !$this->hodCanManage()) {
            return redirect()->back()->with('error', 'You have view-only access to programme courses. Please contact the main administrator if changes are needed.');
        }
        $validated = $req->validate([
            'id' => ['required', 'integer', 'exists:program_course_registration,id'],
            'level' => ['required', 'string', 'max:5'],
            'semester' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:CORE,ELECTIVE'],
            'elective' => ['nullable', 'integer', 'min:0', 'max:20'],
            'change_semester' => ['required', 'boolean'],
            'structure_id' => ['required', 'integer', 'exists:course_structure,id'],
            'de' => ['required', 'boolean'],
            'grading' => ['required', 'string', 'max:50'],
            'prerequisites' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['type'] = strtoupper($validated['type']);

        if ((int) $validated['de'] === 1 && (string) $validated['level'] !== '100') {
            return redirect()->back()->withInput()->with('error', 'Direct-entry foundation marking is only available for 100-level courses.');
        }

        if ($this->isHod() && !$this->hodOwnsMapping((int) $req->input('id'))) {
            return redirect()->back()->with('error', 'This programme is outside your department.');
        }
        if(($validated['elective'] ?? 0) == 0 && $validated['type'] == 'ELECTIVE'){
            return redirect()->back()->with('error', 'You Must Select Elective Number');
        }
        if($validated['type'] == 'CORE'){
            $validated['elective'] = 0;
        }
        // Prerequisites are intentionally not part of the HOD workflow yet.
        // Preserve any existing academic record instead of asking the HOD to
        // re-enter it when updating placement details.
        if ($this->isHod()) {
            unset($validated['prerequisites']);
        } else {
            $validated['prerequisites'] = $this->normalisePrerequisites($validated['prerequisites'] ?? null);
        }
        if (!empty($validated['prerequisites'])) {
            $prerequisiteCodes = collect(explode(',', $validated['prerequisites']))
                ->map(fn ($code) => trim($code))
                ->filter()
                ->values();
            $missingPrerequisites = $prerequisiteCodes
                ->diff(DB::table('course')->whereIn('code', $prerequisiteCodes->all())->pluck('code'))
                ->values();
            if ($missingPrerequisites->isNotEmpty()) {
                return redirect()->back()->withInput()->with('error', 'These prerequisite course codes were not found: ' . $missingPrerequisites->implode(', ') . '.');
            }
        }
        unset($validated['id']);
        if (!Schema::hasColumn($this->table, 'prerequisites')) {
            unset($validated['prerequisites']);
        }
        DB::table($this->table)->where('id', $req->id)->update(array_merge($validated, ['updated_at' => now()]));

        return redirect()->back()->with('success', 'Course mapping updated successfully.');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if ($this->isHod() && !$this->hodCanManage()) {
            return redirect()->back()->with('error', 'You have view-only access to programme courses. Please contact the main administrator if changes are needed.');
        }
        if ($this->isHod() && !$this->hodOwnsMapping((int) $req->input('id'))) {
            return redirect()->back()->with('error', 'This programme is outside your department.');
        }
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    /**
     * Render the current curriculum filters as an inline PDF preview.
     * The query intentionally mirrors the programme-course-registration page,
     * but does not apply the first-visit 100-row limit.
     */
    public function previewPdf(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // This preview route uses a hyphenated URL, so do not derive the
        // database table name from the first URL segment here.
        $query = DB::table('program_course_registration as pcr')
            ->leftJoin('course as c', 'c.code', '=', 'pcr.code')
            ->leftJoin('program as p', 'p.code', '=', 'pcr.program')
            ->leftJoin('department as d', 'd.code', '=', 'p.department')
            ->leftJoin('faculty as f', 'f.code', '=', 'd.faculty')
            ->leftJoin('course_structure as cs', 'cs.id', '=', 'pcr.structure_id')
            ->select(
                'pcr.*',
                'c.title as course_title',
                'p.title as program_title',
                'd.title as department_title',
                'f.title as faculty_title',
                'cs.name as structure_name',
                'cs.from_session as structure_from_session',
                'cs.to_session as structure_to_session'
            )
            ->whereNotNull('pcr.structure_id');

        if ($this->isHod() && session('department')) {
            $query->where('p.department', session('department'));
        } elseif (session('appointment') === 'DEAN' && session('faculty')) {
            $query->where('f.code', session('faculty'));
        }

        $filterMap = [
            'faculty' => 'f.code',
            'department' => 'p.department',
            'program' => 'pcr.program',
            'level' => 'pcr.level',
            'semester' => 'pcr.semester',
            'structure_id' => 'pcr.structure_id',
            'type' => 'pcr.type',
            'grading' => 'pcr.grading',
        ];

        foreach ($filterMap as $input => $column) {
            $value = $req->input($input);
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        if ($req->filled('q')) {
            $search = trim((string) $req->input('q'));
            $query->where(function ($builder) use ($search) {
                $builder->where('pcr.code', 'like', '%' . $search . '%')
                    ->orWhere('c.title', 'like', '%' . $search . '%')
                    ->orWhere('p.title', 'like', '%' . $search . '%');
            });
        }

        $rows = $query
            // Keep the PDF in the same order used by the curriculum selector:
            // active/current structures first, followed by their academic
            // session range, then the structure name. This prevents courses
            // from different curriculum versions being mixed together.
            ->orderByDesc('cs.status')
            ->orderByDesc('cs.from_session')
            ->orderBy('cs.name')
            ->orderBy('f.title')
            ->orderBy('d.title')
            ->orderBy('p.title')
            ->orderBy('pcr.level')
            ->orderBy('pcr.semester')
            ->orderBy('pcr.code')
            ->get();

        $lookup = function (string $table, string $column, $value, string $fallback = 'All') {
            if ($value === null || $value === '') {
                return $fallback;
            }

            return DB::table($table)->where($column, $value)->value('title') ?: (string) $value;
        };

        $structure = $req->filled('structure_id')
            ? DB::table('course_structure')->where('id', $req->input('structure_id'))->first()
            : null;

        $filters = [
            'Curriculum structure' => $structure
                ? ($structure->name . ' · ' . $structure->from_session . '–' . $structure->to_session)
                : 'All structures',
            'Faculty / college' => $lookup('faculty', 'code', $req->input('faculty'), 'All faculties'),
            'Department' => $lookup('department', 'code', $req->input('department'), 'All departments'),
            'Programme' => $lookup('program', 'code', $req->input('program'), 'All programmes'),
            'Level' => $req->filled('level') ? $req->input('level') . ' level' : 'All levels',
            'Semester' => $req->filled('semester') ? $req->input('semester') : 'All semesters',
            'Course type' => $req->filled('type') ? $req->input('type') : 'Core and elective',
        ];
        if ($req->filled('q')) {
            $filters['Search'] = trim((string) $req->input('q'));
        }

        $logoData = null;
        $logoPath = public_path('uploads/logo.png');
        if (is_file($logoPath)) {
            $logoData = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.program-course-registration-preview', [
            'rows' => $rows,
            'filters' => $filters,
            'logoData' => $logoData,
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="curriculum-preview-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    private function normalisePrerequisites(?string $value): ?string
    {
        $codes = collect(preg_split('/[\s,;]+/', strtoupper((string) $value), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($code) => preg_replace('/[^A-Z0-9.\/-]/', '', trim($code)))
            ->filter()
            ->unique()
            ->values();

        return $codes->isEmpty() ? null : $codes->implode(', ');
    }

    private function isHod(): bool
    {
        return session('accType') === 'Staff' && strtoupper((string) session('appointment')) === 'HOD';
    }

    private function hodCanManage(): bool
    {
        if (!$this->isHod()) {
            return true;
        }

        try {
            $mode = (string) SystemSettingsController::get('hod_program_course_management_mode', 'view');
            if ($mode === 'all') {
                return true;
            }
            if ($mode !== 'selected') {
                return false;
            }

            $users = json_decode((string) SystemSettingsController::get('hod_program_course_management_users', '[]'), true);
            return is_array($users) && in_array((string) session('username'), array_map('strval', $users), true);
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    private function hodOwnsMapping(int $id): bool
    {
        if (!$this->isHod()) {
            return true;
        }

        return DB::table($this->table . ' as pcr')
            ->join('program as p', 'p.code', '=', 'pcr.program')
            ->where('pcr.id', $id)
            ->where('p.department', session('department'))
            ->exists();
    }
}
