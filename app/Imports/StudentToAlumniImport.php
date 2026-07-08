<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Alumni;
use App\Models\Admitted;

class StudentToAlumniImport implements ToCollection
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
        $nullJambNo = [];
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }

            // Check if email field is not empty

            //echo $row[1].$this -> upload_type;
            //die;
            if (!empty($row[0]) && strtolower($this->upload_type) == 'new') {

                // check if $row[2] is not 100 or 200 return invalid level
                $level = trim($row[2]);
                $mode = strtoupper(trim($row[3]));
                if (($level == '100' && $mode = 'UTME') || ($level == '200' && $mode == 'DE')) {

                }else{
                    return redirect()->back()->with('error', 'Invalid Level or Entry Mode, level must be UTME 100 or DE 200');
                }

                try {
                     // Initialize an array to store null jamb_no values

                    $admitted = Admitted::where('jamb_no', $row[1])->first();

                    if (is_null($admitted) || is_null($admitted->jamb_no) || $this -> department != $admitted->departments->code || $this -> program != $admitted->programs->code || $this -> faculty != $admitted->facultys->code) {
                        $nullJambNo[] = $row[1]; // Add the row with null jamb_no to the array
                    } else {
                        $user = User::updateOrCreate(
                            ['username' => $admitted->jamb_no],
                            [
                                'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                                'accType' => 'Alumni',
                                'gender' => $admitted->gender == 'F' ? 'FEMALE' : 'MALE',
                                'name' => $admitted->fullname,
                                'status' => '0'
                            ]
                        );

                        $f = $admitted->facultys->no;
                        $d = $admitted->departments->no;
                        $session = $admitted->session;

                        Student::updateOrCreate(
                            ['jamb_no' => $row[1]],
                            [
                                'user_id' => $user->id,
                                'last_name' => $admitted->last_name,
                                'first_name' => $admitted->first_name,
                                'other_name' => $admitted->middle_name,
                                'program' => $admitted->program,
                                'department' => $admitted->department,
                                'faculty' => $admitted->faculty,
                                'id_format' => '/' . str_pad($f, 2, '0', STR_PAD_LEFT) . '/' . str_pad($d, 2, '0', STR_PAD_LEFT) . '/',
                                'gender' => $admitted->gender,
                                'session_of_entry' => $session,
                                'level_of_entry' => strtoupper($row[2]),
                                'level' => strtoupper($row[2]),
                                'mode_of_entry' => strtoupper($row[3]),
                                'state_origin' => $admitted->state,
                                'lga_origin' => $admitted->lga,
                                'contact_phone' => $row[4],
                                'fullname' => $admitted->fullname,
                                'status' => '3',
                                'school_fee' => '1'
                            ]
                        );
                    }
                } catch (QueryException $e) {
                    dd($e->getMessage());
                    return redirect()->back()->with('error', 'Something went Wrong. Check Your Upload File or Contact Support Team.');
                } catch (\Exception $e) {
                    dd($e->getMessage());
                    return redirect()->back()->with('error', 'Something went Wrong. Check Your Upload File or Contact Support Team.');
                } finally {
                }
            }
            if (!empty($row[0]) && strtolower($this->upload_type) == 'old') {
                $user = User::where('username', $row[1])->first();
                if ($user) {
                } else {
                    User::updateOrCreate(
                        ['username' => $row[1]],
                        [
                            'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                            'accType' => 'Alumni',
                            'name' => strtoupper($row[2] . ' ' . $row[3] . ' ' . $row[4]),
                            'status' => '0'
                        ]
                    );
                }

                $id = DB::table('users')->where('username', $row[1])->value('id');
                $f = DB::table('faculty')->where('code', $this->faculty)->value('no');
                $d = DB::table('department')->where('code', $this->department)->value('no');
                $fullname = strtoupper($row[2] . ' ' . $row[3] . ' ' . $row[4]);
                $updateData = [
                    'user_id' => $id,
                    'username' => $row[1],
                    'first_name' => $row[2] ? strtoupper($row[2]) : null,
                    'last_name' => $row[3] ? strtoupper($row[3]) : null,
                    'other_name' => $row[4] ? strtoupper($row[4]) : null,
                    'program' => strtoupper($this->program),
                    'department' => strtoupper($this->department),
                    'faculty' => strtoupper($this->faculty),
                    'gender' => $row[8] ? strtoupper($row[8]) : null,
                    'marital_status' => $row[9] ? strtoupper($row[9]) : null,
                    'state_origin' => $row[10] ? strtoupper($row[10]) : null,
                    'lga_origin' => $row[11] ? strtoupper($row[11]) : null,
                    'contact_phone' => $row[12] ? strtoupper($row[12]) : null,
                    'sponsor_name' => $row[13] ? strtoupper($row[13]) : null,
                    'sponsor_address' => $row[14] ? strtoupper($row[14]) : null,
                    'sponsor_phone' => $row[15] ? strtoupper($row[15]) : null,
                    'level' => $row[16] ? strtoupper($row[16]) : null,
                    'contact_email' => $row[17] ? strtoupper($row[17]) : null,
                    'nin' => $row[18] ? strtoupper($row[18]) : null,
                    'date_of_birth' => $row[19] ? strtoupper($row[19]) : null,
                    'jamb_no' => $row[20] ? strtoupper($row[20]) : null,
                    'fullname' => $row[2] ? $fullname : null,
                    'id_format' => '/0' . $f . '/0' . $d . '/',
                    'status' => '3',
                    'session_of_entry' => '2021/2022',
                    'school_fee' => '1',
                    'level_flag' => '0'
                ];
                
                    Alumni::updateOrCreate(
                        ['username' => $row[1]],
                        [
                            'user_id' => $id,
                            'password' => $password,
                            'username' => strtolower($row[1]),
                            'fullname' => strtoupper($fullname),
                            'gender' => strtoupper($row[8]) ?? null,
                            'phone' => $row[12] ?? null,
                            'email' => $row[1] ?? null,
                            'id_no' => $row[3] ?? null,
                            'year' => $row[16] ?? null,
                            'program' => $this->program ?? null,
                        ]
                    );

                // Remove any key with null or empty value except mandatory fields
                $updateData = array_filter($updateData, function ($value, $key) {
                    return $key === 'user_id' || !is_null($value) && $value !== '';
                }, ARRAY_FILTER_USE_BOTH);

                Student::updateOrCreate(
                    ['user_id' => $id],
                    $updateData
                );
            }
        }

        //dd($nullJambNo);

        if (!empty($nullJambNo)) {
            $errorMessage = "Error!!!, the following candidate(s) are either yet to be uploaded/admitted or admitted in wrong program : ";
            foreach ($nullJambNo as $candidate) {
                $errorMessage .= (isset($candidate) ? $candidate : 'Unknown') . ","; // Assuming $candidate[0] holds a name or identifier
            }

            //dd($nullJambNo);

            // return redirect()->back()->with('error', $errorMessage);
            session() -> put('studentImportStatus', 'error');
            session() -> put('studentImportMsg', $errorMessage);
        }else{
            //return redirect()->back()->with('success', 'Students imported successfully.');
            session() -> put('studentImportStatus', 'success');
            session() -> put('studentImportMsg', 'Students imported successfully.');
        }
    }
}
