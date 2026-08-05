<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\GradingSystem;
use App\Models\Student;

class ResultImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    private $faculty;
    private $department;
    private $program;
    private $level;
    private $course;
    private $semester;
    private $session;
    private $per;
    private $ses;

    public function __construct($faculty, $department, $program, $course, $per, $semester, $session)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->course = $course;
        $this->per = $per;
        //$this->session = DB::table('session')->where('status', '1')->value('title');
        // $this->semester = DB::table('semester')
        //     ->where('status', '1')
        //     ->value('semester');
        $this->semester = $semester;
        $this->ses = $session;
        $this->session = $session;
    }

    public function collection(Collection $rowss)
    {
        set_time_limit(0);
        $data = DB::table('course')
            ->where(['code' => $this->course])
            ->get();
        foreach ($data as $rows) {
        }
        foreach ($rowss as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;
            }
            // Check if email field is not empty
            if (!empty($row[1])) {
                $id = strtoupper(str_replace(' ', '', $row[1]));
                //echo $id;
                $students = DB::table('students')
                    ->where(['username' => $id])
                    ->select('program', 'level', 'department')
                    ->first();

                //dd($students -> program);

                if (isset($students)) {
                    $std_program = $students->program;
                    $std_level = $students->level;
                    $std_department = $students->department;
                    $std_duration = DB::table('program')
                        ->where(['code' => $std_program])
                        ->select('duration')
                        ->value('duration');
                    $grading = DB::table('program_course_registration')
                        ->where([
                            'code' => $rows->code,
                            'program' => $std_program,
                        ])
                        ->select('grading')
                        ->value('grading');

                    $currentYear = date('Y');
                    $getYear = explode('/', $id);
                    $getYear = '20' . $getYear[0];

                    $year = $id[0] . $id[1];
                    $ca = strtoupper(str_replace(' ', '', $row[2]));
                    $exam = strtoupper(str_replace(' ', '', $row[3]));
                    $ca = (double) $ca;
                    $exam = (double) $exam;
                    $total = (double) $ca + (double) $exam;
                    //$total = (int) $total;
                    // round total
                    $total = round($total, 0, PHP_ROUND_HALF_UP);
                    //dd($total);


                    $unit = $rows->unit;
                    $level = $rows->level;
                    $remark = 'PASS';

                        //dd($total);

                    $gradings = DB::table('grading_system')
                        ->where(function ($query) use ($getYear) {
                            $query
                                ->where(function ($innerQuery) use ($getYear) {
                                    $innerQuery
                                        ->where('to', '!=', 'current')
                                        ->where('from', '<=', $getYear)
                                        ->where('to', '>=', $getYear);
                                })
                                ->orWhere(function ($innerQuery) use (
                                    $getYear
                                ) {
                                    $innerQuery
                                        ->where('to', '=', 'current')
                                        ->where('from', '<=', $getYear);
                                });
                        })
                        ->where('min_score', '<=', $total)
                        ->where('max_score', '>=', $total)
                        ->where('name', $grading)
                        ->get();
                    //dd($total);

                    foreach ($gradings as $gs) {
                        $grade = $gs->grade;
                        $point = $unit * $gs->point;
                        $remark = $gs->remark;
                        $records['grading'] = $gs->ref;
                        $records['username'] = $id;
                        $records['code'] = $rows->code;
                        $records['ca'] = $ca;
                        $records['exam'] = $exam;
                        $records['total'] = $total;
                        $records['per'] = $this->per;
                        $records['grade'] = $grade;
                        $records['remark'] = $remark;
                        $records['ugp'] = $point;
                        $records['semester'] = $this->semester;
                        $records['session'] = $this->session;
                        $records['level'] = $rows->level;
                        $records['current_level'] = $std_level;
                        $records['final_level'] = $std_duration * 100;
                        $records['program'] = $rows->program;
                        $records['department'] = $std_department;
                        $records['lecturer'] = session('username');
                        $records['updated_at'] = NOW();

                        //dd($grade);
                        if ($point > 0) {
                            $records['point'] = $point / $unit;
                        } else {
                            $records['point'] = $point;
                        }



                        $checking = DB::table('results')
                            ->select('id', 'approve')
                            ->where([
                                'username' => $id,
                                'code' => $this->course,
                                'session' => $this->session,
                            ])
                            ->where('approve', '!=', 'system')
                            ->first();
                        if ($checking) {

                        } else {
                            $exist = DB::table('results')
                            ->select('id')
                            ->where([
                                'username' => $id,
                                'code' => $this->course,
                            ])
                            ->where('session', '<', $this->session)
                            ->first();

                            if ($exist) {
                                $records['unit'] = 0;
                            } else {
                                $records['unit'] = $rows->unit;
                            }

                            try {
                                DB::table('results')->insert($records);
                            } catch (QueryException $e) {
                                if ($e->errorInfo[1] == 1062) {
                                    DB::table('results')
                                        ->where([
                                            'username' => $id,
                                            'code' => $rows->code,
                                            'session' => $this->session,
                                            'semester' => $this->semester,
                                        ])
                                        ->update($records);
                                } else {
                                }
                            } catch (\Exception $e) {
                                if ($e->errorInfo[1] == 1062) {
                                    echo 'Double';
                                    DB::table('results')
                                        ->where([
                                            'username' => $id,
                                            'code' => $rows->code,
                                            'session' => $this->session,
                                            'semester' => $this->semester,
                                        ])
                                        ->update($records);
                                } else {
                                }
                            } finally {
                            }
                        }

                    }
                    //dd($gradings);
                }
            }
        }
    }
}
