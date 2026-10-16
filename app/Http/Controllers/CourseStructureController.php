<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CourseStructure;
use App\Services\HodCurriculumReviewService;


class CourseStructureController extends Controller
{
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = 'course structure';
        $this->table = 'course_structure';
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if(!session()->has('log')){return redirect('/');}
        $data['data'] = DB::table($this->table)->get();
        $data['page'] = 'course structure';
        $data['title'] = 'Course Structure';
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        $id = DB::table($this->table)->insertGetId($datas);
        HodCurriculumReviewService::invalidateStructure(
            (int) $id,
            'Curriculum structure created',
            (string) session('username')
        );
        return redirect()->back()->with('success', 'Record Created!!!');
    }
    
    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        DB::table($this->table)->where('id', '>', '0')->update(['status' => '0']);
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);
        HodCurriculumReviewService::invalidateStructure(
            (int) $id,
            'Curriculum structure updated',
            (string) session('username')
        );

        return redirect()->back()->with('success', 'Record Updated!!!');
    }
    
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();
        HodCurriculumReviewService::invalidateStructure(
            (int) $req->id,
            'Curriculum structure removed',
            (string) session('username')
        );

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
