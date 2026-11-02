<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
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
        $query = DB::table($this->table . ' as f')
            ->select('f.*')
            ->selectSub(DB::table('department')->selectRaw('COUNT(*)')->whereColumn('department.faculty', 'f.code'), 'department_count')
            ->selectSub(DB::table('program')->selectRaw('COUNT(*)')->whereColumn('program.faculty', 'f.code'), 'program_count')
            ->selectSub(DB::table('students')->selectRaw('COUNT(*)')->whereColumn('students.faculty', 'f.code'), 'student_count');
        $search = trim((string) request('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('f.code', 'like', "%{$search}%")
                    ->orWhere('f.title', 'like', "%{$search}%");
            });
        }
        if (request('college') !== null && request('college') !== '' && request('college') !== 'all') {
            $query->where('f.college', request('college'));
        }
        if (request('status') !== null && request('status') !== '' && request('status') !== 'all') {
            $query->where('f.status', request('status'));
        }
        $data['data'] = $query->orderBy('f.no', 'ASC')->orderBy('f.title')->paginate(50)->withQueryString();
        $data['summary'] = [
            'total' => DB::table($this->table)->count(),
            'active' => DB::table($this->table)->where('status', '1')->count(),
            'departments' => DB::table('department')->count(),
            'programs' => DB::table('program')->count(),
        ];
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $no = $datas['no'];
        // $check = DB::table($this->table)->where(['no' => $no])->select('id')->value('id');
        // if($check > 0){
        //     return redirect()->back()->with('error', 'Record Already Exist!!!');
        // }
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
        $no = $datas['no'];
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
