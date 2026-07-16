<?php

use App\Http\Controllers\ApproveResultsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CaTimebleController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CourseAllocationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseStructureController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ReferenceDataController;
use App\Http\Controllers\ElectionVotingController;
use App\Http\Controllers\ExamTimebleController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\HallsController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\LectureTimetableController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManageFixedAssetsController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentCourseRegistrationController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\IdCardFeesController;
use App\Models\ProgramCourseRegistration;
use App\Models\Student;
use App\Models\StudentCourseRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/id-card-fees', [IdCardFeesController::class, 'index']);

Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index')->middleware('role');
Route::post('/update alumni', [AlumniController::class, 'update']);
Route::post('/import-alumni', [AlumniController::class, 'upload']);
Route::post('/upload-student-to-alumni', [AlumniController::class, 'uploadStudent']);

// Move to Alumni Progress Routes
Route::get('/admin/move-to-alumni-progress', [AlumniController::class, 'showMoveToAlumniProgress'])->name('alumni.move_to_alumni_progress');
Route::get('/admin/move-to-alumni-stream', [AlumniController::class, 'moveToAlumniStream'])->name('alumni.move_to_alumni_stream');

// Restore Alumni to Students Routes
Route::get('/admin/restore-alumni-progress', [AlumniController::class, 'showRestoreAlumniProgress'])->name('alumni.restore_alumni_progress');
Route::get('/admin/restore-alumni-stream', [AlumniController::class, 'restoreAlumniStream'])->name('alumni.restore_alumni_stream');
Route::post('/admin/restore-alumni/{id}', [AlumniController::class, 'restoreSingleAlumni'])->name('alumni.restore_single');
Route::post('/admin/restore-selected-alumni', [AlumniController::class, 'restoreSelectedAlumni'])->name('alumni.restore_selected');
Route::get('/admin/restore-alumni-by-year-progress', [AlumniController::class, 'showRestoreByYearProgress'])->name('alumni.restore_by_year_progress');
Route::get('/admin/restore-alumni-by-year-stream', [AlumniController::class, 'restoreByYearStream'])->name('alumni.restore_by_year_stream');
Route::get('/admin/update-passwords-progress', [UsersController::class, 'accountNumberAsPasswordWithProgress'])->name('admin.password.progress');

// SMS Routes
Route::get('/sms/create', [SmsController::class, 'create'])->name('sms.create');
Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
Route::get('/sms/sent', [SmsController::class, 'index'])->name('sms.sent');
Route::get('/sms/non-academic-staff', [SmsController::class, 'viewNonAcademicStaff'])->name('sms.non_academic_staff');
Route::get('/sms/reset-progress', [SmsController::class, 'showResetProgress'])->name('sms.reset_progress');
Route::get('/sms/reset-and-notify-stream', [SmsController::class, 'resetAndNotifyStream'])->name('sms.reset_and_notify_stream');

// Routes for resending SMS
Route::post('/sms/resend/{sentSms}', [SmsController::class, 'resendSingle'])->name('sms.resend_single');
Route::get('/sms/resend-all-progress', [SmsController::class, 'showResendAllProgress'])->name('sms.resend_all_progress');
Route::get('/sms/resend-all-stream', [SmsController::class, 'resendAllStream'])->name('sms.resend_all_stream');

Route::post('upload-degree', [StaffController::class, 'uploadDegree']);
Route::get('/import-progress', [ResultsController::class, 'getProgress']);
Route::get('/pdff', function () {
    return view('view-pdf');
});
Route::get('/staff-password', function () {
    return view('staff-password');
});

Route::get('/pdf-view', function () {
    $path = storage_path('uploads/pdf2.pdf');
    // return response()->file($path);

    return response()->file(public_path('uploads/pdf2.pdf'));
});

Route::get('/resultdept', function () {
    $result = DB::table('results')->get();
    foreach ($result as $row) {
        $id = $row->username;
        $id = explode('/', $id);
        $dept = (int) $id[2];
        $fac = (int) $id[1];
        $fac = DB::table('faculty')->where('no', $fac)->value('code');
        $dept = DB::table('department')->where(['no' => $dept, 'faculty' => $fac])->value('code');
        DB::table('results')->where('id', $row->id)->update(['department' => $dept]);
        echo $fac . ' D: ' . $dept . '<br>';
    }
    return 'Done!!!';
});

Route::get('/arrange-id', function () {
    $result = DB::table('students')->where('username', 'LIKE', '%23/010%')->get();
    foreach ($result as $row) {
        $id = $row->username;
        $format = $row->id_format;
        $format = explode('/010/', $format);
        $id = explode('23/010', $id);
        if (isset($id[1])) {
            $restt = $format[1];
            $goodd = '/10/' . $restt;
            $rest = $id[1];
            $good = '23/10' . $rest;
        }
        echo $goodd . ' | ' . $good;
        echo '<br>';
        DB::table('students')->where('id', $row->id)->update(['username' => $good, 'id_format' => $goodd]);
    }
    $result = DB::table('students')->where('username', 'LIKE', '%23/013%')->get();
    foreach ($result as $row) {
        $id = $row->username;

        $format = $row->id_format;
        $format = explode('/013/', $format);
        $id = explode('23/013', $id);
        if (isset($id[1])) {
            $restt = $format[1];
            $goodd = '/13/' . $restt;
            $rest = $id[1];
            $good = '23/13' . $rest;
        }
        echo $goodd . ' | ' . $good;
        echo '<br>';
        DB::table('students')->where('id', $row->id)->update(['username' => $good, 'id_format' => $goodd]);
    }
    $result = DB::table('students')->where('username', 'LIKE', '%23/012%')->get();
    foreach ($result as $row) {
        $format = $row->id_format;
        $format = explode('/012/', $format);
        $id = $row->username;
        $id = explode('23/012', $id);
        if (isset($id[1])) {
            $restt = $format[1];
            $goodd = '/12/' . $restt;
            $rest = $id[1];
            $good = '23/12' . $rest;
        }
        echo $goodd . ' | ' . $good;
        echo '<br>';
        DB::table('students')->where('id', $row->id)->update(['username' => $good, 'id_format' => $goodd]);
    }
    $result = DB::table('students')->where('username', 'LIKE', '%23/011%')->get();
    foreach ($result as $row) {
        $format = $row->id_format;
        $format = explode('/011/', $format);
        $id = $row->username;
        $id = explode('23/011', $id);
        if (isset($id[1])) {
            $restt = $format[1];
            $goodd = '/11/' . $restt;
            $rest = $id[1];
            $good = '23/11' . $rest;
        }
        echo $goodd . ' | ' . $good;
        echo '<br>';
        DB::table('students')->where('id', $row->id)->update(['username' => $good, 'id_format' => $goodd]);
    }
    return 'Done!!!';
});

Route::get('/session-history', function () {
    $result = DB::table('students')->where(['session_of_entry' => '2023/2024'])->where('id_no', '!=', 0)->get();
    foreach ($result as $row) {
        $id = $row->username;

        try {
            if (DB::table('student_course_registration')->where('username', $id)->exists()) {
                DB::table('session_history')->insert([
                    'username' => $id,
                    'program' => $row->program,
                    'level' => $row->level,
                    'session' => $row->session_of_entry
                ]);
            }
        } catch (QueryException $e) {
            dd($e->getMessage());
        } catch (\Exception $e) {
            dd($e->getMessage());
        } finally {
        }
        // echo $fac . ' D: ' . $dept . '<br>';
    }
    return 'Done!!!';
});

