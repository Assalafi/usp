<?php

namespace App\Http\Controllers;

use App\Imports\DegreeImport;
use App\Imports\StaffImport;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
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

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->get();
        } else {
            $data['data'] = DB::table($this->table)->whereIn('designation', ['UNIVERSITY LIBRARIAN'])->orWhere('designation', 'LIKE', '%PROFESSOR%')->orWhere('designation', 'LIKE', '%LECTURER%')->get();
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['designation'] = DB::table($this->table)->where('current_rank', '!=', '')->select('current_rank')->distinct()->orderBy('current_rank', 'ASC')->get('current_rank');
        $data['unit'] = DB::table($this->table)->where('unit', '!=', '')->select('unit')->distinct()->orderBy('unit', 'ASC')->get('unit');
        $data['grade'] = DB::table($this->table)->where('grade', '!=', '')->select('grade')->distinct()->orderBy('grade', 'ASC')->get('grade');
        $data['step'] = DB::table($this->table)->where('step', '!=', '')->select('step')->distinct()->orderBy('step', 'ASC')->get('step');
        $data['fees_type'] = DB::table('fees_type')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
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
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        $id = $datas['username'];
        $name = $datas['name'];
        User::updateOrCreate(
            ['username' => $id],
            [
                'password' => Hash::make($id),
                'accType' => 'Staff',
                'name' => strtoupper($name),
                'status' => '1'
            ]
        );
        $id = DB::table('users')->where('username', $id)->value('id');
        $datas['user_id'] = $id;
        // DB::table($this->table)->insert();
        Staff::updateOrCreate(['user_id' => $id], $datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        $user_id = DB::table('staff')->where('id', $id)->value('user_id');
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('user_id', $user_id)->update($datas);

        if ($req->file('picture')) {
            $dot = $req->file('picture')->getClientOriginalExtension();
            $req->file('picture')->storeAs('picture', $user_id . '.' . $dot, 'public');

            $applicant = Staff::where(['user_id' => $user_id])->update([
                'picture' => $user_id . '.' . $dot
            ]);
        }

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('users')->where('id', $req->id)->delete();
        $id = DB::table($this->table)->where('user_id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function upload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new StaffImport($faculty, $department, $program);
            // print_r($file);
            // die;
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function uploadDegree(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $degree = $request->degree;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new DegreeImport($degree);
            // print_r($file);
            // die;
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }
}
