<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\ResultImport;

class ResultsController extends Controller
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
        $session = DB::table('session')->where('status', '1')->value('title');
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            unset($data['faculty']);
            unset($data['department']);
            unset($data['program']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->get();
        } else {

            $data['data'] = DB::table($this->table)->where(['session' => 'none'])->get();
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        $data['lecturerCourses'] = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->get();
        $data['sessions'] = $session;
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function pendingResults(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $session = DB::table('session')->where('status', '1')->value('title');
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            unset($data['faculty']);
            unset($data['department']);
            unset($data['program']);
            $filteredData = array_filter($data);
            $query = DB::table('student_course_registration');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->get();
        } else {
            $rs = DB::table('results')->select('username', 'ca', 'code', 'exam', 'total', 'grade', 'approve')->where(['session' => $req->session, 'semester' => $req->semester, 'code' => $req->code]);
            $data['results'] = $rs->get();
            $result = $rs->pluck('username');
            $data['data'] = DB::table('student_course_registration')->select('username', 'comment', 'code')->where(['session' => $req->session, 'code' => $req->code])->whereNotIn('username', $result)->get();
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        $data['lecturerCourses'] = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->get();
        $data['lecturer'] = DB::table('course_allocation')->where(['username' => session('username'), 'course' => $req->code])->select('username')->value('username');
        $data['sessions'] = $session;
        $data['session'] = $req->session;
        $data['semester'] = $req->semester;
        $data['code'] = $req->code;
        $data['page'] = 'pending results';
        $data['title'] = 'Pending Results';
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
        return redirect()->back()->with('success', 'Record Created!!!');
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
        $getYear = explode("/", $id);
        $getYear = '20' . $getYear[0];
        if ($exam > 70) {
            $exam = 70;
        }
        if ($ca > 30) {
            $ca = 30;
        }

        $std_program = DB::table('students')->where(['username' => $id])->select('program')->value('program');
        $grading = DB::table('program_course_registration')->where(['code' => $code, 'program' => $std_program])->select('grading')->value('grading');
        $data = DB::table('results')->where(['id' => $idd])->select('id', 'program')->get();
        foreach ($data as $row) {
            $year = $id[0] . $id[1];
            $total = (int) $ca + (int) $exam;
            $unit = DB::table('course')->where(['code' => $code])->value('unit');
            $unit = (int) $unit;
            $remark = 'PASS';


            $gradings = DB::table('grading_system')
                ->where(function ($query) use ($getYear) {
                    $query->where(function ($innerQuery) use ($getYear) {
                        $innerQuery->where('to', '!=', 'current')
                            ->where('from', '<=', $getYear)
                            ->where('to', '>=', $getYear);
                    })
                        ->orWhere(function ($innerQuery) use ($getYear) {
                            $innerQuery->where('to', '=', 'current')
                                ->where('from', '<=', $getYear);
                        });
                })
                ->where('min_score', '<=', $total)
                ->where('max_score', '>=', $total)
                ->where('name', $grading)
                ->get();
            //dd($grading);

            foreach ($gradings as $gs) {
                $grade = $gs->grade;
                $point = $unit * $gs->point;
                $remark = $gs->remark;
            }

            if ($row->program == 'MBBS' || $row->program == 'BDS') {
                if ($total < 50) {
                    $grade = 'F';
                    $point = $unit * 0;
                    $remark = 'FAIL';
                } elseif ($total < 60) {
                    $grade = 'C';
                    $point = $unit * 3;
                } elseif ($total < 70) {
                    $grade = 'B';
                    $point = $unit * 4;
                } elseif ($total < 101) {
                    $grade = 'A';
                    $point = $unit * 5;
                }
            } else {
                if ($year < 14) {
                    if ($total < 40) {
                        $grade = 'F';
                        $point = $unit * 0;
                        $remark = 'FAIL';
                    } elseif ($total < 45) {
                        $grade = 'E';
                        $point = $unit * 1;
                    } elseif ($total < 50) {
                        $grade = 'D';
                        $point = $unit * 2;
                    } elseif ($total < 55) {
                        $grade = 'C';
                        $point = $unit * 3;
                    } elseif ($total < 60) {
                        $grade = 'C+';
                        $point = $unit * 3.5;
                    } elseif ($total < 65) {
                        $grade = 'B';
                        $point = $unit * 4;
                    } elseif ($total < 70) {
                        $grade = 'B+';
                        $point = $unit * 4.5;
                    } elseif ($total < 101) {
                        $grade = 'A';
                        $point = $unit * 5;
                    }

                } else if ($year > 13 && $year < 18) {
                    if ($total < 45) {
                        $grade = 'F';
                        $point = $unit * 0;
                        $remark = 'FAIL';
                    } elseif ($total < 50) {
                        $grade = 'D';
                        $point = $unit * 2;
                    } elseif ($total < 60) {
                        $grade = 'C';
                        $point = $unit * 3;
                    } elseif ($total < 70) {
                        $grade = 'B';
                        $point = $unit * 4;
                    } elseif ($total < 101) {
                        $grade = 'A';
                        $point = $unit * 5;
                    }

                } else if ($year > 17) {
                    if ($total < 40) {
                        $grade = 'F';
                        $point = $unit * 0;
                        $remark = 'FAIL';
                    } elseif ($total < 45) {
                        $grade = 'E';
                        $point = $unit * 1;
                    } elseif ($total < 50) {
                        $grade = 'D';
                        $point = $unit * 2;
                    } elseif ($total < 60) {
                        $grade = 'C';
                        $point = $unit * 3;
                    } elseif ($total < 70) {
                        $grade = 'B';
                        $point = $unit * 4;
                    } elseif ($total < 101) {
                        $grade = 'A';
                        $point = $unit * 5;
                    }

                }
            }
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
            DB::table('results')->where(['id' => $idd])->update($records);
        }

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function pending(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('student_course_registration')->where(['username' => $req->username, 'code' => $req->code])->update([
            'comment' => $req->comment
        ]);

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

    public function upload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = 'faculty';
            $department = 'department';
            $program = 'program';
            $level = 'level';
            $course = $request->course;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new ResultImport($faculty, $department, $program, $course);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }
}
