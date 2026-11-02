<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
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
        // Aggregate related records once and join the results. Correlated counts
        // in the paginated row query become very expensive as the directory grows.
        $programCounts = DB::table('program')
            ->select('department')
            ->selectRaw('COUNT(*) as program_count')
            ->groupBy('department');
        $studentCounts = DB::table('students')
            ->select('department')
            ->selectRaw('COUNT(*) as student_count')
            ->groupBy('department');

        $query = DB::table($this->table . ' as d')
            ->leftJoin('faculty as f', 'f.code', '=', 'd.faculty')
            ->leftJoinSub($programCounts, 'pc', function ($join) {
                $join->on('pc.department', '=', 'd.code');
            })
            ->leftJoinSub($studentCounts, 'sc', function ($join) {
                $join->on('sc.department', '=', 'd.code');
            })
            ->select('d.*', 'f.title as faculty_title')
            ->selectRaw('COALESCE(pc.program_count, 0) as program_count')
            ->selectRaw('COALESCE(sc.student_count, 0) as student_count');
        $search = trim((string) request('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('d.code', 'like', "%{$search}%")
                    ->orWhere('d.title', 'like', "%{$search}%")
                    ->orWhere('f.title', 'like', "%{$search}%");
            });
        }
        if (request('faculty') !== null && request('faculty') !== '' && request('faculty') !== 'all') {
            $query->where('d.faculty', request('faculty'));
        }
        if (request('status') !== null && request('status') !== '' && request('status') !== 'all') {
            $query->where('d.status', request('status'));
        }
        $data['data'] = $query->orderBy('d.no', 'ASC')->orderBy('d.title')->paginate(50)->withQueryString();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['summary'] = [
            'total' => DB::table($this->table)->count(),
            'active' => DB::table($this->table)->where('status', '1')->count(),
            'programs' => DB::table('program')->count(),
            'students' => DB::table('students')->where('status', 1)->count(),
        ];
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $code = $datas['code'];
        $check = DB::table($this->table)->where(['code' => $code])->select('id')->value('id');
        if($check > 0){
            return redirect()->back()->with('error', 'Record Already Exist!!!');
        }
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
        $code = $datas['code'];
        $check = DB::table($this->table)->where(['id' => $id])->select('code', 'code')->get();
        foreach ($check as $row) {
            if($code != $row -> code){
                $checks = DB::table($this->table)->where(['code' => $code])->select('id')->value('id');
                if($checks > 0){
                    return redirect()->back()->with('error', 'Record Already Exist!!!');
                }
            }
        }
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
