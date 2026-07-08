<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\Course;

class CourseImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }

            // Check if email field is not empty
            if (!empty($row[0])) {
                Course::updateOrCreate(
                    ['code' => strtoupper(str_replace(' ', '', $row[0]))],
                    ['title' => strtoupper(addslashes($row[1])),
                    'type' => strtoupper($row[2]),
                    'class' => strtoupper($row[3]),
                    'unit' => strtoupper($row[4]),
                    'level' => strtoupper($row[5]),
                    'semester' => strtoupper($row[6]),
                    'faculty' => strtoupper($row[7]),
                    'department' => strtoupper($row[8]),
                    'program' => strtoupper($row[9]),
                    'status' => '1'
                    ]
                );
            }
        }
    }
}