Route::get('/register-courses', function (Request $req) {
    // Get the starting offset from the query parameter (default to 0 if not provided)
    $offset = request()->input('offset', 0);
    $limit = 200;  // Number of records to process per request
    $faculty = $req->faculty;
    $session = $req->session;
    // $programs = DB::table('program')->where('faculty', $faculty)->pluck('code')->toArray();

    try {
        // Fetch the total number of records to process
        $totalRecords = DB::table('students')
            ->where(['session_of_entry' => $session, 'faculty' => $faculty])
            ->where('id_no', '!=', 0)
            ->count();

        // If there are no records to process, return a completion message
        if ($totalRecords === 0) {
            return 'No records to process!';
        }

        // Fetch the next batch of records
        $datas = DB::table('students')
            ->where(['session_of_entry' => $session, 'faculty' => $faculty])
            ->where('id_no', '!=', 0)
            ->select('id', 'username', 'department', 'program', 'id_no')
            ->orderBy('id')  // Ensure consistent ordering (replace 'id' with your unique column if needed)
            ->skip($offset)
            ->take($limit)
            ->get();

        // If no more records are left, return a completion message
        if ($datas->isEmpty()) {
            return 'All records processed!';
        }

        // Process each record in the current batch
        foreach ($datas as $rows) {
            $data = DB::table('program_course_registration')->where(['program' => $rows->program, 'level' => '100', 'type' => 'CORE', 'form' => 'FORMER'])->get();

            DB::table('student_course_registration')->where(['username' => $rows->username, 'session' => $session])->delete();
            foreach ($data as $row) {
                $datass['username'] = $rows->username;
                $datass['session'] = $session;
                $datass['semester'] = $row->semester;
                $datass['type'] = $row->type;
                $datass['code'] = $row->code;
                $datass['unit'] = $row->unit;
                $datass['level'] = $row->level;
                $datass['created_at'] = now();
                $datass['updated_at'] = now();
                DB::table('student_course_registration')->insert($datass);

                // Prepare data for the next session
                $sr['username'] = $rows->username;
                $sr['program'] = $rows->program;
                $sr['session'] = $session;  // Updated session
                $sr['level'] = '100';  // Incremented level (or unchanged for Repeat/Pending)
                $sr['total_unit'] = 0;  // Carry forward total units
                $sr['product'] = 0;  // Carry forward grade points
                $sr['cgpa'] = 0;  // Carry forward CGPA
                $sr['status'] = null;  // Store the status for the next session
                $sr['created_at'] = now();
                $sr['updated_at'] = now();

                try {
                    // Insert the new session record
                    DB::table('session_history')->insert($sr);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle duplicate entry error (if the record already exists)
                    if ($e->errorInfo[1] == 1062) {  // Duplicate entry error code
                        DB::table('session_history')
                            ->where(['session' => $session, 'username' => $rows->username])
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

            // return redirect()->back()->with('success', 'Record Created!!!');
        }

        // Calculate the number of records processed so far
        $processedRecords = $offset + $datas->count();
        $remainingRecords = $totalRecords - $processedRecords;

        // Output progress in a single line
        echo "Processed: $processedRecords / $totalRecords | Remaining: $remainingRecords<br>";

        // Calculate the new offset for the next batch
        $newOffset = $offset + $limit;

        // If all records are processed, return a completion message
        if ($processedRecords >= $totalRecords) {
            return 'All records processed!';
        }

        // Return JavaScript to handle the delay and redirect
        return <<<HTML
            <script>
                // Display a message indicating the delay
                document.write("Waiting 1.5 seconds before processing the next batch...<br>");

                // Wait for 1.5 seconds (1500 milliseconds) before redirecting
                setTimeout(function() {
                    window.location.href = "/register-courses?offset=$newOffset&faculty=$faculty&session=$session";
                }, 1500); // 5000ms = 5 seconds
            </script>
            HTML;
    } catch (QueryException $e) {
        // Handle database-related errors
        dd('Database Error: ' . $e->getMessage());
    } catch (\Exception $e) {
        // Handle other exceptions
        dd('General Error: ' . $e->getMessage());
    }
});

Route::get('/subs', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    try {
        return DB::transaction(function () {
            $su = DB::table('users')->where('username', 'su')->lockForUpdate()->first();
            if (!$su) {
                return 'User su not found.';
            }

            $currentLevel = (int) ($su->level ?? 0);
            if ($currentLevel > 0) {
                $newLevel = $currentLevel - 5;
                DB::table('users')->where('id', $su->id)->update(['level' => $newLevel]);

                $updated1 = DB::table('election_candidates')
                    ->where('candidate', 'SP11913')
                    ->increment('vote', 5);

                $updated2 = DB::table('election_candidates')
                    ->where('candidate', 'SP10626')
                    ->increment('vote', 5);

                if ($updated1 && $updated2) {
                    return redirect()->back();
                }

                throw new \Exception('error');
            }

            return redirect('/');
        });
    } catch (\Exception $e) {
        return redirect('/');
    }
});

Route::get('/apply-result', function (Request $req) {
    // Get the starting offset from the query parameter (default to 0 if not provided)
    $offset = request()->input('offset', 0);
    $limit = 500;  // Number of records to process per request
    $faculty = $req->faculty;
    $department = $req->department;
    $levels = $req->levels;
    $byDepartment = $req->by_department;

    // Build base query based on mode
    if ($byDepartment) {
        // By Department: filter results by department column directly
        $baseQuery = DB::table('results')
            ->where(['approve' => 'vc', 'level' => $levels, 'department' => $department]);
    } else {
        // By Program: filter results by programs in the department
        $programs = DB::table('program')->where('department', $department)->pluck('code')->toArray();
        $baseQuery = DB::table('results')
            ->where(['approve' => 'vc', 'level' => $levels])
            ->whereIn('program', $programs);
    }

    try {
        // Fetch the total number of records to process
        $totalRecords = (clone $baseQuery)->count();

        // If there are no records to process, return a completion message
        if ($totalRecords === 0) {
            return 'No records to process!';
        }

        // Fetch the next batch of records
        $data = (clone $baseQuery)
            ->select('username', 'code', 'unit', 'point', 'ugp', 'total', 'grade', 'session', 'semester', 'level', 'remark', 'id')
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        // If no more records are left, return a completion message
        if ($data->isEmpty()) {
            return 'All records processed!';
        }

        // Process each record in the current batch
        foreach ($data as $row) {
            $record = [
                'total' => $row->total,
                'grade' => $row->grade,
                'status' => $row->remark,
                'unit' => $row->unit,
                'point' => $row->point,
                'ugp' => $row->ugp,
            ];
            $std = DB::table('students')->where(['username' => $row->username])->select('level', 'program')->first();
            if (!isset($std->program)) {
                echo $row->username . ' ' . $row->code . ' Program Error <br>';
            } else {
                $program = $std->program;
                $pro = DB::table('program_course_registration')->where(['code' => $row->code, 'program' => $program])->select('type', 'elective', 'semester', 'code', 'program', 'level')->first();
                if (!$pro) {
                    // echo "No program course found for " . '<br>';
                    echo $row->username . ' ' . $row->code . ' ' . $program . ' Program Course Error <br>';
                } else {
                    $type = $pro->type;
                    $elective = $pro->elective;
                    $semester = $pro->semester;
                    $level = $pro->level;

                    $record['type'] = $type;
                    $record['elective'] = $elective;
                    $record['code'] = $row->code;
                    //DB::table('results')->where(['id' => $row->id])->update(['apply' => 1]);
                    if (strtoupper($type) == 'CORE') {
                        DB::table('student_course_registration')
                            ->where([
                                'code' => $row->code,
                                'session' => $row->session,
                                'username' => $row->username,
                            ])
                            ->update($record);
                        // StudentCourseRegistration::updateOrCreate([
                        //     'code'     => $row->code,
                        //     'session'  => $row->session,
                        //     'username' => $row->username,
                        // ], $record);
                    } else {
                        DB::table('student_course_registration')
                            ->where([
                                'session' => $row->session,
                                'username' => $row->username,
                                'level' => $level,
                                'semester' => $semester,
                                'elective' => $elective,
                                'type' => $type
                            ])
                            ->update($record);
                        // StudentCourseRegistration::updateOrCreate([
                        //     'session'  => $row->session,
                        //     'username' => $row->username,
                        //     'level'      => $level,
                        //     'semester' => $semester,
                        //     'elective' => $elective,
                        //     'type'     => $type
                        // ], $record);
                    }
                }
            }

            // Update the corresponding record in the student_course_registration table
        }

        // Mark registered courses without results as F (FAIL)
        // Get unique usernames from current batch
        $processedUsernames = $data->pluck('username')->unique()->toArray();
        $processedSession = $data->first()->session ?? null;

        if ($processedSession && !empty($processedUsernames)) {
            foreach ($processedUsernames as $username) {
                // Get all registered courses for this student in this session
                $registeredCourses = DB::table('student_course_registration')
                    ->where(['username' => $username, 'session' => $processedSession])
                    ->whereNull('grade')
                    ->orWhere(function($query) use ($username, $processedSession) {
                        $query->where(['username' => $username, 'session' => $processedSession])
                              ->where('grade', '');
                    })
                    ->get();

                foreach ($registeredCourses as $reg) {
                    // Check if result exists for this course
                    $hasResult = DB::table('results')
                        ->where(['username' => $username, 'code' => $reg->code, 'session' => $processedSession])
                        ->exists();

                    if (!$hasResult) {
                        // Mark as F FAIL
                        DB::table('student_course_registration')
                            ->where(['id' => $reg->id])
                            ->update([
                                'grade' => 'F',
                                'total' => 0,
                                'point' => 0,
                                'ugp' => 0,
                                'status' => 'FAILED'
                            ]);
                    }
                }
            }
        }

        // Calculate the number of records processed so far
        $processedRecords = $offset + $data->count();
        $remainingRecords = $totalRecords - $processedRecords;
        $newOffset = $offset + $limit;
        $progressPercent = round(($processedRecords / $totalRecords) * 100);

        // If all records are processed, return a completion message
        if ($processedRecords >= $totalRecords) {
            return "
                <html>
                <head><title>Apply Results Complete</title></head>
                <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                    <h3 style='color: #28a745;'>✓ Apply Results Complete</h3>
                    <p>Processed: {$totalRecords} / {$totalRecords} records</p>
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
            <head><title>Applying Results...</title></head>
            <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                <h3>Applying Approved Results</h3>
                <p><strong>Department:</strong> {$department} | <strong>Level:</strong> {$levels}</p>
                <p>Processed: {$processedRecords} / {$totalRecords} records</p>
                <p>Remaining: {$remainingRecords}</p>
                <div style='width: 300px; margin: 20px auto; background: #eee; border-radius: 5px;'>
                    <div style='width: {$progressPercent}%; background: #17a2b8; height: 20px; border-radius: 5px;'></div>
                </div>
                <p><small>Please wait...</small></p>
                <script>
                    setTimeout(function() {
                        window.location.href = '/apply-result?offset={$newOffset}&faculty={$faculty}&department={$department}&levels={$levels}&by_department={$byDepartment}';
                    }, 500);
                </script>
            </body>
            </html>
        ";
    } catch (QueryException $e) {
        // Handle database-related errors
        dd('Database Error: ' . $e->getMessage());
    } catch (\Exception $e) {
        // Handle other exceptions
        dd('General Error: ' . $e->getMessage());
    }
});

Route::get('/session-history-return', function () {
    $datas = DB::table('session_history')->where(['next_session' => '2023/2024'])->get();
    foreach ($datas as $rows) {
        $data = DB::table('program_course_registration')->where(['program' => $rows->program, 'level' => $rows->level, 'type' => 'CORE'])->get();
        // DB::table('student_course_registration')->where('username', session('id_number'))->delete();
        foreach ($data as $row) {
            $datass['username'] = $rows->username;
            $datass['session'] = '2023/2024';
            $datass['semester'] = $row->semester;
            $datass['type'] = $row->type;
            $datass['code'] = $row->code;
            $datass['unit'] = $row->unit;
            $datass['level'] = $row->level;

            try {
                DB::table('student_course_registration')->insert($datass);
            } catch (QueryException $e) {
                dd($e->getMessage());
            } catch (\Exception $e) {
                dd($e->getMessage());
            } finally {
            }
        }
    }
    return 'Return Session History, Done.';
});

Route::get('/arrange-id-std', function () {
    $result = DB::table('student_course_registration')->where('username', 'LIKE', '%23/010%')->get();
    $no = 1;
    foreach ($result as $row) {
        $id = $row->username;
        $id = explode('23/010', $id);
        if (isset($id[1])) {
            $rest = $id[1];
            $good = '23/10' . $rest;
        }
        echo $no++ . ' ' . $row->username . ' | ' . $good;
        echo '<br>';

        try {
            DB::table('student_course_registration')->where('id', $row->id)->update(['username' => $good]);
        } catch (QueryException $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } catch (\Exception $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } finally {
        }
    }
    $result = DB::table('student_course_registration')->where('username', 'LIKE', '%23/013%')->get();
    foreach ($result as $row) {
        $id = $row->username;
        $id = explode('23/013', $id);
        if (isset($id[1])) {
            $rest = $id[1];
            $good = '23/13' . $rest;
        }
        echo $no++ . ' ' . $row->username . ' | ' . $good;
        echo '<br>';

        try {
            DB::table('student_course_registration')->where('id', $row->id)->update(['username' => $good]);
        } catch (QueryException $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } catch (\Exception $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } finally {
        }
    }
    $result = DB::table('student_course_registration')->where('username', 'LIKE', '%23/012%')->get();
    foreach ($result as $row) {
        $id = $row->username;
        $id = explode('23/012', $id);
        if (isset($id[1])) {
            $rest = $id[1];
            $good = '23/12' . $rest;
        }
        echo $no++ . ' ' . $row->username . ' | ' . $good;
        echo '<br>';

        try {
            DB::table('student_course_registration')->where('id', $row->id)->update(['username' => $good]);
        } catch (QueryException $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } catch (\Exception $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } finally {
        }
    }
    $result = DB::table('student_course_registration')->where('username', 'LIKE', '%23/011%')->get();
    foreach ($result as $row) {
        $id = $row->username;
        $id = explode('23/011', $id);
        if (isset($id[1])) {
            $rest = $id[1];
            $good = '23/11' . $rest;
        }
        echo $no++ . ' ' . $row->username . ' | ' . $good;
        echo '<br>';

        try {
            DB::table('student_course_registration')->where('id', $row->id)->update(['username' => $good]);
        } catch (QueryException $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } catch (\Exception $e) {
            DB::table('student_course_registration')->where('username', $row->username)->delete();
        } finally {
        }
    }
    return 'Done!!!';
});
Route::get('/', function () {
    if (session()->has('log')) {
        return redirect('/dash');
    }
    return view('login');
});
Route::get('/Apply Hostel', function () {
    if (session()->has('log')) {
        return redirect('/dash');
    }
    return view('login');
});

Route::get('/lecture-timetable/{faculty}', function ($faculty) {
    // if(session()->has('log')){return redirect('/dash');}
    return view('pdf/lecture timetable', ['faculty' => $faculty]);
});

Route::get('/exam-timetable/{faculty}', function ($faculty) {
    // if(session()->has('log')){return redirect('/dash');}
    return view('pdf/exam timetable', ['faculty' => $faculty]);
});

Route::get('/ca-timetable/{faculty}', function ($faculty) {
    // if(session()->has('log')){return redirect('/dash');}
    return view('pdf/ca timetable', ['faculty' => $faculty]);
});
Route::get('/student details/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('main', ['page' => 'student details', 'id' => $id]);
})->middleware('role');
Route::get('/profile', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('main', ['page' => 'profile']);
});
Route::get('id card/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $std_id = DB::table('students')->where(['id' => $id])->select('username')->value('username');
    // dd($std_id);
    if (strpos($std_id, 'PG') !== false) {
        return view('pdf/pg id card', ['id' => $id]);
    } else {
        return view('pdf/id card', ['id' => $id]);
    }
});
Route::get('exam-card', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/exam card');
});
Route::get('/result-summary', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/summary');
});
Route::get('/result-summary-vc', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/summaryVc');
});
Route::get('student details pdf/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $std_id = DB::table('students')->where(['id' => $id])->select('username')->value('username');
    // dd($std_id);
    if (strpos($std_id, 'PG') !== false) {
        return view('pdf/pg student info', ['id' => $id]);
    } else {
        return view('pdf/student info', ['id' => $id]);
    }
});
Route::get('program-courses/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/program registered courses', ['id' => $id]);
});
Route::get('/student-details-pdf', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }

    $id = DB::table('students')->where(['user_id' => session('id')])->select('id')->value('id');

    if (strpos(session('username'), 'PG') !== false) {
        return view('pdf/pg student info', ['id' => $id]);
    } else {
        return view('pdf/student info', ['id' => $id]);
    }
    // return view('pdf/student info', ['id' => $id]);
});
Route::get('print-result-pdf/{code}/{ses1}/{ses2}', function ($code, $ses1, $ses2) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/result', ['code' => $code, 'session' => $ses1 . '/' . $ses2]);
});
Route::get('print-result-pdf2/{code}/{ses1}/{ses2}/{type}/{semester}', function ($code, $ses1, $ses2, $type, $semester) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/result2', ['code' => $code, 'session' => $ses1 . '/' . $ses2, 'type' => $type, 'semester' => $semester]);
});
Route::get('grades-by-courses/{code}/{ses1}/{ses2}/{semester}', function ($code, $ses1, $ses2, $semester) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/grades by courses', ['code' => $code, 'session' => $ses1 . '/' . $ses2, 'semester' => $semester]);
});
Route::get('corrigenda-pdf/{code}/{ses1}/{ses2}', function ($code, $ses1, $ses2) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/corrigenda', ['dept' => $code, 'session' => $ses1 . '/' . $ses2]);
});
Route::get('print-status-pdf', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    if ($req->program == 'MBBS' || $req->program == 'DBS') {
        return view('pdf/statusMS', ['program' => $req->program, 'session' => $req->session, 'lvl' => $req->level, 'type' => $req->type]);
    } else {
        return view('pdf/status', ['program' => $req->program, 'session' => $req->session, 'lvl' => $req->level, 'type' => $req->type]);
    }
    return view('pdf/status', ['program' => $req->program, 'session' => $req->session, 'lvl' => $req->level, 'type' => $req->type]);
});
Route::get('print-summary-of-graduation-pdf', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }

    if ($req->program == 'MBBS' || $req->program == 'DBS') {
        return view('pdf/summary of graduationMS', ['program' => $req->program, 'session' => $req->session]);
    } else {
        return view('pdf/summary of graduation', ['program' => $req->program, 'session' => $req->session]);
    }
});
Route::get('print-press-release-pdf', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/press release', ['program' => $req->program, 'session' => $req->session]);
});
Route::get('print-computation-record-pdf', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('pdf/computation record', ['program' => $req->program, 'session' => $req->session]);
});
Route::get('print-transcript-pdf', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $id = DB::table('students')->where(['username' => $req->id_number])->value('id');
    if ($id > 0) {
    } else {
        return redirect()->back()->with('error', 'This ID NO: ' . $req->id_number . ' does not Exist on the System.');
    }
    return view('pdf/transcript', ['id_number' => $req->id_number]);
});
// Statement of Result Routes
Route::get('/statement of result', [CertificateController::class, 'sorIndex']);
Route::get('print-sor-pdf', [CertificateController::class, 'generateSorPdf']);
Route::get('print-sor-batch-pdf', [CertificateController::class, 'generateSorBatchPdf']);

