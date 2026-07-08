<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class FixedAssetsDisposalController extends Controller
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
        $this->table = 'fixed_assets';
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
            $data['data'] = $query->where('disposal_date', '<=', date('Y-m-d'))->orderBy('class', 'ASC')->get();
        }else{
            $data['data'] = DB::table($this->table)->where('disposal_date', 'none')->where('disposal_date', '<=', date('Y-m-d'))->orderBy('class', 'ASC')->get();
        }
        $data['locations'] = DB::table($this->table)->select('location')->groupBy('location')->orderBy('location', 'ASC')->get(['location']);
        $data['class'] = DB::table('manage_fixed_assets')->select('class')->groupBy('class')->orderBy('class', 'ASC')->get(['class']);
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
}
