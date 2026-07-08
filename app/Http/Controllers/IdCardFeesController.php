<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;

class IdCardFeesController extends Controller
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
            $start = $req -> start;
            $end = $req -> end;
            $fac = $req -> faculty;
            if($fac == 'none'){
                unset($data['faculty']);
            }
            unset($data['_token']);
            unset($data['start']);
            unset($data['end']);
            if($data['faculty'] == 'all'){
                unset($data['faculty']);
            }
            if($data['department'] == 'all'){
                unset($data['department']);
            }
            if($data['program'] == 'all'){
                unset($data['program']);
            }
            $filteredData = array_filter($data);
            $query = Invoice::where(['session' => $req->session, 'description' => 'ID CARDS']);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            
            $data['data'] = $query->whereBetween('updated_at', [$start,$end])->get();
        }else{

            $data['data'] = Invoice::where(['description' => 'ID CARDS', 'status' => 'Paid'])->orderBy('updated_at', 'DESC')->limit(500)->get();
        }
        $data['invoice'] = Invoice::where(['description' => 'ID CARDS', 'status' => 'Paid'])->get();
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'id-card-fees';
        $data['title'] = 'ID CARD PAYMENTS';
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
        $datas = $req->all();
        $id = $datas['id'];
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
