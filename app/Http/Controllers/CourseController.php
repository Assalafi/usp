<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\CourseImport;
use App\Models\User;

class CourseController extends Controller
{
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
            $data['data'] = $query->orderBy('code', 'ASC')->get();
        }else{

            $data['data'] = DB::table($this->table)->where(['status' => '3'])->orderBy('id', 'DESC')->orderBy('code', 'ASC')->limit(100)->get();
        }
        if(session('appointment') == 'HOD' || session('appointment') == 'DEAN'){
            $data['faculty'] = DB::table('faculty')->where(['code' => session('faculty')])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }else{
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        }
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        $data['get_courses'] = DB::table('course')->orderBy('code', 'ASC')->get();
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas['code'] = strtoupper(str_replace(' ', '', $datas['code']));
        $datas = array_map('strtoupper', $datas);
        try {
            DB::table($this->table)->insert($datas);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Record Already Exist');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Record Already Exist');
        } finally {
            return redirect()->back()->with('success', 'Record Created!!!');
        }
    }

    public function upload(Request $request)
    {
    if(!session()->has('log')){return redirect('/');}
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the uploaded file using Maatwebsite/Excel
            $import = new CourseImport();
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        $datas['code'] = strtoupper(str_replace(' ', '', $datas['code']));
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);

        try {
            DB::table($this->table)->where('id',$id)->update($datas);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Record Already Exist');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Record Already Exist');
        } finally {
            return redirect()->back()->with('success', 'Record Updated!!!');
        }


    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
