<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsListController extends Controller
{
    protected $page;
    protected $table;
    protected $title;

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
            $allowedFilters = [
                'faculty', 'department', 'program', 'level', 'session_of_entry',
                'gender', 'marital_status', 'state_origin', 'lga_origin',
                'contact_phone', 'jamb_no', 'username', 'profile_status',
            ];
            $data = array_intersect_key($data, array_flip($allowedFilters));
            $profileStatus = $data['profile_status'] ?? null;
            unset($data['profile_status']);
            $filteredData = array_filter($data, function ($v) {
                return $v !== '' && $v !== 'all' && $v !== null;
            });
            $query = DB::table('students');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $currentSession = DB::table('session')->where('status', '1')->value('title');
            if ($profileStatus === 'submitted') {
                $query->where('profile_status', 'submitted')
                    ->where('profile_submission_session', $currentSession)
                    ->where('profile_level_session', $currentSession);
            } elseif ($profileStatus === 'draft') {
                $query->where(function ($statusQuery) use ($currentSession) {
                    $statusQuery->whereNull('profile_status')
                        ->orWhere('profile_status', '!=', 'submitted')
                        ->orWhereNull('profile_submission_session')
                        ->orWhere('profile_submission_session', '!=', $currentSession)
                        ->orWhereNull('profile_level_session')
                        ->orWhere('profile_level_session', '!=', $currentSession);
                });
            }
            $data['data'] = $query->orderBy('fullname', 'ASC')->paginate(100)->withQueryString();
        }else{

            $data['data'] = DB::table('students')->where(['level_of_entry' => '900'])->orderBy('fullname', 'ASC')->paginate(100)->withQueryString();
        }
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['fees_type'] = DB::table('fees_type')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
            $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
            $data['sessions'] = DB::table('session')->orderBy('title', 'DESC')->get();
            $data['page'] = $this->page;
            $data['title'] = $this->title;
            $data['exportColumns'] = \App\Exports\UsersExport::columns();
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
}
