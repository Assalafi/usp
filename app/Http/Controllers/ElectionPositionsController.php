<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ElectionPositionsController extends Controller
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
        $data['data'] = DB::table($this->table)->orderBy('order', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $position = trim((string) $req->input('position'));
        $category = trim((string) $req->input('category'));
        $gender = trim((string) $req->input('gender', 'Both'));
        $order = $req->input('order');
        if ($position === '' || $category === '') {
            return $this->failure($req, 'Enter an office name and choose its election category.');
        }
        $duplicate = DB::table($this->table)
            ->whereRaw('LOWER(position) = ?', [mb_strtolower($position)])
            ->whereRaw('LOWER(category) = ?', [mb_strtolower($category)])
            ->exists();
        if ($duplicate) {
            return $this->failure($req, "The office {$position} already exists in the {$category} category. Edit the existing office instead of creating it again.");
        }
        $id = DB::table($this->table)->insertGetId(compact('position', 'category', 'gender', 'order'));
        if ($req->expectsJson()) return response()->json(['ok' => true, 'id' => $id, 'message' => 'Office created.']);
        return redirect()->back()->with('success', 'Record Created!!!');
    }
    
    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = $req->input('id');
        $position = trim((string) $req->input('position'));
        $category = trim((string) $req->input('category'));
        $gender = trim((string) $req->input('gender', 'Both'));
        $order = $req->input('order');
        if (!$id || $position === '' || $category === '') {
            return $this->failure($req, 'Enter an office name and choose its election category.');
        }
        $duplicate = DB::table($this->table)
            ->whereRaw('LOWER(position) = ?', [mb_strtolower($position)])
            ->whereRaw('LOWER(category) = ?', [mb_strtolower($category)])
            ->where('id', '<>', $id)
            ->exists();
        if ($duplicate) {
            return $this->failure($req, "The office {$position} already exists in the {$category} category. Edit the existing office instead of creating it again.");
        }
        DB::table($this->table)->where('id',$id)->update(compact('position', 'category', 'gender', 'order'));
        if ($req->expectsJson()) return response()->json(['ok' => true, 'id' => $id, 'message' => 'Office updated.']);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }
    
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();
        if ($req->expectsJson()) return response()->json(['ok' => (bool) $id, 'id' => $req->id, 'message' => 'Office removed.']);

        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    private function failure(Request $req, string $message)
    {
        if ($req->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return redirect()->back()->withInput()->with('error', $message);
    }
}
