<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\CourseImport;
use App\Models\User;

class CourseController extends Controller
{
    //
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
        if (!session()->has('log')) {
            return redirect('/');
        }

        $filters = [
            'search' => trim((string) $req->query('search', '')),
            'faculty' => trim((string) $req->query('faculty', '')),
            'department' => trim((string) $req->query('department', '')),
            'program' => trim((string) $req->query('program', '')),
            'type' => trim((string) $req->query('type', '')),
            'class' => trim((string) $req->query('class', '')),
            'unit' => trim((string) $req->query('unit', '')),
            'level' => trim((string) $req->query('level', '')),
            'semester' => trim((string) $req->query('semester', '')),
            'status' => trim((string) $req->query('status', '1')),
        ];
        $filters = array_map(static fn ($value) => $value === 'all' ? '' : $value, $filters);

        $isRestrictedFaculty = in_array(session('appointment'), ['HOD', 'DEAN'], true);
        if ($isRestrictedFaculty && session('faculty')) {
            $filters['faculty'] = (string) session('faculty');
        }

        $perPage = (int) $req->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

        $facultyLookup = DB::table('faculty')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $departmentLookup = DB::table('department')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $programLookup = DB::table('program')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $query = DB::table('course as c')
            ->leftJoinSub($programLookup, 'p', 'p.code', '=', 'c.program')
            ->leftJoinSub($departmentLookup, 'd', 'd.code', '=', 'c.department')
            ->leftJoinSub($facultyLookup, 'f', 'f.code', '=', 'c.faculty')
            ->select([
                'c.id', 'c.code', 'c.title', 'c.type', 'c.class', 'c.unit',
                'c.level', 'c.semester', 'c.faculty', 'c.department', 'c.program',
                'c.status', 'c.user', 'p.title as program_title',
                'd.title as department_title', 'f.title as faculty_title',
            ]);

