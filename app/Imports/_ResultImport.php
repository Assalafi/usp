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

    public function __construct($faculty, $department, $program, $course)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->course = $course;
        //$this->session = DB::table('session')->where('status', '1')->value('title');
        $this->semester = DB::table('semester')->where('status', '1')->value('semester');
        $this->ses = '2023/2024';
        $this->session = DB::table('session')->where('status', '1')->value('title');
    }

    public function collection(Collection $rowss)
    {
        set_time_limit(0);
        $data = DB::table('course')->where(['code' => $this->course])->get();
        foreach ($data as $rows) {
        }
        foreach ($rowss as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }
            // Check if email field is not empty
            if (!empty($row[1])) {
                $id = strtoupper(str_replace(' ', '', $row[1]));
                //echo $id;
                $std_program = DB::table('students')->where(['username' => $id])->select('program')->value('program');
                $std_level = DB::table('students')->where(['username' => $id])->select('level')->value('level');
                $std_duration = DB::table('program')->where(['code' => $std_program])->select('duration')->value('duration');
                $grading = DB::table('program_course_registration')->where(['code' => $rows->code, 'program' => $rows->program])->select('grading')->value('grading');

                $currentYear = date('Y');
                $getYear = explode("/", $id);
                $getYear = '20' . $getYear[0];

                $year = $id[0] . $id[1];
                $ca = strtoupper(str_replace(' ', '', $row[2]));
                $exam = strtoupper(str_replace(' ', '', $row[3]));
                $total = (int) $ca + (int) $exam;
                $unit = $rows->unit;
                $level = $rows->level;
                $remark = 'PASS';

                $gradings = DB::table('grading_system')
                    ->where(function ($query) use ($getYear) {
                        $query->where(function ($innerQuery) use ($getYear) {
                            $innerQuery->where('to', '!=', 'current')
                                ->where('from', '<=', $getYear)
                                ->where('to', '>=', $getYear);
                        })
                            ->orWhere(function ($innerQuery) use ($getYear) {
                                $innerQuery->where('to', '=', 'current')
                                    ->where('from', '<=', $getYear);
                            });
                    })
                    ->where('min_score', '<=', $total)
                    ->where('max_score', '>=', $total)
                    ->where('name', $grading)
                    ->get();
                dd($grading);
                foreach ($gradings as $gs) {
                    $grade = $gs -> grade;
                    $point = $unit * $gs -> point;
                    $remark = $gs -> remark;
                }

                if ($std_program == 'MBBS' || $std_program == 'BDS') {
                    if ($total < 50) {
                        $grade = 'F';
                        $point = $unit * 0;
                        $remark = 'FAIL';
                    } elseif ($total < 60) {
                        $grade = 'C';
                        $point = $unit * 3;
                    } elseif ($total < 70) {
                        $grade = 'B';
                        $point = $unit * 4;
                    } elseif ($total < 101) {
                        $grade = 'A';
                        $point = $unit * 5;
                    }
                } else {
                    if ($year < 14) {
                        if ($total < 40) {
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        } elseif ($total < 45) {
                            $grade = 'E';
                            $point = $unit * 1;
                        } elseif ($total < 50) {
                            $grade = 'D';
                            $point = $unit * 2;
                        } elseif ($total < 55) {
                            $grade = 'C';
                            $point = $unit * 3;
                        } elseif ($total < 60) {
                            $grade = 'C+';
                            $point = $unit * 3.5;
                        } elseif ($total < 65) {
                            $grade = 'B';
                            $point = $unit * 4;
                        } elseif ($total < 70) {
                            $grade = 'B+';
                            $point = $unit * 4.5;
                        } elseif ($total < 101) {
                            $grade = 'A';
                            $point = $unit * 5;
                        }

                    } else if ($year > 13 && $year < 18) {
                        if ($total < 45) {
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        } elseif ($total < 50) {
                            $grade = 'D';
                            $point = $unit * 2;
                        } elseif ($total < 60) {
                            $grade = 'C';
                            $point = $unit * 3;
                        } elseif ($total < 70) {
                            $grade = 'B';
                            $point = $unit * 4;
                        } elseif ($total < 101) {
                            $grade = 'A';
                            $point = $unit * 5;
                        }

                    } else if ($year > 17) {
                        if ($total < 40) {
                            $grade = 'F';
                            $point = $unit * 0;
                            $remark = 'FAIL';
                        } elseif ($total < 45) {
                            $grade = 'E';
                            $point = $unit * 1;
                        } elseif ($total < 50) {
                            $grade = 'D';
                            $point = $unit * 2;
                        } elseif ($total < 60) {
                            $grade = 'C';
                            $point = $unit * 3;
                        } elseif ($total < 70) {
                            $grade = 'B';
                            $point = $unit * 4;
                        } elseif ($total < 101) {
                            $grade = 'A';
                            $point = $unit * 5;
                        }

                    }
                }

                $records['username'] = $id;
                $records['code'] = $rows->code;
                $records['ca'] = $ca;
                $records['exam'] = $exam;
                $records['total'] = $total;
                $records['grade'] = $grade;
                $records['remark'] = $remark;
                $records['ugp'] = $point;
                $records['semester'] = $this->semester;
                $records['session'] = $this->session;
                $records['level'] = $rows->level;
                $records['current_level'] = $std_level;
                $records['final_level'] = $std_duration * 100;
                $records['program'] = $rows->program;
                $records['lecturer'] = session('username');
                $records['updated_at'] = NOW();
                // print_r($records['grade']);
                // die;
                if ($point > 0) {
                    $records['point'] = $point / $unit;
                } else {
                    $records['point'] = $point;
                }

                $exist = DB::table('results')->where(['username' => $id, 'code' => $this->course])->where('session', '<', $this->session)->first();
                if ($exist) {
                    $records['unit'] = 0;
                } else {
                    $records['unit'] = $rows->unit;
                }

                try {
                    DB::table('results')->insert($records);
                } catch (QueryException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        DB::table('results')->where(['username' => $id, 'code' => $rows->code, 'session' => $this->session, 'semester' => $this->semester])->update($records);
                    } else {

                    }
                } catch (\Exception $e) {
                    if ($e->errorInfo[1] == 1062) {
                        echo 'Double';
                        DB::table('results')->where(['username' => $id, 'code' => $rows->code, 'session' => $this->session, 'semester' => $this->semester])->update($records);
                    } else {

                    }
                } finally {

                }
            }
        }
    }
}

