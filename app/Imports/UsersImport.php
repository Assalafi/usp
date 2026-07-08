<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersImport implements ToCollection
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
                $user = User::where('username', $row[0])->first();
                User::updateOrCreate(
                    ['username' => $row[0]],
                    ['password' => Hash::make('Hostel@2023'),
                    'accType' => 'Student',
                    'name' => strtoupper($row[1]),
                    'status' => '0']
                );
            }
        }
    }
}

