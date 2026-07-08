<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
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
        //if(!session()->has('log')){return redirect('/');}
        $data['data'] = DB::table($this->table)->orderBy('no', 'ASC')->get();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $ward = $datas['award'];
        $award_title = $datas['award_title'];
        $code = $datas['code'];
        $check = DB::table($this->table)->where(['code' => $code])->select('id')->value('id');
        if($check > 0){
            return redirect()->back()->with('error', 'Record Already Exist!!!');
        }
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);

        $datas['award'] = $ward;
        $datas['award_title'] = $award_title;

        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        $ward = $datas['award'];
        $award_title = $datas['award_title'];
        $code = $datas['code'];
        $check = DB::table($this->table)->where(['id' => $id])->select('code', 'code')->get();
        foreach ($check as $row) {
            if($code != $row -> code){
                $checks = DB::table($this->table)->where(['code' => $code])->select('id')->value('id');
                if($checks > 0){
                    return redirect()->back()->with('error', 'Record Already Exist!!!');
                }
            }
        }
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        $datas['award'] = $ward;
        $datas['award_title'] = $award_title;
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function departmentAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if(session('accType') == 'Admin' || session('appointment') == 'HOD' || session('appointment') == 'DEAN' || session('appointment') == 'VC' || session('unit') == 'COURSE SYSTEM'){
            if(session('appointment') == 'HOD'){
                $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'code' => session('department')])->orderBy('title', 'asc')->get();

            }else{
                $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();
            }

        }else{
            $datas = DB::table('rolls')->where(['link' => session('links'), 'username' => session('username')])->select('page', 'action', 'department')->get();
            foreach ($datas as $roll) {
                if($roll -> department != 'all' && $roll -> department != '' && $roll -> department != null){
                    $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'code' => $roll -> department, 'status' => '1'])->orderBy('title', 'asc')->get();
                }else{
                    $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();
                }
            }

        }

        $add = '<option value="all">Select Department</option>';
        foreach ($data as $roww) {
            $add .= '<option value="'.$roww -> code.'">'.$roww -> code.': '.$roww -> title.'</option>';
        }

        return $add;
    }

    public function programAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if(session('accType') == 'Admin' || session('appointment') == 'HOD' || session('appointment') == 'DEAN' || session('appointment') == 'VC' || session('unit') == 'COURSE SYSTEM'){
            $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();
        }else{
            $datas = DB::table('rolls')->where(['link' => session('links'), 'username' => session('username')])->select('page', 'action', 'program')->get();
            foreach ($datas as $roll) {
                if($roll -> program != 'all' && $roll -> program != '' && $roll -> program != null){
                    $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'code' => $roll -> program, 'status' => '1'])->orderBy('title', 'asc')->get();
                }else{
                    $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();
                }
            }

        }

        $add = '<option value="all">Select Program</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> code.': '.$row -> title.'</option>';
        }

        return $add;
    }

    public function departmentAjaxPublic(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();

        $add = '<option value="">Select Department</option>';
        foreach ($data as $roww) {
            $add .= '<option value="'.$roww -> code.'">'.$roww -> title.'</option>';
        }

        return $add;
    }

    public function programAjaxPublic(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();

        $add = '<option value="">Select Program</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> title.'</option>';
        }

        return $add;
    }


    public function courseAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}

        $data = DB::table('course')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'program' => $req -> program, 'status' => '1'])->orderBy('code', 'asc')->get();

        $add = '<option value="all">Select Course</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> code.': '.$row -> title.'</option>';
        }

        return $add;
    }
    public function allocationPrograms(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}

        $data = DB::table('program_course_registration')->select('program')->where(['code' => $req -> course])->orderBy('code', 'asc')->get();

        $programs = '';
        foreach ($data as $row) {
            $title = DB::table('program')->select('title')->where(['code' => $row -> program])->value('title');
            $programs .= '<br><input type="checkbox" name="programs[]" checked id="programs'.$row -> program.'" value="'.$row -> program.'"> <label for="programs'.$row -> program.'">'.$row -> program.': '.$title.'</label>';
        }

        return $programs;
    }
}
