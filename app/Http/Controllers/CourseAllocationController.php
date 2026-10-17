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
        // Check for session log
        if (!session()->has('log')) {
            return redirect('/');
        }
        ini_set('memory_limit', '1024M');

        // Get all request data
        $datas = $req->all();
        $datas['username'] = strtoupper($datas['username']);
        $datas['created_at'] = now();
        $datas['updated_at'] = now();

        // Validate programs field
        if (!isset($datas['programs'])) {
            return redirect()->back()->with('error', 'You Must Select Atleast ONE Program');
        }

        // Encode programs field
        $datas['programs'] = json_encode($req->programs);

        // Fetch course details (use first() instead of get() to avoid looping unnecessarily)
        $course = DB::table('course')
            ->where('code', $datas['course'])
            ->select('faculty', 'department', 'program', 'title')
            ->first();

        if ($course) {
            $datas['faculty'] = $course->faculty;
            $datas['department'] = $course->department;
            $datas['program'] = $course->program;
        }

        // Fetch staff details (use first() instead of get())
        $staff = DB::table('staff')
            ->where('username', $datas['username'])
            ->select('name', 'user_id')
            ->first();
        $check = 0;

        if ($staff) {
            $datas['name'] = $staff->name;
            $datas['user_id'] = $staff->user_id;
            $check = 1;
        }
        if ($check == 0) {
            return redirect()->back()->with('error', 'Staff Not Found');
        }

        // Remove unnecessary fields and convert values to uppercase
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);

        // Insert data into the table
        DB::table($this->table)->insert($datas);

        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        $datas['username'] = strtoupper($datas['username']);
        $course = DB::table('course')->where(['code' => $datas['course']])->select('faculty', 'department', 'program', 'title')->get();
        foreach ($course as $row) {
            $datas['faculty'] = $row->faculty;
            $datas['department'] = $row->department;
            $datas['program'] = $row->program;
        }
        $staff = DB::table('staff')->where('username', $datas['username'])->select('name', 'user_id')->get();
        $check = 0;
        foreach ($staff as $row) {
            $datas['name'] = $row->name;
            $datas['user_id'] = $row->user_id;
            $check = 1;
        }
        if ($check == 0) {
            return redirect()->back()->with('error', 'Staff Not Found');
        }
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);
        return redirect()->back()->with('success', 'Record Updated!!!');
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