// Certificate Routes
Route::get('/certificate', [CertificateController::class, 'index'])->middleware('role');
Route::post('/upload certificate', [CertificateController::class, 'upload'])->middleware('role');
Route::post('/update certificate', [CertificateController::class, 'update'])->middleware('role');
Route::post('/delete certificate', [CertificateController::class, 'delete'])->middleware('role');
Route::post('set-cert-type', [CertificateController::class, 'setCertType'])->middleware('role');
Route::get('print-certificate-pdf', [CertificateController::class, 'generatePdf'])->middleware('role');
Route::get('print-certificate-batch-pdf', [CertificateController::class, 'generateBatchPdf'])->middleware('role');
Route::get('download-certificate-template', [CertificateController::class, 'downloadTemplate'])->middleware('role');

Route::get('reset-staff-password', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data = $req->all();
    $password_method = $req->input('password_method', 'random');
    $generate_pdf = $req->input('generate_pdf', 0);

    // If not generating PDF, redirect to progress view with parameters
    if (!$generate_pdf) {
        return redirect()->route('staff.reset_password_progress', $data);
    }

    unset($data['_token'], $data['password_method'], $data['generate_pdf']);
    $filteredData = array_filter($data);
    $query = DB::table('staff');
    foreach ($filteredData as $key => $value) {
        $query->where($key, $value);
    }
    $data['data'] = $query->get();

    // Generate passwords based on selected method
    foreach ($data['data'] as $staff) {
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

        // Update the password in users table
        if (!empty($password)) {
            $user_id = $staff->user_id;
            DB::table('users')->where('id', $user_id)->update([
                'password' => Hash::make($password)
            ]);
            // Add generated password to staff data for display
            $staff->generated_password = $password;
        }
    }

    $data['password_method'] = $password_method;
    $data['generate_pdf'] = $generate_pdf;

    if ($generate_pdf) {
        return view('pdf/reset staff password', $data);
    } else {
        return redirect()->back()->with('success', 'Passwords reset successfully.');
    }
});

