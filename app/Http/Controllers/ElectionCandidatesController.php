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
        $category = trim((string) ($datas['category'] ?? ''));
        $pos = $datas['position'];
        $positionRecord = DB::table('election_positions')->where('id', $pos)->first(['id', 'position', 'category', 'order', 'gender']);
        if (!$positionRecord) {
            return $this->failure($req, 'The selected office no longer exists. Refresh the page and choose an available office.');
        }
        $category = $category !== '' ? $category : (string) $positionRecord->category;
        if (strcasecmp((string) $category, (string) $positionRecord->category) !== 0) {
            return $this->failure($req, 'The selected office belongs to the ' . $positionRecord->category . ' category. Please choose a matching category.');
        }
        $check = 0;
        $elec = DB::table('election_candidates')->where('candidate', $id)->select('id')->value('id');
        if ($elec > 0) {
            return $this->failure($req, 'This candidate is already registered for ' . DB::table('election_candidates')->where('candidate', $id)->value('position') . '. Remove the existing record or use another candidate.');
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
                    return $this->failure($req, 'The candidate has no profile photo on record. Add a photo before registering this candidate.');
                }
                $datas['state'] = $row->state_origin;
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender;
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return $this->failure($req, 'No active student record was found for this ID number.');
            }

            $active = DB::table('users')->where('id', $user_id)->select('status')->value('status');
            if ($active == 0) {
                return $this->failure($req, 'This student account is inactive and cannot be registered as a candidate.');
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
                        return $this->failure($req, 'This candidate has no hostel bed assignment, so they cannot contest the hostel representative election.');
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
                return $this->failure($req, 'This office is for level ' . $datas['position'] . ', but the student is in level ' . $datas['level'] . '.');
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return $this->failure($req, 'This office is restricted to ' . $elec_gender . ' candidates.');
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
                    return $this->failure($req, 'The staff member has no profile photo on record. Add a photo before registering this candidate.');
                }
                $datas['state'] = $row->state;
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender;
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return $this->failure($req, 'No staff record was found for this ID number.');
            }

            $records = DB::table('election_positions')->where(['id' => $pos])->select('position', 'category', 'order', 'gender')->get();
            foreach ($records as $row) {
                $datas['position'] = $row->position;
                $datas['order'] = $row->order;
                $elec_gender = $row->gender;
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return $this->failure($req, 'This office is restricted to ' . $elec_gender . ' candidates.');
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
                    return $this->failure($req, 'The alumni member has no profile photo on record. Add a photo before registering this candidate.');
                }
                $datas['state'] = 'alumni';
                $datas['acc_type'] = $accType;
                $check = 1;
                $gender = $row->gender ?? 'MALE';
                $user_id = $row->user_id;
            }
            if ($check == 0) {
                return $this->failure($req, 'No alumni record was found for this ID number.');
            }

            $records = DB::table('election_positions')->where(['id' => $pos])->select('position', 'category', 'order', 'gender')->get();
            foreach ($records as $row) {
                $datas['position'] = $row->position;
                $datas['order'] = $row->order;
                $elec_gender = $row->gender;
            }
            if ($elec_gender != 'Both' && strtolower($elec_gender) != strtolower($gender)) {
                return $this->failure($req, 'This office is restricted to ' . $elec_gender . ' candidates.');
            }
        } else {
            return $this->failure($req, 'No student, staff, or alumni record was found for this ID number.');
        }

        unset($datas['_token']);
        // $datas = array_map('strtoupper', $datas);
        $createdId = DB::table($this->table)->insertGetId($datas);
        if ($req->expectsJson()) {
            $entity = $category === 'Faculty Rep' ? ($datas['faculty'] ?? null) : ($category === 'Hostel Rep' ? ($datas['hostel'] ?? null) : null);
            return response()->json(['ok' => true, 'id' => $createdId, 'entity' => $entity, 'message' => 'Candidate created.']);
        }
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
        if ($req->expectsJson()) return response()->json(['ok' => true, 'id' => $id, 'message' => 'Candidate updated.']);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();
        if ($req->expectsJson()) return response()->json(['ok' => (bool) $id, 'id' => $req->id, 'message' => 'Candidate removed.']);

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    private function failure(Request $req, string $message)
    {
        if ($req->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return redirect()->back()->withInput()->with('error', $message);
    }
}
