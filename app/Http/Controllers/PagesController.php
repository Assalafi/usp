<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class PagesController extends Controller
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
        $main = trim((string) $req->input('main', ''));
        $rows = DB::table($this->table)
            ->when($main !== '', fn ($query) => $query->where('main', 'like', '%' . $main . '%'))
            ->orderBy('main_order')->orderBy('sub_order')->orderBy('id')
            ->get();
        $data['data'] = $rows->groupBy('main')->map(function ($items) {
            $first = $items->first();
            return (object) [
                'main' => $first->main,
                'main_order' => $first->main_order,
                'page_count' => $items->count(),
                'action_count' => $items->sum(function ($item) {
                    return count(array_filter(array_map('trim', explode(',', (string) $item->action))));
                }),
            ];
        })->values();
        $data['pageRows'] = $rows;
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['pageStats'] = [
            'groups' => DB::table($this->table)->distinct()->count('main'),
            'pages' => DB::table($this->table)->count(),
            'links' => DB::table($this->table)->whereNotNull('link')->where('link', '!=', '')->count(),
        ];
        $data['pageFilter'] = $main;
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}


        try {
            DB::beginTransaction();

            $pages = $req->input('page', []);
            $link = $req->input('link');
            $sub_order = $req->input('sub_order');
            $main = $req->input('main');
            $main_order = $req->input('main_order');
            $action = $req->input('action');

            foreach ($pages as $key => $page) {
                if (trim((string) $page) === '') {
                    continue;
                }
                DB::table('pages')->insert([
                    'main' => $main,
                    'main_order' => $main_order,
                    'page' => $page,
                    'link' => $link[$key],
                    'sub_order' => $sub_order[$key],
                    'action' => $action[$key],
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
        $ids = $req->input('id', []);
        if (is_array($ids)) {
            foreach ($ids as $key => $id) {
                if (!$id) {
                    continue;
                }
                DB::table($this->table)->where('id', $id)->update([
                    'main' => strtoupper(trim((string) ($req->input('main') ?: $req->input('main.'.$key, '')))),
                    'main_order' => (int) ($req->input('main_order') ?: $req->input('main_order.'.$key, 0)),
                    'page' => strtoupper(trim((string) ($req->input('page')[$key] ?? ''))),
                    'link' => trim((string) ($req->input('link')[$key] ?? '')),
                    'sub_order' => (int) ($req->input('sub_order')[$key] ?? 0),
                    'action' => strtolower(trim((string) ($req->input('action')[$key] ?? ''))),
                ]);
            }
        } else {
            $id = $req->input('id');
            DB::table($this->table)->where('id', $id)->update($req->except(['id', '_token']));
        }

        return redirect()->back()->with('success', 'Record Updated!!!');
    }
    
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
