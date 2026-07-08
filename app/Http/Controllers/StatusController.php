<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StatusImport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
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
        $this->page = 'status';
        //$this->table = str_replace(" ", "_", $this->page);
        $this->table = 'students';
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if(!session()->has('log')){return redirect('/');}
        $data['data'] = DB::table($this->table)->first();
        if(session('appointment') == 'HOD' || session('appointment') == 'DEAN'){
            $data['faculty'] = DB::table('faculty')->where(['code' => session('faculty')])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }else{
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }

        //return 'stuss';
            $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function upload(Request $request)
    {
    if(!session()->has('log')){return redirect('/');}
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request -> faculty;
            $department = $request -> department;
            $program = $request -> program;
            $level = $request -> level;
            $mode = $request -> mode;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new StatusImport($faculty, $department, $program, $level, $mode);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }
}
