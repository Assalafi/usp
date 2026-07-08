<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Admitted;

class AdmittedImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    private $faculty;
    private $department;
    private $program;
    private $upload_type;

    public function __construct($faculty, $department, $program, $upload_type)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->upload_type = $upload_type;
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

            //echo $row[1].$this -> upload_type;
            //die;
            if (!empty($row[0]) && strtolower($this -> upload_type) == 'new') {
                try {
                    $gender = 'M';
                    $sex = 'MALE';
                    $gen = str_replace(' ', '', $row[3]);
                    if($gen == 'Male' || $gen == 'male' || $gen == 'MALE' || $gen == 'M' || $gen == 'm'){
                        $gender = 'M';
                    }else if($gen == 'Female' || $gen == 'female' || $gen == 'FEMALE' || $gen == 'F' || $gen == 'f'){
                        $gender = 'F';
                        $sex = 'FEMALE';
                    }else{
                        return 'Gender Error';
                        die;
                    }
                    $session = '2024/2025';

                    //return $row[1];
                    //die;
                    $fullname = $row[2];
                    $fullname = trim($fullname);
                    $names = explode(' ', $fullname);
                    $count = count($names);
                    if($count == 3){
                        $last_name = $names[0];
                        $first_name = $names[1];
                        $other_name = $names[2];
                    }else if($count == 4){
                        $last_name = $names[0];
                        $first_name = $names[1];
                        $other_name = $names[2].' '.$names[3];
                    }else if($count == 5){
                        $last_name = $names[0];
                        $first_name = $names[1];
                        $other_name = $names[2].' '.$names[3].' '.$names[4];
                    }else if($count == 2){
                        $last_name = $names[0];
                        $first_name = $names[1];
                        $other_name = '';
                    }else if($count == 1){
                        $last_name = $names[0];
                        $first_name = '';
                        $other_name = '';
                    }else{
                        return 'Name Error';
                        die;
                    }

                    Admitted::updateOrCreate(
                        ['jamb_no' => $row[1]],
                        [
                            'last_name' => strtoupper($last_name),
                            'first_name' => strtoupper($first_name),
                            'middle_name' => strtoupper($other_name),
                            'program' => strtoupper($this -> program),
                            'department' => strtoupper($this -> department),
                            'faculty' => strtoupper($this -> faculty),
                            'gender' => strtoupper($gender),
                            'session' => $session,
                            'state' => strtoupper($row[4]),
                            'lga' => strtoupper($row[5]),
                            'fullname' => strtoupper($first_name).' '.strtoupper($last_name).' '.strtoupper($other_name),
                        ]
                    );

                } catch (QueryException $e) {
                    //dd($e);
                    return redirect()->back()->with('error', 'Something went Wrong. Check Your Upload File or Contact Support Team.');
                } catch (\Exception $e) {
                    dd($e);
                    //print_r($e);
                    //die;
                    return redirect()->back()->with('error', 'Something went Wrong. Check Your Upload File or Contact Support Team.');
                } finally {}

            }
        }
    }
}

