<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ElectionCandidatesController extends Controller
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
        $data['data'] = DB::table($this->table)->orderBy('vote', 'DESC')->get();
        $data['poss'] = DB::table('election_positions')->orderBy('order', 'ASC')->get();
        // dd($data['positions']);
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['candidate'];
        $user_id = DB::table('students')->where('username', $id)->select('user_id')->value('user_id');
        $accType = DB::table('users')->where('id', $user_id)->select('accType')->value('accType');
        $category = $datas['category'];
        $pos = $datas['position'];
        $check = 0;
        $elec = DB::table('election_candidates')->where('candidate', $id)->select('id')->value('id');
        if ($elec > 0) {
            return redirect()->back()->with('error', 'Candidate Already Registered as ' . DB::table('election_candidates')->where('candidate', $id)->select('position')->value('position'));
        }

        if ($accType == 'Student') {
            $records = DB::table('students')->where(['username' => $id])->select('fullname', 'gender', 'faculty', 'program', 'level', 'user_id', 'picture', 'state_origin')->get();
            foreach ($records as $row) {
                $datas['name'] = $row->fullname;
                $datas['gender'] = $row->gender;
                $datas['level'] = $row->level;
                $datas['faculty'] = $row->faculty;
                $datas['program'] = $row->program;
                $datas['picture'] = $row->picture;
                $path = public_path('storage/picture/' . $datas['picture']);
                if (!file_exists($path)) {
                    return redirect()->back()->with('error', 'No Picture Found!!!');
                }
                $datas['state'] = $row->state_origin;
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender;
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return redirect()->back()->with('error', 'No Record Found!!!');
            }

            $active = DB::table('users')->where('id', $user_id)->select('status')->value('status');
            if ($active == 0) {
                return redirect()->back()->with('error', 'Inactive Student!!!');
            }

            $records = DB::table('election_positions')->where(['id' => $pos])->select('position', 'category', 'order', 'gender')->get();
            foreach ($records as $row) {
                $datas['position'] = $row->position;
                $datas['order'] = $row->order;
                if ($category == 'Hostel Rep') {
                    $hostel = DB::table('hostel')->where('occupant', $id)->select('id')->value('id');
                    if ($hostel > 0) {
                        $datas['hostel'] = DB::table('hostel')->where('occupant', $id)->select('hall')->value('hall');
                    } else {
                        return redirect()->back()->with('error', 'No Bed Space Assign for this Candidate');
                    }
                }
                if ($category == 'Lga Rep') {
                    if (DB::table('students')->where('username', $id)->update(['lga_origin' => $req->lga, 'state_origin' => 'BORNO'])) {
                        $datas['lga'] = $req->lga;
                    }
                }
                $elec_gender = $row->gender;
            }
            if ($category == 'Faculty Rep' && $datas['level'] != $datas['position']) {
                return redirect()->back()->with('error', 'Level Error, the student is in ' . $datas['level'] . ' not ' . $datas['position']);
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return redirect()->back()->with('error', 'Gender Error!!!');
            }
        } elseif ($accType == 'Staff') {
            $records = DB::table('staff')->where(['username' => $id])->select('name', 'gender', 'faculty', 'program', 'grade', 'user_id', 'picture', 'state')->get();
            foreach ($records as $row) {
                $datas['name'] = $row->name;
                $datas['gender'] = $row->gender;
                $datas['level'] = $row->grade;
                $datas['faculty'] = $row->faculty;
                $datas['program'] = $row->program;
                $datas['picture'] = $row->picture;
                // make picture exist, if not return, for empty,null etc. also check the path to see the picture
                $path = public_path('storage/picture/' . $datas['picture']);
                if (!file_exists($path)) {
                    return redirect()->back()->with('error', 'No Picture Found!!!');
                }
                $datas['state'] = $row->state;
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender;
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return redirect()->back()->with('error', 'No Record Found!!!');
            }

            $records = DB::table('election_positions')->where(['id' => $pos])->select('position', 'category', 'order', 'gender')->get();
            foreach ($records as $row) {
                $datas['position'] = $row->position;
                $datas['order'] = $row->order;
                $elec_gender = $row->gender;
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return redirect()->back()->with('error', 'Gender Error!!!');
            }
        } elseif ($accType == 'Alumni') {
            $records = DB::table('alumni')->where(['username' => $id])->select('fullname', 'gender', 'user_id', 'picture')->get();
            foreach ($records as $row) {
                $datas['name'] = $row->fullname;
                $datas['gender'] = $row->gender;
                $datas['level'] = 'alumni';
                $datas['faculty'] = 'alumni';
                $datas['program'] = 'alumni';
                $datas['picture'] = $row->picture;
                // make picture exist, if not return, for empty,null etc. also check the path to see the picture
                $path = public_path('storage/picture/' . $datas['picture']);
                if (!file_exists($path)) {
                    return redirect()->back()->with('error', 'No Picture Found!!!');
                }
                $datas['state'] = 'alumni';
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender ?? 'MALE';
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return redirect()->back()->with('error', 'No Record Found!!!');
            }

            $records = DB::table('election_positions')->where(['id' => $pos])->select('position', 'category', 'order', 'gender')->get();
            foreach ($records as $row) {
                $datas['position'] = $row->position;
                $datas['order'] = $row->order;
                $elec_gender = $row->gender;
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return redirect()->back()->with('error', 'Gender Error!!!');
            }
        } else {
            return redirect()->back()->with('error', 'No Record Found!!!');
        }

        unset($datas['_token']);
        // $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
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
