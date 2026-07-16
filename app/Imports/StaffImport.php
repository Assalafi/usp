<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;

class StaffImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true;
    private $faculty;
    private $department;
    private $program;
    private $unit_id;
    private $staff_category;
    private $upload_type;

    public function __construct($faculty, $department, $program, $unit_id = null, $staff_category = null)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->unit_id = $unit_id;
        $this->staff_category = $staff_category;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);

        // Get unit name from unit_id if provided
        $unit_name = null;
        if ($this->unit_id) {
            $unit_name = DB::table('units')->where('id', $this->unit_id)->value('name');
        }

        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }

            // Check if email field is not empty
            if (!empty($row[1])) {
                //$sp = explode("/", $row[1]);
                $sp = str_replace(' ', '', $row[1]);
                $id = DB::table('users')->where('username', $sp)->value('id');

                // Use selected unit or fall back to Excel file value
                $unit = $unit_name ? $unit_name : strtoupper($row[3]);
                // Use selected staff_category or fall back to Excel file value
                $staff_category = $this->staff_category ? $this->staff_category : strtoupper($row[19]);

                if ($id > 0) {
                    Staff::updateOrCreate(
                        ['username' => $sp],
                        [
                            'user_id' => $id,
                            'username' => strtoupper($sp),
                            'name' => strtoupper($row[2]),
                            'unit' => $unit,
                            'unit_id' => $this->unit_id,
                            'date_of_first_appointment' => date('Y-m-d', strtotime($row[4])),
                            'rank_of_first_appointment' => strtoupper($row[5]),
                            'date_of_asumption' => date('Y-m-d', strtotime($row[6])),
                            'date_of_comfirmation' => date('Y-m-d', strtotime($row[7])),
                            'date_of_last_promotion' => date('Y-m-d', strtotime($row[8])),
                            'current_rank' => strtoupper($row[9]),
                            'grade' => strtoupper($row[10]),
                            'step' => strtoupper($row[11]),
                            'date_of_birth' => date('Y-m-d', strtotime($row[12])),
                            'state' => strtoupper($row[13]),
                            'lga' => strtoupper($row[14]),
                            'employee_status' => strtoupper($row[15]),
                            'bank_name' => strtoupper($row[16]),
                            'account_number' => strtoupper($row[17]),
                            'phone' => strtoupper($row[18]),
                            'staff_category' => $staff_category,
                            'remark' => strtoupper($row[20]),
                            'faculty' => $this -> faculty,
                            'department' => $this -> department,
                            'program' => $this -> program,
                        ]
                    );

                } else {
                    // Check if user already exists before updating
                    $existingUser = User::where('username', $sp)->first();

                    if ($existingUser) {
                        // Update existing user without changing password
                        User::where('username', $sp)->update([
                            'accType' => 'Staff',
                            'position' => 'Staff',
                            'name' => strtoupper($row[2]),
                            'status' => '1'
                        ]);
                    } else {
                        // Create new user with username as password
                        User::create([
                            'username' => $sp,
                            'password' => Hash::make(strtoupper($sp)),
                            'accType' => 'Staff',
                            'position' => 'Staff',
                            'name' => strtoupper($row[2]),
                            'status' => '1'
                        ]);
                    }
                }

                $id = DB::table('users')->where('username', $sp)->value('id');
                // 'gender' => strtoupper(preg_replace('/\s+/','',$row[3]))
                Staff::updateOrCreate(
                    ['username' => $sp],
                    [
                        'user_id' => $id,
                        'username' => strtoupper($sp),
                        'name' => strtoupper($row[2]),
                        'unit' => $unit,
                        'unit_id' => $this->unit_id,
                        'date_of_first_appointment' => date('Y-m-d', strtotime($row[4])),
                        'rank_of_first_appointment' => strtoupper($row[5]),
                        'date_of_asumption' => date('Y-m-d', strtotime($row[6])),
                        'date_of_comfirmation' => date('Y-m-d', strtotime($row[7])),
                        'date_of_last_promotion' => date('Y-m-d', strtotime($row[8])),
                        'current_rank' => strtoupper($row[9]),
                        'grade' => strtoupper($row[10]),
                        'step' => strtoupper($row[11]),
                        'date_of_birth' => date('Y-m-d', strtotime($row[12])),
                        'state' => strtoupper($row[13]),
                        'lga' => strtoupper($row[14]),
                        'employee_status' => strtoupper($row[15]),
                        'bank_name' => strtoupper($row[16]),
                        'account_number' => strtoupper($row[17]),
                        'phone' => strtoupper($row[18]),
                        'staff_category' => $staff_category,
                        'remark' => strtoupper($row[20]),
                        'faculty' => $this -> faculty,
                        'department' => $this -> department,
                        'program' => $this -> program,
                    ]
                );
            }
        }
    }
}

