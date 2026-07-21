<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Staff;

class StaffImport implements ToCollection, WithHeadingRow
{
    protected $data = [];
    private $faculty;
    private $department;
    private $program;
    private $unit_id;
    private $staff_category;

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
            // Check if staff_id field is not empty
            if (!empty($row['staff_id'])) {
                $sp = str_replace(' ', '', $row['staff_id']);
                $id = DB::table('users')->where('username', $sp)->value('id');

                // Skip non-existent staff — only update existing records
                if (!$id || !Staff::where('username', $sp)->exists()) {
                    continue;
                }

                // Use selected unit from form
                $unit = $unit_name ?? '';
                // Use selected staff_category from form
                $staff_category = $this->staff_category ?? '';

                // Normalize TI No. format to always be TI12345
                $ti_no = !empty($row['ti_no']) ? strtoupper(trim($row['ti_no'])) : null;
                if ($ti_no) {
                    $ti_no = preg_replace('/[^A-Z0-9]/', '', $ti_no);
                    $ti_no = preg_replace('/^TI/', '', $ti_no);
                    $ti_no = 'TI' . $ti_no;
                }

                $staffData = [
                    'username' => strtoupper($sp),
                ];

                if (!empty($row['name'] ?? '')) {
                    $staffData['name'] = strtoupper($row['name']);
                }
                if (!empty($unit)) {
                    $staffData['unit'] = $unit;
                }
                if (!empty($this->unit_id)) {
                    $staffData['unit_id'] = $this->unit_id;
                }
                if (!empty($row['phone'] ?? '')) {
                    $staffData['phone'] = strtoupper($row['phone']);
                }
                if (!empty($staff_category)) {
                    $staffData['staff_category'] = $staff_category;
                }
                if (!empty($ti_no)) {
                    $staffData['ti_no'] = $ti_no;
                }
                if (!empty($this->faculty)) {
                    $staffData['faculty'] = $this->faculty;
                }
                if (!empty($this->department)) {
                    $staffData['department'] = $this->department;
                }
                if (!empty($this->program)) {
                    $staffData['program'] = $this->program;
                }

                Staff::where('username', $sp)->update(array_merge(['user_id' => $id], $staffData));
            }
        }
    }
}

