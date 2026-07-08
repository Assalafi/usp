<?php
namespace App\Imports;

use App\Models\Applicant;
use App\Models\Jamb;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ApplicantImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true;
    private $faculty;
    private $department;
    private $program;
    private $session;
    private $upload_type;

    public function __construct($faculty, $department, $program, $session)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->session = $session;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;  // Skip the first row
            }

            // Check if sn field is not empty and is number

            if (!empty($row[0])) {
                // $sp = explode("/", $row[1]);
                $sp = str_replace(' ', '', $row[1]);
                $id = DB::table('users')->where('username', $sp)->value('id');
                $fullname = $row[2];
                if ($row[3] == 'M' || $row[3] == 'F') {
                    $gender = strtoupper($row[3]) == 'M' ? 'Male' : 'Female';
                } else {
                    return redirect()->back()->with('error', 'Gender must be M or F');
                }
                $state = strtoupper($row[4]);
                $lga = strtoupper($row[5]);
                $score = $row[6];
                $mode = $score > 0 ? 'UTME' : 'DE';
                $sub1 = $row[7];
                $sco1 = $row[8];
                $sub2 = $row[9];
                $sco2 = $row[10];
                $sub3 = $row[11];
                $sco3 = $row[12];
                $sub4 = 'English';
                $sco4 = $row[13];

                // check if is UTME and one of the subjects is empty or score is not greater than 0

                if ($mode == 'UTME' && ($sub1 == '' || $sub2 == '' || $sub3 == '' || $sub4 == '' || $sco1 == '' || $sco2 == '' || $sco3 == '' || $sco4 == '')) {
                    return redirect()->back()->with('error', 'UTME subjects and scores are required');
                }

                // Extract surname, firstName, and otherName from fullname
                $nameParts = explode(' ', trim(strtoupper($fullname)));
                $surname = $nameParts[0] ?? '';
                $firstName = $nameParts[1] ?? '';
                $otherName = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : null;
                if ($id == 0 || $id == null) {
                    User::updateOrCreate(
                        ['username' => $sp],
                        [
                            'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                            'accType' => 'Applicant',
                            'position' => 'Applicant',
                            'name' => strtoupper($row[2]),
                            'status' => '0'
                        ]
                    );
                    $id = User::where('username', $sp)->value('id');
                }

                Applicant::updateOrCreate(
                    ['username' => $sp],
                    [
                        'user_id' => $id,
                        'username' => strtoupper($sp),
                        'fullname' => strtoupper($fullname),
                        'surname' => $surname,
                        'first_name' => $firstName,
                        'other_name' => $otherName,
                        'gender' => $gender,
                        'state' => $state,
                        'lga' => $lga,
                        'score' => $score,
                        'faculty' => $this->faculty,
                        'department' => $this->department,
                        'program' => $this->program,
                        'mode' => $mode,
                        'session' => $this->session
                    ]
                );
                // Jamb has id, user_id, username, subject, score. add 4 rows at same time, using single query. but all record first incase it exists
                if ($mode == 'UTME') {
                    $jamb = Jamb::where('user_id', $id)->delete();
                    // include uuid for id
                    // create_at and updated_at
                    Jamb::insert([
                        [
                            'id' => Str::uuid(),
                            'user_id' => $id,
                            'username' => strtoupper($sp),
                            'subject' => $sub1,
                            'score' => $sco1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'id' => Str::uuid(),
                            'user_id' => $id,
                            'username' => strtoupper($sp),
                            'subject' => $sub2,
                            'score' => $sco2,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'id' => Str::uuid(),
                            'user_id' => $id,
                            'username' => strtoupper($sp),
                            'subject' => $sub3,
                            'score' => $sco3,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'id' => Str::uuid(),
                            'user_id' => $id,
                            'username' => strtoupper($sp),
                            'subject' => $sub4,
                            'score' => $sco4,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }
        }
    }
}
