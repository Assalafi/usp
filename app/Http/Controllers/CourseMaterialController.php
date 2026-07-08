<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseMaterialController extends Controller
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
        // insertGetId
        if (!session()->has('log')) {
            return redirect('/');
        }
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            // unset($data['faculty']);
            // unset($data['department']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            $queryC = DB::table('course');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
                $queryC->where($key, $value);
            }
            $data['data'] = $query->orderBy('course', 'ASC')->get();
            $data['courses'] = $queryC->orderBy('code', 'ASC')->get();

        } else {
            if (session('accType') == 'Student') {
                $get_course = DB::table('student_course_registration')->where('username', session('id_number'))->pluck('code');
                $data['data'] = DB::table($this->table)->whereIn('course', $get_course)->orderBy('id', 'DESC')->orderBy('course', 'ASC')->limit(100)->get();
            } else {
                $data['data'] = DB::table($this->table)->where(['status' => '3'])->orderBy('id', 'DESC')->orderBy('course', 'ASC')->limit(100)->get();
                $data['courses'] = DB::table('course')->where(['status' => '3'])->orderBy('code', 'ASC')->get();
            }

        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $formFields = $req->validate([
            'pdf' => 'required|mimes:pdf',
        ]);
        $datas = $req->all();
        unset($datas['_token']);
        $course = $datas['course'];
        $record = DB::table('course')->where('code', $course)->get();
        foreach ($record as $row) {
            $datas['faculty'] = $row->faculty;
            $datas['department'] = $row->department;
            $datas['program'] = $row->program;
            $datas['level'] = $row->level;
            $datas['semester'] = $row->semester;
            $datas['title'] = $row->title;
        }
        $datas['pdf'] = $course . '.pdf';
        $datas = array_map('strtoupper', $datas);

        if ($req->file('pdf')->storeAs('material', $datas['pdf'], 'public')) {
            DB::table('course_material')->updateOrInsert(['course' => $datas['course']], $datas);
            return redirect()->back()->with('success', 'Done!!!');
        } else {
            return redirect()->back()->with('success', 'Something Went Wrong!!!');
        }
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
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
