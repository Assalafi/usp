<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Alumni;

class AlumniImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true;
    private $faculty;
    private $department;
    private $program;
    private $upload_type;

    public function __construct($faculty, $department, $program)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
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
                //$sp = explode("/", $row[1]);
                $username = str_replace(' ', '', $row[9]);
                // remove any white space for username
                $username = preg_replace('/\s+/', '', $username);
                $fullname = $row[2] . ' ' . $row[3] . ' ' . $row[4];
                $password = $row[1];
                $id = DB::table('users')->where('username', $username)->value('id');
                if ($id > 0) {
                    Alumni::updateOrCreate(
                        ['username' => $username],
                        [
                            'user_id' => $id,
                            'password' => $password,
                            'username' => strtolower($username),
                            'fullname' => strtoupper($fullname),
                            'gender' => strtoupper($row[5]),
                            'phone' => $row[10],
                            'email' => $username,
                            'id_no' => $row[6],
                            'year' => $row[7],
                            'program' => $row[8],
                        ]
                    );

                } else {
                    User::updateOrCreate(
                        ['username' => $username],
                        [
                            'password' => Hash::make($password),
                            'accType' => 'Alumni',
                            'position' => 'Alumni',
                            'name' => strtoupper($fullname),
                            'status' => '1'
                        ]
                    );
                }

                $id = DB::table('users')->where('username', $username)->value('id');
                // 'gender' => strtoupper(preg_replace('/\s+/','',$row[3]))
                Alumni::updateOrCreate(
                    ['username' => $username],
                    [
                        'user_id' => $id,
                        'password' => $password,
                        'username' => strtolower($username),
                        'fullname' => strtoupper($fullname),
                        'gender' => strtoupper($row[5]),
                        'phone' => $row[10],
                        'email' => $username,
                        'id_no' => $row[6],
                        'year' => $row[7],
                        'program' => $row[8],
                    ]
                );
            }
        }
    }
}

