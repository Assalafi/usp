<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class RollsController extends Controller
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

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $username = trim((string) $req->input('username', ''));
        $facultyCode = trim((string) $req->input('faculty', ''));
        $pageFilter = trim((string) $req->input('page', ''));
        $level = trim((string) $req->input('level', ''));
        $query = DB::table($this->table)
            ->when($username !== '', fn ($q) => $q->where('username', $username))
            ->when($facultyCode !== '', fn ($q) => $q->where('faculty', $facultyCode))
            ->when($pageFilter !== '', fn ($q) => $q->where('page', 'like', '%' . $pageFilter . '%'))
            ->when($level !== '', fn ($q) => $q->where('level', $level));
        $data['data'] = $query->orderBy('username')->orderBy('main_order')->orderBy('sub_order')->paginate(50)->withQueryString();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['sp'] = DB::table('rolls')->select('username')->distinct()->orderBy('username', 'ASC')->get();
        $data['pageOptions'] = DB::table('pages')->orderBy('main_order')->orderBy('sub_order')->get();
        $data['availableActions'] = $this->availableActions();
        $data['rollFilters'] = compact('username', 'facultyCode', 'pageFilter', 'level');
        $data['rollStats'] = [
            'assignments' => DB::table('rolls')->count(),
            'users' => DB::table('rolls')->distinct()->count('username'),
            'pages' => DB::table('rolls')->distinct()->count('page'),
        ];
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    private function availableActions()
    {
        $actions = collect(['view', 'create', 'update', 'delete', 'upload', 'approve', 'export']);
        if (\Illuminate\Support\Facades\Schema::hasTable('navigation_actions')) {
            $actions = $actions->merge(DB::table('navigation_actions')->orderBy('label')->pluck('name'));
        }
        foreach (DB::table('rolls')->select('action')->get() as $row) {
            foreach (array_filter(array_map('trim', explode(',', (string) $row->action))) as $action) {
                $actions->push(strtolower($action));
            }
        }
        return $actions->filter()->unique()->values();
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}


        try {
            DB::beginTransaction();

            $username = $req->input('username');
            $faculty = $req->input('faculty');
            $department = $req->input('department');
            $program = $req->input('program');
            $level = $req->input('level');
            $ids = $req->input('ids', []);

            foreach ($ids as $key => $id) {
                $action = 'action'.$id;
                $action = $req->input($action);
                //echo $id.' '.$action;
                //die;
                $row = DB::table('pages')->where('id', $id)->select('main','main_order', 'page', 'sub_order', 'link')->first();
                if (!$row) {
                    continue;
                }
                // echo $row -> page;
                // die;
                DB::table('rolls')->insert([
                    'username' =>  $username,
                    'faculty' =>  $faculty,
                    'department' =>  $department,
                    'program' =>  $program,
                    'level' =>  $level,
                    'main' =>  $row -> main,
                    'main_order' => $row -> main_order,
                    'page' => $row -> page,
                    'link' => $row -> link,
                    'sub_order' => $row -> sub_order,
                    'action' => $action,
                ]);

            }

            DB::commit();
            return redirect()->back()->with('success', 'Record Created!!!');
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e;
            die;
            return redirect()->back()->with('error', 'Error storing data. '.$e);
        }

    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
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
