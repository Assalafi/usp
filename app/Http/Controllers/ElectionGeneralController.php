<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ElectionGeneralController extends Controller
{
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace('create ', '', $contents);
        $contents = str_replace('upload ', '', $contents);
        $contents = str_replace('download ', '', $contents);
        $contents = str_replace('update ', '', $contents);
        $contents = str_replace('delete ', '', $contents);
        $this->page = $contents;
        $this->table = 'election_candidates';
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (session('accType') == 'Staff') {
            // dd(session('accType'));
            $data['data'] = DB::table($this->table)->select($this->table . '.*', 'program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table . '.program', '=', 'program.code');
            })->where($this->table . '.acc_type', 'Staff')->orderBy($this->table . '.vote', 'DESC')->get();
            // get election positions from election_candidates table, unique positions then pass it to election_positions table
            $positions = DB::table($this->table)->where('acc_type', 'Staff')->select('position')->groupBy('position')->pluck('position');

            $data['poss'] = DB::table('election_positions')->whereIn('position', $positions)->orderBy('order', 'ASC')->get();
        } elseif (session('accType') == 'Student') {
            $data['data'] = DB::table($this->table)->select($this->table . '.*', 'program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table . '.program', '=', 'program.code');
            })->where($this->table . '.acc_type', 'Student')->orderBy($this->table . '.vote', 'DESC')->get();
            // get election positions from election_candidates table, unique positions then pass it to election_positions table
            $positions = DB::table($this->table)->where('acc_type', 'Student')->select('position')->groupBy('position')->pluck('position');

            $data['poss'] = DB::table('election_positions')->whereIn('position', $positions)->orderBy('order', 'ASC')->get();
        }elseif (session('accType') == 'Alumni') {
            // dd(session('accType'));
            $data['data'] = DB::table($this->table)->select($this->table . '.*', 'program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table . '.program', '=', 'program.code');
            })->where($this->table . '.acc_type', 'Alumni')->orderBy($this->table . '.vote', 'DESC')->get();
            // get election positions from election_candidates table, unique positions then pass it to election_positions table
            $positions = DB::table($this->table)->where('acc_type', 'Alumni')->select('position')->groupBy('position')->pluck('position');

            $data['poss'] = DB::table('election_positions')->whereIn('position', $positions)->orderBy('order', 'ASC')->get();
        } else {
            $data['data'] = DB::table($this->table)->select($this->table . '.*', 'program.title AS program_title')->leftJoin('program', function ($join) {
                $join->on($this->table . '.program', '=', 'program.code');
            })->orderBy($this->table . '.vote', 'DESC')->get();
            $data['poss'] = DB::table('election_positions')->orderBy('order', 'ASC')->get();
        }
        $data['category'] = 'General';
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
