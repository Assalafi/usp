<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LectureTimetableController extends Controller
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

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table($this->table)->get();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['courses'] = DB::table('course')->select('code', 'title', 'faculty', 'level')->orderBy('code', 'ASC')->get();
        $data['hall'] = DB::table('hall_allocation')->where(['status' => '1'])->groupBy('hall', 'faculty')->select('hall', 'faculty')->orderBy('hall', 'ASC')->get();
        if (session('accType') == 'Staff') {
            $myCourses = DB::table('course_allocation')->select('course')->where(['username' => session('username')])->pluck('course');
        } else {
            $myCourses = DB::table('student_course_registration')->select('code')->where(['username' => session('id_number')])->pluck('code');
        }
        $data['myLectureTimetable'] = DB::table($this->table)->whereIn('course', $myCourses)->where(['session' => session('system_session')])->get();
        //$data['rows'] = $routines->orderBy('start_time', 'asc')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }
    public function myTable()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table('lecture_timetable')->get();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['courses'] = DB::table('course')->select('code', 'title', 'faculty', 'level')->orderBy('code', 'ASC')->get();
        $data['hall'] = DB::table('hall_allocation')->where(['status' => '1'])->groupBy('hall', 'faculty')->select('hall', 'faculty')->orderBy('hall', 'ASC')->get();
        if (session('accType') == 'Staff') {
            $myCourses = DB::table('course_allocation')->select('course')->where(['username' => session('username')])->pluck('course');
        } else {
            $myCourses = DB::table('student_course_registration')->select('code')->where(['username' => session('id_number'), 'semester' => session('system_semester'), 'status' => 'awaiting'])->pluck('code');
        }
        $data['myLectureTimetable'] = DB::table('lecture_timetable')->whereIn('course', $myCourses)->where(['semester' => session('system_semester'), 'session' => session('system_session')])->get();
        //$data['rows'] = $routines->orderBy('start_time', 'asc')->get();
        $data['page'] = 'my lecture timetable';
        $data['title'] = $this->title;
        return view('main', $data);
    }
    public function create(Request $req)
    {

        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        unset($datas['_token']);
        $datas['fac'] = json_encode($req->fac);
        $course = $datas['course'];
        $hall = $datas['hall'];
        $startTime = $datas['start'];
        $endTime = $datas['end'];
        $day_no = $datas['day_no'];
        $level = DB::table('course')->where(['code' => $course])->select('level')->value('level');
        if ($day_no == 1)
            $datas['day'] = 'Monday';
        if ($day_no == 2)
            $datas['day'] = 'Tuesday';
        if ($day_no == 3)
            $datas['day'] = 'Wednesday';
        if ($day_no == 4)
            $datas['day'] = 'Thursday';
        if ($day_no == 5)
            $datas['day'] = 'Friday';
        if ($day_no == 6)
            $datas['day'] = 'Saturday';
        if ($day_no == 7)
            $datas['day'] = 'Sunday';

        $valid = DB::table('hall_allocation')
            ->where(['faculty' => $datas['faculty'], 'hall' => $datas['hall']])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($subQuery) use ($startTime, $endTime) {
                    $subQuery->where('start_at', '<=', $startTime)
                        ->where('end_at', '>=', $startTime);
                })
                    ->where(function ($subQuery) use ($startTime, $endTime) {
                        $subQuery->where('start_at', '<', $endTime)
                            ->where('end_at', '>=', $endTime);
                    });
            })
            ->exists();

        if ($valid) {
        } else {
            return redirect()->back()->with('error', $hall . ' is not allocated to your faculty at this time');
        }

        $clashQuery = DB::table('lecture_timetable')
            ->where('hall', '=', $hall)
            ->where(function ($query) use ($startTime, $endTime, $day_no) {
                $query->where(function ($innerQuery) use ($startTime, $endTime) {
                    $innerQuery->where('start', '<=', $startTime)
                        ->where('end', '>', $startTime);
                })
                    ->orWhere(function ($innerQuery) use ($startTime, $endTime) {
                        $innerQuery->where('start', '<', $endTime)
                            ->where('end', '>=', $endTime);
                    })
                    ->orWhere(function ($innerQuery) use ($startTime, $endTime) {
                        $innerQuery->where('start', '>', $startTime)
                            ->where('end', '<', $endTime);
                    });
            })
            ->where('day_no', '=', $day_no)
            ->count();

        if ($clashQuery >= 1) {
            return redirect()->back()->with('error', $hall . ' is already occupied by other course at this time');
        }
        if (isset($req->fac)) {
            $getPrograms = DB::table('program_course_registration')->where('code', $course)->select('program', 'level')->get();
            foreach ($getPrograms as $program) {
                $checkCourses = DB::table('program_course_registration')->where(['program' => $program->program, 'level' => $level])->select('code')->get();
                $getFaculty = DB::table('program')->where(['code' => $program->program])->select('faculty')->value('faculty');
                foreach ($checkCourses as $check) {
                    //dd($req -> fac);
                    $getLectures = DB::table('lecture_timetable')->where(['course' => $check->code])->select('course', 'start', 'end', 'day_no', 'hall', 'id', 'fac')->get();
                    //print_r($getLectures);
                    foreach ($getLectures as $lect) {
                        $start = $lect->start;
                        $end = $lect->end;
                        $day_no_ = $lect->day_no;

                        //dd($req -> fac);
                        if ((($start <= $startTime && $end > $startTime) || ($start < $endTime && $end >= $endTime) || ($start > $startTime && $end < $endTime)) && $day_no_ == $day_no && $lect->course != $course) {
                            foreach ($req->fac as $key => $value) {
                                $getFac = json_decode($lect->fac);
                                foreach ($getFac as $key => $facValue) {

                                    $idd = DB::table('program_course_registration')->where(['program' => $program->program, 'level' => $level, 'code' => $lect->course])->select('id')->value('id');
                                    if ($facValue == $value && $idd > 0) {
                                        $getAllProgram = DB::table('program')->where('faculty', $value)->select('code')->get();
                                        foreach ($getAllProgram as $gap) {
                                            $checkCourseReg = DB::table('program_course_registration')->where(['program' => $gap->code, 'code' => $lect->course])->exists();
                                            if ($checkCourseReg) {
                                                //dd($value . ' = ' . $getFaculty . ' ' . $lect->id);
                                                return redirect()->back()->with('error','Other Program (' . DB::table('program')->where(['code' => $program->program])->select('title')->value('title') . ') that are borrowing this course have lecture at this time, for ' . $lect->course . ' at ' . $lect->hall . ' ' . date('H:i A', strtotime($lect->start)) . ' to ' . date('H:i A', strtotime($lect->end)));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //dd('Good');
        $datas = array_map('strtoupper', $datas);
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
        //return response()->json(['message' => 'All forms submitted successfully']);
    }
}
