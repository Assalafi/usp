<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Models\CourseStructure;

class ProgramCourseRegistrationController extends Controller
{
    //
    //

    private $page;
    private $table;
    private $title;
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
            unset($data['faculty']);
            unset($data['department']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }

            $data['data'] = $query->orderBy('code', 'ASC')->get();

        }else{

            if(session('appointment') == 'HOD' || session('appointment') == 'DEAN'){
                $data['data'] = DB::table($this->table)->where(['status' => '3'])->orderBy('id', 'DESC')->orderBy('code', 'ASC')->limit(100)->get();
            }else{
                $data['data'] = DB::table($this->table)->where(['status' => '3'])->orderBy('id', 'DESC')->orderBy('code', 'ASC')->limit(100)->get();
            }
        }
        if(session('appointment') == 'HOD' || session('appointment') == 'DEAN'){
            $data['faculty'] = DB::table('faculty')->where(['code' => session('faculty')])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }else{
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }

        $data['grading'] = DB::table('grading_system')->select('name')->groupBy('name')->orderBy('name', 'ASC')->get();
        $data['semester'] = DB::table('semester')->select('semester')->orderBy('semester', 'ASC')->get();
        $data['courseStructure'] = CourseStructure::all();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $program = $datas['program'];
        $code = $datas['code'];
        $form = $datas['form'];
        $structure_id = $datas['structure_id'];
        $coursej = explode(",", $code);
        $countC = count($coursej);
        $flag = 0;
        for($j = 0; $j < $countC; $j++){
            $course = strtoupper(str_replace(' ', '', $coursej[$j]));
            $data = DB::table('course')->where(['code' => $course])->get();
            foreach($data as $row){
                $records['program'] = $program;
                $records['code'] = $row -> code;
                $records['unit'] = $row -> unit;
                $records['semester'] = $row -> semester;
                $records['level'] = $row -> level;
                $records['type'] = $row -> type;
                $records['form'] = $form;
                $records['structure_id'] = $structure_id;
                try {
                    DB::table($this->table)->insert($records);
                } catch (QueryException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        DB::table($this->table)->where(['program' => $program, 'code' => $row -> code, 'structure_id' => $structure_id])->update($records);
                    } else {
                        return redirect()->back()->with('error', 'Something went Wrong...');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Something went Wrong');
                } finally {

                }
                $flag++;
            }
        }
        if($flag == 0){
            return redirect()->back()->with('error', 'Something went wrong check the course code, make sure is registered on the system.');
        }else{
            return redirect()->back()->with('success', 'Record Created!!!');
        }
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];

        if($datas['elective'] == 0 && $datas['type'] == 'ELECTIVE'){
            return redirect()->back()->with('error', 'You Must Select Elective Number');
        }
        if($datas['type'] == 'CORE'){
            $datas['elective'] = 0;
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
