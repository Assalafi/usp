<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentCourseRegistration;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentCourseRegistrationController extends Controller
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
        // Redirect if session 'log' is not set
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get filter parameters
        $session = $req->session ?? session('system_session');

        // Fetch all available sessions for dropdown
        $data['sessions'] = DB::table('session')
            ->orderBy('title', 'desc')
            ->get();

        // Pass filter values to view
        $data['session'] = $session;

        if (session('accType') == 'Student') {
            if ($session) {
                $data['data'] = DB::table($this->table)
                    ->where(['username' => session('id_number'), 'session' => $session])
                    ->orderBy('semester', 'ASC')
                    ->orderBy('code', 'ASC')
                    ->get();
            } else {
                // Return empty paginated results if no filters
                $data['data'] = DB::table($this->table)
                    ->where(['username' => session('id_number'), 'session' => $session])
                    ->orderBy('semester', 'ASC')
                    ->orderBy('code', 'ASC')
                    ->get();
            }
        } else {
            $semester = $req->semester;
            $data['semester'] = $semester;
            $course_code = $req->course_code;
            $data['course_code'] = $course_code;
            $username = $req->username;
            $data['username'] = $username;

            // Build query with filters
            $query = DB::table($this->table);

            if ($username) {
                $query->where('username', 'like', '%' . $username . '%');
                $getStudent = DB::table('students')->where('username', $username)->first();
                $studentProgram = $getStudent->program;
                $studentLevel = $getStudent->level;
                
                // Get student's structure_id based on entry year
                $studentModel = Student::where('username', $username)->first();
                $structureId = $studentModel ? $studentModel->structure_id : null;

                $data['getProgramCourses'] = DB::table('program_course_registration')
                    ->where(['program' => $studentProgram, 'structure_id' => $structureId])
                    ->orderBy('level')->orderBy('semester')->orderBy('code')->get();
            }

            if ($session) {
                $query->where('session', $session);
            }

            if ($semester) {
                $query->where('semester', $semester);
            }

            if ($course_code) {
                $query->where('code', 'like', '%' . $course_code . '%');
            }

            // Only execute the query if at least one filter is provided
            if ($username || $session || $semester || $course_code) {
                // dd($query);
                $data['data'] = $query
                    ->orderBy('session', 'DESC')
                    ->orderBy('level', 'DESC')
                    ->orderBy('semester', 'ASC')
                    ->orderBy('code', 'ASC')
                    ->orderBy('username', 'ASC')
                    ->paginate(50)
                    ->appends([
                        'username' => $username,
                        'session' => $session,
                        'semester' => $semester,
                        'course_code' => $course_code
                    ]);
            } else {
                // Return empty paginated results if no filters
                $data['data'] = DB::table($this->table)
                    ->orderBy('level', 'DESC')
                    ->orderBy('semester', 'ASC')
                    ->whereRaw('1 = 0')  // This will always be false
                    ->paginate(50);
            }
        }

        // Fetch main data
        $lvl = DB::table($this->table)
            ->where([
                'username' => session('id_number'),
                'session' => $session,
            ])
            ->orderBy('semester', 'ASC')
            ->orderBy('level', 'DESC')
            ->limit(1)
            ->value('level');

        // Fetch default program courses
        $data['default'] = DB::table('program_course_registration')
            ->where([
                'program' => session('program'),
                'structure_id' => session('structure_id'),
                'level' => session('current_level'),
            ])
            ->get();

        // Fetch elective courses in a single query
        $electives = DB::table('program_course_registration')
            ->where([
                'program' => session('program'),
                'structure_id' => session('structure_id'),
                'level' => $lvl,
                'type' => 'ELECTIVE',
            ])
            ->whereIn('elective', ['1', '2', '3', '4', '5'])
            ->whereIn('semester', ['FIRST', 'SECOND'])
            ->get()
            ->groupBy(['semester', 'elective']);  // Group by semester and elective

        // Assign grouped electives to data array
        foreach (['FIRST', 'SECOND'] as $semester) {
            foreach (['1', '2', '3', '4', '5'] as $elective) {
                $key = strtolower($semester[0]) . $elective;  // e.g., f1, s2
                $data[$key] = $electives[$semester][$elective] ?? collect();  // Default to empty collection if not found
            }
        }

        // Fetch faculty data
        $data['faculty'] = DB::table('faculty')
            ->where('status', '1')
            ->select('code', 'title')
            ->orderBy('title', 'ASC')
            ->get();

        // Add page and title to data
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        $data['lvl'] = $lvl;
        $data['ses'] = $session;

        // Return view with data
        return view('main', $data);
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        if ($datas['register'] == 'new') {
            if (session('system_session') == session('student_session')) {
                if (session('current_level') == '200') {
                    $data = DB::table('program_course_registration')
                        ->where(function ($query) use ($datas) {
                            $query
                                ->where(['program' => session('program'), 'level' => '200', 'type' => 'CORE'])
                                ->orWhere(function ($query) use ($datas) {
                                    $query->where(['program' => session('program'), 'de' => '1']);
                                })
                                ->orWhere(function ($query) use ($datas) {
                                    $query->where(['program' => session('program'), 'de' => '1']);
                                });
                        })
                        ->get();
                } else {
                    $data = DB::table('program_course_registration')->where(['program' => session('program'), 'level' => session('current_level'), 'type' => 'CORE', 'form' => 'NEW'])->get();
                }

                DB::table($this->table)->where('username', session('id_number'))->delete();
                foreach ($data as $row) {
                    $datass['username'] = session('id_number');
                    $datass['session'] = session('system_session');
                    $datass['semester'] = $row->semester;
                    $datass['type'] = $row->type;
                    $datass['code'] = $row->code;
                    $datass['unit'] = $row->unit;
                    $datass['level'] = $row->level;
                    DB::table($this->table)->insert($datass);
                }

                return redirect()->back()->with('success', 'Record Created!!!');
            } elseif (strpos(session('faculty'), '.PG') !== false) {
                $data = DB::table('program_course_registration')->where(['program' => session('program'), 'type' => 'CORE'])->get();

                DB::table($this->table)->where('username', session('id_number'))->delete();
                foreach ($data as $row) {
                    $datass['username'] = session('id_number');
                    $datass['session'] = session('system_session');
                    $datass['semester'] = $row->semester;
                    $datass['type'] = $row->type;
                    $datass['code'] = $row->code;
                    $datass['unit'] = $row->unit;
                    $datass['level'] = $row->level;
                    DB::table($this->table)->insert($datass);
                }

                return redirect()->back()->with('success', 'Record Created!!!');
            } else {
                return redirect()->back()->with('error', 'This feature is only for new student, your core courses will be registered by the system.');
            }
        }

        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function registerCoursesManually(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        // get the array of selected courses
        $selectedCourses = $req->input('courses');
        $session = $req->input('session');
        $username = $req->input('username');
        if ($selectedCourses == null) {
            return redirect()->back()->with('error', 'No courses selected!!!');
        }
        foreach ($selectedCourses as $row) {
            $check = 1;
            $courseJson = json_decode($row);

            $semester = $courseJson->semester;
            $type = $courseJson->type;
            $code = $courseJson->code;
            $unit = $courseJson->unit;
            $level = $courseJson->level;

            // updateOrCreate
            StudentCourseRegistration::updateOrCreate(
                ['username' => $username, 'session' => $session, 'code' => $code],
                [
                    'username' => $username,
                    'session' => $session,
                    'semester' => $semester,
                    'type' => $type,
                    'code' => $code,
                    'unit' => $unit,
                    'level' => $level,
                ]
            );
        }
        return redirect()->back()->with('success', 'Courses Registered!!!');
    }

    public function registerMyCourses(Request $req)
    {
        // dd($req->all());
        if (!session()->has('log')) {
            return redirect('/');
        }
        // get the array of selected courses (merge mobile and desktop, remove duplicates)
        $mobileCourses = $req->input('courses', []);
        $desktopCourses = $req->input('desktop_courses', []);
        $allCourses = array_merge($mobileCourses, $desktopCourses);
        $selectedCourses = array_unique($allCourses);

        $session = $req->input('session');
        $username = session('id_number');
        if (empty($selectedCourses)) {
            return redirect()->back()->with('error', 'No courses selected!!!');
        }

        // delete existing courses for this session
        StudentCourseRegistration::where(['username' => $username, 'session' => $session])->delete();

        foreach ($selectedCourses as $row) {
            $courseJson = json_decode($row);

            $semester = $courseJson->semester;
            $type = $courseJson->type;
            $code = $courseJson->code;
            $unit = $courseJson->unit;
            $level = $courseJson->level;

            // create new course registration
            StudentCourseRegistration::create([
                'username' => $username,
                'session' => $session,
                'semester' => $semester,
                'type' => $type,
                'code' => $code,
                'unit' => $unit,
                'level' => $level,
            ]);
        }
        return redirect()->back()->with('success', $session . ' Courses Registered!!!');
    }

    public function registerCoursesNewStudentt(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = DB::table('students')->select('level', 'session_of_entry', 'program')->where(['session_of_entry' => '2024/2025', 'level' => '200'])->where('id_no', '!=', '0')->get();
        foreach ($datas as $rows) {
            $data = DB::table('program_course_registration')->where(['program' => $rows->program, 'level' => '200', 'type' => 'CORE'])->orWhere(['program' => $rows->program, 'de' => '1'])->get();

            DB::table($this->table)->where('username', $rows->id_number)->delete();
            foreach ($data as $row) {
                $datass['username'] = $rows->id_number;
                $datass['session'] = '2024/2025';
                $datass['semester'] = $row->semester;
                $datass['type'] = $row->type;
                $datass['code'] = $row->code;
                $datass['unit'] = $row->unit;
                $datass['level'] = $row->level;
                DB::table($this->table)->insert($datass);
            }
        }

        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function registerCoursesNewStudent(Request $req)
    {
        // Ensure the user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get the starting offset from the query parameter (default to 0 if not provided)
        $offset = $req->input('offset', 0);
        $limit = 100;  // Number of records to process per request
        $session = $req->session;
        // dd($session);

        try {
            if ($session == '2024/2025') {
                // Fetch the total number of students to process
                $totalStudents = DB::table('students')
                    ->where(['session_of_entry' => $session, 'level_of_entry' => '200'])
                    ->where('id_no', '!=', '0')
                    ->count();
                // dd($totalStudents);

                // If there are no students to process, return a completion message
                if ($totalStudents === 0) {
                    return 'No students to process!';
                }

                // Fetch the next batch of students
                $students = DB::table('students')
                    ->where(['session_of_entry' => $session, 'level_of_entry' => '200'])
                    ->where('id_no', '!=', '0')
                    ->skip($offset)
                    ->take($limit)
                    ->get();
            } else if ($session == '2023/2024') {
                // Fetch the total number of students to process
                $totalStudents = DB::table('students')
                    ->where(['session_of_entry' => $session, 'level_of_entry' => '200'])
                    ->count();
                // dd($totalStudents);

                // If there are no students to process, return a completion message
                if ($totalStudents === 0) {
                    return 'No students to process!';
                }

                // Fetch the next batch of students
                $students = DB::table('students')
                    ->where(['session_of_entry' => $session, 'level_of_entry' => '200'])
                    ->skip($offset)
                    ->take($limit)
                    ->get();
            }
            // If no more students are left, return a completion message
            if ($students->isEmpty()) {
                return 'All students processed!';
            }

            // Process each student in the current batch
            foreach ($students as $student) {
                // Fetch courses for the student's program and level
                $courses = DB::table('program_course_registration')
                    ->where(function ($query) use ($student) {
                        $query
                            ->where(['program' => $student->program, 'level' => '200', 'type' => 'CORE'])
                            ->orWhere(function ($query) use ($student) {
                                $query->where(['program' => $student->program, 'de' => '1']);
                            })
                            ->orWhere(function ($query) use ($student) {
                                $query->where(['program' => $student->program, 'de' => '1']);
                            });
                    })
                    ->get();

                // Delete existing course registrations for the student
                StudentCourseRegistration::where(['username' => $student->username, 'session' => $session])->delete();

                // Insert new course registrations for the student
                foreach ($courses as $course) {
                    $courseData = [
                        'username' => $student->username,
                        'session' => $session,
                        'semester' => $course->semester,
                        'type' => $course->type,
                        'code' => $course->code,
                        'unit' => $course->unit,
                        'level' => $course->level,
                    ];

                    StudentCourseRegistration::insert($courseData);
                }

                // Prepare data for the next session
                $sr['username'] = $student->username;
                $sr['program'] = $student->program;
                $sr['session'] = $session;  // Updated session
                $sr['level'] = '200';  // Incremented level (or unchanged for Repeat/Pending)
                $sr['total_unit'] = 0;  // Carry forward total units
                $sr['product'] = 0;  // Carry forward grade points
                $sr['cgpa'] = 0;  // Carry forward CGPA
                $sr['status'] = null;  // Store the status for the next session

                try {
                    // Insert the new session record
                    DB::table('session_history')->insert($sr);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle duplicate entry error (if the record already exists)
                    if ($e->errorInfo[1] == 1062) {  // Duplicate entry error code
                        DB::table('session_history')
                            ->where(['session' => $session, 'username' => $student->username])
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

            // Calculate the number of students processed so far
            $processedStudents = $offset + $students->count();
            $remainingStudents = $totalStudents - $processedStudents;

            // Output progress in a single line
            echo "Processed: $processedStudents / $totalStudents | Remaining: $remainingStudents<br>";

            // Calculate the new offset for the next batch
            $newOffset = $offset + $limit;

            // If all students are processed, return a completion message
            if ($processedStudents >= $totalStudents) {
                // return 'All students processed!';
                return <<<HTML
                    <script>
                        // Display a message indicating the delay
                        document.write("All students processed! Redirecting to Status Page in 5 seconds...<br>");

                        // Wait for 5 seconds (5000 milliseconds) before redirecting
                        setTimeout(function() {
                            window.location.href = "/status";
                        }, 5000); // 5000ms = 5 seconds
                    </script>
                    HTML;
            }

            // Return JavaScript to handle the delay and redirect
            return <<<HTML
                <script>
                    // Display a message indicating the delay
                    document.write("Waiting 1 seconds before processing the next batch...<br>");

                    // Wait for 5 seconds (1000 milliseconds) before redirecting
                    setTimeout(function() {
                        window.location.href = "/register-courses-new-student?offset=$newOffset&session=$session";
                    }, 1000); // 5000ms = 5 seconds
                </script>
                HTML;
        } catch (\Exception $e) {
            dd($e);
            // Handle exceptions
            \Log::error('Error registering courses for new students: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request.'], 500);
        }
    }

    public function reRegister(Request $req)
    {
        // Ensure the user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get the starting offset from the query parameter (default to 0 if not provided)
        $offset = $req->input('offset', 0);
        $limit = 100;  // Number of records to process per request
        $session = $req->session;
        $program = $req->program;
        $level = $req->level;
        $list_of_students = DB::table('session_history')
            ->where(['session' => $session, 'program' => $program, 'level' => $level])
            ->select('username')
            ->pluck('username')
            ->toArray();
        // dd($session);

        try {
            // Fetch the total number of students to process
            $totalStudents = Student::whereIn('username', $list_of_students)
                ->count();
            // dd($totalStudents);

            // If there are no students to process, return a completion message
            if ($totalStudents === 0) {
                return 'No students to process!';
            }

            // Fetch the next batch of students
            $students = Student::whereIn('username', $list_of_students)
                ->skip($offset)
                ->take($limit)
                ->get();

            // If no more students are left, return a completion message
            if ($students->isEmpty()) {
                return 'All students processed!';
            }

            // Process each student in the current batch
            foreach ($students as $student) { 

                $studentModel = Student::where('username', $student->username)->first();
                $structureId = $studentModel ? $studentModel->structure_id : null;
                   // Delete existing course registrations for the student
                StudentCourseRegistration::where(['username' => $student->username])->delete();

                // Fetch courses for the student's program and level
                if($student->session_of_entry == $req->session){
                    if($level == '200'){
                    $courses = DB::table('program_course_registration')->where(['program' => $student->program, 'level' => $level, 'type' => 'CORE', 'form' => 'NEW', 'de' => '1', 'structure_id' => $structureId])->get();
            
                // Insert new course registrations for the student
                foreach ($courses as $course) {
                    $courseData = [
                        'username' => $student->username,
                        'session' => $session,
                        'semester' => $course->semester,
                        'type' => $course->type,
                        'code' => $course->code,
                        'unit' => $course->unit,
                        'level' => $course->level,
                    ];

                    StudentCourseRegistration::insert($courseData);
                }
                    }
                    $courses = DB::table('program_course_registration')->where(['program' => $student->program, 'level' => $level, 'type' => 'CORE', 'form' => 'NEW', 'structure_id' => $structureId])->get();
                    
                }else{
                    $courses = DB::table('program_course_registration')->where(['program' => $student->program, 'level' => $level, 'type' => 'CORE', 'form' => 'FORMER', 'structure_id' => $structureId])->get();
                }

                // Delete existing course registrations for the student
                //StudentCourseRegistration::where(['username' => $student->username, 'session' => $session, 'level' => $level])->delete();

                // Insert new course registrations for the student
                foreach ($courses as $course) {
                    $courseData = [
                        'username' => $student->username,
                        'session' => $session,
                        'semester' => $course->semester,
                        'type' => $course->type,
                        'code' => $course->code,
                        'unit' => $course->unit,
                        'level' => $course->level,
                    ];

                    StudentCourseRegistration::insert($courseData);
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
                    <head><title>Re-Registration Complete</title></head>
                    <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                        <h3 style='color: #28a745;'>✓ Re-Registration Complete</h3>
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
                <head><title>Re-Registration in Progress...</title></head>
                <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                    <h3>Re-Registration in Progress</h3>
                    <p><strong>Session:</strong> {$session} | <strong>Program:</strong> {$program} | <strong>Level:</strong> {$level}</p>
                    <p>Processed: {$processedStudents} / {$totalStudents} students</p>
                    <p>Remaining: {$remainingStudents}</p>
                    <div style='width: 300px; margin: 20px auto; background: #eee; border-radius: 5px;'>
                        <div style='width: {$progressPercent}%; background: #007bff; height: 20px; border-radius: 5px;'></div>
                    </div>
                    <p><small>Please wait...</small></p>
                    <script>
                        setTimeout(function() {
                            window.location.href = '/re-register?offset={$newOffset}&session={$session}&program={$program}&level={$level}';
                        }, 500);
                    </script>
                </body>
                </html>
            ";
        } catch (\Exception $e) {
            dd($e);
            // Handle exceptions
            \Log::error('Error registering courses for new students: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request.'], 500);
        }
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function elective(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $codes = $req->input('code');

        foreach ($codes as $key => $code) {
            if ($code != 'NIL') {
                $cod = explode(',', $code);
                $data = DB::table('program_course_registration')->where(['program' => session('program'), 'code' => $cod[0]])->get();
                foreach ($data as $row) {
                    $datass['username'] = session('id_number');
                    $datass['session'] = $req->session;
                    $datass['semester'] = $row->semester;
                    $datass['type'] = $row->type;
                    $datass['code'] = $row->code;
                    $datass['unit'] = $row->unit;
                    $datass['level'] = $row->level;
                    $datass['elective'] = $cod[1];

                    StudentCourseRegistration::updateOrCreate(
                        ['username' => session('id_number'), 'elective' => $cod[1], 'semester' => $row->semester],
                        $datass
                    );
                }
            }
        }
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function getCourses(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (strpos(session('faculty'), '.PG') !== false) {
            return view('pdf/pg student registered courses', ['session' => $req->session]);
        } else {
            return view('pdf/student registered courses', ['session' => $req->session]);
        }
    }

    public function changeSemester(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table('student_course_registration')->where('id', $req->id)->update([
            'semester' => $req->semester
        ]);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Check if it's a bulk delete operation
        if ($req->has('ids')) {
            // Convert comma-separated string to array if needed
            $ids = is_array($req->ids) ? $req->ids : explode(',', $req->ids);

            // Count how many records are deleted
            $count = DB::table($this->table)->whereIn('id', $ids)->count();
            DB::table($this->table)->whereIn('id', $ids)->delete();

            return redirect()->back()->with('success', $count . ' records deleted successfully');
        } else if ($req->has('id')) {
            // Single record delete
            DB::table($this->table)->where('id', $req->id)->delete();
            return redirect()->back()->with('success', 'Record deleted successfully');
        } else {
            return redirect()->back()->with('error', 'No records selected for deletion');
        }
    }

    public function deleteMyCourse(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $username = session('id_number');

        // Check if it's a bulk delete operation
        if ($req->has('ids')) {
            // Convert comma-separated string to array if needed
            $ids = is_array($req->ids) ? $req->ids : explode(',', $req->ids);

            // Count how many records are deleted
            $count = StudentCourseRegistration::whereIn('id', $ids)->where('username', $username)->count();
            StudentCourseRegistration::whereIn('id', $ids)->where('username', $username)->delete();

            return redirect()->back()->with('success', $count . ' records deleted successfully');
        } else if ($req->has('id')) {
            // Single record delete
            StudentCourseRegistration::where('id', $req->id)->where('username', $username)->delete();
            return redirect()->back()->with('success', 'Record deleted successfully');
        } else {
            return redirect()->back()->with('error', 'No records selected for deletion');
        }
    }
}
