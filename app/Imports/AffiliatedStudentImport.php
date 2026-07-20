<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\GraduatedStudent;

class AffiliatedStudentImport implements ToCollection
{
    protected $firstRow = true;
    private $schoolId;
    private $faculty;
    private $department;
    private $program;
    private $graduationDate;
    private $imported = 0;
    private $skipped = 0;
    private $errors = [];

    public function __construct($schoolId, $faculty, $department, $program, $graduationDate)
    {
        $this->schoolId = $schoolId;
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->graduationDate = $graduationDate;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;
            }

            // Row: 0 = SN, 1 = ID No, 2 = Name, 3 = Class of Degree
            if (empty($row[1])) {
                continue;
            }

            $username = trim(preg_replace('/\s+/', '', $row[1]));
            $fullname = trim($row[2] ?? '');
            $classOfDegree = trim($row[3] ?? '');

            if (empty($fullname)) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: Missing name for {$username}";
                continue;
            }

            if (empty($classOfDegree)) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: Missing class of degree for {$username}";
                continue;
            }

            // Format graduation date
            $readableDate = $this->graduationDate;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->graduationDate)) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $this->graduationDate);
                if ($dateObj) {
                    $readableDate = $dateObj->format('jS') . ' Day of ' . $dateObj->format('F') . ', ' . $dateObj->format('Y');
                }
            }

            // Skip records that already exist for this username and school
            if (GraduatedStudent::where(['username' => $username, 'school_id' => $this->schoolId])->exists()) {
                $this->skipped++;
                continue;
            }

            // Get award title from program
            $program = DB::table('program')->where('code', $this->program)->first();
            $degree = $program->award ?? '';

            // Generate certificate ID: UM/CERT/YEAR/SERIAL based on the highest existing serial
            $year = date('Y');
            $maxSerial = DB::table('graduated_students')
                ->where('certificate_id', 'LIKE', "UM/CERT/{$year}/%")
                ->max(DB::raw('CAST(SUBSTRING_INDEX(certificate_id, "/", -1) AS UNSIGNED)'));
            $serial = str_pad(($maxSerial ?? 0) + 1, 4, '0', STR_PAD_LEFT);
            $certificateId = "UM/CERT/{$year}/{$serial}";

            GraduatedStudent::updateOrCreate(
                ['username' => $username, 'school_id' => $this->schoolId],
                [
                    'fullname' => strtoupper($fullname),
                    'faculty' => $this->faculty,
                    'department' => $this->department,
                    'program' => $this->program,
                    'degree' => $degree,
                    'class_of_degree' => strtoupper($classOfDegree),
                    'graduation_date' => $readableDate,
                    'certificate_id' => $certificateId,
                ]
            );

            $this->imported++;
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
