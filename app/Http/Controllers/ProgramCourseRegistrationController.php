<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\CourseStructure;

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
            );

        // Academic officers should only see mappings in their own faculty.
        if (in_array(session('appointment'), ['HOD', 'DEAN'], true) && session('faculty')) {
            $query->where('f.code', session('faculty'));
        }

        $filterMap = [
            'faculty' => 'f.code',
            'department' => 'p.department',
            'program' => 'pcr.program',
            'level' => 'pcr.level',
            'semester' => 'pcr.semester',
            'form' => 'pcr.form',
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
        if (!$hasFilters) {
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
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $validated = $req->validate([
            'faculty' => ['required', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:20'],
            'program' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'max:10000'],
            'form' => ['required', 'in:NEW,FORMER'],
            'structure_id' => ['required', 'integer', 'exists:course_structure,id'],
        ]);

        $program = strtoupper(trim($validated['program']));
        $faculty = strtoupper(trim($validated['faculty']));
        $department = strtoupper(trim($validated['department']));
        $form = strtoupper($validated['form']);
        $structureId = (int) $validated['structure_id'];
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

        $courses = DB::table('course')->whereIn('code', $codes->all())->get()->keyBy('code');
        $missing = $codes->diff($courses->keys())->values();
        if ($missing->isNotEmpty()) {
            return redirect()->back()->withInput()->with('error', 'These course codes were not found: ' . $missing->implode(', ') . '. Nothing was changed.');
        }

        try {
            DB::transaction(function () use ($courses, $program, $form, $structureId) {
                foreach ($courses as $row) {
                    DB::table($this->table)->updateOrInsert(
                        ['program' => $program, 'code' => $row->code, 'structure_id' => $structureId],
                        [
                            'unit' => $row->unit,
                            'semester' => $row->semester,
                            'level' => $row->level,
                            'type' => strtoupper($row->type ?: 'CORE'),
                            'form' => $form,
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
        $validated = $req->validate([
            'id' => ['required', 'integer', 'exists:program_course_registration,id'],
            'level' => ['required', 'string', 'max:5'],
            'semester' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:CORE,ELECTIVE'],
            'elective' => ['nullable', 'integer', 'min:0', 'max:20'],
            'change_semester' => ['required', 'boolean'],
            'form' => ['required', 'in:NEW,FORMER'],
            'structure_id' => ['required', 'integer', 'exists:course_structure,id'],
            'de' => ['required', 'boolean'],
            'grading' => ['required', 'string', 'max:50'],
            'prerequisites' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['type'] = strtoupper($validated['type']);
        $validated['form'] = strtoupper($validated['form']);
        if(($validated['elective'] ?? 0) == 0 && $validated['type'] == 'ELECTIVE'){
            return redirect()->back()->with('error', 'You Must Select Elective Number');
        }
        if($validated['type'] == 'CORE'){
            $validated['elective'] = 0;
        }
        $validated['prerequisites'] = $this->normalisePrerequisites($validated['prerequisites'] ?? null);
        if ($validated['prerequisites']) {
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
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
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
}
