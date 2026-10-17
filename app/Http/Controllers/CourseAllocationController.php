<?php

namespace App\Http\Controllers;

use App\Models\CourseSystemAllocation;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CourseAllocationController extends Controller
{
    //
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

        $isAdmin = session('accType') === 'Admin' || session('username') === 'SP11913';
        $isHod = session('appointment') === 'HOD';
        $currentSession = DB::table('session')->where('status', '1')->value('title')
            ?: DB::table('session')->orderByDesc('title')->value('title');
        $filters = [
            'search' => trim((string) $req->query('search', '')),
            'faculty' => trim((string) $req->query('faculty', '')),
            'department' => trim((string) $req->query('department', '')),
            'program' => trim((string) $req->query('program', '')),
            'course' => trim((string) $req->query('course', '')),
            'username' => trim((string) $req->query('username', '')),
            'type' => strtoupper(trim((string) $req->query('type', ''))),
            'session' => trim((string) $req->query('session', $currentSession)),
        ];
        $filters = array_map(static fn ($value) => $value === 'all' ? '' : $value, $filters);

        if (!$isAdmin) {
            $filters['username'] = $isHod ? '' : (string) session('username');
            if ($isHod && session('department')) {
                $filters['department'] = (string) session('department');
            }
        }

        $facultyLookup = DB::table('faculty')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $departmentLookup = DB::table('department')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $programLookup = DB::table('program')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $query = DB::table('course_allocation as ca')
            ->leftJoin('course as c', 'c.code', '=', 'ca.course')
            ->leftJoinSub($facultyLookup, 'f', 'f.code', '=', 'ca.faculty')
            ->leftJoinSub($departmentLookup, 'd', 'd.code', '=', 'ca.department')
            ->leftJoinSub($programLookup, 'p', 'p.code', '=', 'ca.program')
            ->select([
                'ca.id', 'ca.user_id', 'ca.username', 'ca.name', 'ca.course', 'ca.type',
                'ca.faculty', 'ca.department', 'ca.program', 'ca.programs', 'ca.session',
                'ca.status', 'c.title as course_title', 'f.title as faculty_title',
                'd.title as department_title', 'p.title as program_title',
            ]);

        foreach (['faculty', 'department', 'program', 'course', 'username', 'type', 'session'] as $column) {
            if ($filters[$column] !== '') {
                $query->where('ca.' . $column, $filters[$column]);
            }
        }
        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($scopedQuery) use ($search): void {
                $scopedQuery->where('ca.username', 'like', $search)
                    ->orWhere('ca.name', 'like', $search)
                    ->orWhere('ca.course', 'like', $search)
                    ->orWhere('c.title', 'like', $search);
            });
        }

        $perPage = (int) $req->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;
        $data['data'] = $query->orderBy('ca.course')->orderBy('ca.username')->paginate($perPage)->withQueryString();

        $data['faculty'] = Cache::remember('course-allocation.filter.faculties', 600, static fn () => DB::table('faculty')
            ->where('status', '1')->select('code', 'title')->orderBy('title')->get());
        $data['filterDepartments'] = $filters['faculty'] !== ''
            ? DB::table('department')->where('faculty', $filters['faculty'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['filterPrograms'] = $filters['department'] !== ''
            ? DB::table('program')->where('department', $filters['department'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['sessions'] = Cache::remember('course-allocation.filter.sessions', 600, static fn () => DB::table('session')->select('title')->orderByDesc('title')->get());
        $data['currentSession'] = $currentSession;
        $createDepartment = $filters['department'] ?: (string) session('department', '');
        $data['createDepartment'] = $createDepartment;
        $data['courses'] = DB::table('course')->where('status', '1')->select('code', 'title', 'department')->when($createDepartment !== '', static function ($courseQuery) use ($createDepartment): void {
            $courseQuery->where('department', $createDepartment);
        })->orderBy('code')->get();
        $data['filters'] = $filters;
        $data['perPage'] = $perPage;
        $data['isAdminAllocation'] = $isAdmin;
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function courseSystem()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = CourseSystemAllocation::orderBy('username', 'ASC')->get();
        $data['faculty'] = Faculty::where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'course system allocation';
        $data['title'] = 'Course System Allocation';
        return view('main', $data);
    }

    public function courseSystemResult()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $department = CourseSystemAllocation::where('username', session('username'))->pluck('department');
        $programs = DB::table('program')->whereIn('department', $department)->pluck('code');
        $data['data'] = DB::table('results')->whereIn('program', $programs)->where(['approve' => 'dean'])->select('code')->groupBy('code')->get();
        $data['page'] = 'course system results';
        $data['title'] = 'Course System Results';
        return view('main', $data);
    }

    public function courseSystemCreate(Request $req)
    {
        // Check for session log
        if (!session()->has('log')) {
            return redirect('/');
        }

        $departments = $req->input('department');
        $username = $req->input('username');

        $staff = DB::table('staff')
            ->where('username', $username)
            ->select('name', 'user_id')
            ->first();
        $check = 0;

        if ($staff) {
            $check = 1;
        }
        if ($check == 0) {
            return redirect()->back()->with('error', 'Staff Not Found');
        }

        foreach ($departments as $key => $department) {
            DB::table('course_system_allocation')->insert([
                'username' => $username,
                'department' => $department,
            ]);
        }

        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function courseSystemDelete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table('course_system_allocation')->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $username = strtoupper(trim((string) $req->input('username')));
        $courseCode = strtoupper(preg_replace('/\s+/', '', (string) $req->input('course')));
        $type = strtoupper(trim((string) $req->input('type')));
        $session = trim((string) $req->input('session'));
        $req->merge(['username' => $username, 'course' => $courseCode, 'type' => $type, 'session' => $session]);

        $validated = $req->validate([
            'username' => ['required', 'string', 'max:100'],
            'course' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:MAIN,SUPPORT'],
            'session' => ['required', 'string', 'max:25', 'exists:session,title'],
            'programs' => ['required', 'array', 'min:1'],
            'programs.*' => ['required', 'string', 'max:50'],
        ], [
            'username.required' => 'Enter the staff ID for the lecturer or coordinator.',
            'course.required' => 'Choose a course before saving the allocation.',
            'type.required' => 'Choose whether this person is the course coordinator or a course lecturer.',
            'session.required' => 'Choose the academic session for this allocation.',
            'programs.required' => 'Select at least one programme linked to this course.',
        ]);

        $staff = DB::table('staff')->where('username', $username)->select('name', 'user_id')->first();
        if (!$staff) {
            return redirect()->back()->withInput()->with('error', 'The staff ID was not found. Check the ID and try again.');
        }

        $course = DB::table('course')->where('code', $courseCode)
            ->select('faculty', 'department', 'program', 'title')->first();
        if (!$course) {
            return redirect()->back()->withInput()->with('error', 'The course code was not found. Select a course from the list and try again.');
        }

        $programs = collect($validated['programs'])->map(static fn ($program) => strtoupper(trim($program)))->unique()->values()->all();
        $availablePrograms = DB::table('program_course_registration')->where('code', $courseCode)->pluck('program')->map(static fn ($program) => strtoupper(trim($program)))->all();
        if (empty($availablePrograms)) {
            return redirect()->back()->withInput()->with('error', 'This course is not linked to any programme yet. Link the course to a programme before allocating it.');
        }
        if ($invalidPrograms = array_diff($programs, $availablePrograms)) {
            return redirect()->back()->withInput()->with('error', 'One or more selected programmes are not linked to this course. Refresh the course selection and try again.');
        }

        $duplicate = DB::table($this->table)
            ->where('username', $username)
            ->where('course', $courseCode)
            ->where('session', $session)
            ->exists();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'This staff member is already assigned to this course for the selected session.');
        }

        DB::transaction(function () use ($staff, $course, $username, $courseCode, $type, $session, $programs): void {
            DB::table($this->table)->insert([
                'user_id' => $staff->user_id,
                'username' => $username,
                'name' => $staff->name,
                'course' => $courseCode,
                'type' => $type,
                'faculty' => $course->faculty,
                'department' => $course->department,
                'program' => $course->program,
                'programs' => json_encode($programs),
                'session' => $session,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Course allocation created successfully.');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = (int) $req->input('id');
        $username = strtoupper(trim((string) $req->input('username')));
        $type = strtoupper(trim((string) $req->input('type')));
        $req->merge(['username' => $username, 'type' => $type]);
        $validated = $req->validate([
            'id' => ['required', 'integer', 'exists:course_allocation,id'],
            'username' => ['required', 'string', 'max:100'],
            'course' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:MAIN,SUPPORT'],
        ], [
            'username.required' => 'Enter the staff ID for the lecturer or coordinator.',
            'type.required' => 'Choose the assignment role.',
        ]);

        $existing = DB::table($this->table)->where('id', $id)->select('course', 'session')->first();
        if (!$existing) {
            return redirect()->back()->with('error', 'This allocation no longer exists. Refresh the page and try again.');
        }
        $courseCode = strtoupper(preg_replace('/\s+/', '', (string) $validated['course']));
        $staff = DB::table('staff')->where('username', $username)->select('name', 'user_id')->first();
        if (!$staff) {
            return redirect()->back()->withInput()->with('error', 'The staff ID was not found. Check the ID and try again.');
        }
        $course = DB::table('course')->where('code', $courseCode)->select('faculty', 'department', 'program')->first();
        if (!$course) {
            return redirect()->back()->withInput()->with('error', 'The course code was not found.');
        }
        $duplicate = DB::table($this->table)
            ->where('username', $username)->where('course', $courseCode)->where('session', $existing->session)
            ->where('id', '!=', $id)->exists();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'This staff member is already assigned to this course for the selected session.');
        }

        DB::transaction(function () use ($id, $staff, $course, $username, $courseCode, $type): void {
            DB::table($this->table)->where('id', $id)->update([
                'user_id' => $staff->user_id,
                'username' => $username,
                'name' => $staff->name,
                'course' => $courseCode,
                'type' => $type,
                'faculty' => $course->faculty,
                'department' => $course->department,
                'program' => $course->program,
                'updated_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Course allocation updated successfully.');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
