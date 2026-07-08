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
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->get();
        }else{

            $data['data'] = DB::table($this->table)->where(['username' => '900'])->get();
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['sp'] = DB::table('rolls')->select('username')->distinct()->orderBy('username', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
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
            $ids = $req->input('ids');

            foreach ($ids as $key => $id) {
                $action = 'action'.$id;
                $action = $req->input($action);
                //echo $id.' '.$action;
                //die;
                $data = DB::table('pages')->where('id', $id)->select('main','main_order', 'page', 'sub_order', 'link')->get();
                foreach ($data as $row) {
                    // code...
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
