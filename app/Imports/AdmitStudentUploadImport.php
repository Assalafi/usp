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

class AdmitStudentUploadImport implements ToCollection
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

            if (!empty($row[1])) {
                // $sp = explode("/", $row[1]);
                $sp = str_replace(' ', '', $row[1]);

                Applicant::where(['username' => $sp, 'status' => 'Pending'])->update([
                    'faculty' => $this->faculty,
                    'department' => $this->department,
                    'program' => $this->program,
                    'admitted' => 1
                ]);

                Applicant::where(['username' => $sp, 'status' => 'Submitted'])->update([
                    'status' => 'Admitted',
                    'admitted_by' => session('username'),
                    'faculty' => $this->faculty,
                    'department' => $this->department,
                    'program' => $this->program,
                    'admission_date' => now(),
                    'admitted' => 1
                ]);

                Applicant::where(['username' => $sp, 'status' => 'Admitted'])->update([
                    'status' => 'Admitted',
                    'admitted_by' => session('username'),
                    'faculty' => $this->faculty,
                    'department' => $this->department,
                    'program' => $this->program,
                    'admission_date' => now(),
                    'admitted' => 1
                ]);
            }
        }
    }
}
