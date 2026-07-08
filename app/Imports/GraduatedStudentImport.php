<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\GraduatedStudent;

class GraduatedStudentImport implements ToCollection
{
    protected $firstRow = true;
    private $graduationDate;
    private $imported = 0;
    private $skipped = 0;
    private $errors = [];

    public function __construct($graduationDate)
    {
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

            // Row: 0 = SN, 1 = ID No, 2 = Class of Degree
            if (empty($row[1])) {
                continue;
            }

            $username = trim(preg_replace('/\s+/', '', $row[1]));
            $classOfDegree = trim($row[2] ?? '');

            if (empty($classOfDegree)) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: Missing class of degree for {$username}";
                continue;
            }

            // Fetch student data
            $student = DB::table('students')->where('username', $username)->first();

            if (!$student) {
                $this->skipped++;
                $this->errors[] = "Row {$row[0]}: Student {$username} not found in students table";
                continue;
            }

            // Get program details for degree title (award)
            $program = DB::table('program')->where('code', $student->program)->first();
            $degree = $program->award ?? '';
            $programTitle = $program->title ?? '';

            // Generate certificate ID: UM/CERT/YEAR/SERIAL
            $year = date('Y');
            $lastCert = GraduatedStudent::where('certificate_id', 'LIKE', "UM/CERT/{$year}/%")
                ->orderBy('id', 'DESC')
                ->value('certificate_id');
            if ($lastCert) {
                $lastSerial = (int) substr($lastCert, strrpos($lastCert, '/') + 1);
                $serial = str_pad($lastSerial + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $count = GraduatedStudent::where('certificate_id', 'LIKE', "UM/CERT/{$year}/%")->count();
                $serial = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
            $certificateId = "UM/CERT/{$year}/{$serial}";

            // Convert date from Y-m-d to readable format
            $readableDate = $this->graduationDate;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->graduationDate)) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $this->graduationDate);
                if ($dateObj) {
                    $readableDate = $dateObj->format('jS') . ' Day of ' . $dateObj->format('F') . ', ' . $dateObj->format('Y');
                }
            }

            GraduatedStudent::updateOrCreate(
                ['username' => $username],
                [
                    'fullname' => $student->fullname,
                    'faculty' => $student->faculty,
                    'department' => $student->department,
                    'program' => $student->program,
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
