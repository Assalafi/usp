<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;

class StatusImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    private $faculty;
    private $department;
    private $program;
    private $level;
    private $mode;
    private $session;
    private $ses;

    public function __construct($faculty, $department, $program, $level, $mode)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->level = $level;
        $this->session = '2024/2025';
        $this->ses = '2024/2025';
        $this->mode = $mode;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }

            // Check if email field is not empty
            if (!empty($row[1])) {
                $ID = $row[1];
                $exist = DB::table('students')->where('username', $ID)->first();
                DB::table('student_course_registration')->where(['username' => $ID])->delete();
                DB::table('session_history')->where(['username' => $ID])->delete();

                $status = strtoupper($row[6]);
                $status = strtoupper(str_replace(' ', '', $status));
                $status = strtoupper(str_replace(' ', '', $status));
                $status = preg_replace('/\s+/', '', $status);
                if($status == 'PROCEED' || $status == 'REPEAT'){

                }else{
                    $status = 'PENDING';
                }
                if ($exist) {
                    // make sure $row[3] is not empty and is number
                    
                    // Get student's structure_id based on entry year
                    $studentModel = Student::where('username', $ID)->first();
                    $structureId = $studentModel ? $studentModel->structure_id : null;
                    //dd($structureId);

                    $unit = is_numeric($row[3]) ? $row[3] : 0;
                    $product = is_numeric($row[4]) ? $row[4] : 0;
                    $cgpa = is_numeric($row[5]) ? $row[5] : 0;
                    $level = $this->level;
                    if ($status == 'PROCEED') {
                        $level = $level + 100;
                    }
                    if ($status == 'PROCEED' && $this -> mode == 'regular') {
                        // echo $this -> program;
                        // die;
                        //$data = DB::table('program_course_registration')->where(['program' => $this->program, 'level' => $level])->get();

                        $coreCourses = DB::table('program_course_registration')
                            ->where('program', $this->program)
                            ->where('structure_id', $structureId)
                            ->where('level', $level)
                            ->where('type', 'CORE')
                            ->get();

                        $electiveCourses = DB::table('program_course_registration as pr1')
                            ->select('pr1.*')
                            ->where('pr1.program', $this->program)
                            ->where('pr1.structure_id', $structureId)
                            ->where('pr1.level', $level)
                            ->where('pr1.type', 'ELECTIVE')
                            ->join(
                                DB::raw('(SELECT MIN(id) as min_id, level, semester, elective
                                        FROM program_course_registration
                                        WHERE program = "' . $this->program . '"
                                        AND structure_id = ' . ($structureId ?? 'NULL') . '
                                        AND level = ' . $level . '
                                        AND type = "ELECTIVE"
                                        GROUP BY level, semester, elective) as pr2'),
                                function ($join) {
                                    $join->on('pr1.id', '=', 'pr2.min_id');
                                }
                            )
                            ->get();

                        $data = $coreCourses->merge($electiveCourses);

                        foreach ($data as $rows) {
                            $code_level = DB::table('course')->where(['code' => $rows->code])->select('level')->value('level');
                            if ($code_level != $level) {
                                $records['unit'] = 0;
                            } else {
                                $records['unit'] = $rows->unit;
                            }
                            $records['username'] = $ID;
                            $records['code'] = $rows->code;
                            $records['unit'] = $rows->unit;
                            $records['type'] = $rows->type;
                            $records['elective'] = $rows->elective;
                            $records['semester'] = $rows->semester;
                            $records['session'] = $this->ses;
                            $records['level'] = $rows->level;
                            $records['updated_at'] = now();

                            try {
                                $records['created_at'] = now();
                                DB::table('student_course_registration')->insert($records);
                            } catch (QueryException $e) {
                                if ($e->errorInfo[1] == 1062) {
                                    DB::table('student_course_registration')->where(['username' => $ID, 'code' => $rows->code, 'session' => $this->session])->update($records);
                                } else {
                                }
                            } catch (\Exception $e) {
                            } finally {
                            }
                        }
                    }

                    $sr1['username'] = $row[1];
                    $sr1['program'] = $this->program;
                    $sr1['session'] = '2023/2024';
                    $sr1['level'] = $this->level;
                    $sr1['next_session'] = $this->ses;
                    $sr1['next_level'] = $this->level;
                    $sr1['total_unit'] = 0;
                    $sr1['product'] = 0;
                    $sr1['cgpa'] = 0;
                    $sr1['status'] = 'PENDING';
                    $sr1['updated_at'] = now();

                    $sr['username'] = $row[1];
                    $sr['program'] = $this->program;
                    $sr['session'] = $this->session;
                    $sr['level'] = $level;
                    $sr['next_session'] = $this->ses;
                    $sr['next_level'] = $level;
                    $sr['total_unit'] = $unit;
                    $sr['product'] = $product;
                    $sr['cgpa'] = $cgpa;
                    $sr['status'] = $status;
                    $sr['updated_at'] = now();

                    try {
                        $sr1['created_at'] = now();
                        $sr['created_at'] = now();
                        DB::table('session_history')->insert($sr1);
                        DB::table('session_history')->insert($sr);
                    } catch (QueryException $e) {
                        if ($e->errorInfo[1] == 1062) {
                            DB::table('session_history')->where(['session' => $this->session, 'username' => $ID])->update($sr);
                        } else {
                        }
                    } catch (\Exception $e) {
                        if ($e->errorInfo[1] == 1062) {
                            DB::table('session_history')->where(['session' => $this->session, 'username' => $ID])->update($sr);
                        }
                    } finally {
                    }

                    try {
                        $lvl['level'] = $level;
                        $lvl['faculty'] = $this -> faculty;
                        $lvl['department'] = $this ->  department;
                        $lvl['program'] = $this ->  program;
                        DB::table('students')->where(['username' => $ID])->update($lvl);
                    } catch (QueryException $e) {
                        if ($e->errorInfo[1] == 1062) {
                        } else {
                        }
                    }

                    $text = strtoupper($row[7]);
                    $pos = strpos($text, ':');
                    $slice = substr($text, $pos + 1, strlen($text));
                    if ($pos !== false && strtoupper($slice) != 'NIL') {
                        $courses = explode(",", $slice);
                        $count = count($courses);
                        for ($i = 0; $i < $count; $i++) {
                            $course = strtoupper(str_replace(' ', '', $courses[$i]));
                            $course = strtoupper(str_replace(' ', '', $course));
                            $course = strtoupper(str_replace(' ', '', $course));
                            $course = preg_replace('/\s+/', '', $course);
                            //$data = DB::table('course')->where(['code' => $course])->get();
                            $data = DB::table('program_course_registration')->where(['program' => $this->program, 'structure_id' => $structureId, 'code' => $course])->get();
                            foreach ($data as $rows) {
                                $records['username'] = $ID;
                                $records['code'] = $rows->code;
                                $records['unit'] = $rows->unit;
                                $records['type'] = $rows->type;
                                $records['elective'] = $rows->elective;
                                $records['semester'] = $rows->semester;
                                $records['session'] = $this->ses;
                                $records['level'] = $rows->level;
                                $records['updated_at'] = now();
                                try {
                                    $records['created_at'] = now();
                                    DB::table('student_course_registration')->insert($records);
                                } catch (QueryException $e) {
                                    if ($e->errorInfo[1] == 1062) {
                                        DB::table('student_course_registration')->where(['username' => $ID, 'code' => $rows->code, 'session' => $this->session])->update($records);
                                    } else {
                                    }
                                } catch (\Exception $e) {
                                } finally {
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
