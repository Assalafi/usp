<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ElectionVotingController extends Controller
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
        $this->table = str_replace(' ', '_', $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table($this->table)->orderBy('order', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function live()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table('election_candidates')->select('election_candidates.*', 'program.title AS program_title')->leftJoin('program', function ($join) {
            $join->on('election_candidates.program', '=', 'program.code');
        })->orderBy('election_candidates.vote', 'DESC')->get();
        $data['positions'] = DB::table('election_positions')->orderBy('order', 'ASC')->get();
        $data['category'] = 'General';
        $data['page'] = 'election live';
        $data['title'] = 'election live';
        return view('election live', $data);
    }

    public function voting(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data = $request->all();
        $category = $data['category'];
        unset($data['_token']);
        unset($data['category']);
        $flag = DB::table('election_votes')->where(['username' => session('id_number'), 'category' => $category])->select('id')->first();
        if ($flag)
            return redirect()->back()->with('error', 'You Already vote for this Category');

        // dd($data);

        foreach ($data as $key => $candidateId) {
            // check if candidateId is array
            if ($key !== 0) {
                $positionId = str_replace('_', ' ', $key);
                if (is_array($candidateId)) {
                    foreach ($candidateId as $candidate) {
                        $datas['username'] = session('id_number', session('username'));
                        $datas['candidate'] = $candidate;
                        $datas['category'] = $category;
                        $datas['position'] = $positionId;
                        $datas['acc_type'] = session('accType');
                        $datas['created_at'] = now();
                        $datas['updated_at'] = now();
                        DB::table('election_votes')->insert($datas);
                        DB::table('election_candidates')->where(['candidate' => $candidate, 'category' => $category, 'position' => $positionId])->increment('vote', 1);
                    }
                } else {
                    if ($candidateId) {
                        $datas['username'] = session('id_number', session('username'));
                        $datas['candidate'] = $candidateId;
                        $datas['category'] = $category;
                        $datas['position'] = $positionId;
                        $datas['acc_type'] = session('accType');
                        $datas['created_at'] = now();
                        $datas['updated_at'] = now();
                        DB::table('election_votes')->insert($datas);
                        DB::table('election_candidates')->where(['candidate' => $candidateId, 'category' => $category, 'position' => $positionId])->increment('vote', 1);
                    }
                }
            }
        }

        // die;
        // DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Your vote has been submitted!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];

        // $check = DB::table($this->table)->where(['id' => $id])->select('no', 'no')->get();
        // foreach ($check as $row) {
        //     if($no != $row -> no){
        //         $checks = DB::table($this->table)->where(['no' => $no])->select('id')->value('id');
        //         if($checks > 0){
        //             return redirect()->back()->with('error', 'Record Already Exist!!!');
        //         }
        //     }
        // }
        unset($datas['id']);
        unset($datas['_token']);
        // $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
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
