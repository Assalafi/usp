<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class HostelFeesController extends Controller
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
        $this->table = 'hostel';
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if(!session()->has('log')){return redirect('/');}
            $data['data'] = DB::table($this->table)->groupBy(['hall', 'block', 'category', 'payment_method'])->select('hall', 'block', 'category', 'payment_method')->get();
            $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }
    
    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas['amount'] = $req -> amount;
        $datas['payment_method'] = $req -> payment_method;
        $hall = $req -> hall;
        $block = $req -> block;
        $category = $req -> category;
        //echo $hall.' '.$block.' '.$category.' '.$datas['amount'];
        //die;

        try {
            DB::table($this->table)->where('hall',$hall)->where('block',$block)->where('category',$category)->update($datas);
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
