<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AffiliatedSchoolsImport implements ToCollection
{
    protected $firstRow = true;
    private $imported = 0;
    private $skipped = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;
            }

            // Row: 0 = SN, 1 = School Name
            if (empty($row[1])) {
                continue;
            }

            $name = trim($row[1]);
            if (empty($name)) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: Missing school name";
                continue;
            }

            try {
                DB::table('affiliated_schools')->insert([
                    'name' => strtoupper($name),
                    'faculty' => null,
                    'department' => null,
                    'program' => null,
                    'status' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: " . $e->getMessage();
            }
        }
    }

    public function getImported()
    {
        return $this->imported;
    }

    public function getSkipped()
    {
        return $this->skipped;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
