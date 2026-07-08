<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Student;
use App\Models\Program;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApproveResultsController extends Controller
{
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
        $this->table = 'results';
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $session = DB::table('session')->where('status', '1')->value('title');
        if ($req->has('_token')) {
            $data = $req->all();
            $ses = $data['session'];
            $session = $ses;
            unset($data['_token']);
            unset($data['faculty']);
            unset($data['department']);
            $filteredData = array_filter($data);
            $query = DB::table($this->table);
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            if (session('appointment') != 'HOD') {
                $data['data'] = $query->select('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->groupBy('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->get();
            }
            $data['sessions'] = $ses;
        } else {
            $data['data'] = DB::table($this->table)->where(['status' => '2'])->get();
            $data['sessions'] = $session;

            if (session('appointment') == 'DEAN') {
                $lecturerCourses = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->pluck('course');

                $data['data'] = DB::table($this->table)->whereIn('code', $lecturerCourses)->select('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->groupBy('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->get();
            }
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        if (session('accType') == 'Staff' && session('appointment') != 'DEAN' && session('appointment') != 'VC' && session('unit') != 'COURSE SYSTEM') {
            $data['sessions'] = $session;
            if (session('appointment') == 'HOD') {
                $lecturerCourses = DB::table('course')->where(['department' => session('department')])->select('code')->pluck('code');
            } else {
                $lecturerCourses = DB::table('course_allocation')->where(['username' => session('username')])->select('course')->orderBy('course', 'ASC')->pluck('course');
            }
            $data['data'] = DB::table($this->table)->whereIn('code', $lecturerCourses)->where('session', $session)->select('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->groupBy('code', 'session', 'semester', 'approve', 'lecturer', 'updated_at')->get();
        }

        if (strtoupper(session('appointment')) == 'VC' || session('accType') == 'Admin') {
            $getPrograms = DB::table('results')->select('program')->where(['approve' => 'cs'])->groupBy('program')->pluck('program');
            $data['getDept'] = Program::whereIn('code', $getPrograms)->select('department')->groupBy('department')->get();
        } else {
            $getPrograms = DB::table('results')->select('program')->where(['approve' => 'cs'])->groupBy('program')->limit(1)->pluck('program');
            $data['getDept'] = Program::whereIn('code', $getPrograms)->select('department')->groupBy('department')->limit(1)->get();
        }

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
        $code = $datas['code'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $check = 0;
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester, 'approve' => $req->approve])->select('approve')->limit(1)->get();
        // dd($req -> approve);
        foreach ($data as $row) {
            $check = 1;
            if ($req->approve == 'system') {
                $status = 'Submitted to Department';
                $records['approve'] = 'lecturer';
                $records['lecturer'] = session('username');
                // dd('Hi');
            } else if ($req->approve == 'lecturer') {
                $status = 'Submitted to Faculty';
                $records['approve'] = 'hod';
                $records['hod'] = session('username');
            } else if ($req->approve == 'hod') {
                $status = 'Submitted to Course System';
                $records['approve'] = 'dean';
                $records['dean'] = session('username');
            } else if ($req->approve == 'dean') {
                $status = 'Submitted to Senate';
                $records['approve'] = 'cs';
                $records['cs'] = session('username');
            } else if ($req->approve == 'cs') {
                $status = 'Approved!!!';
                $records['approve'] = 'vc';
                $records['vc'] = session('username');
                $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('username', 'unit', 'point', 'ugp', 'total', 'grade', 'session', 'semester', 'level', 'remark')->get();
                foreach ($data as $row) {
                    $record['total'] = $row->total;
                    $record['grade'] = $row->grade;
                    $record['status'] = $row->remark;
                    $record['unit'] = $row->unit;
                    $record['point'] = $row->point;
                    $record['ugp'] = $row->ugp;

                    // $current = $row -> current_level;
                    // $final = $row -> final_level;

                    $current = 2;
                    $final = 1;
                    // echo $row -> username;
                    // die;
                    DB::table('student_course_registration')->where(['code' => $code, 'session' => $row->session, 'username' => $row->username])->update($record);
                    $id = DB::table('student_course_registration')->where(['username' => $row->username, 'status' => 'awaiting'])->select('id')->value('id');
                    if ($id > 0) {
                    } else {
                        if ($current == $final) {
                        } else {
                            $unit = 0;
                            $ugp = 0;
                            $cgpa = 0;
                            $carry = '';
                            $statuss = '';
                            $class = '';
                            $f = 0;
                            $flag = 0;
                            $p = 0;
                            $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
                            foreach ($reg as $result) {
                                $unit = $unit + $result->unit;
                                $ugp = $ugp + $result->ugp;
                                if ($result->grade == 'F') {
                                    $carry = $carry . ' ' . $result->code;
                                    $f++;
                                }
                            }
                            $cgpa = $ugp / $unit;
                            if ($f > 6 || $cgpa < 1.0) {
                            }
                        }
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        }
        if ($check == 0) {
            return redirect()->back()->with('error', 'Something Went Wrong with ' . $code . ' of ' . $session . ' ' . $semester);
        }

        $records['updated_at'] = NOW();
        DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester, 'approve' => $req->approve])->update($records);
        return redirect()->back()->with('success', 'Results ' . $status);
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $code = $datas['code'];
        $records['comment'] = $datas['comment'];
        // $session = $datas['session'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->select('approve')->limit(1)->get();
        // dd($datas);
        foreach ($data as $row) {
            if ($req->approve == 'system') {
                $status = 'Rejeted Back to Lecturer';
                $records['approve'] = 'system';
                $records['lecturer'] = session('username');
            } else if ($req->approve == 'lecturer') {
                $status = 'Rejeted Back to Lecturer';
                $records['approve'] = 'system';
                $records['hod'] = session('username');
            } else if ($req->approve == 'hod') {
                $status = 'Rejeted Back to Department';
                $records['approve'] = 'lecturer';
                $records['dean'] = session('username');
            } else if ($req->approve == 'dean') {
                $status = 'Rejeted Back to Faculty';
                $records['approve'] = 'hod';
                $records['cs'] = session('username');
            } else if ($req->approve == 'cs') {
                $status = 'Rejeted!!!';
                $records['approve'] = 'dean';
                $records['vc'] = session('username');
            } else {
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        }

        $records['updated_at'] = NOW();
        try {
            DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->update($records);
            dd($code,$session,$semester,$records);
            return redirect()->back()->with('success', 'Results ' . $status);
        } catch (QueryException $e) {
            Log::error('Error updating results: ' . $e->getMessage(), [
                'code' => $code,
                'session' => $session,
                'semester' => $semester,
                'exception' => $e
            ]);
            dd($e);
            return redirect()->back()->with('error', 'Failed to update results. Please try again.');
        }
    }

    public function approveDeptResultss(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $status = $datas['status'];
        $session = '2023/2024';
        $dept = $datas['dept'];
        $getPrograms = DB::table('program')->where(['department' => $dept])->select('code')->pluck('code');

        if ($status == 'approved') {
            $records['approve'] = 'vc';
            $records['vc'] = session('username');
        } elseif ($status == 'rejected') {
            $records['approve'] = 'dean';
            $records['cs'] = session('username');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
        DB::table('results')->whereIn('program', $getPrograms)->where(['session' => $session, 'approve' => 'cs'])->update($records);
        return redirect()->back()->with('success', 'Results ' . $status);
    }

    public function approveDeptResults(Request $req)
    {
        if (!session()->has('log')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $data = $req->all();
            $status = $data['status'];
            $dept = $data['dept'];
            $session = '2023/2024';

            $programs = DB::table('program')->where('department', $dept)->pluck('code')->toArray();
            $totalResults = DB::table('results')
                ->whereIn('program', $programs)
                ->where('approve', 'cs')
                ->count();

            session(['processed' => 0, 'total' => $totalResults]);

            // Log::info('Processing started for department: ' . $dept, [
            //     'status' => $status,
            //     'total_results' => $totalResults,
            // ]);

            return response()->json([
                'status' => 'processing',
                'progress' => 0,
                'nextBatchUrl' => route('process-next-batch', ['dept' => $dept, 'status' => $status])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in approveDeptResults: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    public function processNextBatch(Request $req)
    {
        $dept = $req->dept;
        $status = $req->status;
        $session = '2023/2024';

        try {
            $programs = DB::table('program')->where('department', $dept)->pluck('code')->toArray();
            $batchSize = 100;
            $processed = session('processed', 0);
            $total = session('total', 0);

            $results = DB::table('results')
                ->whereIn('program', $programs)
                ->where('approve', 'cs')
                ->skip($processed)
                ->take($batchSize)
                ->get();

            foreach ($results as $result) {
                $records = [];
                if ($status == 'Approved') {
                    $records['approve'] = 'vc';
                    $records['apply'] = 0;
                    $records['vc'] = session('username');
                } elseif ($status == 'Rejected') {
                    $records['approve'] = 'dean';
                    $records['cs'] = session('username');
                }

                DB::table('results')
                    ->where('id', $result->id)
                    ->update($records);
            }

            $processed += count($results);
            session(['processed' => $processed]);

            // Ensure $processed does not exceed $total
            if ($processed > $total) {
                $processed = $total;
                session(['processed' => $processed]);
            }

            $progress = ($total > 0) ? ($processed / $total) * 100 : 0;
            $progress = (int) $progress;

            // Log::info('Batch processed for department: ' . $dept, [
            //     'processed' => $processed,
            //     'total' => $total,
            //     'progress' => $progress,
            // ]);

            if ($processed >= $total) {
                session()->forget(['processed', 'total']);
                return response()->json(['status' => 'complete', 'progress' => 100]);
            }

            return response()->json([
                'status' => 'processing',
                'progress' => $progress,
                'nextBatchUrl' => route('process-next-batch', ['dept' => $dept, 'status' => $status])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in processNextBatch: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    // generate status mehtod
    public function generateStatuss(Request $req)
    {
        // Input parameters
        $session = '2023/2024';  // Replace with your current session
        $lvl = $req->input('level');
        $program = $req->input('program');

        // Validate inputs
        if (empty($lvl) || empty($program)) {
            return response()->json(['error' => 'Level and program are required'], 400);
        }

        // Fetch session history
        $sessionHistory = DB::table('session_history')
            ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
            ->get();

        // Initialize counters
        $proceedWithoutCarryover = 0;
        $proceedWithCarryover = 0;
        $repeatProbation = 0;
        $pending = 0;

        // Initialize results array
        $rows = [];

        // Helper function to format course lists
        $formatCourses = function ($courses) {
            if (count($courses) === 0) {
                return '';
            }
            if (count($courses) === 1) {
                return $courses[0];
            }
            return implode(', ', array_slice($courses, 0, -1)) . ' and ' . end($courses);
        };

        // Process each student
        foreach ($sessionHistory as $record) {
            $studentId = $record->username;
            $studentName = DB::table('students')->where('username', $studentId)->value('fullname');
            $registrations = DB::table('student_course_registration')
                ->where('username', $studentId)
                ->orderBy('username', 'ASC')
                ->get();

            $totalUnits = 0;
            $totalGradePoints = 0;
            $failedCourses = [];
            $pendingCourses = [];
            $failedCoursesCount = 0;
            $pendingCoursesCount = 0;

            foreach ($registrations as $registration) {
                $totalUnits += $registration->unit;

                if ($registration->status === 'awaiting') {
                    $pendingCoursesCount++;
                    $pendingCourses[] = codeMod($registration->code);
                } else {
                    $totalGradePoints += $registration->ugp;
                    if ($registration->grade === 'F') {
                        $failedCoursesCount++;
                        $failedCourses[] = $registration;  // Store the entire registration object
                    }
                }
            }

            $cgpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

            // Determine status and remarks
            $status = 'Unknown';
            $remarks = '';

            // $f == 0 && $p == 0 && $cgpa > 1.0
            // $cgpa > 1.0 && ($p + $f) < 7
            // $f > 6 || ($cgpa < 1.0 && $p == 0 && $ugp != 0)
            // ($f + $p) > 6

            if ($failedCoursesCount == 0 && $pendingCoursesCount == 0 && $cgpa > 1.0) {
                $status = 'Proceed';
                $remarks = $pendingCoursesCount > 0 ? "F: Nil\nP[$pendingCoursesCount]: " . $formatCourses($pendingCourses) : 'F: Nil';
                $proceedWithoutCarryover++;
            } elseif ($cgpa > 1.0 && ($failedCoursesCount + $pendingCoursesCount) < 7) {
                $status = 'Proceed';
                $remarks = $pendingCoursesCount > 0 ? "F[$failedCoursesCount]: " . $formatCourses($failedCourses) . "\nP[$pendingCoursesCount]: " . $formatCourses($pendingCourses) : "F[$failedCoursesCount]: " . $formatCourses($failedCourses);
                $proceedWithCarryover++;
            } elseif ($failedCoursesCount > 6 || ($cgpa < 1.0 && $pendingCoursesCount == 0 && $totalGradePoints != 0)) {
                $status = 'Repeat';
                $remarks = $pendingCoursesCount > 0 ? "F[$failedCoursesCount]: " . $formatCourses($failedCourses) . "\nP[$pendingCoursesCount]: " . $formatCourses($pendingCourses) : "F[$failedCoursesCount]: " . $formatCourses($failedCourses);
                $repeatProbation++;
            } elseif (($failedCoursesCount + $pendingCoursesCount) > 6) {
                $status = 'Pending';
                $remarks = $pendingCoursesCount > 0 ? "F[$failedCoursesCount]: " . $formatCourses($failedCourses) . "\nP[$pendingCoursesCount]: " . $formatCourses($pendingCourses) : "F[$failedCoursesCount]: " . $formatCourses($failedCourses);
                $pending++;
            }

            // Add row to results
            $rows[] = [
                'S/No' => count($rows) + 1,
                'ID No' => $studentId,
                'Name' => $studentName,
                'Cum. Unit' => $totalUnits,
                'Cum. Product' => $totalGradePoints,
                'CGPA' => number_format((float) $cgpa, 2, '.', ''),
                'Status' => $status,
                'Remarks' => $remarks,
            ];

            // Prepare data for the next session
            $this->updateNextSession($studentId, $program, $session, $lvl, $totalUnits, $totalGradePoints, $cgpa, $status);

            // Register failed courses for the next session
            if ($failedCoursesCount > 0) {
                $this->registerFailedCoursesForNextSession($studentId, $failedCourses, $session, $lvl);
            }
        }

        // Output results
        echo json_encode($rows, JSON_PRETTY_PRINT);

        // Generate summary
        $summary = [
            'Proceed without carryover' => $proceedWithoutCarryover,
            'Proceed with carryover' => $proceedWithCarryover,
            'Repeat (probation)' => $repeatProbation,
            'Pending' => $pending,
            'Total' => $proceedWithoutCarryover + $proceedWithCarryover + $repeatProbation + $pending,
        ];

        echo "\nSummary:\n";
        echo json_encode($summary, JSON_PRETTY_PRINT);
    }

    public function generateStatus_(Request $req)
    {
        // Input parameters
        $session = '2023/2024';  // Replace with your current session
        $lvl = $req->input('level');
        $program = $req->input('program');

        // Validate inputs
        if (empty($lvl) || empty($program)) {
            return response()->json(['error' => 'Level and program are required'], 400);
        }

        // Get the starting offset from the query parameter (default to 0 if not provided)
        $offset = $req->input('offset', 0);
        $limit = 100;  // Number of records to process per request

        try {
            // Fetch the total number of students to process
            $totalStudents = DB::table('session_history')
                ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
                ->count();

            // If there are no students to process, return a completion message
            if ($totalStudents === 0) {
                return 'No students to process!';
            }

            // Fetch the next batch of students
            $students = DB::table('session_history')
                ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
                ->skip($offset)
                ->take($limit)
                ->get();

            // If no more students are left, return a completion message
            if ($students->isEmpty()) {
                return 'All students processed!';
            }

            // Initialize counters
            $proceedWithoutCarryover = 0;
            $proceedWithCarryover = 0;
            $repeatProbation = 0;
            $pending = 0;

            // Helper function to format course lists
            $formatCourses = function ($courses) {
                if (count($courses) === 0) {
                    return '';
                }
                if (count($courses) === 1) {
                    return $courses[0];
                }
                return implode(', ', array_slice($courses, 0, -1)) . ' and ' . end($courses);
            };

            // Process each student in the current batch
            foreach ($students as $record) {
                $studentId = $record->username;
                $level = $record->level;
                $session_history = $record->status;
                $registrations = DB::table('student_course_registration')
                    ->where(['username' => $studentId, 'session' => $session])
                    ->orderBy('username', 'ASC')
                    ->get();

                $totalUnits = 0;
                $totalGradePoints = 0;
                $failedCourses = [];
                $pendingCourses = [];
                $failedCoursesCount = 0;
                $pendingCoursesCount = 0;

                foreach ($registrations as $registration) {
                    if ($registration->level == $level && strtoupper($session_history) != 'REPEAT') {
                        $totalUnits += $registration->unit;
                    }

                    if ($registration->status === 'awaiting') {
                        $pendingCoursesCount++;
                        $pendingCourses[] = $registration->code;
                    } else {
                        $totalGradePoints += $registration->ugp;
                        if ($registration->grade === 'F') {
                            $failedCoursesCount++;
                            $failedCourses[] = $registration;
                        }
                    }
                }

                $cgpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

                // Determine status and remarks
                $status = 'Unknown';
                $remarks = '';

                if ($failedCoursesCount == 0 && $pendingCoursesCount == 0 && $cgpa > 1.0) {
                    $status = 'Proceed';
                } elseif ($cgpa > 1.0 && ($failedCoursesCount + $pendingCoursesCount) < 7) {
                    $status = 'Proceed';
                } elseif ($failedCoursesCount > 6 || ($cgpa < 1.0 && $pendingCoursesCount == 0 && $totalGradePoints != 0)) {
                    $status = 'Repeat';
                } elseif (($failedCoursesCount + $pendingCoursesCount) > 6) {
                    $status = 'Pending';
                }

                // Prepare data for the next session
                $this->updateNextSession($studentId, $program, $session, $lvl, $totalUnits, $totalGradePoints, $cgpa, $status);

                // Register failed courses for the next session
                if ($failedCoursesCount > 0) {
                    $this->registerFailedCoursesForNextSession($studentId, $failedCourses, $session, $lvl);
                }
            }

            // Calculate the number of students processed so far
            $processedStudents = $offset + $students->count();
            $remainingStudents = $totalStudents - $processedStudents;

            // Output progress in a single line
            echo "Processed: $processedStudents / $totalStudents | Remaining: $remainingStudents<br>";

            // Calculate the new offset for the next batch
            $newOffset = $offset + $limit;

            // If all students are processed, return a completion message
            if ($processedStudents >= $totalStudents) {
                return 'All students processed!';
            }

            // Return JavaScript to handle the delay and redirect
            return <<<HTML
                <script>
                    // Display a message indicating the delay
                    document.write("Waiting 1 second before processing the next batch...<br>");

                    // Wait for 5 seconds (5000 milliseconds) before redirecting
                    setTimeout(function() {
                        window.location.href = "/generate-status?level=$lvl&program=$program&offset=$newOffset";
                    }, 1000); // 5000ms = 5 seconds
                </script>
                HTML;
        } catch (\Exception $e) {
            // Handle exceptions
            \Log::error('Error generating status: ' . $e->getMessage());
            dd($e->getMessage());
            // return response()->json(['error' => 'An error occurred while processing the request.'], 500);
        }
    }

    public function generateStatus(Request $req)
    {
        // Input parameters
        $session = '2024/2025';  // Replace with your current session
        $lvl = $req->input('level');
        $faculty = $req->input('faculty');
        $program = $req->input('program');

        // Validate inputs
        if (empty($lvl) || empty($program)) {
            return response()->json(['error' => 'Level and program are required'], 400);
        }

        // Get the starting offset from the query parameter (default to 0 if not provided)
        $offset = $req->input('offset', 0);
        $limit = 100;  // Number of records to process per request

        try {
            // Fetch the total number of students to process
            $totalStudents = DB::table('session_history')
                ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
                ->count();

            // If there are no students to process, return a completion message
            if ($totalStudents === 0) {
                return 'No students to process!';
            }

            // Fetch the next batch of students
            $students = DB::table('session_history')
                ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
                ->skip($offset)
                ->take($limit)
                ->get();

            // If no more students are left, return a completion message
            if ($students->isEmpty()) {
                return 'All students processed!';
            }

            // Initialize counters
            $proceedWithoutCarryover = 0;
            $proceedWithCarryover = 0;
            $repeatProbation = 0;
            $pending = 0;

            // Helper function to format course lists
            $formatCourses = function ($courses) {
                if (count($courses) === 0) {
                    return '';
                }
                if (count($courses) === 1) {
                    return $courses[0];
                }
                return implode(', ', array_slice($courses, 0, -1)) . ' and ' . end($courses);
            };

            // Process each student in the current batch
            foreach ($students as $record) {
                $studentId = $record->username;
                $level = $record->level;
                $sessionHistoryStatus = $record->status;

                // Fetch course registrations for the student
                $registrations = DB::table('student_course_registration')
                    ->where(['username' => $studentId, 'session' => $session])
                    ->orderBy('username', 'ASC')
                    ->get();

                $totalUnits = 0;
                $totalGradePoints = 0;
                $failedCourses = [];
                $pendingCourses = [];
                $failedCoursesCount = 0;
                $pendingCoursesCount = 0;

                foreach ($registrations as $registration) {
                    if ($registration->level == $level && strtoupper($sessionHistoryStatus) != 'REPEAT') {
                        $totalUnits += $registration->unit;
                    }

                    if ($registration->status === 'awaiting') {
                        $pendingCoursesCount++;
                        $pendingCourses[] = $registration->code;
                    } else {
                        $totalGradePoints += $registration->ugp;
                        if ($registration->grade === 'F') {
                            $failedCoursesCount++;
                            $failedCourses[] = $registration;
                        }
                    }
                }

                $cgpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

                // Determine status and remarks
                $status = 'Unknown';
                $remarks = '';

                $getFaculty = $faculty;
                $baseCGPA = 0.99;
                if($getFaculty == 'PHARM' || $getFaculty == 'VET'){
                    $baseCGPA = 2.39;
                }

                if ($failedCoursesCount == 0 && $pendingCoursesCount == 0 && $cgpa > $baseCGPA) {
                    $status = 'Proceed';
                } elseif ($cgpa > $baseCGPA && ($failedCoursesCount + $pendingCoursesCount) < 7) {
                    $status = 'Proceed';
                } elseif ($failedCoursesCount > 6 || ($cgpa < $baseCGPA && $pendingCoursesCount == 0 && $totalGradePoints != 0)) {
                    $status = 'Repeat';
                } elseif (($failedCoursesCount + $pendingCoursesCount) > 6) {
                    $status = 'Pending';
                }

                // Prepare data for the next session
                $this->updateNextSession($studentId, $program, $session, $lvl, $totalUnits, $totalGradePoints, $cgpa, $status);

                // Register failed courses for the next session
                if ($failedCoursesCount > 0) {
                    $this->registerFailedCoursesForNextSession($studentId, $failedCourses, $session, $lvl);
                }

                // Register next-level courses for proceeding students
                if ($status == 'Proceed') {
                    $this->registerNextLevelCourses($studentId, $req->program, $session, $lvl);
                }
            }

            // Calculate the number of students processed so far
            $processedStudents = $offset + $students->count();
            $remainingStudents = $totalStudents - $processedStudents;
            $newOffset = $offset + $limit;
            $progressPercent = round(($processedStudents / $totalStudents) * 100);

            // If all students are processed, return a completion message
            if ($processedStudents >= $totalStudents) {
                return "
                    <html>
                    <head><title>Generate Status Complete</title></head>
                    <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                        <h3 style='color: #28a745;'>✓ Generate Status Complete</h3>
                        <p><strong>Program:</strong> {$program} | <strong>Level:</strong> {$lvl}</p>
                        <p>Processed: {$totalStudents} / {$totalStudents} students</p>
                        <div style='width: 300px; margin: 20px auto; background: #eee; border-radius: 5px;'>
                            <div style='width: 100%; background: #28a745; height: 20px; border-radius: 5px;'></div>
                        </div>
                        <p style='color: #666;'>Redirecting to Status Page in 3 seconds...</p>
                        <script>
                            setTimeout(function() {
                                window.location.href = '/status';
                            }, 3000);
                        </script>
                    </body>
                    </html>
                ";
            }

            // Return styled progress page with auto-redirect
            return "
                <html>
                <head><title>Generating Status...</title></head>
                <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                    <h3>Generating Student Status</h3>
                    <p><strong>Program:</strong> {$program} | <strong>Level:</strong> {$lvl} | <strong>Session:</strong> {$session}</p>
                    <p>Processed: {$processedStudents} / {$totalStudents} students</p>
                    <p>Remaining: {$remainingStudents}</p>
                    <div style='width: 300px; margin: 20px auto; background: #eee; border-radius: 5px;'>
                        <div style='width: {$progressPercent}%; background: #ffc107; height: 20px; border-radius: 5px;'></div>
                    </div>
                    <p><small>Please wait...</small></p>
                    <script>
                        setTimeout(function() {
                            window.location.href = '/generate-status?level={$lvl}&faculty={$faculty}&program={$program}&offset={$newOffset}';
                        }, 500);
                    </script>
                </body>
                </html>
            ";
        } catch (\Exception $e) {
            // Handle exceptions
            \Log::error('Error generating status: ' . $e->getMessage());
            dd($e->getMessage());
        }
    }

    private function updateNextSession(string $studentId, string $program, string $currentSession, string $currentLevel, float $totalUnits, float $totalGradePoints, float $cgpa, string $status)
    {
        // Increment the session
        $nextSession = $this->incrementSession($currentSession);

        // Increment the level only for students who are proceeding and repeat
        $nextLevel = $status === 'Proceed' || $status === 'Repeat' ? $this->incrementLevel($currentLevel) : $currentLevel;

        DB::table('student_course_registration')
                        ->where([
                            'username' => $studentId,
                            'session' => $nextSession,
                        ])
                        ->delete();

        // get total units and grade points for other sessions from the session_history table and add them to the current total units and grade points to get new cgpa as double
        $totalUnits = DB::table('session_history')->where('username', $studentId)->where('session', '<=', $currentSession)->sum('total_unit') + $totalUnits;
        $totalGradePoints = DB::table('session_history')->where('username', $studentId)->where('session', '<=', $currentSession)->sum('product') + $totalGradePoints;
        $cgpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

        // Prepare data for the next session
        $sr['username'] = $studentId;
        $sr['program'] = $program;
        $sr['session'] = $nextSession;  // Updated session
        $sr['level'] = $nextLevel;  // Incremented level (or unchanged for Repeat/Pending)
        $sr['total_unit'] = $totalUnits;  // Carry forward total units
        $sr['product'] = $totalGradePoints;  // Carry forward grade points
        $sr['cgpa'] = $cgpa;  // Carry forward CGPA
        $sr['status'] = $status;  // Store the status for the next session

        $lvl['level'] = $nextLevel;
        DB::table('students')->where(['username' => $studentId])->update($lvl);

        try {
            // Insert the new session record
            DB::table('session_history')->insert($sr);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error (if the record already exists)
            if ($e->errorInfo[1] == 1062) {  // Duplicate entry error code
                DB::table('session_history')
                    ->where(['session' => $nextSession, 'username' => $studentId])
                    ->update($sr);
            } else {
                // Log other exceptions
                \Log::error('Error inserting session history: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Handle other exceptions
            \Log::error('General error inserting session history: ' . $e->getMessage());
        }
    }

    private function registerNextLevelCourses(string $studentId, string $program, string $currentSession, string $currentLevel)
    {
        // Increment the session
        $nextSession = $this->incrementSession($currentSession);

        // Increment the level
        $nextLevel = $this->incrementLevel($currentLevel);

        // Get student's structure_id based on entry year
        $studentModel = Student::where('username', $studentId)->first();
        $structureId = $studentModel ? $studentModel->structure_id : null;

        $coreCourses = DB::table('program_course_registration')
            ->where('program', $program)
            ->where('structure_id', $structureId)
            ->where('level', $nextLevel)
            ->where('type', 'CORE')
            ->get();

        $electiveCourses = DB::table('program_course_registration as pr1')
            ->select('pr1.*')
            ->where('pr1.program', $program)
            ->where('pr1.structure_id', $structureId)
            ->where('pr1.level', $nextLevel)
            ->where('pr1.type', 'ELECTIVE')
            ->join(
                DB::raw('(SELECT MIN(id) as min_id, level, semester, elective
                        FROM program_course_registration
                        WHERE program = "' . $program . '"
                        AND structure_id = ' . ($structureId ?? 'NULL') . '
                        AND level = ' . $nextLevel . '
                        AND type = "ELECTIVE"
                        GROUP BY level, semester, elective) as pr2'),
                function ($join) {
                    $join->on('pr1.id', '=', 'pr2.min_id');
                }
            )
            ->get();

        $data = $coreCourses->merge($electiveCourses);

        // Register courses for the student
        foreach ($data as $rows) {
            $codeLevel = DB::table('course')->where(['code' => $rows->code])->value('level');
            if ($codeLevel != $nextLevel) {
                $records['unit'] = 0;
            } else {
                $records['unit'] = $rows->unit;
            }

            $records['username'] = $studentId;
            $records['code'] = $rows->code;
            $records['type'] = $rows->type;
            $records['elective'] = $rows->elective;
            $records['semester'] = $rows->semester;
            $records['session'] = $nextSession;
            $records['level'] = $nextLevel;
            $records['updated_at'] = now();

            try {
                $records['created_at'] = now();
                // dd($rows);
                DB::table('student_course_registration')->insert($records);
            } catch (\Illuminate\Database\QueryException $e) {
                // dd($e);
                // Handle duplicate entry error (if the record already exists)
                if ($e->errorInfo[1] == 1062) {  // Duplicate entry error code

                    DB::table('student_course_registration')
                        ->where([
                            'username' => $studentId,
                            'code' => $rows->code,
                            'session' => $nextSession,
                        ])
                        ->update($records);
                    // dd($rows);
                } else {
                    // Log other exceptions
                    \Log::error('Error inserting course registration: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                // Handle other exceptions
                dd($e);
                \Log::error('General error inserting course registration: ' . $e->getMessage());
            }
        }
    }

    private function registerFailedCoursesForNextSession(string $studentId, array $failedCourses, string $currentSession, string $currentLevel)
    {
        // Increment the session
        $nextSession = $this->incrementSession($currentSession);

        // Increment the level only for students who are proceeding
        $nextLevel = $this->incrementLevel($currentLevel);

        // Insert or update records for the next session
        foreach ($failedCourses as $course) {
            $records['username'] = $studentId;
            $records['code'] = $course->code;
            $records['unit'] = $course->unit;
            $records['type'] = $course->type;
            $records['elective'] = $course->elective;
            $records['semester'] = $course->semester;
            $records['session'] = $nextSession;  // Updated session
            $records['level'] = $nextLevel;  // Incremented level

            try {
                // Insert the new course registration record
                DB::table('student_course_registration')->insert($records);
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle duplicate entry error (if the record already exists)
                if ($e->errorInfo[1] == 1062) {  // Duplicate entry error code
                    DB::table('student_course_registration')
                        ->where([
                            'username' => $studentId,
                            'code' => $course->code,
                            'session' => $nextSession,
                        ])
                        ->update($records);
                } else {
                    // Log other exceptions
                    \Log::error('Error inserting course registration: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                // Handle other exceptions
                \Log::error('General error inserting course registration: ' . $e->getMessage());
            }
        }
    }

    private function incrementSession(string $session): string
    {
        [$startYear, $endYear] = explode('/', $session);
        $nextStartYear = (int) $startYear + 1;
        $nextEndYear = (int) $endYear + 1;
        return "$nextStartYear/$nextEndYear";
    }

    private function incrementLevel(string $level): string
    {
        $levels = ['100', '200', '300', '400', '500'];
        $currentIndex = array_search($level, $levels);

        if ($currentIndex !== false && isset($levels[$currentIndex + 1])) {
            return $levels[$currentIndex + 1];
        }

        // If the student is already at the highest level, keep the level unchanged
        return $level;
    }

    public function updateMark(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $code = $datas['code'];
        $session = $datas['session'];
        $semester = $datas['semester'];
        $mark = $datas['mark'];
        $data = DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester])->whereBetween('total', [$datas['min'], $datas['max']])->select('id', 'ca', 'exam', 'total', 'username', 'total', 'program')->get();
        foreach ($data as $row) {
            $exam = (int) ($row->exam) + (int) $mark;
            if ($exam > 70) {
                $exam = 70;
            }

            $id = $row->username;
            // echo $id;
            // die;
            $getYear = explode('/', $id);
            $getYear = '20' . $getYear[0];

            $std_program = DB::table('students')->where(['username' => $id])->select('program')->value('program');
            $grading = DB::table('program_course_registration')->where(['code' => $code, 'program' => $std_program])->select('grading')->value('grading');

            $year = $id[0] . $id[1];

            $ca = $row->ca;
            $total = (int) $ca + (int) $exam;
            $unit = DB::table('course')->where(['code' => $code])->value('unit');
            $unit = (int) $unit;
            $remark = 'PASS';

            $gradings = DB::table('grading_system')
                ->where(function ($query) use ($getYear) {
                    $query
                        ->where(function ($innerQuery) use ($getYear) {
                            $innerQuery
                                ->where('to', '!=', 'current')
                                ->where('from', '<=', $getYear)
                                ->where('to', '>=', $getYear);
                        })
                        ->orWhere(function ($innerQuery) use ($getYear) {
                            $innerQuery
                                ->where('to', '=', 'current')
                                ->where('from', '<=', $getYear);
                        });
                })
                ->where('min_score', '<=', $total)
                ->where('max_score', '>=', $total)
                ->where('name', $grading)
                ->get();

            foreach ($gradings as $gs) {
                $grade = $gs->grade;
                $point = $unit * $gs->point;
                $remark = $gs->remark;
                $records['ca'] = $ca;
                $records['exam'] = $exam;
                $records['total'] = $total;
                $records['grade'] = $grade;
                $records['remark'] = $remark;
                $records['ugp'] = $point;
                $records['updated_at'] = NOW();
                // print_r($records['grade']);
                // die;
                if ($point > 0) {
                    $records['point'] = $point / $unit;
                } else {
                    $records['point'] = $point;
                }
            }
            DB::table('results')->where(['code' => $code, 'session' => $session, 'semester' => $semester, 'username' => $row->username])->update($records);
        }
        return redirect()->back()->with('success', 'Results Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();
        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    /**
     * Delete results by code
     *
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Count records matching the given code and session
     *
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function countRecords(Request $req)
    {
        try {
            if (!session()->has('log')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: User not logged in.'
                ], 401);
            }

            $code = $req->input('code');
            $session = $req->input('session');

            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Result code is required.'
                ], 400);
            }

            if (empty($session)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Session is required.'
                ], 400);
            }

            // Log the query being executed
            $query = DB::table($this->table)
                ->where('code', $code)
                ->where('session', $session);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            // Log the raw SQL and bindings
            \Log::info('Count query: ', [
                'sql' => $sql,
                'bindings' => $bindings,
                'table' => $this->table,
                'code' => $code,
                'session' => $session
            ]);

            $count = $query->count();

            // Log the count result
            \Log::info('Count result: ' . $count . ' records found for code: ' . $code . ' and session: ' . $session);

            return response()->json([
                'success' => true,
                'total_records' => $count,
                'code' => $code,
                'session' => $session,
                'debug' => [
                    'table' => $this->table,
                    'sql' => $sql,
                    'bindings' => $bindings
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error counting records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while counting records.'
            ], 500);
        }
    }

    /**
     * Delete results by code and session
     *
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteResult(Request $req)
    {
        try {
            if (!session()->has('log')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: User not logged in.'
                ], 401);
            }

            $code = $req->input('code');
            $session = $req->input('session');
            $semester = $req->input('semester');

            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Result code is required.'
                ], 400);
            }

            if (empty($session)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Session is required.'
                ], 400);
            }

            // Log the deletion attempt for debugging
            \Log::info("Attempting to delete results for code: $code and session: $session");

            // Log the query being executed
            $query = DB::table($this->table)
                ->where('code', $code)
                ->where('session', $session)
                ->where('semester', $semester);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            // Log the raw SQL and bindings
            \Log::info('Delete query: ', [
                'sql' => $sql,
                'bindings' => $bindings,
                'table' => $this->table,
                'code' => $code,
                'session' => $session,
                'semester' => $semester
            ]);

            // First check if any records exist
            $exists = $query->exists();

            if (!$exists) {
                \Log::warning("No records found to delete for code: $code and session: $session");
                return response()->json([
                    'success' => false,
                    'error' => "No results found with code: $code for session: $session",
                    'debug' => [
                        'table' => $this->table,
                        'sql' => $sql,
                        'bindings' => $bindings
                    ]
                ], 404);
            }

            // Get the records that will be deleted
            $recordsToDelete = $query->get();
            \Log::info('Records to be deleted:', $recordsToDelete->toArray());

            // Perform the deletion
            $deleted = $query->delete();

            \Log::info("Successfully deleted $deleted record(s) for code: $code and session: $session");

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deleted result(s) for code: $code in session: $session",
                'deleted_count' => $deleted,
                'debug' => [
                    'table' => $this->table,
                    'sql' => $sql,
                    'bindings' => $bindings
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting results: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting results.'
            ], 500);
        }
    }

    /**
     * Update result status by code, session, and semester
     *
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateResultStatus(Request $req)
    {
        try {
            if (!session()->has('log')) {
                return response()->json(['success' => false, 'error' => 'Unauthorized: User not logged in.'], 401);
            }

            $code = $req->input('code');
            $session = $req->input('session');
            $semester = $req->input('semester');
            $status = $req->input('status');

            if (empty($code)) {
                return response()->json(['success' => false, 'error' => 'Result code is required.'], 400);
            }

            if (empty($session)) {
                return response()->json(['success' => false, 'error' => 'Session is required.'], 400);
            }

            if (empty($semester)) {
                return response()->json(['success' => false, 'error' => 'Semester is required.'], 400);
            }

            if (empty($status)) {
                return response()->json(['success' => false, 'error' => 'Status is required.'], 400);
            }

            // Validate status is one of the allowed values
            $allowedStatuses = ['system', 'lecturer', 'hod', 'dean', 'cs', 'vc'];
            if (!in_array($status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid status value. Must be one of: ' . implode(', ', $allowedStatuses)
                ], 400);
            }

            // Log the update attempt for debugging
            \Log::info("Attempting to update result status for code: $code, session: $session, semester: $semester, status: $status");

            // Log the query being executed
            $query = DB::table($this->table)
                ->where('code', $code)
                ->where('session', $session)
                ->where('semester', $semester);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            // Log the raw SQL and bindings
            \Log::info('Update status query: ', [
                'sql' => $sql,
                'bindings' => $bindings,
                'table' => $this->table,
                'code' => $code,
                'session' => $session,
                'semester' => $semester,
                'status' => $status
            ]);

            // First check if any records exist
            $exists = $query->exists();

            if (!$exists) {
                \Log::warning("No records found to update for code: $code, session: $session, semester: $semester");
                return response()->json([
                    'success' => false,
                    'error' => "No results found with code: $code for session: $session and semester: $semester",
                    'debug' => [
                        'table' => $this->table,
                        'sql' => $sql,
                        'bindings' => $bindings
                    ]
                ], 404);
            }

            // Get the records that will be updated
            $recordsToUpdate = $query->get();
            \Log::info('Records to be updated:', $recordsToUpdate->toArray());

            // Perform the update
            $updated = $query->update(['approve' => $status]);

            \Log::info("Successfully updated $updated record(s) for code: $code, session: $session, semester: $semester to status: $status");

            return response()->json([
                'success' => true,
                'message' => "Successfully updated $updated result(s) for code: $code in session: $session, semester: $semester to status: $status",
                'updated_count' => $updated,
                'debug' => [
                    'table' => $this->table,
                    'sql' => $sql,
                    'bindings' => $bindings
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating result status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating result status: ' . $e->getMessage()
            ], 500);
        }
    }
}