        foreach (['faculty', 'department', 'program', 'type', 'class', 'unit', 'level', 'semester', 'status'] as $column) {
            if ($filters[$column] !== '') {
                $query->where('c.' . $column, $filters[$column]);
            }
        }
        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($scopedQuery) use ($search): void {
                $scopedQuery->where('c.code', 'like', $search)
                    ->orWhere('c.title', 'like', $search);
            });
        }

        $data['data'] = $query
            ->orderBy('c.code')
            ->paginate($perPage)
            ->withQueryString();

        if ($isRestrictedFaculty) {
            $data['faculty'] = DB::table('faculty')
                ->where('code', session('faculty'))
                ->select('code', 'title')
                ->orderBy('title')
                ->get();
        } else {
            $data['faculty'] = Cache::remember('course.filter.faculties', 600, static fn () => DB::table('faculty')
                ->where('status', '1')
                ->select('code', 'title')
                ->orderBy('title')
                ->get());
        }
        $data['filterDepartments'] = $filters['faculty'] !== ''
            ? DB::table('department')->where('faculty', $filters['faculty'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['filterPrograms'] = $filters['department'] !== ''
            ? DB::table('program')->where('department', $filters['department'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['courseLevels'] = Cache::remember('course.filter.levels', 600, static fn () => DB::table('course')
            ->whereNotNull('level')->where('level', '!=', '')
            ->select('level')->distinct()
            ->orderByRaw("CASE WHEN level REGEXP '^[0-9]+$' THEN CAST(level AS UNSIGNED) ELSE 999999 END")
            ->orderBy('level')->pluck('level'));
        $data['courseUnits'] = Cache::remember('course.filter.units', 600, static fn () => DB::table('course')
            ->select('unit')->distinct()->orderBy('unit')->pluck('unit'));
        $data['courseSemesters'] = Cache::remember('course.filter.semesters', 600, static fn () => DB::table('course')
            ->whereNotNull('semester')->where('semester', '!=', '')
            ->select('semester')->distinct()->orderBy('semester')->pluck('semester'));
        $data['semesters'] = $data['courseSemesters'];
        $data['academicSessions'] = Cache::remember('course.filter.sessions', 600, static fn () => DB::table('session')
            ->select('title')->orderByDesc('title')->get());
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        $data['filters'] = $filters;
        $data['perPage'] = $perPage;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $req->merge($this->normaliseCourseInput($req));
        $validated = $req->validate($this->courseRules(), $this->courseMessages());

        if ($contextError = $this->academicContextError($validated)) {
            return redirect()->back()->withInput()->with('error', $contextError);
        }
        if (DB::table($this->table)->where('code', $validated['code'])->exists()) {
            return redirect()->back()->withInput()->with('error', 'A course with this code already exists. Use a different course code.');
        }

        try {
            DB::transaction(function () use ($validated): void {
                DB::table($this->table)->insert([
                    ...$validated,
                    'status' => 1,
                    'user' => session('username', 'system'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->withInput()->with('error', 'The course could not be created. Check the details and try again.');
        }

        return redirect()->back()->with('success', 'Course created successfully.');
    }

    public function upload(Request $request)
    {
    if(!session()->has('log')){return redirect('/');}
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the uploaded file using Maatwebsite/Excel
            $import = new CourseImport();
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $req->merge($this->normaliseCourseInput($req));
        $validated = $req->validate($this->courseRules(true), $this->courseMessages());
        $id = (int) $validated['id'];
        unset($validated['id']);

        if (!DB::table($this->table)->where('id', $id)->exists()) {
            return redirect()->back()->withInput()->with('error', 'This course no longer exists. Refresh the page and try again.');
        }
        if ($contextError = $this->academicContextError($validated)) {
            return redirect()->back()->withInput()->with('error', $contextError);
        }
        if (DB::table($this->table)->where('code', $validated['code'])->where('id', '!=', $id)->exists()) {
            return redirect()->back()->withInput()->with('error', 'A different course already uses this code. Use a different course code.');
        }

        try {
            DB::transaction(function () use ($id, $validated): void {
                DB::table($this->table)->where('id', $id)->update([
                    ...$validated,
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->withInput()->with('error', 'The course could not be updated. Check the details and try again.');
        }

        return redirect()->back()->with('success', 'Course updated successfully.');
    }

    private function normaliseCourseInput(Request $req): array
    {
        $code = strtoupper(preg_replace('/\s+/', '', (string) $req->input('code')));
        $normalise = static fn ($value): string => strtoupper(trim((string) $value));

        return [
            'code' => $code,
            'title' => $normalise($req->input('title')),
            'type' => $normalise($req->input('type')),
            'class' => $normalise($req->input('class')),
            'unit' => (int) $req->input('unit', 0),
            'level' => trim((string) $req->input('level')),
            'semester' => $normalise($req->input('semester')),
            'faculty' => $normalise($req->input('faculty')),
            'department' => $normalise($req->input('department')),
            'program' => $normalise($req->input('program')),
        ];
    }

    private function courseRules(bool $withId = false): array
    {
        return [
            'id' => $withId ? ['required', 'integer', 'exists:course,id'] : ['sometimes'],
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9._\/-]*$/'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'integer', 'min:0', 'max:50'],
            'level' => ['required', 'string', 'max:5', 'regex:/^\d{3}$/'],
            'semester' => ['required', 'string', 'max:100'],
            'faculty' => ['required', 'string', 'max:10', 'exists:faculty,code'],
            'department' => ['required', 'string', 'max:10', 'exists:department,code'],
            'program' => ['required', 'string', 'max:10', 'exists:program,code'],
        ];
    }

    private function courseMessages(): array
    {
        return [
            'code.required' => 'Enter a course code, for example ACC101.',
            'code.regex' => 'Course code may contain letters, numbers, hyphens, slashes, dots, or underscores only.',
            'title.required' => 'Enter the full course title.',
            'type.required' => 'Choose the course type.',
            'class.required' => 'Choose the class format.',
            'unit.required' => 'Enter the credit unit for this course.',
            'unit.integer' => 'Credit units must be a whole number.',
            'level.required' => 'Choose the course level.',
            'level.regex' => 'Choose a valid level such as 100, 200, or 300.',
            'semester.required' => 'Choose the semester for this course.',
            'faculty.required' => 'Choose the faculty or college.',
            'department.required' => 'Choose the department.',
            'program.required' => 'Choose the programme.',
        ];
    }

    private function academicContextError(array $course): ?string
    {
        $department = DB::table('department')->where('code', $course['department'])->first(['faculty']);
        if (!$department || (string) $department->faculty !== (string) $course['faculty']) {
            return 'The selected department does not belong to the selected faculty or college.';
        }

        $program = DB::table('program')->where('code', $course['program'])->where('department', $course['department'])->first(['faculty']);
        if (!$program) {
            return 'The selected programme does not belong to the selected department.';
        }
        if (!empty($program->faculty) && (string) $program->faculty !== (string) $course['faculty']) {
            return 'The selected programme does not belong to the selected faculty or college.';
        }

        return null;
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $validated = $req->validate(['id' => ['required', 'integer', 'exists:course,id']]);
        try {
            DB::transaction(function () use ($validated): void {
                DB::table($this->table)->where('id', $validated['id'])->delete();
            });
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', 'The course could not be removed. It may already be in use by another academic record.');
        }

        return redirect()->back()->with('success', 'Course removed successfully.');
    }
}
