<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ApproveResultsController extends Controller
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
        $this->table = 'results';
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $session = DB::table('session')->where('status', '1')->value('title');
        if ($req->has('_token')) {
            $data = $req->all();
            $ses = $data['session'];
            unset($data['_token']);
            unset($data['faculty']);
            unset($data['department']);
            unset($data['session']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->select('code','session','semester','approve','lecturer')->groupBy('code','session','semester','approve','lecturer')->get();
            $data['sessions'] = $ses;
        }else{
            $data['data'] = DB::table($this->table)->where(['status' => '2'])->get();
            $data['sessions'] = $session;
        }
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        if(session('accType') == 'Staff' && session('appointment') != 'DEAN' && session('appointment') != 'VC' && session('unit') != 'COURSE SYSTEM'){
            $data['sessions'] = $session;
            if(session('appointment') == 'HOD'){
                $lecturerCourses = DB::table('course')->where(['department' => session('department')])->select('code')->pluck('code');

            }else{
                $lecturerCourses = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->pluck('course');
            }
            $data['data'] = DB::table($this->table)->whereIn('code',$lecturerCourses)->select('code','session','semester','approve','lecturer')->groupBy('code','session','semester','approve','lecturer')->get();
        }
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $code = $datas['code'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('approve')->limit(1)->get();
                //dd($datas);
        foreach ($data as $row) {
            if($row -> approve == 'system'){
                $status = 'Submitted to HOD';
                $records['approve'] = 'lecturer';
                $records['lecturer'] = session('username');
            }else if($row -> approve == 'lecturer'){
                $status = 'Submitted to Dean of the Faculty';
                $records['approve'] = 'hod';
                $records['hod'] = session('username');
            }else if($row -> approve == 'hod'){
                $status = 'Submitted to Course System';
                $records['approve'] = 'dean';
                $records['dean'] = session('username');
            }else if($row -> approve == 'dean'){
                $status = 'Submitted to VC';
                $records['approve'] = 'cs';
                $records['cs'] = session('username');
            }else if($row -> approve == 'cs'){

                $status = 'Approved!!!';
                $records['approve'] = 'vc';
                $records['vc'] = session('username');
                $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('username', 'unit', 'point', 'ugp', 'total', 'grade', 'session', 'semester', 'level', 'remark')->get();
                foreach ($data as $row) {
                    $record['total'] = $row -> total;
                    $record['grade'] = $row -> grade;
                    $record['status'] = $row -> remark;
                    $record['unit'] = $row -> unit;
                    $record['point'] = $row -> point;
                    $record['ugp'] = $row -> ugp;

                    // $current = $row -> current_level;
                    // $final = $row -> final_level;
                    
                    $current = 2;
                    $final = 1;
                    //echo $row -> username;
                    //die;
                    DB::table('student_course_registration')->where(['code' => $code, 'session' => $row -> session, 'semester' => $row -> semester, 'username' => $row -> username])->update($record);
                    $id = DB::table('student_course_registration')->where(['username' => $row -> username, 'status' => 'awaiting'])->select('id')->value('id');
                    if($id > 0){}else{
                        if($current == $final){

                        }else{
                            $unit = 0;
                            $ugp = 0;
                            $cgpa = 0;
                            $carry = '';
                            $statuss = '';
                            $class = '';
                            $f = 0;$flag = 0;
                            $p = 0;
                            $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
                            foreach ($reg as $result) {
                                $unit = $unit + $result -> unit;
                                $ugp = $ugp + $result -> ugp;
                                if($result -> grade == 'F'){
                                  $carry = $carry .' '. $result -> code;
                                  $f++;
                                }
                          }
                        $cgpa = $ugp/$unit;
                            if($f > 6 || $cgpa < 1.0){

                            }
                        }
                    }
                }

            }else{
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        }
        DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->update($records);
        return redirect()->back()->with('success', 'Results '.$status);
    }
    
    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $code = $datas['code'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('approve')->limit(1)->get();
                //dd($datas);
        foreach ($data as $row) {
            if($row -> approve == 'system'){
                $status = 'Rejeted Back to Lecturer';
                $records['approve'] = 'system';
                $records['lecturer'] = session('username');
            }else if($row -> approve == 'lecturer'){
                $status = 'Rejeted Back to Lecturer';
                $records['approve'] = 'system';
                $records['hod'] = session('username');
            }else if($row -> approve == 'hod'){
                $status = 'Rejeted Back to HOD';
                $records['approve'] = 'lecturer';
                $records['dean'] = session('username');
            }else if($row -> approve == 'dean'){
                $status = 'Rejeted Back to DEAN';
                $records['approve'] = 'hod';
                $records['cs'] = session('username');
            }else if($row -> approve == 'cs'){

                $status = 'Rejeted!!!';
                $records['approve'] = 'dean';
                $records['vc'] = session('username');
            }else{
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        }
        DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->update($records);
        return redirect()->back()->with('success', 'Results '.$status);
    }
    
    public function updateMark(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $code = $datas['code'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $mark = $datas['mark'];
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('id', 'ca', 'exam', 'total', 'username', 'total', 'program')->get();
        foreach ($data as $row) {
            $exam = (int)($row -> exam) + (int)$mark;
            if($exam > 70){
                $exam = 70;
            }

                $id = $row -> username;
                //echo $id;
                //die;
                $getYear = explode("/", $id);
                $getYear = '20' . $getYear[0];

                $std_program = DB::table('students')->where(['username' => $id])->select('program')->value('program');
                $grading = DB::table('program_course_registration')->where(['code' => $code, 'program' => $std_program])->select('grading')->value('grading');

                $year = $id[0].$id[1];

                $ca =  $row -> ca;
                $total = (int)$ca+(int)$exam;
                $unit = DB::table('course')->where(['code' => $code])->value('unit');
                $unit = (int)$unit;
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

                foreach ($gradings as $gs) {
                    $grade = $gs->grade;
                    $point = $unit * $gs->point;
                    $remark = $gs->remark;
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

                if($row -> program == 'MBBS' || $row -> program == 'BDS'){
                    if($total<50){
                        $grade = 'F';
                        $point = $unit * 0;
                        $remark = 'FAIL';
                    }elseif($total<60){
                        $grade = 'C';
                        $point = $unit * 3;
                    }elseif($total<70){
                        $grade = 'B';
                        $point = $unit * 4;
                    }elseif($total<101){
                        $grade = 'A';
                        $point = $unit * 5;
                    }
                }else{
                    if($year<14){
                        if($total<40){
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        }elseif($total<45){
                            $grade = 'E';
                            $point = $unit * 1;
                        }elseif($total<50){
                            $grade = 'D';
                            $point = $unit * 2;
                        }elseif($total<55){
                            $grade = 'C';
                            $point = $unit * 3;
                        }elseif($total<60){
                            $grade = 'C+';
                            $point = $unit * 3.5;
                        }elseif($total<65){
                            $grade = 'B';
                            $point = $unit * 4;
                        }elseif($total<70){
                            $grade = 'B+';
                            $point = $unit * 4.5;
                        }elseif($total<101){
                            $grade = 'A';
                            $point = $unit * 5;
                        }

                    }else if($year>13 && $year<18){
                        if($total<45){
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        }elseif($total<50){
                            $grade = 'D';
                            $point = $unit * 2;
                        }elseif($total<60){
                            $grade = 'C';
                            $point = $unit * 3;
                        }elseif($total<70){
                            $grade = 'B';
                            $point = $unit * 4;
                        }elseif($total<101){
                            $grade = 'A';
                            $point = $unit * 5;
                        }

                    }else if($year>17){
                        if($total<40){
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        }elseif($total<45){
                            $grade = 'E';
                            $point = $unit * 1;
                        }elseif($total<50){
                            $grade = 'D';
                            $point = $unit * 2;
                        }elseif($total<60){
                            $grade = 'C';
                            $point = $unit * 3;
                        }elseif($total<70){
                            $grade = 'B';
                            $point = $unit * 4;
                        }elseif($total<101){
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
                if($point > 0){
                    $records['point'] = $point/$unit;
                }else{
                    $records['point'] = $point;
                }
                DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester, 'username' => $row -> username])->update($records);
        }
        return redirect()->back()->with('success', 'Results Updated!!!');
    }
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();
        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
