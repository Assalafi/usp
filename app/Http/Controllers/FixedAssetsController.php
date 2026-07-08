<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FixedAssetsImport;

class FixedAssetsController extends Controller
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
        if(!session()->has('log')){return redirect('/');}
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->where('disposal_date', '>', date('Y-m-d'))->orderBy('class', 'ASC')->get();
        }else{
            $data['data'] = DB::table($this->table)->where('disposal_date', 'none')->orderBy('class', 'ASC')->get();
        }
        $data['locations'] = DB::table($this->table)->select('location')->groupBy('location')->orderBy('location', 'ASC')->get(['location']);
        $data['class'] = DB::table('manage_fixed_assets')->select('class')->groupBy('class')->orderBy('class', 'ASC')->get(['class']);
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $data = DB::table('manage_fixed_assets')->where(['class' => $req -> class, 'description' => $req -> description])->get();
        foreach($data as $row){
            $datas['class_id'] = $row -> class_id;
            $datas['life'] = $row -> life;
            $datas['depreciation'] = $row -> depreciation;
            $datas['ncoa'] = $row -> ncoa;
            $datas['disposal_date'] = date('Y-m-d', strtotime($req -> capitalization.' '.$row -> life.' years'));
            $datas['created_at'] = now();
            $datas['updated_at'] = now();

        }
        $datas['month'] = date('m',strtotime($req -> capitalization));
        $datas['year'] = date('Y',strtotime($req -> capitalization));
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
        $data = DB::table('manage_fixed_assets')->where('class', $req -> class)->get();
        foreach($data as $row){
            $datas['class_id'] = $row -> class_id;
            $datas['life'] = $row -> life;
            $datas['depreciation'] = $row -> depreciation;
            $datas['ncoa'] = $row -> ncoa;
            $datas['disposal_date'] = date('Y-m-d', strtotime($req -> capitalization.' '.$row -> life.' years'));
            $datas['updated_at'] = now();
        }
        $datas['month'] = date('m',strtotime($req -> capitalization));
        $datas['year'] = date('Y',strtotime($req -> capitalization));
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
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
            $import = new FixedAssetsImport();
            Excel::import($import, $file);
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();
        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