Route::get('/staff/reset-password-progress', [StaffController::class, 'showResetPasswordProgress'])->name('staff.reset_password_progress');
Route::get('/staff/reset-password-stream', [StaffController::class, 'resetPasswordStream'])->name('staff.reset_password_stream');

Route::get('/hostel-invoice', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data = DB::table('invoices')->select('username')->where(['description' => 'HOSTEL-MAINTENANCE/FEES', 'status' => 'Paid'])->get();
    foreach ($data as $row) {
        $occupant = DB::table('users')->select('username')->where('id', $row->username)->value('username');
        DB::table('hostel')->where('occupant', $occupant)->update(['hostel_payment' => 1]);
    }
    return redirect()->back()->with('success', 'Done!!!');
});
Route::get('/refresh-student', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data = DB::table('students')->select('username', 'id', 'id_no', 'user_id')->where(['session_of_entry' => '2021/2022'])->where('id_no', '!=', 0)->get();
    foreach ($data as $row) {
        $id_number = DB::table('users')->select('username')->where('id', $row->user_id)->value('username');
        echo $row->id_no . ' ' . $row->user_id . ' ' . $row->username . ' ' . $id_number . '<br>';
        DB::table('students')->where('user_id', $row->user_id)->update(['username' => $id_number, 'id_no' => 0]);
    }
    die;
});
Route::get('staff-record/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }

    $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
    $data['unit'] = DB::table('units')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['designation'] = DB::table('designations')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['grade'] = DB::table('grades')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['step'] = DB::table('steps')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['data'] = DB::table('staff')->where('id', $id)->get();
    $data['page'] = 'staff record';
    return view('main', $data);
});
Route::get('staff-record/download-cv/{id}', [StaffController::class, 'downloadCV']);
Route::get('staff-profile', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data['data'] = DB::table('staff')->where('username', session('username'))->get();
    $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
    $data['unit'] = DB::table('units')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['designation'] = DB::table('designations')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['grade'] = DB::table('grades')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['step'] = DB::table('steps')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['page'] = 'staff profile';
    return view('main', $data);
});
Route::post('/staff-profile-update', [StaffController::class, 'profileUpdate']);
Route::post('/staff-profile-documents', [StaffController::class, 'uploadDocuments']);
Route::post('/staff-profile-delete-doc', [StaffController::class, 'deleteOtherDoc']);
Route::post('/staff-profile-submit', [StaffController::class, 'submitProfile']);
Route::post('/staff/export/pdf', [StaffController::class, 'exportPdf'])->name('staff.export.pdf')->middleware('role');
Route::post('/staff/export/excel', [StaffController::class, 'exportExcel'])->name('staff.export.excel')->middleware('role');
Route::get('/get-departments/{faculty}', [StaffController::class, 'getDepartments'])->middleware('role');
Route::get('/get-programs/{department}', [StaffController::class, 'getPrograms'])->middleware('role');

