<?php

namespace App\Http\Controllers;

use App\Imports\DegreeImport;
use App\Imports\AlumniImport;
use App\Imports\StudentToAlumniImport;
use App\Imports\StudentIdsToAlumniImport;
use App\Exports\StudentIdsToAlumniTemplateExport;
use App\Models\Alumni;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumniController extends Controller
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
        $this->table = 'alumni';
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
            $data['data'] = $query->orderBy('fullname', 'ASC')->paginate(100);
        } else {
            $data['data'] = DB::table($this->table)->orderBy('fullname', 'ASC')->paginate(100);
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'alumni';
        $data['title'] = 'Alumni';
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
                'accType' => 'Alumni',
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
        $user_id = $datas['id'];
        // unset($datas['id']);
        // unset($datas['_token']);
        // $datas = array_map('strtoupper', $datas);
        // DB::table($this->table)->where('user_id', $user_id)->update($datas);

        if ($req->file('picture')) {
            $dot = $req->file('picture')->getClientOriginalExtension();
            $req->file('picture')->storeAs('picture', $user_id . '.' . $dot, 'public');

            $applicant = Alumni::where(['user_id' => $user_id])->update([
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
            $faculty = 'fac';
            $department = 'dept';
            $program = 'program';

            // Load the uploaded file using Maatwebsite/Excel
            $import = new AlumniImport($faculty, $department, $program);
            // print_r($file);
            // die;
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function uploadStudent(Request $request)
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
            $upload_type = $request->upload_type;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new StudentToAlumniImport($faculty, $department, $program, $upload_type);
            Excel::import($import, $file);
            // dd(session('studentImportMsg'));
            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with(session('studentImportStatus'), session('studentImportMsg'));
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    /**
     * Convert existing student records to alumni from a one-column ID list.
     * No record is created when the uploaded username is not in students.
     */
    public function uploadStudentIds(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Choose an Excel file containing the student usernames.',
            'file.mimes' => 'Upload an Excel workbook (.xlsx or .xls) or a CSV file.',
            'file.max' => 'The upload must not be larger than 10 MB.',
        ]);

        try {
            $import = new StudentIdsToAlumniImport();
            Excel::import($import, $request->file('file'));
            $stats = $import->stats();

            $message = sprintf(
                'Import complete: %d converted, %d already alumni, %d not found, %d duplicate, %d invalid, %d failed.',
                $stats['converted'],
                $stats['already_alumni'],
                count($stats['not_found']),
                $stats['duplicates'],
                count($stats['invalid']),
                count($stats['errors'])
            );

            $details = [];
            if ($stats['not_found']) {
                $details[] = 'Not found: ' . implode(', ', array_slice($stats['not_found'], 0, 20));
            }
            if ($stats['invalid']) {
                $details[] = 'Invalid rows: ' . implode(' ', array_slice($stats['invalid'], 0, 10));
            }
            if ($stats['errors']) {
                $details[] = 'Failed rows: ' . implode(' | ', array_slice($stats['errors'], 0, 10));
            }

            $status = ($stats['converted'] || $stats['already_alumni']) ? 'success' : 'warning';
            return redirect()->route('alumni.index')->with($status, $message . ($details ? ' ' . implode(' ', $details) : ''));
        } catch (\Throwable $exception) {
            \Log::error('Student-to-alumni ID import failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()->route('alumni.index')->with('error', 'The file could not be imported. Check that it contains one username column and try again.');
        }
    }

    /**
     * Download the one-column workbook used by the student-to-alumni import.
     */
    public function downloadStudentTemplate()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        return Excel::download(
            new StudentIdsToAlumniTemplateExport(),
            'students-to-alumni-template.xlsx'
        );
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
            $degree = 'degree';

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
     * Show the move to alumni progress page
     */
    public function showMoveToAlumniProgress()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['page'] = 'alumni/move_to_alumni_progress';
        $data['title'] = 'Move Students to Alumni';
        $data['sessions'] = DB::table('session')->select('title')->orderByDesc('title')->get();
        $data['currentSession'] = DB::table('session')->where('status', '1')->value('title');
        return view('main', $data);
    }
    /**
     * Stream the progress of moving students to alumni
     */
    public function moveToAlumniStream(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $selectedSession = trim((string) $request->query('session', ''));
        $usernamePrefix = trim((string) $request->query('username_prefix', ''));
        $validSession = DB::table('session')->where('title', $selectedSession)->exists();

        if (!$validSession) {
            return response()->json(['error' => 'Please select a valid academic session.'], 422);
        }

        if ($usernamePrefix === '' || !preg_match('/^\d{2}$/', $usernamePrefix)) {
            return response()->json(['error' => 'Please select a matric-number cutoff, for example 22.'], 422);
        }

        $scopeLabel = "matric-number prefixes {$usernamePrefix} and earlier";
        $response = new StreamedResponse(function() use ($selectedSession, $usernamePrefix, $scopeLabel) {
            // Set headers for Server-Sent Events
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering
            
            // Set time and memory limits to prevent timeout
            set_time_limit(0); // Unlimited execution time
            ini_set('memory_limit', '512M'); // Increase memory limit
            ignore_user_abort(true); // Continue script even if user disconnects

            // Send initial status
            $this->sendSseMessage('status', [
                'message' => "Starting process for session {$selectedSession} ({$scopeLabel})..."
            ]);

            try {
                // Eligibility is based on active status and the absence of a result in the selected session.
                $studentsQuery = Student::whereNotNull('username') // Ensure username is not null
                    ->where('status', 1) // Only active students
                    ->whereNotExists(function($query) use ($selectedSession) {
                        $query->select(DB::raw(1))
                              ->from('results')
                              ->whereRaw('results.username = students.username')
                              ->where('session', $selectedSession);
                    })
                    ->with(['facultys', 'departments', 'programs']); // Load relationships

                $studentsQuery
                    ->whereRaw("students.username REGEXP '^[0-9]{2}/'")
                    ->whereRaw("CAST(SUBSTRING_INDEX(students.username, '/', 1) AS UNSIGNED) <= ?", [(int) $usernamePrefix]);
                $totalStudents = $studentsQuery->count();
                $processedCount = 0;
                $successCount = 0;
                $errorCount = 0;

                if ($totalStudents === 0) {
                    $this->sendSseMessage('status', [
                        'message' => "No eligible students found for session {$selectedSession} ({$scopeLabel}). Eligible students must be active and have no result in that session."
                    ]);
                    $this->sendSseMessage('finished', [
                        'message' => 'Process completed. No students to process.'
                    ]);
                    return;
                }

                $this->sendSseMessage('status', [
                    'message' => "Found {$totalStudents} eligible students to process..."
                ]);

                // Get all students at once to avoid chunking issues
                $allStudents = $studentsQuery->get();
                
                $this->sendSseMessage('status', [
                    'message' => "Processing {$totalStudents} students..."
                ]);
                
                foreach ($allStudents as $student) {
                    // Add connection check
                    if (connection_aborted()) {
                        break;
                    }
                        try {
                            // Check if student already exists in alumni table
                            $existingAlumni = Alumni::where('username', $student->username)->first();

                            $alumniData = [
                                'user_id' => $student->user_id,
                                'id_no' => $student->username,
                                'username' => $student->username,
                                'fullname' => $student->fullname ?? 'N/A',
                                'email' => $student->contact_email ?? 'N/A',
                                'phone' => $student->contact_phone ?? 'N/A',
                                'gender' => $student->gender,
                                'program' => $student->programs?->title ?? $student->program ?? 'N/A',
                                'year' => date('Y'), // Current year as graduation year
                                'picture' => 'N/A',
                                'password' => $student->username,
                                'updated_at' => now()
                            ];

                            if (!$existingAlumni) {
                                // Create new alumni record
                                $alumniData['created_at'] = now();
                                Alumni::create($alumniData);

                                $successCount++;
                                $programTitle = $student->programs?->title ?? $student->program ?? 'N/A';
                                $this->sendSseMessage('progress', [
                                    'message' => "✓ Created alumni record {$student->username} - Program: {$programTitle}",
                                    'progress' => round((++$processedCount / $totalStudents) * 100)
                                ]);
                            } else {
                                // Update existing alumni record
                                $existingAlumni->update($alumniData);

                                $successCount++;
                                $programTitle = $student->programs?->title ?? $student->program ?? 'N/A';
                                $this->sendSseMessage('progress', [
                                    'message' => "✓ Updated alumni record {$student->username} - Program: {$programTitle}",
                                    'progress' => round((++$processedCount / $totalStudents) * 100)
                                ]);
                            }

                            // Update student status to 3 (alumni)
                            $student->status = 3;
                            $student->updated_at = now();
                            $student->save();

                            // Update user account type to Alumni
                            if ($student->user_id) {
                                DB::table('users')
                                    ->where('id', $student->user_id)
                                    ->update(['accType' => 'Alumni', 'updated_at' => now()]);
                            }

                        } catch (\Exception $e) {
                            $errorCount++;
                            \Log::error('Error processing student to alumni', [
                                'username' => $student->username ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            $this->sendSseMessage('progress', [
                                'message' => "✗ Error processing {$student->username}: " . $e->getMessage(),
                                'progress' => round((++$processedCount / $totalStudents) * 100)
                            ]);
                        } catch (\Throwable $e) {
                            $errorCount++;
                            \Log::error('Fatal error processing student to alumni', [
                                'username' => $student->username ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            $this->sendSseMessage('progress', [
                                'message' => "✗ Fatal error processing student: " . $e->getMessage(),
                                'progress' => round((++$processedCount / $totalStudents) * 100)
                            ]);
                        }

                    // Flush output to send messages immediately
                    if (ob_get_level()) ob_flush();
                    flush();
                    
                    // Small delay to prevent overwhelming the connection
                    usleep(10000); // 10ms delay
                }

                // Send final status
                $finalMessage = "Process completed! Success: {$successCount}, Errors: {$errorCount}";
                $this->sendSseMessage('finished', [
                    'message' => $finalMessage
                ]);

            } catch (\Exception $e) {
                $this->sendSseMessage('finished', [
                    'message' => 'Process failed: ' . $e->getMessage()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Restore a single alumni back to student status
     */
    public function restoreSingleAlumni($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            // Find the alumni record
            $alumni = Alumni::find($id);
            
            if (!$alumni) {
                return redirect()->back()->with('error', 'Alumni record not found.');
            }

            // Find the corresponding student record
            $student = Student::where('username', $alumni->username)->first();
            
            if (!$student) {
                return redirect()->back()->with('error', 'No corresponding student record found for ' . $alumni->username);
            }

            // Update student status back to active (1)
            $student->status = 1;
            $student->updated_at = now();
            $student->save();

            // Update user account type back to Student
            if ($student->user_id) {
                DB::table('users')
                    ->where('id', $student->user_id)
                    ->update(['accType' => 'Student', 'updated_at' => now()]);
            }

            // Delete the alumni record
            $alumni->delete();

            return redirect()->back()->with('success', 'Successfully restored ' . $alumni->username . ' to student status.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error restoring alumni: ' . $e->getMessage());
        }
    }

    /**
     * Restore multiple selected alumni back to student status
     */
    public function restoreSelectedAlumni(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $selectedIds = $request->input('selected_ids');
            
            if (empty($selectedIds)) {
                return redirect()->back()->with('error', 'No alumni records selected.');
            }

            $idsArray = explode(',', $selectedIds);
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($idsArray as $id) {
                try {
                    // Find the alumni record
                    $alumni = Alumni::find($id);
                    
                    if (!$alumni) {
                        $errorCount++;
                        $errors[] = "Alumni record ID {$id} not found.";
                        continue;
                    }

                    // Find the corresponding student record
                    $student = Student::where('username', $alumni->username)->first();
                    
                    if (!$student) {
                        $errorCount++;
                        $errors[] = "No corresponding student record found for {$alumni->username}.";
                        continue;
                    }

                    // Update student status back to active (1)
                    $student->status = 1;
                    $student->updated_at = now();
                    $student->save();

                    // Update user account type back to Student
                    if ($student->user_id) {
                        DB::table('users')
                            ->where('id', $student->user_id)
                            ->update(['accType' => 'Student', 'updated_at' => now()]);
                    }

                    // Delete the alumni record
                    $alumni->delete();
                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error processing alumni ID {$id}: " . $e->getMessage();
                }
            }

            $message = "Restore completed! Success: {$successCount}, Errors: {$errorCount}";
            
            if ($errorCount > 0) {
                $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " ... and " . (count($errors) - 3) . " more errors.";
                }
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error restoring selected alumni: ' . $e->getMessage());
        }
    }

    /**
     * Show the restore alumni by year progress page
     */
    public function showRestoreByYearProgress(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        $year = $request->input('year');
        if (!$year) {
            return redirect()->route('alumni.index')->with('error', 'Please select a year.');
        }
        
        $data['page'] = 'alumni/restore_by_year_progress';
        $data['title'] = 'Restore Alumni by Year';
        $data['year'] = $year;
        return view('main', $data);
    }

    /**
     * Stream the progress of restoring alumni by year
     */
    public function restoreByYearStream(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $year = $request->input('year');
        if (!$year) {
            return response()->json(['error' => 'Year is required'], 400);
        }

        $response = new StreamedResponse(function() use ($year) {
            // Set headers for Server-Sent Events
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering
            
            // Set time and memory limits to prevent timeout
            set_time_limit(0); // Unlimited execution time
            ini_set('memory_limit', '512M'); // Increase memory limit
            ignore_user_abort(true); // Continue script even if user disconnects

            // Send initial status
            $this->sendSseMessage('status', [
                'message' => "Starting process to restore alumni from year {$year}..."
            ]);

            try {
                // Get all alumni records for the specified year
                $alumniQuery = Alumni::where('year', $year);
                $totalAlumni = $alumniQuery->count();
                $processedCount = 0;
                $successCount = 0;
                $errorCount = 0;

                if ($totalAlumni === 0) {
                    $this->sendSseMessage('finished', [
                        'message' => "No alumni found for year {$year}."
                    ]);
                    return;
                }

                $this->sendSseMessage('status', [
                    'message' => "Found {$totalAlumni} alumni from year {$year} to restore..."
                ]);

                // Get all alumni at once
                $allAlumni = $alumniQuery->get();
                
                $this->sendSseMessage('status', [
                    'message' => "Processing {$totalAlumni} alumni..."
                ]);
                
                foreach ($allAlumni as $alumni) {
                    // Add connection check
                    if (connection_aborted()) {
                        break;
                    }

                    try {
                        // Find the corresponding student record
                        $existingStudent = Student::where('username', $alumni->username)->first();
                        
                        if (!$existingStudent) {
                            $errorCount++;
                            $this->sendSseMessage('progress', [
                                'message' => "✗ No student record found for {$alumni->username}",
                                'progress' => round((++$processedCount / $totalAlumni) * 100)
                            ]);
                            continue;
                        }

                        // Update student status back to active (1)
                        $existingStudent->status = 1;
                        $existingStudent->updated_at = now();
                        $existingStudent->save();

                        // Update user account type back to Student
                        if ($existingStudent->user_id) {
                            DB::table('users')
                                ->where('id', $existingStudent->user_id)
                                ->update(['accType' => 'Student', 'updated_at' => now()]);
                        }

                        // Delete the alumni record
                        $alumni->delete();
                        
                        $successCount++;
                        $this->sendSseMessage('progress', [
                            'message' => "✓ Restored {$alumni->username} - {$alumni->fullname}",
                            'progress' => round((++$processedCount / $totalAlumni) * 100)
                        ]);

                    } catch (\Exception $e) {
                        $errorCount++;
                        \Log::error('Error restoring alumni by year', [
                            'username' => $alumni->username ?? 'unknown',
                            'year' => $year,
                            'error' => $e->getMessage()
                        ]);
                        $this->sendSseMessage('progress', [
                            'message' => "✗ Error restoring {$alumni->username}: " . $e->getMessage(),
                            'progress' => round((++$processedCount / $totalAlumni) * 100)
                        ]);
                    }

                    // Flush output to send messages immediately
                    if (ob_get_level()) ob_flush();
                    flush();
                    
                    // Small delay to prevent overwhelming the connection
                    usleep(10000); // 10ms delay
                }

                // Send final status
                $finalMessage = "Restore completed! Success: {$successCount}, Errors: {$errorCount}";
                $this->sendSseMessage('finished', [
                    'message' => $finalMessage
                ]);

            } catch (\Exception $e) {
                $this->sendSseMessage('finished', [
                    'message' => 'Process failed: ' . $e->getMessage()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Show the restore alumni to students progress page
     */
    public function showRestoreAlumniProgress()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        $data['page'] = 'alumni/restore_alumni_progress';
        $data['title'] = 'Restore Alumni to Students';
        return view('main', $data);
    }

    /**
     * Stream the progress of restoring alumni to students
     */
    public function restoreAlumniStream()
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $response = new StreamedResponse(function() {
            // Set headers for Server-Sent Events
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering
            
            // Set time limits to prevent timeout
            set_time_limit(0); // Unlimited execution time
            ignore_user_abort(true); // Continue script even if user disconnects

            // Send initial status
            $this->sendSseMessage('status', [
                'message' => 'Starting process to restore alumni to students...'
            ]);

            try {
                // Get all alumni records
                $alumniQuery = Alumni::with(['facultys', 'departments', 'programs']);

                $totalAlumni = $alumniQuery->count();
                $processedCount = 0;
                $successCount = 0;
                $errorCount = 0;

                if ($totalAlumni === 0) {
                    $this->sendSseMessage('status', [
                        'message' => 'No alumni records found to restore.'
                    ]);
                    $this->sendSseMessage('finished', [
                        'message' => 'Process completed. No alumni to restore.'
                    ]);
                    return;
                }

                $this->sendSseMessage('status', [
                    'message' => "Found {$totalAlumni} alumni records to restore..."
                ]);

                // Process alumni in smaller batches to prevent memory issues
                $batchSize = 50;
                $alumniQuery->orderBy('username')->chunk($batchSize, function($alumniRecords) use (&$processedCount, &$successCount, &$errorCount, $totalAlumni) {
                    foreach ($alumniRecords as $alumni) {
                        try {
                            // Check if student record exists
                            $existingStudent = Student::where('username', $alumni->username)->first();

                            if (!$existingStudent) {
                                $errorCount++;
                                $processedCount++;
                                $this->sendSseMessage('progress', [
                                    'message' => "✗ Skipped {$alumni->username}: No corresponding student record found",
                                    'progress' => round(($processedCount / $totalAlumni) * 100)
                                ]);
                                continue;
                            }

                            // Update student status back to 1 (active)
                            $existingStudent->status = 1;
                            $existingStudent->updated_at = now();
                            $existingStudent->save();

                            // Update user account type back to Student
                            if ($existingStudent->user_id) {
                                DB::table('users')
                                    ->where('id', $existingStudent->user_id)
                                    ->update(['accType' => 'Student', 'updated_at' => now()]);
                            }

                            // Delete the alumni record
                            $alumni->delete();

                            $this->sendSseMessage('progress', [
                                'message' => "✓ Restored {$alumni->username} to student status and removed from alumni",
                                'progress' => round(($processedCount / $totalAlumni) * 100)
                            ]);

                            $successCount++;
                            $processedCount++;

                        } catch (\Exception $e) {
                            $errorCount++;
                            $processedCount++;
                            $this->sendSseMessage('progress', [
                                'message' => "✗ Error processing {$alumni->username}: " . $e->getMessage(),
                                'progress' => round(($processedCount / $totalAlumni) * 100)
                            ]);
                        }

                        // Flush output to send messages immediately
                        if (ob_get_level()) ob_flush();
                        flush();
                        
                        // Small delay to prevent overwhelming the connection
                        usleep(10000); // 10ms delay
                    }
                });

                // Send final status
                $finalMessage = "Process completed! Success: {$successCount}, Errors: {$errorCount}";
                $this->sendSseMessage('finished', [
                    'message' => $finalMessage
                ]);

            } catch (\Exception $e) {
                $this->sendSseMessage('finished', [
                    'message' => 'Process failed: ' . $e->getMessage()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Send Server-Sent Event message
     */
    private function sendSseMessage($event, $data)
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        
        if (ob_get_level()) ob_flush();
        flush();
    }
}
