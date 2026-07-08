<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamTimetableController extends Controller
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
        $data['course'] = DB::table('course')->where(['status' => '1'])->select('code', 'title', 'faculty', 'level')->orderBy('code', 'ASC')->get();
        $data['hall'] = DB::table('halls')->where(['status' => '1'])->select('hall')->orderBy('hall', 'ASC')->get();
        //$data['rows'] = $routines->orderBy('start', 'asc')->get();
        $data['page'] = $this->page;
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
        $course = $datas['course'];
        $hall = $datas['hall'];
        $startTime = $datas['start'];
        $endTime = $datas['end'];
        $date = date('Y-m-d', strtotime($datas['date']));

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
            return redirect()->back()->with('error', $hall . ' is not allocated your faculty on this selected time');
        }

        $clashQuery = DB::table('exam_timetable')
            ->where('hall', '=', $hall)
            ->where(function ($query) use ($startTime, $endTime, $date) {
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
            ->where('date', '=', $date)
            ->count();

        if ($clashQuery >= 2) {
            return redirect()->back()->with('error', $hall . ' is already occupied by two courses at this selected time and date');
        }

        $getCourses = DB::table('exam_timetable')->where('date', '=', $date)->get();
        foreach ($getCourses as $courses) {
            // $startTime = $courses->start;
            // $endTime = $courses->end;
            $getPrograms = DB::table('program_course_registration')->where('code', $courses->course)->select('program', 'level')->get();
            foreach ($getPrograms as $program) {
                $checkCourses = DB::table('program_course_registration')->where(['code' => $course, 'program' => $program->program, 'level' => $program->level])->select('code')->get();
                foreach ($checkCourses as $check) {

                    $clashQuery = DB::table('exam_timetable')
                        ->where(function ($query) use ($startTime, $endTime, $date) {
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
                        ->where('date', '=', $date)
                        ->exists();

                    if ($clashQuery) {
                        return redirect()->back()->with('error', 'Other faculty that are borrowing this course have exam at this time and date');
                    }

                }
            }
        }


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
        return 'Up';
        //return redirect()->back()->with('success', 'Record Updated!!!');
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
