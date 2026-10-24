<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ElectionHostelController extends Controller
{
    public function create(Request $req)
    {
        return app(ElectionCandidatesController::class)->create($req);
    }

    public function update(Request $req)
    {
        return app(ElectionCandidatesController::class)->update($req);
    }

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
        $this->table = 'election_candidates';
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table)->select($this->table.'.*','program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table.'.program', '=', 'program.code'); })->orderBy($this->table.'.vote', 'DESC');
            foreach ($filteredData as $key => $value) {
                $query->where($this->table.'.'.$key, $value);
            }
            $data['data'] = $query->get();
        }else{
            if(session('accType') == 'Student'){
                //die;
                $data['data'] = DB::table($this->table)->select($this->table.'.*','program.title AS program_title')->leftJoin('program', function ($join) {
                    $join->on($this->table.'.program', '=', 'program.code'); })->orderBy($this->table.'.vote', 'DESC')->get();
            }else{
                // Administrators need all hostel candidates in order to review
                // the halls represented on this page. Do not apply the old
                // placeholder faculty filter here.
                $data['data'] = DB::table($this->table)->select($this->table.'.*','program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table.'.program', '=', 'program.code'); })->orderBy($this->table.'.vote', 'DESC')->get();
            }
        }
        $data['poss'] = DB::table('election_positions')->orderBy('order', 'ASC')->get();
        $data['faculty'] = DB::table('faculty')->orderBy('title', 'ASC')->get();
        $data['hostel'] = DB::table('hostel')->select('hall')->distinct()->orderBy('hall', 'ASC')->get();
        $data['category'] = 'Hostel Rep';
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();
        if ($req->expectsJson()) return response()->json(['ok' => (bool) $id, 'id' => $req->id, 'message' => 'Candidate removed.']);

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
