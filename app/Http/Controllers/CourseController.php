<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        if (!session()->has('log')) {
            return redirect('/');
        }

        $filters = [
            'search' => trim((string) $req->query('search', '')),
            'faculty' => trim((string) $req->query('faculty', '')),
            'department' => trim((string) $req->query('department', '')),
            'program' => trim((string) $req->query('program', '')),
            'type' => trim((string) $req->query('type', '')),
            'class' => trim((string) $req->query('class', '')),
            'unit' => trim((string) $req->query('unit', '')),
            'level' => trim((string) $req->query('level', '')),
            'semester' => trim((string) $req->query('semester', '')),
            'status' => trim((string) $req->query('status', '1')),
        ];
        $filters = array_map(static fn ($value) => $value === 'all' ? '' : $value, $filters);

        $isRestrictedFaculty = in_array(session('appointment'), ['HOD', 'DEAN'], true);
        if ($isRestrictedFaculty && session('faculty')) {
            $filters['faculty'] = (string) session('faculty');
        }

        $perPage = (int) $req->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

        $facultyLookup = DB::table('faculty')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $departmentLookup = DB::table('department')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $programLookup = DB::table('program')->select('code', DB::raw('MAX(title) as title'))->groupBy('code');
        $query = DB::table('course as c')
            ->leftJoinSub($programLookup, 'p', 'p.code', '=', 'c.program')
            ->leftJoinSub($departmentLookup, 'd', 'd.code', '=', 'c.department')
            ->leftJoinSub($facultyLookup, 'f', 'f.code', '=', 'c.faculty')
            ->select([
                'c.id', 'c.code', 'c.title', 'c.type', 'c.class', 'c.unit',
                'c.level', 'c.semester', 'c.faculty', 'c.department', 'c.program',
                'c.status', 'c.user', 'p.title as program_title',
                'd.title as department_title', 'f.title as faculty_title',
            ]);

        foreach (['faculty', 'department', 'program', 'type', 'class', 'unit', 'level', 'semester', 'status'] as $column) {
            if ($filters[$column] !== '') {
                $query->where('c.' . $column, $filters[$column]);
            }
        }
        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($scopedQuery) use ($search): void {
                $scopedQuery->where('c.code', 'like', $search)
                    ->orWhere('c.title', 'like', $search);
            });
        }

        $data['data'] = $query
            ->orderBy('c.code')
            ->paginate($perPage)
            ->withQueryString();

        if ($isRestrictedFaculty) {
            $data['faculty'] = DB::table('faculty')
                ->where('code', session('faculty'))
                ->select('code', 'title')
                ->orderBy('title')
                ->get();
        } else {
            $data['faculty'] = Cache::remember('course.filter.faculties', 600, static fn () => DB::table('faculty')
                ->where('status', '1')
                ->select('code', 'title')
                ->orderBy('title')
                ->get());
        }
        $data['filterDepartments'] = $filters['faculty'] !== ''
            ? DB::table('department')->where('faculty', $filters['faculty'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['filterPrograms'] = $filters['department'] !== ''
            ? DB::table('program')->where('department', $filters['department'])->select('code', 'title')->orderBy('title')->get()
            : collect();
        $data['courseLevels'] = Cache::remember('course.filter.levels', 600, static fn () => DB::table('course')
            ->whereNotNull('level')->where('level', '!=', '')
            ->select('level')->distinct()
            ->orderByRaw("CASE WHEN level REGEXP '^[0-9]+$' THEN CAST(level AS UNSIGNED) ELSE 999999 END")
            ->orderBy('level')->pluck('level'));
        $data['courseUnits'] = Cache::remember('course.filter.units', 600, static fn () => DB::table('course')
            ->select('unit')->distinct()->orderBy('unit')->pluck('unit'));
        $data['courseSemesters'] = Cache::remember('course.filter.semesters', 600, static fn () => DB::table('course')
            ->whereNotNull('semester')->where('semester', '!=', '')
            ->select('semester')->distinct()->orderBy('semester')->pluck('semester'));
        $data['semesters'] = $data['courseSemesters'];
        $data['academicSessions'] = Cache::remember('course.filter.sessions', 600, static fn () => DB::table('session')
            ->select('title')->orderByDesc('title')->get());
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        $data['filters'] = $filters;
        $data['perPage'] = $perPage;
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
