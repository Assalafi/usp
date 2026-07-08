<?php

namespace App\Http\Controllers;

use App\Models\CourseSystemAllocation;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $queryC = DB::table('course');


            if ($req->has('username')) {
                $data['faculty'] = session('faculty');
                $data['department'] = session('department');

                if (session('appointment') == 'HOD') {
                    $query = DB::table($this->table)->where(['department' => session('department'), 'session' => $data['session']]);
                } else {
                    $query = DB::table($this->table)->where(['username' => session('username'), 'session' => $data['session']]);
                }
            }else{
                $query = DB::table($this->table);
            }

            unset($data['username']);

            $filteredData = array_filter($data);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            unset($data['session']);
            $filteredData = array_filter($data);
            foreach ($filteredData as $key => $value) {
                $queryC->where($key, $value);
            }
            $data['data'] = $query->orderBy('course', 'ASC')->get();
            // dd($data['data']);
            $data['courses'] = $queryC->orderBy('code', 'ASC')->get();
        } else {
            $session = DB::table('session')->where(['status' => '1'])->first()->title;
            if (session('appointment') == 'HOD') {
                $data['data'] = DB::table($this->table)->where(['department' => session('department'), 'session' => $session])->get();
            } else {
                $data['data'] = DB::table($this->table)->where(['username' => session('username'), 'session' => $session])->get();
            }
            $data['courses'] = DB::table('course')->where(['status' => '1'])->select('code', 'title', 'department')->orderBy('code', 'ASC')->get();
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['staff'] = DB::table('staff')->where(['status' => '1'])->select('username', 'name', 'department')->orderBy('username', 'ASC')->get();
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
