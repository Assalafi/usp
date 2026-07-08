<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\CourseImport;
use App\Models\User;

class GradingSystemController extends Controller
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
        if(!session()->has('log')){return redirect('/');}
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->orderBy('code', 'ASC')->get();
        }else{
            $data['data'] = DB::table($this->table)->where(['status' => '1'])->select('ref','name','from','to')->groupBy('ref','name','from','to')->orderBy('name', 'DESC')->orderBy('from', 'ASC')->get();
        }
        $data['grading'] = DB::table($this->table)->select('name')->groupBy('name')->orderBy('name', 'ASC')->get();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        try {
            DB::beginTransaction();

            $min_score = $req->input('min_score');
            $max_score = $req->input('max_score');
            $grade = $req->input('grade');
            $remark = $req->input('remark');
            $point = $req->input('point');
            $name = $req->input('name');
            $from = $req->input('from');
            $to = $req->input('to');

            foreach ($min_score as $key => $page) {
                DB::table($this->table)->insert([
                    'ref' => strtoupper($name.$from.$to),
                    'name' => strtoupper($name),
                    'from' => $from,
                    'to' => $to,
                    'min_score' => $min_score[$key],
                    'max_score' => $max_score[$key],
                    'grade' => strtoupper($grade[$key]),
                    'remark' => $remark[$key],
                    'point' => $point[$key],
                    'user' => session('username'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Record Created!!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            // die;
            return redirect()->back()->with('error', 'Error storing data.');
        }
    }

    public function upload(Request $request)
    {
    if(!session()->has('log')){return redirect('/');}
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the uploaded file using Maatwebsite/Excel
            $import = new CourseImport();
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if(isset($req -> program)){
            $program = [$req -> program];
        }else if(isset($req -> department)){
            $program = DB::table('program')->where('department',$req -> department)->pluck('code');
        }else if(isset($req -> faculty)){
            $program = DB::table('program')->where('faculty',$req -> faculty)->pluck('code');
        }
        $datas['grading'] = $req -> name;

        try {
            DB::table('program_course_registration')->whereIn('program',$program)->update($datas);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        } finally {
            return redirect()->back()->with('success', 'Applied!!!');
        }

    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('ref',$req->ref)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
