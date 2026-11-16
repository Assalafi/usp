<?php

namespace App\Imports;

use App\Models\Applicant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Bulk admission import for the Applicants page.
 *
 * The published admission list is authoritative by JAMB number only. The
 * selected academic entity is applied to every matched applicant that the
 * upload admits (including applicants already marked as admitted).
 */
class AdmitStudentUploadImport implements ToCollection
{
    private string $faculty;
    private string $department;
    private string $program;

    public function __construct(string $faculty, string $department, string $program)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
    }

    private bool $firstRow = true;

    private array $stats = [
        'checked' => 0,
        'admitted' => 0,
        'already_admitted' => 0,
        'not_found' => 0,
        'skipped' => 0,
        'invalid' => 0,
        'details' => [],
    ];

    public function collection(Collection $rows)
    {
        set_time_limit(0);

        foreach ($rows as $rowNumber => $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;
            }

            $jamb = $this->extractJambNumber($row);
            if ($jamb === '') {
                if (collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isNotEmpty()) {
                    $this->stats['invalid']++;
                    $this->addDetail($rowNumber + 1, '', 'No valid JAMB number was found in this row.');
                }
                continue;
            }

            $this->stats['checked']++;
            $applicant = Applicant::where('username', $jamb)->first();

            if (!$applicant) {
                $this->stats['not_found']++;
                $this->addDetail($rowNumber + 1, $jamb, 'No applicant record matched this JAMB number.');
                continue;
            }

            $status = trim((string) $applicant->status);
            if (strcasecmp($status, 'Admitted') === 0) {
                $applicant->update($this->academicAttributes());
                $this->stats['already_admitted']++;
                continue;
            }

            // The published list is the authority for this operation. Pending,
            // submitted and previously rejected applicants are admitted; any
            // other status is left untouched and reported for safety.
            if (!in_array(strtolower($status), ['pending', 'submitted', 'rejected'], true)) {
                $this->stats['skipped']++;
                $this->addDetail($rowNumber + 1, $jamb, 'Applicant status is ' . ($status ?: 'not set') . '; no change was made.');
                continue;
            }

            $applicant->update([
                ...$this->academicAttributes(),
                'status' => 'Admitted',
                'admitted' => 1,
                'admission_date' => now(),
                'admitted_by' => session('username', 'Admin'),
            ]);

            $this->stats['admitted']++;
        }
    }

    public function summary(): array
    {
        $summary = $this->stats;
        $summary['matched'] = $summary['admitted'] + $summary['already_admitted'];
        $summary['not_affected'] = $summary['not_found'] + $summary['skipped'] + $summary['invalid'];
        $summary['accounted_for'] = $summary['checked'] + $summary['invalid'];

        return $summary;
    }

    private function extractJambNumber($row): string
    {
        foreach (collect($row)->values() as $value) {
            $candidate = strtoupper(preg_replace('/\s+/', '', trim((string) $value)));
            if ($candidate === '' || preg_match('/^(S\/?NO|REG\.?|REGISTRATION|JAMB|JAMBNO|NAME)$/i', $candidate)) {
                continue;
            }

            $candidate = preg_replace('/[^A-Z0-9]/', '', $candidate);
            if (preg_match('/^20[A-Z0-9]{10,}$/', $candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    private function academicAttributes(): array
    {
        return [
            'faculty' => $this->faculty,
            'department' => $this->department,
            'program' => $this->program,
        ];
    }

    private function addDetail(int $row, string $jamb, string $reason): void
    {
        // Keep every unaffected row available to the administrator. This is
        // intentionally bounded well above normal admission-list sizes so a
        // malformed workbook cannot exhaust the session payload.
        if (count($this->stats['details']) < 10000) {
            $this->stats['details'][] = compact('row', 'jamb', 'reason');
        }
    }
}
