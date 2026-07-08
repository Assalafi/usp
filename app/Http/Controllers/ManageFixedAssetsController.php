<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ManageFixedAssetsController extends Controller
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
        if(!session()->has('log')){return redirect('/');}
        $data['data'] = DB::table($this->table)->orderBy('ncoa', 'ASC')->get();
        $data['class'] = DB::table('fixed_assets_depreciation')->select('class')->groupBy('class')->orderBy('class', 'ASC')->get(['class']);
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $data = DB::table('fixed_assets_depreciation')->where('class', $req -> class)->get();
        foreach($data as $row){
            $datas['class_id'] = $row -> id;
            $datas['life'] = $row -> life;
            $datas['depreciation'] = $row -> depreciation;
            $datas['ncoa'] = $row -> ncoa.$req -> ncoa;
        }
        $datas['created_at'] = now();
        $datas['updated_at'] = now();
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
        $data = DB::table('fixed_assets_depreciation')->where('class', $req -> class)->get();
        foreach($data as $row){
            $datas['class_id'] = $row -> id;
            $datas['life'] = $row -> life;
            $datas['depreciation'] = $row -> depreciation;
            $datas['ncoa'] = $req -> ncoa;
        }
        $datas['updated_at'] = now();
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);


        return redirect()->back()->with('success', 'Record Updated!!!');
    }
    public function descriptionAjax(Request $req)
    {
        //return $req -> class;
        $data = DB::table('manage_fixed_assets')->select('description')->where(['class' => $req -> class])->get();

        $add = '<option value="">Select Description</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> description.'">'.$row -> description.'</option>';
        }

        return $add;
    }
    
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