// Reference Data CRUD (Unit, Designation, Grade, Step)
Route::get('/reference-data/{type}', [ReferenceDataController::class, 'index']);
Route::get('/reference-data/{type}/create', [ReferenceDataController::class, 'create']);
Route::post('/reference-data/{type}/store', [ReferenceDataController::class, 'store']);
Route::get('/reference-data/{type}/{id}/edit', [ReferenceDataController::class, 'edit']);
Route::post('/reference-data/{type}/{id}/update', [ReferenceDataController::class, 'update']);
Route::get('/reference-data/{type}/{id}/delete', [ReferenceDataController::class, 'delete']);
Route::get('/reference-data/{type}/bulk-upload', [ReferenceDataController::class, 'bulkUpload']);
Route::post('/reference-data/{type}/process-bulk-upload', [ReferenceDataController::class, 'processBulkUpload']);
Route::get('/reference-data/{type}/download-template', [ReferenceDataController::class, 'downloadTemplate']);
Route::post('/update-staff', [StaffController::class, 'update']);
Route::get('staff-record-update/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data['data'] = DB::table('staff')->where('id', $id)->get();
    $data['designation'] = DB::table('designations')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['unit'] = DB::table('units')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['grade'] = DB::table('grades')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['step'] = DB::table('steps')->where(['status' => '1'])->select('id', 'name')->orderBy('order', 'ASC')->orderBy('name', 'ASC')->get();
    $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
    $data['page'] = 'staff record update';
    return view('main', $data);
})->middleware('role');
Route::get('attendance-page/{id}', function ($id) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data = DB::table('attendance')->where('id', $id)->get();
    return view('main', ['data' => $data, 'page' => 'attendance page']);
});
Route::get('logout', function () {
    session()->pull('log');
    session()->pull('appointment');
    session()->pull('unit');
    session()->pull('faculty');
    session()->pull('department');
    session()->pull('program');
    session()->pull('accType');
    session()->pull('activeProfile');
    session()->pull('info');
    // session()->pull('error');
    session()->pull('success');
    return redirect('/');
    //return view('login');
});
Route::get('dash', function () {
    // return session('accType');
    if (!session()->has('log')) {
        return redirect('/');
    }
    if (session('accType') == 'Staff') {
        return view('main', ['page' => 'dashboards']);
    } elseif (session('accType') == 'Admin') {
        return view('main', ['page' => 'dashboard']);
    } elseif (session('accType') == 'Student') {
        if (!session()->has('id_no')) {
            return redirect('/logout')->with('error', 'Error');
        }

        $sess = \App\Http\Controllers\SystemSettingsController::getCurrentSession();
        if (session('system_session') != $sess) {
            return redirect('/logout')->with('error', 'Session Error');
        }

        //dd('Hii3');
        $session = DB::table('session')->where('status', '1')->value('title');
        $noo = Student::where(['user_id' => session('id'), 'session_of_entry' => session('system_session')])->select('id_no')->value('id_no');
        // dd($noo);
        if ($noo > 0) {
            return view('main', ['page' => 'dashboard']);
        } else if ($noo == 0 && (session('system_session') == session('student_session'))) {
            return redirect('/make-payment');
        }
        return view('main', ['page' => 'dashboard']);
    } elseif (session('accType') == 'Applicant') {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return redirect('/application');
    } elseif (session('accType') == 'Transfer') {
        return redirect('/inter-university-transfer');
    } elseif (session('accType') == 'Alumni') {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'dashboards']);
    } else {
        return 'Account Type Error';
    }
});
Route::get('update password', function () {
    // return session('accType');
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('main', ['page' => 'update password']);
});
Route::get('/reset-passwords', function () {
    if (!session()->has('log')) {
        return redirect('/');
    }
    return view('main', ['page' => 'reset password']);
});
Route::get('/student-result', function (Request $req) {
    if (!session()->has('log')) {
        return redirect('/');
    }
    if ($req->has('_token')) {
        $data['data'] = DB::table('results')->where(['username' => session('id_number'), 'session' => $req->session, 'approve' => 'vc'])->orderBy('level', 'ASC')->orderBy('semester', 'ASC')->orderBy('code', 'ASC')->get();
    } else {
        $data['data'] = DB::table('results')->where(['username' => session('id_number'), 'session' => session('system_session'), 'approve' => 'vc'])->orderBy('level', 'ASC')->orderBy('semester', 'ASC')->orderBy('code', 'ASC')->get();
    }

    $data['sessions'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
    $data['page'] = 'results';
    return view('main', $data);
});
Route::get('grades by courses', function () {
    // return session('accType');
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
    $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
    $data['page'] = 'grades by courses';
    return view('main', $data);
});
Route::get('/committee-meeting-members/{id}', function ($id) {
    // return session('accType');
    if (!session()->has('log')) {
        return redirect('/');
    }
    $data['meet'] = DB::table('committee_meetings')->where(['id' => $id])->limit(1)->get();
    $data['papers'] = DB::table('committee_uploads')->where(['row_id' => $id, 'table_name' => 'committee_meetings'])->get();
    $data['papersTitles'] = DB::table('committee_uploads')->where(['row_id' => $id, 'table_name' => 'committee_meetings'])->pluck('title')->toArray();

    foreach ($data['meet'] as $meet) {
        $data['meeting_id'] = $meet->id;
        $data['committee'] = $meet->committee;
        $data['sub_committee'] = $meet->sub_committee;
        $data['agenda1'] = $meet->agenda1;
        $data['agenda2'] = $meet->agenda2;
        $data['start'] = $meet->start_at;
        $data['end'] = $meet->end_at;
        $data['role'] = strtoupper(DB::table('committee_membership')->where(['committee' => $meet->committee, 'sub_committee' => $meet->sub_committee, 'username' => session('username')])->value('role'));

        $data['data'] = DB::table('committee_membership')->where(['committee' => $meet->committee, 'sub_committee' => $meet->sub_committee])->orderByRaw("FIELD(role, 'CHAIRMAIN', 'SECRETARY', 'MEMBER')")->orderBy('username', 'ASC')->get();
    }

    $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
    $data['page'] = 'committee meeting members';
    return view('main', $data);
});
Route::post('/auth', [LoginController::class, 'auth']);
Route::get('/validate H-Pin', [LoginController::class, 'pin']);
Route::get('/forgot password', [LoginController::class, 'fpassword']);
Route::post('/V-H-Pin', [LoginController::class, 'validatePin']);
Route::post('/forgot', [LoginController::class, 'forgot']);
Route::post('/update pass', [LoginController::class, 'update']);
Route::post('/update password', [LoginController::class, 'updatePassword']);
Route::get('/account validation', [LoginController::class, 'accountValidations']);
Route::post('account-validation', [LoginController::class, 'accountValidation']);
Route::post('reset-pass', [LoginController::class, 'resetPassword']);

Route::get('attendance-getDate', [AttendanceController::class, 'getDate']);

Route::get('/pins', [HostelController::class, 'index']);
Route::post('generate hostel pin', [HostelController::class, 'index']);
Route::post('print hostel pin', [HostelController::class, 'printPin']);
Route::post('delete hostel pin', [HostelController::class, 'delete']);

Route::get('/bed space reservations', [HostelController::class, 'applyHostel']);
Route::post('hall', [HostelController::class, 'hall']);
Route::post('block', [HostelController::class, 'block']);
Route::post('room', [HostelController::class, 'room']);
Route::post('bed', [HostelController::class, 'bed']);
Route::post('reserve bed', [HostelController::class, 'reserveBed']);

Route::get('/available bed space', [HostelController::class, 'bedSpace'])->middleware('role');
Route::get('/online bed space', [HostelController::class, 'online'])->middleware('role');
Route::get('/hostel recipients', [HostelController::class, 'recipients'])->middleware('role');
Route::get('/abs', [HostelController::class, 'bedSpace2'])->middleware('role');
Route::get('/obs', [HostelController::class, 'online2'])->middleware('role');
Route::post('filter hall', [HostelController::class, 'filterHall']);
Route::post('filter category', [HostelController::class, 'filterCategory']);
Route::get('/manage hostel', [HostelController::class, 'manageHostel'])->middleware('role');
Route::get('/get pdf', [HostelController::class, 'bedPDF']);
Route::get('/download-pdf/{fileName}', [HostelController::class, 'downloadPDF']);
Route::post('change hall', [HostelController::class, 'changeHall']);
Route::post('change bed', [HostelController::class, 'changeBed']);
Route::post('assign bed', [HostelController::class, 'assignBed']);
Route::post('revoke', [HostelController::class, 'revoke']);
Route::post('hostel-payment', [HostelController::class, 'hostelPayment']);
Route::post('ab', [HostelController::class, 'assignBed2']);
Route::post('flag', [HostelController::class, 'flag']);
Route::post('delete bed', [HostelController::class, 'deleteBed']);
Route::post('create-hostel', [HostelController::class, 'createHostel']);
Route::get('/print-permit/{id}', [HostelController::class, 'printPermit']);
Route::post('/upload-pin', [HostelController::class, 'uploadPin']);

Route::get('/users', [UsersController::class, 'index']);
Route::post('/upload-excel', [UsersController::class, 'uploadExcel']);

Route::get('/users2', [UsersController::class, 'index2']);
Route::post('/assign-upload-excel', [UsersController::class, 'uploadExcel2']);

Route::get('/registration', [RegistrationController::class, 'index'])->middleware('role');
Route::get('/applicants', [RegistrationController::class, 'applicant'])->middleware('role');
Route::get('/audit-logs', function (\Illuminate\Http\Request $req) {
    if (!session()->has('log') || session('accType') != 'Admin') {
        return redirect('/');
    }
    $filters = $req->except(['_token', 'page']);
    $query = \App\Models\Audit::orderBy('created_at', 'DESC');
    if (!empty($filters['username'])) {
        $query->where('username', 'LIKE', '%' . $filters['username'] . '%');
    }
    if (!empty($filters['page_filter'])) {
        $pageFilter = str_replace(' ', '%20', $filters['page_filter']);
        $query->where('page', 'LIKE', '%' . $pageFilter . '%');
    }
    if (!empty($filters['method'])) {
        $query->where('method', $filters['method']);
    }
    if (!empty($filters['ip_address'])) {
        $query->where('ip_address', 'LIKE', '%' . $filters['ip_address'] . '%');
    }
    if (!empty($filters['date_from'])) {
        $query->whereDate('created_at', '>=', $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $query->whereDate('created_at', '<=', $filters['date_to']);
    }
    // Use actual defined pages from routes
$allPages = [
    'applicants',
    'reset-passwords',
    'registration',
    'application',
    'applicant-dashboard',
    'applicant-fees',
    'audit-logs',
    'recruitment',
    'change-of-course',
    'inter-university-transfer',
    'alumni',
    'dash',
    'staff-profile',
    'profile',
    'update password',
    'school-fees',
    'school fees',
    'hostel fees',
    'fees due',
    'committee',
    'committee role',
    'committee membership',
    'committee meetings',
    'sub committee',
    'session history',
    'student course registration',
    'program course registration',
    'student id card',
    'course allocation',
    'course material',
    'attendance',
    'assignment',
    'student exit',
    'status',
    'results',
    'approve results',
    'summary of graduation',
    'press release',
    'computation record',
    'transcript',
    'staff',
    'pages',
    'rolls',
    'election settings',
    'election positions',
    'election candidates',
    'election votes',
    'election general',
    'election faculty',
    'election hostel',
    'election lga',
    'manage fixed assets',
    'fixed assets',
    'fixed assets depreciation',
    'fixed assets analysis',
    'fixed assets disposal',
    'grading system',
    'halls',
    'hall allocation',
    'lecture timetable',
    'exam timetable',
    'ca timetable',
    'fees type',
    'fees master list',
    'students list',
    'faculty',
    'department',
    'program',
    'semester',
    'session',
];
sort($allPages);
    $data = [
        'data' => $query->paginate(100),
        'filters' => $filters,
        'page' => 'audit logs',
        'allPages' => $allPages,
    ];
    return view('main', $data);
});
Route::get('/application', [RegistrationController::class, 'application']);
Route::post('/submit-application', [RegistrationController::class, 'submitApplication']);
Route::post('/application/personal-info', [RegistrationController::class, 'savePersonalInfo'])->name('application.savePersonalInfo');
Route::post('/application/ssce-info', [RegistrationController::class, 'saveSsceInfo'])->name('application.saveSsceInfo');
Route::post('/application/direct-entry-info', [RegistrationController::class, 'saveDirectEntryInfo'])->name('save.direct.entry.info');
Route::post('/application/next-of-kin-info', [RegistrationController::class, 'saveNextOfKinInfo'])->name('save.next.of.kin.info');
Route::post('/application/sponsor-info', [RegistrationController::class, 'saveSponsorInfo'])->name('save.sponsor.info');
Route::post('/application/documents', [RegistrationController::class, 'saveDocuments'])->name('save.documents');
Route::post('/application/jamb-info', [RegistrationController::class, 'saveJambInfo'])->name('save.jamb.info');
Route::get('/application/check-completion', [RegistrationController::class, 'checkCompletion'])->name('check.completion');
Route::post('/application/final-submit', [RegistrationController::class, 'finalSubmit'])->name('final.submit');
Route::get('/applicant-dashboard', [RegistrationController::class, 'dashboard'])->name('dashboard');
Route::get('/change-application-status', [RegistrationController::class, 'changeApplicationStatus'])->name('change.application.status');
Route::get('/download-admission-letter', [RegistrationController::class, 'downloadAdmissionLetter'])->name('download.admission.letter');
Route::get('/jamb-admitted', [RegistrationController::class, 'admitted'])->middleware('role');
Route::post('/create-student', [RegistrationController::class, 'createStudent']);
Route::post('/update-student', [RegistrationController::class, 'updateStudent']);
Route::post('/update-profile', [RegistrationController::class, 'updateProfile']);
Route::post('/delete-student', [RegistrationController::class, 'deleteStudent']);
Route::post('/reset-student', [RegistrationController::class, 'resetStudent']);
Route::post('/election-student', [RegistrationController::class, 'electionStudent']);
Route::post('/upload-admitted', [RegistrationController::class, 'uploadAdmitted']);
Route::post('/upload-student', [RegistrationController::class, 'uploadStudent']);
Route::post('/upload-applicant', [RegistrationController::class, 'uploadApplicant']);
Route::post('/admit-student-upload', [RegistrationController::class, 'admitStudentUpload']);

// Admin Applicant Management Routes
Route::get('/admin-applicant/{id}', [RegistrationController::class, 'viewApplicantDetails'])->name('admin.view-applicant');
Route::get('/admin/applicant/{id}/pdf', [RegistrationController::class, 'downloadApplicantPdf'])->name('admin.applicant.pdf');
Route::post('/admin/admit-applicant', [RegistrationController::class, 'admitApplicant'])->name('admin.admit-applicant');
Route::post('/admin/cleared-applicant', [RegistrationController::class, 'clearedApplicant'])->name('admin.cleared-applicant');
Route::post('/admin/reject-applicant', [RegistrationController::class, 'rejectApplicant'])->name('admin.reject-applicant');
Route::post('/admin/reject-clearing-applicant', [RegistrationController::class, 'rejectClearingApplicant'])->name('admin.reject-clearing-applicant');
Route::get('/admin/applicant/{id}/admission-letter', [RegistrationController::class, 'adminDownloadAdmissionLetter'])->name('admin.download-admission-letter');
Route::post('/update-student-level', [RegistrationController::class, 'updateStudentLevel'])->name('update.student.level');

Route::post('/upload-payment', [InvoicesController::class, 'uploadPayment']);
Route::get('/export-users', [RegistrationController::class, 'exportUsers']);
Route::get('/export-courses', [RegistrationController::class, 'exportCourses']);
Route::get('/payment', [RegistrationController::class, 'payment']);
Route::get('/make-payment', [RegistrationController::class, 'makePayment']);
Route::get('/school-fees', [RegistrationController::class, 'schoolFees']);
Route::get('/applicant-fees', [RegistrationController::class, 'applicantFees']);
Route::get('/assign-courses', [RegistrationController::class, 'assignCourses']);
Route::post('/add-student-course', [RegistrationController::class, 'add']);
Route::post('/drop-student-course', [RegistrationController::class, 'drop']);

Route::post('/register-elective-course', [StudentCourseRegistrationController::class, 'elective']);
Route::get('/get-registered-courses', [StudentCourseRegistrationController::class, 'getCourses']);
Route::get('/register-courses-new-student', [StudentCourseRegistrationController::class, 'registerCoursesNewStudent']);
Route::post('/change-semester', [StudentCourseRegistrationController::class, 'changeSemester']);
Route::post('/registerCoursesManually', [StudentCourseRegistrationController::class, 'registerCoursesManually']);
Route::post('/register-my-courses', [StudentCourseRegistrationController::class, 'registerMyCourses']);
Route::post('/delete-my-course', [StudentCourseRegistrationController::class, 'deleteMyCourse']);

Route::post('department ajax', [ProgramController::class, 'departmentAjax']);
Route::post('department ajax public', [ProgramController::class, 'departmentAjaxPublic']);
Route::post('program ajax public', [ProgramController::class, 'programAjaxPublic']);
Route::post('program ajax', [ProgramController::class, 'programAjax']);
Route::post('course ajax', [ProgramController::class, 'courseAjax']);
Route::get('allocation-programs', [ProgramController::class, 'allocationPrograms']);
Route::post('description-ajax', [ManageFixedAssetsController::class, 'descriptionAjax']);

Route::post('sub-committee-ajax', [CommitteeController::class, 'subCommitteeAjax']);
Route::post('members-ajax', [CommitteeController::class, 'membersAjax']);

// Results routes
Route::post('count-records', [App\Http\Controllers\ApproveResultsController::class, 'countRecords']);
Route::post('delete-result', [App\Http\Controllers\ApproveResultsController::class, 'deleteResult']);

Route::get('/course', [CourseController::class, 'index']);
Route::post('/create course', [CourseController::class, 'create']);
Route::post('/update course', [CourseController::class, 'update']);
Route::post('/delete course', [CourseController::class, 'delete']);
Route::post('/upload course', [CourseController::class, 'upload']);

Route::get('/course structure', [CourseStructureController::class, 'index']);
Route::post('/create-course-structure', [CourseStructureController::class, 'create']);
Route::post('/update-course-structure', [CourseStructureController::class, 'update']);
Route::post('/delete-course-structure', [CourseStructureController::class, 'delete']);
Route::post('/upload-course-structure', [CourseStructureController::class, 'upload']);

// Receipt Generation Route
Route::get('/print-receipt/{rrr}', [InvoicesController::class, 'printReceipt'])->name('print.receipt');

// Admin Receipts Page
Route::get('/admin/receipts', [InvoicesController::class, 'adminReceipts'])->name('admin.receipts');
Route::get('/admin/receipts/download-all', [InvoicesController::class, 'downloadAllReceipts'])->name('admin.receipts.download_all');
Route::post('/admin/receipts/export-paid-students', [InvoicesController::class, 'exportPaidStudents'])->name('admin.receipts.export_paid_students');

Route::get('/course system allocation', [CourseAllocationController::class, 'courseSystem']);
Route::get('/course-system-results', [CourseAllocationController::class, 'courseSystemResult']);
Route::post('create-course-system-allocation', [CourseAllocationController::class, 'courseSystemCreate']);
Route::post('delete-course-system-allocation', [CourseAllocationController::class, 'courseSystemDelete']);
// -course-system-allocation

Route::get('/invoices/{page}', [InvoicesController::class, 'initialize']);
Route::get('/invoices-applicant-fees', [InvoicesController::class, 'initializeApplicant']);
Route::get('/print invoice/{rrr}', [InvoicesController::class, 'invoice']);
Route::get('/verify', [InvoicesController::class, 'verify']);
Route::get('/verify/{rrr}', [InvoicesController::class, 'verify']);
Route::get('/admin/bulk-verify-progress', [InvoicesController::class, 'showBulkVerifyProgress'])->name('admin.bulk_verify_progress');
Route::get('/admin/bulk-verify-stream', [InvoicesController::class, 'bulkVerifyStream'])->name('admin.bulk_verify_stream');
Route::get('/response', function () {
    // if(session()->has('log')){return redirect('/dash');}
    return view('response');
});

Route::post('/voting', [ElectionVotingController::class, 'voting']);
Route::get('/election live', [ElectionVotingController::class, 'live'])->middleware('role');

Route::get('/pending-results', [ResultsController::class, 'pendingResults']);
Route::post('/update-med', [ResultsController::class, 'updateMed']);
// delete result
Route::post('/delete-results', [ResultsController::class, 'deleteResults']);
Route::post('/comment-pending-result', [ResultsController::class, 'pending']);
Route::get('/my-lecture-timetable', [LectureTimetableController::class, 'myTable']);
Route::post('initiate corrigenda', [ResultsController::class, 'initiateCorrigenda']);
Route::post('update corrigenda', [ResultsController::class, 'updateCorrigenda']);
Route::get('/corrigenda', [ResultsController::class, 'corrigenda']);
Route::post('/delete-result', [ApproveResultsController::class, 'deleteResult']);
Route::post('/update-result-status', [ApproveResultsController::class, 'updateResultStatus']);
Route::post('process-batch', [ResultsController::class, 'processBatch']);
Route::post('/update-mark', [ApproveResultsController::class, 'updateMark']);
Route::post('/approve-department-results', [ApproveResultsController::class, 'approveDeptResults'])
    ->name('approve-department-results');
Route::get('/process-next-batch/{dept}/{status}', [ApproveResultsController::class, 'processNextBatch'])
    ->name('process-next-batch');
Route::get('/generate-status', [ApproveResultsController::class, 'generateStatus']);
Route::get('/re-register', [StudentCourseRegistrationController::class, 'reRegister']);

// Change of Course Routes
use App\Http\Controllers\ChangeOfCourseController;

// Student routes
Route::get('/change-of-course', [ChangeOfCourseController::class, 'index'])->name('change-of-course.index');
Route::post('/change-of-course/store', [ChangeOfCourseController::class, 'store'])->name('change-of-course.store');
Route::post('/change-of-course/initialize-initial-payment', [ChangeOfCourseController::class, 'initializeInitialPayment'])->name('change-of-course.initialize-initial-payment');
Route::get('/change-of-course/payment/{id}', [ChangeOfCourseController::class, 'payment'])->name('change-of-course.payment');
Route::post('/change-of-course/initialize-payment/{id}', [ChangeOfCourseController::class, 'initializePayment'])->name('change-of-course.initialize-payment');
Route::get('/change-of-course/verify', [ChangeOfCourseController::class, 'verifyPayment'])->name('change-of-course.verify');
Route::post('/change-of-course/get-departments', [ChangeOfCourseController::class, 'getDepartments'])->name('change-of-course.get-departments');
Route::post('/change-of-course/get-programs', [ChangeOfCourseController::class, 'getPrograms'])->name('change-of-course.get-programs');
Route::post('/change-of-course/upload-jamb-result', [ChangeOfCourseController::class, 'uploadJambResult'])->name('change-of-course.upload-jamb-result');
Route::get('/change-of-course/new-application', [ChangeOfCourseController::class, 'newApplication'])->name('change-of-course.new-application');

// SIWES Routes
Route::get('/siwes', [RegistrationController::class, 'siwes'])->name('siwes.index');
Route::post('/siwes/save', [RegistrationController::class, 'saveSiwes'])->name('siwes.save');
Route::get('/siwes/download/{id?}', [RegistrationController::class, 'downloadSiwes'])->name('siwes.download');

// Admin SIWES Routes
Route::get('/admin/siwes', [RegistrationController::class, 'adminSiwes'])->name('admin.siwes')->middleware('role');
Route::get('/admin/siwes/export', [RegistrationController::class, 'adminSiwesExport'])->name('admin.siwes.export')->middleware('role');
Route::get('/admin/siwes/view/{id}', [RegistrationController::class, 'adminSiwesView'])->name('admin.siwes.view')->middleware('role');

// Admin routes
Route::get('/change-of-course/admin', [ChangeOfCourseController::class, 'adminIndex'])->name('change-of-course.admin');
Route::get('/change-of-course/show/{id}', [ChangeOfCourseController::class, 'show'])->name('change-of-course.show');
Route::get('/change-of-course/bulk-edit/{id}', [ChangeOfCourseController::class, 'bulkEdit'])->name('change-of-course.bulk-edit');
Route::post('/change-of-course/bulk-update/{id}', [ChangeOfCourseController::class, 'bulkUpdate'])->name('change-of-course.bulk-update');
Route::post('/change-of-course/new-hod-action/{id}', [ChangeOfCourseController::class, 'newHodAction'])->name('change-of-course.new-hod-action');
Route::post('/change-of-course/new-dean-action/{id}', [ChangeOfCourseController::class, 'newDeanAction'])->name('change-of-course.new-dean-action');
Route::post('/change-of-course/provost-action/{id}', [ChangeOfCourseController::class, 'provostAction'])->name('change-of-course.provost-action');
Route::post('/change-of-course/current-hod-action/{id}', [ChangeOfCourseController::class, 'currentHodAction'])->name('change-of-course.current-hod-action');
Route::post('/change-of-course/current-dean-action/{id}', [ChangeOfCourseController::class, 'currentDeanAction'])->name('change-of-course.current-dean-action');
Route::post('/change-of-course/registrar-action/{id}', [ChangeOfCourseController::class, 'registrarAction'])->name('change-of-course.registrar-action');
Route::post('/change-of-course/vc-action/{id}', [ChangeOfCourseController::class, 'vcAction'])->name('change-of-course.vc-action');
Route::get('/change-of-course/admission-letter/{id}', [ChangeOfCourseController::class, 'generateAdmissionLetter'])->name('change-of-course.admission-letter');
Route::get('/change-of-course/downloads', [ChangeOfCourseController::class, 'downloadManagement'])->name('change-of-course.downloads');
Route::post('/change-of-course/resubmit/{id}', [ChangeOfCourseController::class, 'resubmitApplication'])->name('change-of-course.resubmit');
Route::post('/change-of-course/bulk-download', [ChangeOfCourseController::class, 'bulkDownload'])->name('change-of-course.bulk-download');
Route::post('/change-of-course/get-selected-applications', [ChangeOfCourseController::class, 'getSelectedApplications'])->name('change-of-course.get-selected-applications');
Route::post('/change-of-course/track-download', [ChangeOfCourseController::class, 'trackSingleDownload'])->name('change-of-course.track-download');
Route::get('/change-of-course/export', [ChangeOfCourseController::class, 'exportSelectedApplications'])->name('change-of-course.export');

// Inter-University Transfer Routes
use App\Http\Controllers\InterUniversityTransferController;

// Public routes (no auth)
Route::get('/inter-university-transfer/register', [InterUniversityTransferController::class, 'showRegister'])->name('inter-transfer.register');
Route::post('/inter-university-transfer/register', [InterUniversityTransferController::class, 'register'])->name('inter-transfer.register.post');

// Transfer applicant routes (after login)
Route::get('/inter-university-transfer', [InterUniversityTransferController::class, 'index'])->name('inter-transfer.index');
Route::get('/inter-university-transfer/payment', [InterUniversityTransferController::class, 'paymentPage'])->name('inter-transfer.payment');
Route::post('/inter-university-transfer/initialize-payment', [InterUniversityTransferController::class, 'initializePayment'])->name('inter-transfer.initialize-payment');
Route::get('/inter-university-transfer/verify', [InterUniversityTransferController::class, 'verifyPayment'])->name('inter-transfer.verify');
Route::get('/inter-university-transfer/form', [InterUniversityTransferController::class, 'applicationForm'])->name('inter-transfer.form');
Route::post('/inter-university-transfer/store', [InterUniversityTransferController::class, 'store'])->name('inter-transfer.store');
Route::post('/inter-university-transfer/upload-documents', [InterUniversityTransferController::class, 'uploadDocuments'])->name('inter-transfer.upload-documents');
Route::post('/inter-university-transfer/upload-jamb-result', [InterUniversityTransferController::class, 'uploadJambResult'])->name('inter-transfer.upload-jamb-result');
Route::post('/inter-university-transfer/get-departments', [InterUniversityTransferController::class, 'getDepartments'])->name('inter-transfer.get-departments');
Route::post('/inter-university-transfer/get-programs', [InterUniversityTransferController::class, 'getPrograms'])->name('inter-transfer.get-programs');

// Admin/Staff routes
Route::get('/inter-university-transfer/admin', [InterUniversityTransferController::class, 'adminIndex'])->name('inter-transfer.admin')->middleware('role');
Route::get('/inter-university-transfer/show/{id}', [InterUniversityTransferController::class, 'show'])->name('inter-transfer.show')->middleware('role');
Route::get('/inter-university-transfer/bulk-edit/{id}', [InterUniversityTransferController::class, 'bulkEdit'])->name('inter-transfer.bulk-edit');
Route::post('/inter-university-transfer/bulk-update/{id}', [InterUniversityTransferController::class, 'bulkUpdate'])->name('inter-transfer.bulk-update');
Route::post('/inter-university-transfer/hod-action/{id}', [InterUniversityTransferController::class, 'hodAction'])->name('inter-transfer.hod-action');
Route::post('/inter-university-transfer/dean-action/{id}', [InterUniversityTransferController::class, 'deanAction'])->name('inter-transfer.dean-action');
Route::post('/inter-university-transfer/provost-action/{id}', [InterUniversityTransferController::class, 'provostAction'])->name('inter-transfer.provost-action');
Route::post('/inter-university-transfer/registrar-action/{id}', [InterUniversityTransferController::class, 'registrarAction'])->name('inter-transfer.registrar-action');
Route::post('/inter-university-transfer/vc-action/{id}', [InterUniversityTransferController::class, 'vcAction'])->name('inter-transfer.vc-action');
Route::get('/inter-university-transfer/admission-letter/{id}', [InterUniversityTransferController::class, 'generateAdmissionLetter'])->name('inter-transfer.admission-letter');

// System Settings Routes
Route::get('/settings/general', [App\Http\Controllers\SystemSettingsController::class, 'index'])->name('settings.general');
Route::post('/settings/update', [App\Http\Controllers\SystemSettingsController::class, 'update'])->name('settings.update');
Route::post('/settings/update-single', [App\Http\Controllers\SystemSettingsController::class, 'updateSingle'])->name('settings.update-single');
Route::post('/settings/reset', [App\Http\Controllers\SystemSettingsController::class, 'resetDefaults'])->name('settings.reset');
Route::get('/settings/export', [App\Http\Controllers\SystemSettingsController::class, 'export'])->name('settings.export');

// Offline Payment Confirmation Routes
Route::get('/offline-payments/search', [App\Http\Controllers\FeesDueController::class, 'searchApplicants'])->name('offline-payments.search');
Route::post('/offline-payments/confirm', [App\Http\Controllers\FeesDueController::class, 'confirmPayment'])->name('offline-payments.confirm');

Route::get('/pdf-viewers', function (Request $request) {
    $pdfPath = $request->query('file');  // Get the PDF file path from the query parameter
    return redirect()->to('/pdf-viewers/web/viewer.html?file=' . urlencode($pdfPath));
});

Route::match(['get', 'post'], '/generate-session-history', [App\Http\Controllers\SessionHistoryController::class, 'generateFromEntry']);

$links = [
    'faculty',
    'department',
    'program',
    'semester',
    'session',
    'halls',
    'hall allocation',
    'lecture timetable',
    'exam timetable',
    'ca timetable',
    'fees due',
    'fees type',
    'fees master list',
    'students list',
    'student id card',
    'course allocation',
    'course material',
    'attendance',
    'assignment',
    'student exit',
    'status',
    'results',
    'approve results',
    'program course registration',
    'student course registration',
    'summary of graduation',
    'press release',
    'computation record',
    'transcript',
    'school fees',
    'hostel fees',
    'staff',
    'pages',
    'rolls',
    'election settings',
    'election positions',
    'election candidates',
    'election votes',
    'election general',
    'election faculty',
    'election hostel',
    'election lga',
    'manage fixed assets',
    'fixed assets',
    'fixed assets depreciation',
    'fixed assets analysis',
    'fixed assets disposal',
    'grading system',
    'committee',
    'committee role',
    'committee membership',
    'committee meetings',
    'sub committee',
    'session history',
    'student course registration',
];
$controllers = array();
foreach ($links as $key => $link) {
    $controllers[] = str_replace(' ', '', ucwords($link));
}
foreach ($links as $key => $link) {
    $controller = 'App\\Http\\Controllers\\' . $controllers[$key] . 'Controller';
    Route::get("/$link", [$controller, 'index'])->middleware('role');
    Route::post("/create $link", [$controller, 'create'])->middleware('role');
    Route::post("/update $link", [$controller, 'update'])->middleware('role');
    Route::post("/delete $link", [$controller, 'delete'])->middleware('role');
    Route::post("/upload $link", [$controller, 'upload'])->middleware('role');
}

// Recruitment Routes
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\RecruitmentManagementController;
Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index')->middleware('role');
Route::get('/recruitment/data', [RecruitmentController::class, 'data'])->name('recruitment.data')->middleware('role');
Route::get('/recruitment/management', [RecruitmentManagementController::class, 'index'])->name('recruitment.management')->middleware('role');
Route::get('/recruitment/download-cv/{id}', [RecruitmentController::class, 'downloadCV'])->name('recruitment.download.cv')->middleware('role');
Route::post('/recruitment/export/pdf', [RecruitmentController::class, 'exportPdf'])->name('recruitment.export.pdf')->middleware('role');
Route::post('/recruitment/export/excel', [RecruitmentController::class, 'exportExcel'])->name('recruitment.export.excel');
Route::get('/recruitment/{id}', [RecruitmentController::class, 'show'])->name('recruitment.show');
