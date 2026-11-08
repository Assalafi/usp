<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\CourseImport;
use App\Models\User;

class GradingSystemController extends Controller
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
        $contents = str_replace("edit ", "", $contents);
        $this->page = $contents;
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $filters = $req->only(['search', 'name', 'from', 'to']);
        $query = DB::table($this->table)->where('status', '1');
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', '%' . $search . '%')
                    ->orWhere('ref', 'like', '%' . $search . '%')
                    ->orWhere('grade', 'like', '%' . $search . '%');
            });
        }
        foreach (['name', 'from', 'to'] as $field) {
            if (($value = trim((string) ($filters[$field] ?? ''))) !== '') {
                $query->where($field, $value);
            }
        }
        $data['data'] = $query->select('ref', 'name', 'from', 'to')
            ->groupBy('ref', 'name', 'from', 'to')
            ->orderBy('name', 'ASC')->orderBy('from', 'ASC')->get();
        $refs = $data['data']->pluck('ref')->filter()->values();
        $data['gradingDetails'] = DB::table($this->table)->whereIn('ref', $refs)
            ->orderBy('min_score', 'ASC')->get()->groupBy('ref');
        $data['filters'] = $filters;
        $data['scaleCount'] = $data['data']->count();
        $data['bandCount'] = $data['gradingDetails']->flatten(1)->count();
        $data['grading'] = DB::table($this->table)->select('name')->groupBy('name')->orderBy('name', 'ASC')->get();
        $data['gradingScales'] = $data['data'];
        $data['gradingNames'] = $data['grading'];
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $validated = $req->validate([
            'name' => 'required|string|max:50',
            'from' => 'required|digits:4|integer|min:1900|max:3100',
            'to' => 'required|string|max:20',
            'min_score' => 'required|array|min:1',
            'min_score.*' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|array|min:1',
            'max_score.*' => 'required|numeric|min:0|max:100',
            'grade' => 'required|array|min:1',
            'grade.*' => 'required|string|max:10',
            'remark' => 'required|array|min:1',
            'remark.*' => 'required|string|max:60',
            'point' => 'required|array|min:1',
            'point.*' => 'required|numeric|min:0|max:10',
        ]);
        if (!preg_match('/^(current|\d{4})$/i', (string) $validated['to'])) {
            return redirect()->back()->withInput()->with('error', 'The ending academic year must be a four-digit year or current.');
        }
        if (strtolower((string) $validated['to']) !== 'current' && (int) $validated['from'] > (int) $validated['to']) {
            return redirect()->back()->withInput()->with('error', 'The starting academic year cannot be later than the ending year.');
        }
        $bandCount = count($validated['min_score']);
        foreach (['max_score', 'grade', 'remark', 'point'] as $field) {
            if (count($validated[$field]) !== $bandCount) {
                return redirect()->back()->withInput()->with('error', 'Please complete every grade band before saving.');
            }
        }
        try {
            $ref = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $validated['name'] . $validated['from'] . $validated['to']));
            if (DB::table($this->table)->where('ref', $ref)->exists()) {
                return redirect()->back()->withInput()->with('error', 'A grading scale with the same name and mark range already exists. Edit the existing scale instead.');
            }
            DB::beginTransaction();
            foreach ($validated['min_score'] as $key => $min) {
                if ((float) $min > (float) $validated['max_score'][$key]) {
                    throw new \RuntimeException('A minimum mark cannot be greater than its maximum mark.');
                }
                DB::table($this->table)->insert([
                    'ref' => $ref,
                    'name' => strtoupper($validated['name']),
                    'from' => $validated['from'],
                    'to' => $validated['to'],
                    'min_score' => $min,
                    'max_score' => $validated['max_score'][$key],
                    'grade' => strtoupper($validated['grade'][$key]),
                    'remark' => strtoupper($validated['remark'][$key]),
                    'point' => $validated['point'][$key],
                    'user' => session('username'),
                    'status' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Record Created!!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            // die;
            return redirect()->back()->with('error', 'Error storing data.');
        }
    }

    public function edit(Request $req)
    {
        if (!session()->has('log')) { return redirect('/'); }

        $validated = $req->validate([
            'ref' => 'required|string|max:120',
            'name' => 'required|string|max:50',
            'from' => 'required|digits:4|integer|min:1900|max:3100',
            'to' => 'required|string|max:20',
            'min_score' => 'required|array|min:1',
            'min_score.*' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|array|min:1',
            'max_score.*' => 'required|numeric|min:0|max:100',
            'grade' => 'required|array|min:1',
            'grade.*' => 'required|string|max:10',
            'remark' => 'required|array|min:1',
            'remark.*' => 'required|string|max:60',
            'point' => 'required|array|min:1',
            'point.*' => 'required|numeric|min:0|max:10',
        ]);

        if (count($validated['min_score']) !== count($validated['max_score']) || count($validated['min_score']) !== count($validated['grade']) || count($validated['min_score']) !== count($validated['remark']) || count($validated['min_score']) !== count($validated['point'])) {
            return redirect()->back()->withInput()->with('error', 'Please complete every grade band before saving.');
        }
        if (!preg_match('/^(current|\d{4})$/i', (string) $validated['to'])) {
            return redirect()->back()->withInput()->with('error', 'The ending academic year must be a four-digit year or current.');
        }
        if (strtolower((string) $validated['to']) !== 'current' && (int) $validated['from'] > (int) $validated['to']) {
            return redirect()->back()->withInput()->with('error', 'The starting academic year cannot be later than the ending year.');
        }

        try {
            DB::transaction(function () use ($validated) {
                $now = now();
                DB::table($this->table)->where('ref', $validated['ref'])->delete();
                foreach ($validated['min_score'] as $index => $min) {
                    if ((float) $min > (float) $validated['max_score'][$index]) {
                        throw new \RuntimeException('A minimum mark cannot be greater than its maximum mark.');
                    }
                    DB::table($this->table)->insert([
                        'ref' => $validated['ref'],
                        'name' => strtoupper($validated['name']),
                        'from' => $validated['from'] ?: null,
                        'to' => $validated['to'],
                        'min_score' => $min,
                        'max_score' => $validated['max_score'][$index],
                        'grade' => strtoupper($validated['grade'][$index]),
                        'remark' => strtoupper($validated['remark'][$index]),
                        'point' => $validated['point'][$index],
                        'user' => session('username'),
                        'status' => '1',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
            return redirect()->back()->with('success', 'Grading scale updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'The grading scale could not be updated.');
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
        if(isset($req -> program)){
            $program = [$req -> program];
        }else if(isset($req -> department)){
            $program = DB::table('program')->where('department',$req -> department)->pluck('code');
        }else if(isset($req -> faculty)){
            $program = DB::table('program')->where('faculty',$req -> faculty)->pluck('code');
        }
        $datas['grading'] = $req -> name;

        try {
            DB::table('program_course_registration')->whereIn('program',$program)->update($datas);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        } finally {
            return redirect()->back()->with('success', 'Applied!!!');
        }

    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('ref',$req->ref)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
