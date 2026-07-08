<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\Hostel;

class AssignImport implements ToCollection
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
                //dd($row[1]);
                Hostel::where(['id' => $row[2], 'gender' => $row[1], 'bed_type' => 0, 'flag' => 1, 'hostel_payment' => 0, 'payment_method' => 'Online'])->update(
                    [
                        'occupant' => $row[0],
                        'status' => 1
                    ]
                );
            }
        }
    }
}

