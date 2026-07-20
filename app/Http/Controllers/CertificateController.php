<?php

namespace App\Http\Controllers;

use App\Imports\AffiliatedSchoolsImport;
use App\Imports\AffiliatedStudentImport;
use App\Imports\GraduatedStudentImport;
use App\Models\GraduatedStudent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CertificateController extends Controller
{
    public function __construct(Request $req)
    {
        $contents = $req->segment(1);
        $contents = str_replace('create ', '', $contents);
        $contents = str_replace('upload ', '', $contents);
        $contents = str_replace('download ', '', $contents);
        $contents = str_replace('update ', '', $contents);
        $contents = str_replace('delete ', '', $contents);
        $this->page = $contents;
        $this->table = 'graduated_students';
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = DB::table('graduated_students');

        if ($req->has('_token')) {
            $filters = $req->only(['faculty', 'department', 'program', 'graduation_date', 'username']);
            foreach (array_filter($filters) as $key => $value) {
                $query->where($key, $value);
            }
            if ($req->filled('school_id') || $req->input('school_id') === '0') {
                $query->where('school_id', $req->school_id);
            }
        }

        $data['data'] = $query->orderBy('fullname', 'ASC')->paginate(100);
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['departments'] = DB::table('department')->where(['status' => '1'])->select('code', 'title', 'faculty')->orderBy('title', 'ASC')->get();
        $data['graduation_years'] = DB::table('graduated_students')->select('graduation_date')->distinct()->orderBy('graduation_date', 'DESC')->pluck('graduation_date');
        $data['programs'] = DB::table('program')->where(['status' => '1'])->select('code', 'title', 'department')->orderBy('title', 'ASC')->get();
        $data['affiliated_schools'] = DB::table('affiliated_schools')->where('status', '1')->orderBy('name', 'ASC')->get();
        $data['page'] = 'certificate';
        $data['title'] = 'CERTIFICATE';
        return view('main', $data);
    }

    public function upload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'graduation_date' => 'required|string',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $graduationDate = $request->graduation_date;

            $import = new GraduatedStudentImport($graduationDate);
            Excel::import($import, $file);

            $imported = $import->getImported();
            $skipped = $import->getSkipped();
            $errors = $import->getErrors();

            $message = "Imported: {$imported}, Skipped: {$skipped}";
            if (!empty($errors)) {
                $message .= '. Errors: ' . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= ' ... and ' . (count($errors) - 5) . ' more.';
                }
            }

            return redirect()->back()->with($skipped > 0 ? 'warning' : 'success', $message);
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return redirect()->back();
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $id = $req->input('id');
        $data = $req->only(['class_of_degree', 'graduation_date']);
        $data = array_filter($data);
        $data = array_map('strtoupper', $data);
        $data['updated_at'] = now();

        DB::table('graduated_students')->where('id', $id)->update($data);

        return redirect()->back()->with('success', 'Record Updated!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        DB::table('graduated_students')->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Deleted!');
    }

    public function downloadTemplate()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Create a simple Excel template
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="certificate_template.xlsx"',
        ];

        // Create a simple CSV that Excel can open
        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, ['SN', 'ID No', 'Class of Degree']);

            // Sample data
            fputcsv($file, [1, '15/07/02/054', 'First Class']);
            fputcsv($file, [2, '15/07/02/055', 'Second Class Upper']);
            fputcsv($file, [3, '15/07/02/056', 'Second Class Lower']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function setCertType(Request $request)
    {
        $type = $request->input('cert_type', '1');
        $bg = $request->input('cert_bg', '1');
        session(['cert_type' => $type, 'cert_bg' => $bg]);
        return response()->json(['success' => true, 'cert_type' => $type, 'cert_bg' => $bg]);
    }

    public function generatePdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $id = $request->input('id');
        $record = GraduatedStudent::find($id);

        if (!$record) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $programData = DB::table('program')->where('code', $record->program)->first();
        $facultyData = DB::table('faculty')->where('code', $record->faculty)->first();
        $departmentData = DB::table('department')->where('code', $record->department)->first();

        // Format name: First Middle LAST (surname uppercase)
        $nameParts = explode(' ', trim($record->fullname));
        $nameCount = count($nameParts);
        if ($nameCount >= 2) {
            $lastName = strtoupper(array_pop($nameParts));
            $firstName = ucwords(strtolower(implode(' ', $nameParts)));
            $formattedName = $firstName . ' ' . $lastName;
        } else {
            $formattedName = strtoupper($record->fullname);
        }
        $excludedProgramsForDepartments = ['LIS', 'FOW', 'FSR', 'PHARM', 'PHARM. D', 'SRL', 'PBL', 'PVL']; // Add program codes to exclude here
        if(in_array($programData->code, $excludedProgramsForDepartments)){
            $department = '--------------';
        }else{
            $department = $programData->title;
        }
        // for class of degree
        $excludedProgramsForClassOfDegree = ['PHARM', 'PHARM. D', 'PST']; // Add program codes to exclude here
        if(in_array($programData->code, $excludedProgramsForClassOfDegree)){
            $classOfDegree = '--------------';
        }else{
            $classOfDegree = $record->class_of_degree;
        }

        //for degree only
        $excludedProgramsForDegree = ['VM', 'PHARM', 'PHARM. D', 'PST', 'MBBS', 'DBS']; // Add program codes to exclude here
        if(in_array($programData->code, $excludedProgramsForDegree)){
            $degree = $programData->award_title ?? ' ';
        }else{
            $degree = ($programData->award_title ?? ' ') . ' (Honours) ';
        }


        $certData = [
            'student_name' => $formattedName,
            'username' => $record->username,
            // 'degree' => ($programData->award ?? ' ') . ' (Hons) ' . $programData->title,
            'degree' => $degree,
            'degree_title' => $programData->title,
            'class_of_degree' => $classOfDegree,
            'department' => $department,
            'faculty' => $facultyData->title ?? '',
            'graduation_date' => $record->graduation_date,
            'certificate_id' => $record->certificate_id,
        ];

        $certType = session('cert_type', '1');
        $certBg = session('cert_bg', '1');

        // Determine which view to use based on certificate type
        switch ($certType) {
            case '2':
                $view = 'pdf/certificate_type2';
                break;
            case '3':
                $view = 'pdf/certificate_type3';
                break;
            default:
                $view = 'pdf/certificate';
                break;
        }

        return view($view, ['certificates' => [$certData], 'cert_bg' => $certBg]);
    }

    public function generateBatchPdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = GraduatedStudent::query();

        if ($request->filled('graduation_date')) {
            $query->where('graduation_date', $request->graduation_date);
        }
        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }
        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }
        if ($request->filled('school_id') || $request->input('school_id') === '0') {
            $query->where('school_id', $request->school_id);
        }

        $records = $query->orderBy('fullname', 'ASC')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No records found for the selected filters.');
        }

        $certificates = [];
        foreach ($records as $record) {
            $programData = DB::table('program')->where('code', $record->program)->first();
            $facultyData = DB::table('faculty')->where('code', $record->faculty)->first();

            // Format name: First Middle LAST (surname uppercase)
            $nameParts = explode(' ', trim($record->fullname));
            $nameCount = count($nameParts);
            if ($nameCount >= 2) {
                $lastName = strtoupper(array_pop($nameParts));
                $firstName = ucwords(strtolower(implode(' ', $nameParts)));
                $formattedName = $firstName . ' ' . $lastName;
            } else {
                $formattedName = strtoupper($record->fullname);
            }

            // Excluded programs for department
            $excludedProgramsForDepartments = ['LIS', 'FOW', 'FSR', 'PHARM', 'PHARM. D', 'SRL', 'PBL', 'PVL'];
            if(in_array($programData->code, $excludedProgramsForDepartments)){
                $department = '--------------';
            }else{
                $department = $programData->title;
            }

            // Excluded programs for class of degree
            $excludedProgramsForClassOfDegree = ['PHARM', 'PHARM. D', 'PST'];
            if(in_array($programData->code, $excludedProgramsForClassOfDegree)){
                $classOfDegree = '--------------';
            }else{
                $classOfDegree = $record->class_of_degree;
            }

            // Excluded programs for degree
            $excludedProgramsForDegree = ['VM', 'PHARM', 'PHARM. D', 'PST', 'MBBS', 'DBS'];
            if(in_array($programData->code, $excludedProgramsForDegree)){
                $degree = $programData->award_title ?? ' ';
            }else{
                $degree = ($programData->award_title ?? ' ') . ' (Honours) ';
            }

            $certificates[] = [
                'student_name' => $formattedName,
                'username' => $record->username,
                //'degree' => ($programData->award ?? ' ') . ' (Hons) '.$programData->title,
                'degree' => $degree,
                'degree_title' => $programData->title,
                'class_of_degree' => $classOfDegree,
                'department' => $department,
                'faculty' => $facultyData->title ?? '',
                'graduation_date' => $record->graduation_date,
                'certificate_id' => $record->certificate_id,
            ];
        }

        $certType = session('cert_type', '1');
        $certBg = session('cert_bg', '1');

        // Determine which view to use based on certificate type
        switch ($certType) {
            case '2':
                $view = 'pdf/certificate_type2';
                break;
            case '3':
                $view = 'pdf/certificate_type3';
                break;
            default:
                $view = 'pdf/certificate';
                break;
        }

        return view($view, ['certificates' => $certificates, 'cert_bg' => $certBg]);
    }

    // ==================== STATEMENT OF RESULT ====================

    public function sorIndex(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = DB::table('graduated_students');

        if ($req->has('_token')) {
            $filters = $req->only(['faculty', 'department', 'program', 'graduation_date', 'username']);
            foreach (array_filter($filters) as $key => $value) {
                $query->where($key, $value);
            }
        }

        $data['data'] = $query->orderBy('fullname', 'ASC')->paginate(100);
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['graduation_years'] = DB::table('graduated_students')->select('graduation_date')->distinct()->orderBy('graduation_date', 'DESC')->pluck('graduation_date');
        $data['programs'] = DB::table('program')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'statement of result';
        $data['title'] = 'STATEMENT OF RESULT';
        return view('main', $data);
    }

    public function generateSorPdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $id = $request->input('id');
        $record = GraduatedStudent::find($id);

        if (!$record) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $programData = DB::table('program')->where('code', $record->program)->first();
        $facultyData = DB::table('faculty')->where('code', $record->faculty)->first();

        $certData = [
            'student_name' => $record->fullname,
            'username' => $record->username,
            'degree' => ($programData->award ?? ' ') . ' (Hons) ' . $programData->title,
            'class_of_degree' => $record->class_of_degree,
            'department' => $programData->title ?? '',
            'faculty' => $facultyData->title ?? '',
            'graduation_date' => $record->graduation_date,
            'certificate_id' => $record->certificate_id,
        ];

        return view('pdf/statement_of_result', ['certificates' => [$certData]]);
    }

    public function generateSorBatchPdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = GraduatedStudent::query();

        if ($request->filled('graduation_date')) {
            $query->where('graduation_date', $request->graduation_date);
        }
        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }
        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        $records = $query->orderBy('fullname', 'ASC')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No records found for the selected filters.');
        }

        $certificates = [];
        foreach ($records as $record) {
            $programData = DB::table('program')->where('code', $record->program)->first();
            $facultyData = DB::table('faculty')->where('code', $record->faculty)->first();

            $certificates[] = [
                'student_name' => $record->fullname,
                'username' => $record->username,
                'degree' => ($programData->award ?? ' ') . ' (Hons) ' . $programData->title,
                'class_of_degree' => $record->class_of_degree,
                'department' => $programData->title ?? '',
                'faculty' => $facultyData->title ?? '',
                'graduation_date' => $record->graduation_date,
                'certificate_id' => $record->certificate_id,
            ];
        }

        return view('pdf/statement_of_result', ['certificates' => $certificates]);
    }

    public function affiliatedSchoolsIndex(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['data'] = DB::table('affiliated_schools')->orderBy('name', 'ASC')->paginate(100);
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['programs'] = DB::table('program')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'affiliated schools';
        $data['title'] = 'AFFILIATED SCHOOLS';
        return view('main', $data);
    }

    public function affiliatedSchoolsStore(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('affiliated_schools')->insert([
            'name' => strtoupper($request->name),
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Affiliated school created successfully.');
    }

    public function affiliatedSchoolsUpdate(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        DB::table('affiliated_schools')->where('id', $request->id)->update([
            'name' => strtoupper($request->name),
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Affiliated school updated successfully.');
    }

    public function affiliatedSchoolsDelete(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $id = $request->input('id');

        $hasStudents = DB::table('graduated_students')->where('school_id', $id)->exists();
        if ($hasStudents) {
            return redirect()->back()->with('error', 'Cannot delete school: it has records in graduated students.');
        }

        DB::table('affiliated_schools')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Affiliated school deleted successfully.');
    }

    public function uploadAffiliatedSchoolsList(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($request->hasFile('file')) {
            try {
                $file = $request->file('file');

                $import = new AffiliatedSchoolsImport();
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file->getRealPath());
                finfo_close($finfo);

                if (in_array($mime, ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'])) {
                    // Read CSV manually to avoid PhpSpreadsheet XLSX fallback issues
                    $handle = fopen($file->getRealPath(), 'r');
                    $rows = collect();
                    while (($line = fgetcsv($handle)) !== false) {
                        if (count(array_filter($line, function ($v) { return trim($v) !== ''; })) === 0) {
                            continue;
                        }
                        $rows->push($line);
                    }
                    fclose($handle);
                    $import->collection($rows);
                } else {
                    Excel::import($import, $file);
                }

                $imported = $import->getImported();
                $skipped = $import->getSkipped();
                $errors = $import->getErrors();

                $message = "Imported: {$imported}, Skipped: {$skipped}";
                if (!empty($errors)) {
                    $message .= '. Errors: ' . implode('; ', array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $message .= ' ... and ' . (count($errors) - 5) . ' more.';
                    }
                }

                return redirect()->back()->with($skipped > 0 ? 'warning' : 'success', $message);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    public function downloadAffiliatedSchoolsTemplate()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="affiliated_schools_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['SN', 'School Name']);
            fputcsv($file, [1, 'College of Nursing']);
            fputcsv($file, [2, 'School of Health Technology']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function affiliatedSchoolsUpload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'affiliated_school_id' => 'required|integer|exists:affiliated_schools,id',
            'faculty' => 'required|string',
            'department' => 'required|string',
            'program' => 'required|string',
            'graduation_date' => 'required|string',
        ]);

        if ($request->hasFile('file')) {
            try {
                $file = $request->file('file');

                $import = new AffiliatedStudentImport(
                    $request->affiliated_school_id,
                    $request->faculty,
                    $request->department,
                    $request->program,
                    $request->graduation_date
                );

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file->getRealPath());
                finfo_close($finfo);

                if (in_array($mime, ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'])) {
                    $handle = fopen($file->getRealPath(), 'r');
                    $rows = collect();
                    while (($line = fgetcsv($handle)) !== false) {
                        if (count(array_filter($line, function ($v) { return trim($v) !== ''; })) === 0) {
                            continue;
                        }
                        $rows->push($line);
                    }
                    fclose($handle);
                    $import->collection($rows);
                } else {
                    Excel::import($import, $file);
                }

                $imported = $import->getImported();
                $skipped = $import->getSkipped();
                $errors = $import->getErrors();

                $message = "Imported: {$imported}, Skipped: {$skipped}";
                if (!empty($errors)) {
                    $message .= '. Errors: ' . implode('; ', array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $message .= ' ... and ' . (count($errors) - 5) . ' more.';
                    }
                }

                return redirect()->back()->with($skipped > 0 ? 'warning' : 'success', $message);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    public function downloadAffiliatedTemplate()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="affiliated_students_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, ['SN', 'ID No', 'Name', 'Class of Degree']);

            // Sample data
            fputcsv($file, [1, '15/07/02/054', 'Adamu Musa', 'First Class']);
            fputcsv($file, [2, '15/07/02/055', 'Saidu Bello', 'Second Class Upper']);
            fputcsv($file, [3, '15/07/02/056', 'Ibrahim Aliyu', 'Second Class Lower']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
