<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    //
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
        // insertGetId
        if (!session()->has('log')) {
            return redirect('/');
        }
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            unset($data['faculty']);
            unset($data['department']);
            unset($data['program']);
            $filteredData = array_filter($data);
            $query = DB::table('course');
            $queryC = DB::table('course');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
                $queryC->where($key, $value);
            }
            $data['students'] = DB::table('student_course_registration')
                ->where('code', $data['code'])
                ->whereIn('students.program',['ACC','BAF'])
                ->select('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->leftJoin('attendance', function ($join) use ($req) {
                    $join->on('student_course_registration.username', '=', 'attendance.username')
                        ->on('student_course_registration.code', '=', 'attendance.course'); // Assuming course_code in attendance table
                })
                ->leftJoin('students', function ($join) use ($req) {
                    $join->on('student_course_registration.username', '=', 'students.username');
                })
                ->groupBy('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->selectRaw('SUM(attendance.status) AS attendance')
                ->orderBy('student_course_registration.username', 'ASC')
                ->get();
            $data['data'] = $query->orderBy('code', 'ASC')->get();
            $data['courses'] = $queryC->orderBy('code', 'ASC')->get();

        } else {
            if (session('accType') == 'Student') {
                $get_course = DB::table('student_course_registration')->where('username', session('id_number'))->pluck('code');
                $data['students'] = DB::table($this->table)->whereIn('course', $get_course)->orderBy('id', 'DESC')->orderBy('course', 'ASC')->limit(100)->get();
            }elseif (session('accType') == 'Staff') {
                $data['students'] = DB::table('student_course_registration')
                    ->whereIn('students.program',['ACC','BAF'])
                    ->select('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                    ->leftJoin('attendance', function ($join) use ($req) {
                        $join->on('student_course_registration.username', '=', 'attendance.username')
                            ->on('student_course_registration.code', '=', 'attendance.course'); // Assuming course_code in attendance table
                    })
                    ->leftJoin('students', function ($join) use ($req) {
                        $join->on('student_course_registration.username', '=', 'students.username');
                    })
                    ->groupBy('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                    ->selectRaw('SUM(attendance.status) AS attendance')
                    ->orderBy('student_course_registration.username', 'ASC')
                    ->get();

            } else {
                $data['students'] = DB::table($this->table)->where(['status' => '3'])->orderBy('id', 'DESC')->orderBy('course', 'ASC')->limit(100)->get();
                $data['courses'] = DB::table('course')->where(['status' => '3'])->orderBy('code', 'ASC')->get();
            }

        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['lecturerCourses'] = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function getDate(Request $req)
    {
        $date = $req->code;
        if ($date == 'All') {
            $data['students'] = DB::table('student_course_registration')
                ->where('code', $req->code)
                ->where('students.program', '=', 'ACCs')
                ->select('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->leftJoin('attendance', function ($join) use ($date) {
                    $join->on('student_course_registration.username', '=', 'attendance.username')
                        ->on('student_course_registration.code', '=', 'attendance.course');
                })
                ->leftJoin('students', function ($join) use ($req) {
                    $join->on('student_course_registration.username', '=', 'students.username');
                })
                ->groupBy('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->selectRaw('SUM(attendance.status) AS attendance')
                ->orderBy('student_course_registration.username', 'ASC')
                ->get();

        } else {
            $data['students'] = DB::table('student_course_registration')->where('student_course_registration.code', $req->code)->where('attendance.date', $req->date)
                ->select('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->leftJoin('attendance', function ($join) use ($date) {
                    $join->on('student_course_registration.username', '=', 'attendance.username')
                        ->on('student_course_registration.code', '=', 'attendance.course');
                })
                ->groupBy('student_course_registration.username', 'student_course_registration.id', 'student_course_registration.code')
                ->selectRaw('SUM(attendance.status) AS attendance')
                ->orderBy('student_course_registration.username', 'ASC')
                ->get();
        }

        //return $req->date . ' ' . $req->code;
        $data['page'] = 'attendance';
        $data['code'] = $req->code;
        $data['date'] = $req->date;

        return view('Admin.attendance page', $data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //dd($req -> all());

        try {
            DB::beginTransaction();

            $ids = $req->input('username');
            $course = $req->input('course');
            //dd($course);
            $faculty = 'no';
            if(DB::table('course_allocation')->select('course')->where(['username' => session('username'), 'course' => $course])->first()){
            }else{
                return redirect()->back()->with('error', 'You are not authorize to take this attendance');

            }
            $courses = DB::table('course')->where('code', $course)->get();
            foreach ($courses as $row) {
                $faculty = $row->faculty;
                $department = $row->department;
                $program = $row->program;
                $level = $row->level;
                $title = $row->title;
            }
            if ($faculty == 'no')
                return redirect()->back()->with('error', 'Somthing Went Wrong');
            $session = session('system_session');
            $semester = session('system_semester');

            foreach ($ids as $key => $username) {
                $status = $req->input($username);
                $check = DB::table('attendance')->where(['username' => $username, 'course' => $course, 'date' => date('Y-m-d', strtotime(now()))])->first();
                DB::table('attendance')->insert([
                    'username' => $username,
                    'status' => $status,
                    'course' => $course,
                    'title' => $title,
                    'faculty' => $faculty,
                    'department' => $department,
                    'program' => $program,
                    'level' => $level,
                    'session' => $session,
                    'semester' => $semester,
                    'date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Record Created!!!');
        } catch (\Exception $e) {
            DB::rollBack();
            //dd($e);
            return redirect()->back()->with('error', 'You Already take this attendace or something went wrong');
        }

    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
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
