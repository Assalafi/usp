<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
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
        $query = DB::table($this->table . ' as p')
            ->leftJoin('faculty as f', 'f.code', '=', 'p.faculty')
            ->leftJoin('department as d', 'd.code', '=', 'p.department')
            ->select([
                'p.*',
                'f.title as faculty_title',
                'd.title as department_title',
            ])
            ->selectSub(
                DB::table('students')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('students.program', 'p.code'),
                'student_count'
            )
            ->selectSub(
                DB::table('program_course_registration')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('program_course_registration.program', 'p.code'),
                'course_count'
            );

        $search = trim((string) request('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('p.code', 'like', "%{$search}%")
                    ->orWhere('p.title', 'like', "%{$search}%")
                    ->orWhere('f.title', 'like', "%{$search}%")
                    ->orWhere('d.title', 'like', "%{$search}%");
            });
        }
        foreach (['faculty', 'department', 'award', 'courses'] as $filter) {
            if (request($filter) !== null && request($filter) !== '' && request($filter) !== 'all') {
                $query->where('p.' . $filter, request($filter));
            }
        }
        if (request('duration') !== null && request('duration') !== '' && request('duration') !== 'all') {
            request('duration') === 'missing'
                ? $query->where(function ($builder) {
                    $builder->whereNull('p.duration')->orWhere('p.duration', '<=', 0);
                })
                : $query->where('p.duration', request('duration'));
        }

        $data['data'] = $query->orderBy('p.no', 'ASC')->orderBy('p.title')->paginate(50)->withQueryString();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['departments'] = DB::table('department')->where(['status' => '1'])->select('code', 'title', 'faculty')->orderBy('title', 'ASC')->get();
        $data['awards'] = DB::table($this->table)->whereNotNull('award')->where('award', '<>', '')->distinct()->orderBy('award')->pluck('award');
        $data['durationOptions'] = DB::table($this->table)->whereNotNull('duration')->where('duration', '>', 0)->distinct()->orderBy('duration')->pluck('duration');
        $data['summary'] = [
            'total' => DB::table($this->table)->count(),
            'active' => DB::table($this->table)->where('courses', '1')->count(),
            'inactive' => DB::table($this->table)->where(function ($builder) {
                $builder->where('courses', '!=', '1')->orWhereNull('courses');
            })->count(),
            'missing_duration' => DB::table($this->table)->where(function ($builder) {
                $builder->whereNull('duration')->orWhere('duration', '<=', 0);
            })->count(),
            'students' => DB::table('students')->where('status', 1)->whereNotNull('program')->count(),
        ];
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function syncDuration(Request $req)
    {
        $validated = $req->validate([
            'id' => ['required'],
            'code' => ['required', 'string', 'max:50'],
            'duration' => ['required', 'integer', 'min:1', 'max:20'],
            'last_id' => ['nullable', 'integer', 'min:0'],
            'processed' => ['nullable', 'integer', 'min:0'],
            'batch' => ['nullable', 'integer', 'min:100', 'max:1000'],
        ]);

        $program = DB::table('program')->where('id', $validated['id'])->where('code', $validated['code'])->first();
        abort_unless($program, 404);

        $batchSize = (int) ($validated['batch'] ?? 500);
        $lastId = (int) ($validated['last_id'] ?? 0);
        $processed = (int) ($validated['processed'] ?? 0);
        $total = DB::table('students')->where('program', $validated['code'])->count();

        DB::table('program')->where('id', $program->id)->update([
            'duration' => $validated['duration'],
            'updated_at' => now(),
        ]);

        $studentIds = DB::table('students')
            ->where('program', $validated['code'])
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->limit($batchSize)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return response()->json([
                'ok' => true,
                'done' => true,
                'total' => $total,
                'processed' => min($processed, $total),
                'last_id' => $lastId,
            ]);
        }

        DB::table('students')->whereIn('id', $studentIds)->update([
            'duration' => $validated['duration'],
            'updated_at' => now(),
        ]);

        $count = $studentIds->count();
        $processed += $count;
        $nextLastId = (int) $studentIds->last();

        return response()->json([
            'ok' => true,
            'done' => $processed >= $total || $count < $batchSize,
            'total' => $total,
            'processed' => min($processed, $total),
            'last_id' => $nextLastId,
        ]);
    }

    public function create(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $ward = $datas['award'];
        $award_title = $datas['award_title'];
        $code = $datas['code'];
        $check = DB::table($this->table)->where(['code' => $code])->select('id')->value('id');
        if($check > 0){
            return redirect()->back()->with('error', 'Record Already Exist!!!');
        }
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);

        $datas['award'] = $ward;
        $datas['award_title'] = $award_title;

        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        $ward = $datas['award'];
        $award_title = $datas['award_title'];
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
        $datas['award'] = $ward;
        $datas['award_title'] = $award_title;
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function departmentAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if(session('accType') == 'Admin' || session('appointment') == 'HOD' || session('appointment') == 'DEAN' || session('appointment') == 'VC' || session('unit') == 'COURSE SYSTEM'){
            if(session('appointment') == 'HOD'){
                $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'code' => session('department')])->orderBy('title', 'asc')->get();

            }else{
                $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();
            }

        }else{
            $datas = DB::table('rolls')->where(['link' => session('links'), 'username' => session('username')])->select('page', 'action', 'department')->get();
            foreach ($datas as $roll) {
                if($roll -> department != 'all' && $roll -> department != '' && $roll -> department != null){
                    $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'code' => $roll -> department, 'status' => '1'])->orderBy('title', 'asc')->get();
                }else{
                    $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();
                }
            }

        }

        $add = '<option value="all">Select Department</option>';
        foreach ($data as $roww) {
            $add .= '<option value="'.$roww -> code.'">'.$roww -> code.': '.$roww -> title.'</option>';
        }

        return $add;
    }

    public function programAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        if(session('accType') == 'Admin' || session('appointment') == 'HOD' || session('appointment') == 'DEAN' || session('appointment') == 'VC' || session('unit') == 'COURSE SYSTEM'){
            $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();
        }else{
            $datas = DB::table('rolls')->where(['link' => session('links'), 'username' => session('username')])->select('page', 'action', 'program')->get();
            foreach ($datas as $roll) {
                if($roll -> program != 'all' && $roll -> program != '' && $roll -> program != null){
                    $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'code' => $roll -> program, 'status' => '1'])->orderBy('title', 'asc')->get();
                }else{
                    $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();
                }
            }

        }

        $add = '<option value="all">Select Program</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> code.': '.$row -> title.'</option>';
        }

        return $add;
    }

    public function departmentAjaxPublic(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $data = DB::table('department')->select('code','title')->where(['faculty' => $req -> faculty, 'status' => '1'])->orderBy('title', 'asc')->get();

        $add = '<option value="">Select Department</option>';
        foreach ($data as $roww) {
            $add .= '<option value="'.$roww -> code.'">'.$roww -> title.'</option>';
        }

        return $add;
    }

    public function programAjaxPublic(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $data = DB::table('program')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'status' => '1'])->orderBy('title', 'asc')->get();

        $add = '<option value="">Select Program</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> title.'</option>';
        }

        return $add;
    }


    public function courseAjax(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}

        $data = DB::table('course')->select('code','title')->where(['faculty' => $req -> faculty, 'department' => $req -> dept, 'program' => $req -> program, 'status' => '1'])->orderBy('code', 'asc')->get();

        $add = '<option value="all">Select Course</option>';
        foreach ($data as $row) {
            $add .= '<option value="'.$row -> code.'">'.$row -> code.': '.$row -> title.'</option>';
        }

        return $add;
    }
    public function allocationPrograms(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}

        $data = DB::table('program_course_registration')->select('program')->where(['code' => $req -> course])->orderBy('code', 'asc')->get();

        $programs = '';
        foreach ($data as $row) {
            $title = DB::table('program')->select('title')->where(['code' => $row -> program])->value('title');
            $programs .= '<br><input type="checkbox" name="programs[]" checked id="programs'.$row -> program.'" value="'.$row -> program.'"> <label for="programs'.$row -> program.'">'.$row -> program.': '.$title.'</label>';
        }

        return $programs;
    }
}
