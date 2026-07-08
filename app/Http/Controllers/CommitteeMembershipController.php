<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CommitteeMembershipController extends Controller
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
        $data['data'] = DB::table($this->table)->orderBy('id', 'DESC')->get();
        $data['committee'] = DB::table('committee')->orderBy('name', 'ASC')->get();
        $data['sub_committee'] = DB::table('sub_committee')->orderBy('name', 'ASC')->get();
        $data['role'] = DB::table('committee_role')->orderBy('name', 'ASC')->get();
        $data['staff'] = DB::table('staff')->select('username', 'name')->orderBy('username', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}


        $staffs = $req->input('staff');

        foreach ($staffs as $key => $staff) {
            $staff = $staff;
            $data = DB::table('staff')->where('username', $staff)->select('name','current_rank')->get();
            foreach ($data as $row) {
                DB::table('committee_membership')->updateOrInsert(['username' =>  $staff, 'committee' => $req->committee, 'sub_committee' => $req->sub_committee],[
                    'username' =>  $staff,
                    'position' =>  $row->current_rank,
                    'name' =>  $row->name,
                    'committee' => $req->committee,
                    'sub_committee' => $req->sub_committee,
                    'role' => $req->role,
                    'updated_at' => now(),
                ]);
            }
        }

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
}
