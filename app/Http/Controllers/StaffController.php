<?php

namespace App\Http\Controllers;

use App\Imports\DegreeImport;
use App\Imports\StaffImport;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                // Map old field names to new field names for filtering
                if ($key === 'unit_id' && $value) {
                    $query->where('unit_id', $value);
                } elseif ($key === 'designation_id' && $value) {
                    $query->where('designation_id', $value);
                } elseif ($key === 'grade_id' && $value) {
                    $query->where('grade_id', $value);
                } elseif ($key === 'step_id' && $value) {
                    $query->where('step_id', $value);
                } elseif ($value) {
                    $query->where($key, $value);
                }
            }
            $data['data'] = $query->paginate(500);
        } else {
            $data['data'] = DB::table($this->table)->select('*')
                ->where('current_rank', 'LIKE', '%LIBRARIAN%')
                ->orWhere('current_rank', 'LIKE', '%PROFESSOR%')
                ->orWhere('current_rank', 'LIKE', '%LECTURER%')
                ->paginate(500);
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['designation'] = DB::table('designations')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
        $data['unit'] = DB::table('units')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
        $data['grade'] = DB::table('grades')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
        $data['step'] = DB::table('steps')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
        $data['appointment'] = DB::table($this->table)->whereNotNull('appointment')->select('appointment')->distinct()->orderBy('appointment', 'ASC')->get();
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

        // Handle unit_id → unit name lookup
        if (isset($req->unit_id) && $req->unit_id) {
            $unit = DB::table('units')->where('id', $req->unit_id)->value('name');
            $datas['unit_id'] = $req->unit_id;
            $datas['unit'] = $unit;
        }

        // Normalize TI No.
        if (!empty($datas['ti_no'])) {
            $ti_no = strtoupper(trim($datas['ti_no']));
            $ti_no = preg_replace('/[^A-Z0-9]/', '', $ti_no);
            $ti_no = preg_replace('/^TI/', '', $ti_no);
            $datas['ti_no'] = 'TI' . $ti_no;
        }

        $datas = array_map('strtoupper', $datas);
        $id = $datas['username'];
        $name = $datas['name'];
        $existingUser = User::where('username', $id)->first();

        if ($existingUser) {
            User::where('username', $id)->update([
                'accType' => 'Staff',
                'name' => strtoupper($name),
                'status' => '1'
            ]);
        } else {
            User::create([
                'username' => $id,
                'password' => Hash::make($id),
                'accType' => 'Staff',
                'name' => strtoupper($name),
                'status' => '1'
            ]);
        }
        $id = DB::table('users')->where('username', $id)->value('id');
        $datas['user_id'] = $id;
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

        // Handle reference data IDs and populate name fields
        if (isset($req->unit_id) && $req->unit_id) {
            $unit = DB::table('units')->where('id', $req->unit_id)->value('name');
            $datas['unit_id'] = $req->unit_id;
            $datas['unit'] = $unit;
        }
        if (isset($req->designation_id) && $req->designation_id) {
            $designation = DB::table('designations')->where('id', $req->designation_id)->value('name');
            $datas['designation_id'] = $req->designation_id;
            $datas['current_rank'] = $designation;
        }
        if (isset($req->rank_of_first_appointment_id) && $req->rank_of_first_appointment_id) {
            $rankFirst = DB::table('designations')->where('id', $req->rank_of_first_appointment_id)->value('name');
            $datas['rank_of_first_appointment_id'] = $req->rank_of_first_appointment_id;
            $datas['rank_of_first_appointment'] = $rankFirst;
        }
        if (isset($req->grade_id) && $req->grade_id) {
            $grade = DB::table('grades')->where('id', $req->grade_id)->value('name');
            $datas['grade_id'] = $req->grade_id;
            $datas['grade'] = $grade;
        }
        if (isset($req->step_id) && $req->step_id) {
            $step = DB::table('steps')->where('id', $req->step_id)->value('name');
            $datas['step_id'] = $req->step_id;
            $datas['step'] = $step;
        }

        // Date fields that should NOT be uppercased
        $dateFields = ['date_of_birth', 'date_of_first_appointment', 'date_of_asumption', 'date_of_last_promotion', 'date_of_comfirmation', 'leave_start_date', 'leave_end_date'];

        // Fields that should keep original case (not uppercased)
        $caseSensitiveFields = ['current_qualification', 'staff_status', 'leave_institution', 'physically_challenged', 'physical_challenge_type'];

        foreach ($datas as $key => $value) {
            if (is_string($value) && !in_array($key, $dateFields) && !in_array($key, $caseSensitiveFields)) {
                $datas[$key] = strtoupper($value);
            }
        }

        // Remove uploaded file from data array to avoid strtoupper errors
        if (isset($datas['picture'])) {
            unset($datas['picture']);
        }

        DB::table('staff')->where('user_id', $user_id)->update($datas);

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
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;
            $unit_id = $request->unit_id;
            $staff_category = $request->staff_category;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new StaffImport($faculty, $department, $program, $unit_id, $staff_category);
            // print_r($file);
            // die;
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function downloadStaffTemplate()
    {
        $headers = [
            'Staff ID', 'Name', 'Phone', 'TI No'
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            // Sample row
            fputcsv($file, [
                'USP/0001', 'JOHN DOE', '08012345678', 'TI12345'
            ]);
            fclose($file);
        };

        $filename = 'staff_upload_template.csv';
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function profileUpdate(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $username = session('username');
        $staff = DB::table('staff')->where('username', $username)->first();
        if (!$staff) {
            return redirect()->back()->with('error', 'Staff record not found');
        }

        $nationality = $req->input('nationality');
        $nin = $req->input('nin');
        if (strcasecmp($nationality, 'Nigerian') === 0 && empty($nin)) {
            $tab = $req->input('tab', 'personal');
            return redirect('/staff-profile?tab=' . $tab . '&mode=edit')->withInput()->with('error', 'NIN is required for Nigerian staff.');
        }

        // JSON fields that should be stored as JSON
        $jsonFields = ['institutions', 'experiences', 'publications', 'honours', 'memberships', 'promotions'];

        // Date fields that should NOT be uppercased
        $dateFields = ['date_of_birth', 'date_of_first_appointment', 'date_of_asumption', 'date_of_last_promotion', 'date_of_comfirmation', 'leave_start_date', 'leave_end_date'];

        // Fields that should keep original case (not uppercased)
        $caseSensitiveFields = ['current_qualification', 'staff_status', 'leave_institution', 'physically_challenged', 'physical_challenge_type'];

        $datas = $req->except(['_token', 'picture', 'tab']);

        // Process fields
        $processed = [];
        foreach ($datas as $key => $value) {
            if (in_array($key, $jsonFields)) {
                // Store as JSON
                $processed[$key] = json_encode(is_array($value) ? $value : []);
            } elseif (is_string($value)) {
                // Keep date fields and case-sensitive fields in original format; uppercase text fields
                $processed[$key] = in_array($key, $dateFields) || in_array($key, $caseSensitiveFields) ? $value : strtoupper($value);
            } else {
                $processed[$key] = $value;
            }
        }

        // Handle reference data IDs and populate name fields
        if (isset($req->unit_id) && $req->unit_id) {
            $unit = DB::table('units')->where('id', $req->unit_id)->value('name');
            $processed['unit_id'] = $req->unit_id;
            $processed['unit'] = $unit;
        }
        if (isset($req->designation_id) && $req->designation_id) {
            $designation = DB::table('designations')->where('id', $req->designation_id)->value('name');
            $processed['designation_id'] = $req->designation_id;
            $processed['current_rank'] = $designation;
        }
        if (isset($req->rank_of_first_appointment_id) && $req->rank_of_first_appointment_id) {
            $rankFirst = DB::table('designations')->where('id', $req->rank_of_first_appointment_id)->value('name');
            $processed['rank_of_first_appointment_id'] = $req->rank_of_first_appointment_id;
            $processed['rank_of_first_appointment'] = $rankFirst;
        }
        if (isset($req->grade_id) && $req->grade_id) {
            $grade = DB::table('grades')->where('id', $req->grade_id)->value('name');
            $processed['grade_id'] = $req->grade_id;
            $processed['grade'] = $grade;
        }
        if (isset($req->step_id) && $req->step_id) {
            $step = DB::table('steps')->where('id', $req->step_id)->value('name');
            $processed['step_id'] = $req->step_id;
            $processed['step'] = $step;
        }

        if (!empty($processed)) {
            DB::table('staff')->where('username', $username)->update($processed);
        }

        if ($req->file('picture')) {
            $dot = $req->file('picture')->getClientOriginalExtension();
            $req->file('picture')->storeAs('picture', $staff->user_id . '.' . $dot, 'public');
            Staff::where(['username' => $username])->update([
                'picture' => $staff->user_id . '.' . $dot
            ]);
        }

        // Recalculate profile completion
        $this->recalculateProfileCompletion($username);

        $tab = $req->input('tab', 'personal');
        return redirect('/staff-profile?tab=' . $tab . '&mode=edit')->with('success', 'Profile Updated Successfully!');
    }

    public function uploadDocuments(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $username = session('username');
        $staff = DB::table('staff')->where('username', $username)->first();
        if (!$staff) {
            return redirect()->back()->with('error', 'Staff record not found');
        }

        $documentFields = [
            'doc_photo', 'doc_birth_certificate', 'doc_primary_cert', 'doc_ssce',
            'doc_diploma', 'doc_degree', 'doc_masters', 'doc_phd', 'doc_indigine',
            'doc_workshop', 'doc_nysc', 'doc_trade_test', 'doc_appointment_letter', 'doc_confirmation',
            'doc_professional_body'
        ];

        $updates = [];
        foreach ($documentFields as $field) {
            if ($req->hasFile($field)) {
                $file = $req->file($field);
                if ($file->getSize() > 307200) {
                    return redirect('/staff-profile?tab=documents&mode=edit')->with('error', ucfirst(str_replace('doc_', '', $field)) . ' exceeds 300KB limit');
                }
                $ext = $file->getClientOriginalExtension();
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array(strtolower($ext), $allowed)) {
                    return redirect('/staff-profile?tab=documents&mode=edit')->with('error', ucfirst(str_replace('doc_', '', $field)) . ' must be PDF or image');
                }
                $filename = $username . '_' . $field . '.' . $ext;
                $file->storeAs('staff_documents', $filename, 'public');
                $updates[$field] = $filename;
            }
        }

        // Handle multiple "other" documents
        $existingOthers = json_decode($staff->doc_others ?? '[]', true) ?: [];
        $otherNames = $req->input('other_doc_names', []);
        $otherFiles = $req->file('other_doc_files', []);

        if (!empty($otherNames)) {
            foreach ($otherNames as $idx => $name) {
                if (empty($name)) continue;

                if (isset($otherFiles[$idx])) {
                    $file = $otherFiles[$idx];
                    if ($file->getSize() > 307200) {
                        return redirect('/staff-profile?tab=documents&mode=edit')->with('error', '"' . $name . '" exceeds 300KB limit');
                    }
                    $ext = $file->getClientOriginalExtension();
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                    if (!in_array(strtolower($ext), $allowed)) {
                        return redirect('/staff-profile?tab=documents&mode=edit')->with('error', '"' . $name . '" must be PDF or image');
                    }
                    $filename = $username . '_other_' . $idx . '_' . time() . '.' . $ext;
                    $file->storeAs('staff_documents', $filename, 'public');
                    $existingOthers[] = ['name' => strtoupper($name), 'file' => $filename];
                }
            }
        }

        $updates['doc_others'] = json_encode($existingOthers);

        if (!empty($updates)) {
            DB::table('staff')->where('username', $username)->update($updates);
        }

        // Recalculate profile completion
        $this->recalculateProfileCompletion($username);

        return redirect('/staff-profile?tab=documents&mode=edit')->with('success', 'Documents Uploaded Successfully!');
    }

    public function deleteOtherDoc(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $username = session('username');
        $staff = DB::table('staff')->where('username', $username)->first();
        if (!$staff) {
            return redirect()->back()->with('error', 'Staff record not found');
        }

        $index = $req->input('index');
        $others = json_decode($staff->doc_others ?? '[]', true) ?: [];

        if (isset($others[$index])) {
            // Delete file from storage
            $filePath = storage_path('app/public/staff_documents/' . $others[$index]['file']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            array_splice($others, $index, 1);
            DB::table('staff')->where('username', $username)->update(['doc_others' => json_encode($others)]);
        }

        return redirect('/staff-profile?tab=documents&mode=edit')->with('success', 'Document removed successfully!');
    }

    public function submitProfile(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $username = session('username');
        $row = DB::table('staff')->where('username', $username)->first();
        if (!$row) {
            return redirect()->back()->with('error', 'Staff record not found');
        }

        // Validate all required fields
        $missingFields = [];

        // Personal Info
        $personalFields = [
            'name' => 'Full Name', 'gender' => 'Gender', 'marital_status' => 'Marital Status',
            'date_of_birth' => 'Date of Birth', 'phone' => 'Phone', 'email' => 'Email',
            'state' => 'State of Origin', 'lga' => 'LGA', 'nationality' => 'Nationality',
            'nin' => 'NIN', 'address' => 'Home Address', 'physically_challenged' => 'Physically Challenged',
        ];
        foreach ($personalFields as $field => $label) {
            if ($field == 'date_of_birth') {
                if (empty($row->$field) || $row->$field == '1970-01-01') $missingFields[] = "Personal Info: $label";
            } else {
                if (empty($row->$field)) $missingFields[] = "Personal Info: $label";
            }
        }
        if ($row->physically_challenged == 'Yes' && empty($row->physical_challenge_type)) {
            $missingFields[] = "Personal Info: Physical Challenge Type";
        }

        // Service Record
        $serviceFields = [
            'designation_id' => 'Designation/Rank', 'staff_category' => 'Staff Category',
            'employee_status' => 'Employment Status', 'grade_id' => 'Grade/Level', 'step_id' => 'Step',
            'date_of_first_appointment' => 'Date of First Appointment',
            'rank_of_first_appointment' => 'Rank on First Appointment',
            'date_of_asumption' => 'Date of Assumption',
            'current_qualification' => 'Current Qualification', 'staff_status' => 'Staff Status',
        ];
        foreach ($serviceFields as $field => $label) {
            if (in_array($field, ['date_of_first_appointment', 'date_of_asumption'])) {
                if (empty($row->$field) || $row->$field == '1970-01-01') $missingFields[] = "Service Record: $label";
            } else {
                if (empty($row->$field)) $missingFields[] = "Service Record: $label";
            }
        }

        // Next of Kin & Bank
        $kinBankFields = [
            'kin_name' => 'Next of Kin Name', 'kin_phone' => 'Next of Kin Phone',
            'kin_relationship' => 'Next of Kin Relationship', 'kin_address' => 'Next of Kin Address',
            'bank_name' => 'Bank Name', 'account_number' => 'Account Number',
            'pension_administrator' => 'Pension Name', 'pension_number' => 'Pension PIN Number',
        ];
        foreach ($kinBankFields as $field => $label) {
            if (empty($row->$field)) $missingFields[] = "Next of Kin & Bank: $label";
        }

        // Education (at least one record)
        $institutions = json_decode($row->institutions ?? '[]', true) ?: [];
        $hasEducation = false;
        foreach ($institutions as $inst) {
            if (!empty($inst['name'])) { $hasEducation = true; break; }
        }
        if (!$hasEducation) $missingFields[] = "Education: At least one Education & Qualification record";

        // Documents
        $requiredDocs = [
            'doc_photo' => 'Photo', 'doc_birth_certificate' => 'Birth Certificate',
            'doc_appointment_letter' => 'Appointment Letter',
            'doc_confirmation' => 'Letter of Confirmation',
        ];
        foreach ($requiredDocs as $field => $label) {
            if (empty($row->$field)) $missingFields[] = "Documents: $label";
        }

        if (!empty($missingFields)) {
            return redirect('/staff-profile?tab=submit')->with('error', 'Profile is incomplete. Please fill all required fields before submitting.');
        }

        // Calculate completion percentage
        $totalRequired = count($personalFields) + count($serviceFields) + count($kinBankFields) + 1 + count($requiredDocs);
        $completionPercent = 100;

        // Update profile status
        DB::table('staff')->where('username', $username)->update([
            'profile_status' => 'submitted',
            'profile_submitted_at' => now(),
            'profile_completion' => $completionPercent,
        ]);

        return redirect('/staff-profile?tab=submit')->with('success', 'Profile submitted successfully! All information has been recorded.');
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

    /**
     * Download staff CV as PDF, merging any uploaded PDF documents (like recruitment).
     */
    public function downloadCV($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $row = DB::table('staff')->where('id', $id)->first();
            if (!$row) {
                return back()->with('error', 'Staff record not found');
            }

            // Prepare lookup names (same logic as staff record view)
            $unitName = (isset($row->unit_id) && $row->unit_id) ? DB::table('units')->where('id', $row->unit_id)->value('name') : ($row->unit ?? '');
            $designationName = (isset($row->designation_id) && $row->designation_id) ? DB::table('designations')->where('id', $row->designation_id)->value('name') : ($row->current_rank ?? '');
            $gradeName = (isset($row->grade_id) && $row->grade_id) ? DB::table('grades')->where('id', $row->grade_id)->value('name') : ($row->grade ?? '');
            $stepName = (isset($row->step_id) && $row->step_id) ? DB::table('steps')->where('id', $row->step_id)->value('name') : ($row->step ?? '');

            // Decode JSON fields
            $institutions = json_decode($row->institutions ?? '[]', true) ?: [];
            $experiences  = json_decode($row->experiences  ?? '[]', true) ?: [];
            $publications = json_decode($row->publications ?? '[]', true) ?: [];
            $honours      = json_decode($row->honours      ?? '[]', true) ?: [];
            $memberships  = json_decode($row->memberships  ?? '[]', true) ?: [];
            $docOthers    = json_decode($row->doc_others   ?? '[]', true) ?: [];

            $documentsMap = [
                'doc_photo' => 'Photo',
                'doc_birth_certificate' => 'Birth Certificate/Declaration of Age',
                'doc_primary_cert' => 'Primary School Certificate',
                'doc_ssce' => 'SSCE/GCE',
                'doc_diploma' => 'Diploma',
                'doc_degree' => 'Degree',
                'doc_masters' => 'Masters',
                'doc_phd' => 'PhD',
                'doc_indigine' => 'Indigene',
                'doc_workshop' => 'Workshop Cert',
                'doc_nysc' => 'NYSC/Exception',
                'doc_trade_test' => 'Trade Test Certificate',
                'doc_appointment_letter' => 'Appointment Letter',
                'doc_confirmation' => 'Letter of Confirmation',
                'doc_professional_body' => 'Certificate of Professional Body Membership',
            ];

            // Temp dir for files
            $tempDir = storage_path('app/temp/documents');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            $tempFiles = [];

            // Collect docs: images for embedding, PDFs for merge
            $downloadedDocs = [];
            $photoDataUri = null;

            foreach ($documentsMap as $field => $label) {
                $filename = $row->$field ?? null;
                if (empty($filename)) {
                    // fallback photo from main picture column
                    if ($field === 'doc_photo' && !empty($row->picture)) {
                        $filename = $row->picture;
                        $basePath = storage_path('app/public/picture/' . $filename);
                    } else {
                        continue;
                    }
                } else {
                    $basePath = storage_path('app/public/staff_documents/' . $filename);
                }

                if (!file_exists($basePath)) continue;

                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $content = @file_get_contents($basePath);
                if ($content === false) continue;

                if (in_array($ext, ['jpg','jpeg','png','gif','bmp'])) {
                    $mimeMap = ['jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','gif'=>'gif','bmp'=>'bmp'];
                    $mime = $mimeMap[$ext] ?? $ext;
                    $b64 = base64_encode($content);
                    $dataUri = 'data:image/' . $mime . ';base64,' . $b64;

                    if ($field === 'doc_photo') {
                        $photoDataUri = $dataUri;
                    }

                    $tmpFile = $tempDir . '/' . uniqid('doc_') . '.' . $ext;
                    file_put_contents($tmpFile, $content);
                    $tempFiles[] = $tmpFile;

                    $downloadedDocs[] = [
                        'label' => $label,
                        'ext' => $ext,
                        'path' => $tmpFile,
                        'content' => $content,
                        'is_image' => true,
                    ];
                } elseif ($ext === 'pdf') {
                    $tmpFile = $tempDir . '/' . uniqid('doc_') . '.pdf';
                    file_put_contents($tmpFile, $content);
                    $tempFiles[] = $tmpFile;

                    $downloadedDocs[] = [
                        'label' => $label,
                        'ext' => 'pdf',
                        'path' => $tmpFile,
                        'content' => $content,
                        'is_image' => false,
                    ];
                }
            }

            // Other documents
            foreach ($docOthers as $other) {
                if (empty($other['file'])) continue;
                $filename = $other['file'];
                $basePath = storage_path('app/public/staff_documents/' . $filename);
                if (!file_exists($basePath)) continue;

                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $content = @file_get_contents($basePath);
                if ($content === false) continue;

                $label = $other['name'] ?? 'Other Document';

                if (in_array($ext, ['jpg','jpeg','png','gif','bmp'])) {
                    $mimeMap = ['jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','gif'=>'gif','bmp'=>'bmp'];
                    $mime = $mimeMap[$ext] ?? $ext;
                    $b64 = base64_encode($content);
                    $dataUri = 'data:image/' . $mime . ';base64,' . $b64;

                    $tmpFile = $tempDir . '/' . uniqid('doc_') . '.' . $ext;
                    file_put_contents($tmpFile, $content);
                    $tempFiles[] = $tmpFile;

                    $downloadedDocs[] = [
                        'label' => $label,
                        'ext' => $ext,
                        'path' => $tmpFile,
                        'content' => $content,
                        'is_image' => true,
                    ];
                } elseif ($ext === 'pdf') {
                    $tmpFile = $tempDir . '/' . uniqid('doc_') . '.pdf';
                    file_put_contents($tmpFile, $content);
                    $tempFiles[] = $tmpFile;

                    $downloadedDocs[] = [
                        'label' => $label,
                        'ext' => 'pdf',
                        'path' => $tmpFile,
                        'content' => $content,
                        'is_image' => false,
                    ];
                }
            }

            // Fallback: if still no photoDataUri but main picture exists, embed it
            if (empty($photoDataUri) && !empty($row->picture)) {
                $picPath = storage_path('app/public/picture/' . $row->picture);
                if (file_exists($picPath)) {
                    $ext = strtolower(pathinfo($row->picture, PATHINFO_EXTENSION));
                    $content = @file_get_contents($picPath);
                    if ($content !== false) {
                        $mimeMap = ['jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','gif'=>'gif','bmp'=>'bmp'];
                        $mime = $mimeMap[$ext] ?? $ext;
                        $b64 = base64_encode($content);
                        $photoDataUri = 'data:image/' . $mime . ';base64,' . $b64;
                    }
                }
            }

            // Render CV HTML
            $options = new Options();
            $options->set('chroot', public_path());
            $options->set('tempDir', sys_get_temp_dir());
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $html = view('Admin.staff-cv', [
                'row' => $row,
                'unitName' => $unitName,
                'designationName' => $designationName,
                'gradeName' => $gradeName,
                'stepName' => $stepName,
                'institutions' => $institutions,
                'experiences' => $experiences,
                'publications' => $publications,
                'honours' => $honours,
                'memberships' => $memberships,
                'docOthers' => $docOthers,
                'photoDataUri' => $photoDataUri,
            ])->render();

            // Embed image docs at end (before </body>)
            $imageDocsHtml = [];
            foreach ($downloadedDocs as $ddoc) {
                if (!empty($ddoc['is_image'])) {
                    $mimeMap = ['jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','gif'=>'gif','bmp'=>'bmp'];
                    $mime = $mimeMap[$ddoc['ext']] ?? $ddoc['ext'];
                    $b64 = base64_encode($ddoc['content']);
                    $imageDocsHtml[] = '<div style="text-align:center;page-break-after:always;"><img src="data:image/' . $mime . ';base64,' . $b64 . '" style="max-width:100%;max-height:750px;" /></div>';
                }
            }

            if (!empty($imageDocsHtml)) {
                $html = str_replace('class="footer"', 'class="footer" style="page-break-after:always;"', $html);
                $lastIdx = count($imageDocsHtml) - 1;
                $imageDocsHtml[$lastIdx] = str_replace('page-break-after:always;', '', $imageDocsHtml[$lastIdx]);
                $html = str_replace('</body>', implode('', $imageDocsHtml) . '</body>', $html);
            }

            // Generate CV PDF
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $cvPdfContent = $dompdf->output();

            $cvTmpFile = $tempDir . '/' . uniqid('cv_') . '.pdf';
            file_put_contents($cvTmpFile, $cvPdfContent);
            $tempFiles[] = $cvTmpFile;

            // PDFs to merge
            $pdfDocsToMerge = [];
            foreach ($downloadedDocs as $ddoc) {
                if ($ddoc['ext'] === 'pdf') {
                    $pdfDocsToMerge[] = $ddoc;
                }
            }

            if (!empty($pdfDocsToMerge)) {
                $fpdi = new Fpdi();

                // Import CV pages
                $cvPageCount = $fpdi->setSourceFile($cvTmpFile);
                for ($p = 1; $p <= $cvPageCount; $p++) {
                    $tpl = $fpdi->importPage($p);
                    $size = $fpdi->getTemplateSize($tpl);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl);
                }

                // Import each PDF document
                foreach ($pdfDocsToMerge as $pdfDoc) {
                    try {
                        $docPageCount = $fpdi->setSourceFile($pdfDoc['path']);
                        for ($p = 1; $p <= $docPageCount; $p++) {
                            $tpl = $fpdi->importPage($p);
                            $size = $fpdi->getTemplateSize($tpl);
                            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $fpdi->useTemplate($tpl);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to merge staff PDF document: ' . $pdfDoc['label'] . ' - ' . $e->getMessage());
                    }
                }

                $mergedContent = $fpdi->Output('S');

                foreach ($tempFiles as $f) { @unlink($f); }

                $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '_', $row->name ?? 'Staff');
                $pdfName = 'CV_' . $cleanName . '_' . ($row->username ?? 'ID') . '_' . date('Y-m-d_H-i-s') . '.pdf';

                return response($mergedContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $pdfName . '"',
                    'Content-Length' => strlen($mergedContent),
                ]);
            }

            // No PDFs — just the CV
            foreach ($tempFiles as $f) { @unlink($f); }

            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '_', $row->name ?? 'Staff');
            $pdfName = 'CV_' . $cleanName . '_' . ($row->username ?? 'ID') . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return $dompdf->stream($pdfName, ['Attachment' => true]);

        } catch (\Exception $e) {
            \Log::error('Staff CV Generation Error: ' . $e->getMessage());
            return back()->with('error', 'CV generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export staff to PDF
     */
    public function exportPdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get filtered staff
        $staff = $this->getFilteredStaff($request);

        // Prepare data for export
        $exportData = [];
        $serialNumber = 1;

        foreach ($staff as $row) {
            $exportData[] = [
                'sno' => $serialNumber++,
                'sp_no' => $row->username ?? 'N/A',
                'name' => $row->name ?? 'N/A',
                'nin' => $row->nin ?? 'N/A',
                'dob' => !empty($row->date_of_birth) && $row->date_of_birth != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_birth)) : 'N/A',
                'state' => $row->state ?? 'N/A',
                'lga' => $row->lga ?? 'N/A',
                'gender' => $row->gender ?? 'N/A',
                'date_of_appointment' => !empty($row->date_of_first_appointment) && $row->date_of_first_appointment != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_first_appointment)) : 'N/A',
                'date_of_confirmation' => !empty($row->date_of_comfirmation) && $row->date_of_comfirmation != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_comfirmation)) : 'N/A',
                'current_rank' => $row->current_rank ?? $row->designation ?? 'N/A',
                'dept_unit' => $row->unit ?? ($row->department ?? 'N/A'),
                'phone' => $row->phone ?? 'N/A',
                'email' => $row->email ?? 'N/A',
            ];
        }

        try {
            // Generate PDF using project's FPDF system
            $pdfContent = view('Admin.staff-pdf-fpdf', [
                'staff' => $exportData,
                'filters' => $request->all()
            ])->render();

            // Evaluate the PDF content (it will output the PDF directly)
            eval('?>' . $pdfContent);
            exit;

        } catch (\Exception $e) {
            \Log::error('Staff PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export staff to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get filtered staff
        $staff = $this->getFilteredStaff($request);

        // Prepare data for export
        $exportData = [];
        $serialNumber = 1;

        foreach ($staff as $row) {
            $exportData[] = [
                'S/NO' => $serialNumber++,
                'SP. NO' => $row->username ?? 'N/A',
                'NAME' => $row->name ?? 'N/A',
                'NIN' => $row->nin ?? 'N/A',
                'DOB' => !empty($row->date_of_birth) && $row->date_of_birth != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_birth)) : 'N/A',
                'STATE OF ORIGIN' => $row->state ?? 'N/A',
                'LGA' => $row->lga ?? 'N/A',
                'GENDER' => $row->gender ?? 'N/A',
                'DATE OF APPT.' => !empty($row->date_of_first_appointment) && $row->date_of_first_appointment != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_first_appointment)) : 'N/A',
                'DATE OF CONFIRMATION' => !empty($row->date_of_comfirmation) && $row->date_of_comfirmation != '1970-01-01' ? date('d/m/Y', strtotime($row->date_of_comfirmation)) : 'N/A',
                'CURRENT RANK' => $row->current_rank ?? $row->designation ?? 'N/A',
                'DEPT/UNIT' => $row->unit ?? ($row->department ?? 'N/A'),
                'PHONE NUMBER' => $row->phone ?? 'N/A',
                'E-MAIL' => $row->email ?? 'N/A',
            ];
        }

        // Generate filename with timestamp
        $filename = 'staff_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV file
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Header row
            if (!empty($exportData)) {
                fputcsv($file, array_keys($exportData[0]));
            }

            // Data rows
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get filtered staff based on request parameters
     */
    private function getFilteredStaff(Request $request)
    {
        $query = DB::table('staff');

        // Apply filters
        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }
        if ($request->filled('lga')) {
            $query->where('lga', 'like', '%' . $request->input('lga') . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        if ($request->filled('faculty')) {
            $query->where('faculty', $request->input('faculty'));
        }
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }
        if ($request->filled('program')) {
            $query->where('program', $request->input('program'));
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }
        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->input('designation_id'));
        }
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->input('grade_id'));
        }
        if ($request->filled('step_id')) {
            $query->where('step_id', $request->input('step_id'));
        }

        return $query->get();
    }

    public function getDepartments($facultyCode)
    {
        $departments = DB::table('department')
            ->select('code', 'title')
            ->where(['faculty' => $facultyCode, 'status' => '1'])
            ->orderBy('title', 'asc')
            ->get();
        return response()->json($departments);
    }

    public function getPrograms($deptCode)
    {
        $programs = DB::table('program')
            ->select('code', 'title')
            ->where(['department' => $deptCode, 'status' => '1'])
            ->orderBy('title', 'asc')
            ->get();
        return response()->json($programs);
    }

    public function showResetPasswordProgress()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'staff.reset_password_progress']);
    }

    public function resetPasswordStream(Request $request)
    {
        $password_method = $request->input('password_method', 'random');
        $filters = $request->except(['password_method', 'generate_pdf', '_token']);

        $response = new StreamedResponse(function () use ($password_method, $filters) {
            set_time_limit(0);

            $sendMessage = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $query = DB::table('staff');
                foreach ($filters as $key => $value) {
                    $query->where($key, $value);
                }

                $totalCount = $query->count();
                $processedCount = 0;
                $successCount = 0;
                $failCount = 0;

                $sendMessage('status', [
                    'message' => "Found {$totalCount} staff records to process...",
                ]);

                if ($totalCount === 0) {
                    $sendMessage('finished', ['message' => 'No staff members matched the criteria. Nothing to do.']);
                    return;
                }

                $query->orderBy('id')->chunk(20, function ($staffMembers) use ($sendMessage, &$processedCount, &$successCount, &$failCount, $totalCount, $password_method) {
                    foreach ($staffMembers as $staff) {
                        DB::beginTransaction();
                        try {
                            $password = '';
                            switch ($password_method) {
                                case 'phone':
                                    $password = $staff->phone ?? '';
                                    if (empty($password)) {
                                        $password = $staff->username ?? '';
                                    }
                                    break;
                                case 'ti_no':
                                    $password = $staff->ti_no ?? '';
                                    if (empty($password)) {
                                        $password = $staff->username ?? '';
                                    }
                                    break;
                                case 'account_no':
                                    $password = $staff->account_number ?? '';
                                    if (empty($password)) {
                                        $password = $staff->username ?? '';
                                    }
                                    break;
                                case 'username':
                                    $password = $staff->username ?? '';
                                    break;
                                case 'random':
                                default:
                                    $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                                    break;
                            }

                            if (!empty($password)) {
                                $user_id = $staff->user_id;
                                DB::table('users')->where('id', $user_id)->update([
                                    'password' => Hash::make($password)
                                ]);

                                $successCount++;
                                $sendMessage('progress', [
                                    'message' => "✅ Password reset for {$staff->username}.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            } else {
                                $failCount++;
                                $sendMessage('progress', [
                                    'message' => "❌ Failed to generate password for {$staff->username}.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            }
                            DB::commit();
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            $failCount++;
                            $sendMessage('progress', [
                                'message' => "❌ Error processing staff '{$staff->username}': " . $e->getMessage() . '. Continuing...',
                                'progress' => round((++$processedCount / $totalCount) * 100),
                            ]);
                        }
                    }
                });

                $sendMessage('finished', [
                    'message' => "✅ Process completed! Success: {$successCount}, Failed: {$failCount}."
                ]);
            } catch (\Throwable $e) {
                $sendMessage('finished', ['message' => '❌ A critical error occurred: ' . $e->getMessage()]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    private function recalculateProfileCompletion($username)
    {
        $row = DB::table('staff')->where('username', $username)->first();
        if (!$row) return;

        $totalRequired = 0;
        $filledRequired = 0;

        // Personal Info
        $personalFields = ['name', 'gender', 'marital_status', 'date_of_birth', 'phone', 'email', 'state', 'lga', 'nationality', 'nin', 'address', 'physically_challenged'];
        foreach ($personalFields as $field) {
            $totalRequired++;
            if ($field == 'date_of_birth') {
                if (!empty($row->$field) && $row->$field != '1970-01-01') $filledRequired++;
            } else {
                if (!empty($row->$field)) $filledRequired++;
            }
        }

        // Service Record
        $serviceFields = ['designation_id', 'staff_category', 'employee_status', 'grade_id', 'step_id', 'date_of_first_appointment', 'rank_of_first_appointment', 'date_of_asumption', 'current_qualification', 'staff_status'];
        foreach ($serviceFields as $field) {
            $totalRequired++;
            if (in_array($field, ['date_of_first_appointment', 'date_of_asumption'])) {
                if (!empty($row->$field) && $row->$field != '1970-01-01') $filledRequired++;
            } else {
                if (!empty($row->$field)) $filledRequired++;
            }
        }

        // Next of Kin & Bank
        $kinBankFields = ['kin_name', 'kin_phone', 'kin_relationship', 'kin_address', 'bank_name', 'account_number', 'pension_administrator', 'pension_number'];
        foreach ($kinBankFields as $field) {
            $totalRequired++;
            if (!empty($row->$field)) $filledRequired++;
        }

        // Education (at least one record)
        $totalRequired++;
        $institutions = json_decode($row->institutions ?? '[]', true) ?: [];
        foreach ($institutions as $inst) {
            if (!empty($inst['name'])) { $filledRequired++; break; }
        }

        // Documents
        $requiredDocs = ['doc_photo', 'doc_birth_certificate', 'doc_appointment_letter', 'doc_confirmation'];
        foreach ($requiredDocs as $field) {
            $totalRequired++;
            if (!empty($row->$field)) $filledRequired++;
        }

        $completionPercent = $totalRequired > 0 ? round(($filledRequired / $totalRequired) * 100) : 0;

        DB::table('staff')->where('username', $username)->update([
            'profile_completion' => $completionPercent,
        ]);
    }
}
