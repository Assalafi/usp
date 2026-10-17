<?php

namespace App\Imports;

use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ResultImport implements ToCollection
{
    protected array $report = [
        'total_rows' => 0,
        'inserted' => 0,
        'updated' => 0,
        'resits' => 0,
        'cleaned_registrations' => 0,
        'skipped' => 0,
        'failed' => 0,
        'details' => [],
    ];

    private $faculty;
    private $department;
    private $program;
    private $level;
    private $course;
    private $semester;
    private $session;
    private $per;
    private $ses;

    public function __construct($faculty, $department, $program, $course, $per, $semester, $session)
    {
        $this->faculty = $faculty;
        $this->department = $department;
        $this->program = $program;
        $this->course = $course;
        $this->per = $per;
        //$this->session = DB::table('session')->where('status', '1')->value('title');
        // $this->semester = DB::table('semester')
        //     ->where('status', '1')
        //     ->value('semester');
        $this->semester = $semester;
        $this->ses = $session;
        $this->session = $session;
    }

    public function report(): array
    {
        $this->report['successful'] = $this->report['inserted'] + $this->report['updated'];
        $this->report['not_uploaded'] = $this->report['skipped'] + $this->report['failed'];

        return $this->report;
    }

    public function collection(Collection $rows): void
    {
        set_time_limit(0);

        $course = DB::table('course')->where('code', $this->course)->first();
        if (!$course) {
            $this->addIssue(1, null, 'failed', 'The selected course is no longer available. Return to the course list, select the course again, and try once more.');
            return;
        }

        $seen = [];
        $firstNonEmptyRow = true;

        foreach ($rows as $index => $row) {
            $excelRow = $index + 1;
            if ($this->isEmptyRow($row)) {
                continue;
            }

            if ($firstNonEmptyRow) {
                $firstNonEmptyRow = false;
                if ($this->looksLikeHeader($row)) {
                    continue;
                }
            }

            $this->report['total_rows']++;
            $id = $this->normaliseId($this->cell($row, 1));
            $ca = $this->score($this->cell($row, 2));
            $exam = $this->score($this->cell($row, 3));

            if ($id === '') {
                $this->addIssue($excelRow, null, 'skipped', 'No student ID was entered. Enter the student\'s matric number in column B.');
                continue;
            }
            if (isset($seen[$id])) {
                $this->addIssue($excelRow, $id, 'skipped', 'This student ID appears more than once in the file. Keep one row for this student and remove the duplicate.');
                continue;
            }
            $seen[$id] = true;

            if ($ca === null || $exam === null) {
                $this->addIssue($excelRow, $id, 'skipped', 'Enter numbers only for the CA and examination scores in columns C and D.');
                continue;
            }
            if ($ca < 0 || $ca > 100 || $exam < 0 || $exam > 100) {
                $this->addIssue($excelRow, $id, 'skipped', 'Each CA and examination score must be between 0 and 100. Check columns C and D.');
                continue;
            }

            $student = DB::table('students')
                ->where('username', $id)
                // The curriculum structure is required to resolve the
                // programme-specific grading configuration below. Keep it in
                // the select list; omitting it makes every structured student
                // fail the upload with a generic processing error.
                ->select('program', 'level', 'department', 'structure_id')
                ->first();
            if (!$student) {
                $this->addIssue($excelRow, $id, 'skipped', 'This student ID was not found in the university records. Check the number and correct it before uploading.');
                continue;
            }

            $previousSession = $this->previousSession();
            $stalePreviousRegistration = false;
            if ($previousSession && $previousSession > '2022/2023') {
                $hasPreviousRegistration = DB::table('student_course_registration')
                    ->where(['username' => $id, 'code' => $this->course, 'session' => $previousSession])
                    ->exists();
                $hasPreviousResult = DB::table('results')
                    ->where(['username' => $id, 'code' => $this->course, 'session' => $previousSession])
                    ->exists();
                if ($hasPreviousRegistration && !$hasPreviousResult) {
                    // A registration without any result is stale/incomplete. It
                    // is cleaned up only inside the transaction that saves the
                    // current result, so a failed row leaves the old data intact.
                    $stalePreviousRegistration = true;
                }
            }

            // Some legacy student records predate curriculum structures and
            // therefore contain 0. Infer their structure only when the
            // matriculation year and the selected programme/course identify
            // exactly one matching structure. Never guess when the mapping is
            // ambiguous.
            $structureId = (int) ($student->structure_id ?? 0);
            if ($structureId <= 0) {
                $entryYear = (int) $this->entryYear($id);
                $matchingStructures = DB::table('course_structure as cs')
                    ->join('program_course_registration as pcr', 'pcr.structure_id', '=', 'cs.id')
                    ->where('pcr.program', $student->program)
                    ->where('pcr.code', $course->code)
                    ->where('cs.from_session', '<=', (string) $entryYear)
                    ->where('cs.to_session', '>=', (string) $entryYear)
                    ->distinct()
                    ->pluck('cs.id');

                if ($matchingStructures->count() === 1) {
                    $structureId = (int) $matchingStructures->first();
                }
            }

            if ($structureId <= 0) {
                $this->addIssue($excelRow, $id, 'skipped', 'The student\'s programme structure is not set up for this course. Ask the Department or Academic Records office to assign the correct structure before uploading.');
                continue;
            }

            $stdDuration = DB::table('program')->where('code', $student->program)->value('duration');
            $gradingName = DB::table('program_course_registration')
                ->where(['code' => $course->code, 'program' => $student->program, 'structure_id' => $structureId])
                ->value('grading');
            if (!$gradingName) {
                $this->addIssue($excelRow, $id, 'skipped', 'This course has not been added to the student\'s programme structure. Ask the Department or Academic Records office to add it before uploading.');
                continue;
            }

            $total = round($ca + $exam, 0, PHP_ROUND_HALF_UP);
            if ($total > 100) {
                $this->addIssue($excelRow, $id, 'skipped', 'The CA and examination scores add up to more than 100. Check the values in columns C and D.');
                continue;
            }
            if (strtoupper((string) $this->semester) === 'RESIT' && $total > 50) {
                $total = 50;
            }

            $entryYear = $this->entryYear($id);
            $grading = DB::table('grading_system')
                ->where(function ($query) use ($entryYear) {
                    $query->where(function ($inner) use ($entryYear) {
                        $inner->where('to', '!=', 'current')
                            ->where('from', '<=', $entryYear)
                            ->where('to', '>=', $entryYear);
                    })->orWhere(function ($inner) use ($entryYear) {
                        $inner->where('to', 'current')->where('from', '<=', $entryYear);
                    });
                })
                ->where('min_score', '<=', $total)
                ->where('max_score', '>=', $total)
                ->where('name', $gradingName)
                ->first();

            if (!$grading) {
                $this->addIssue($excelRow, $id, 'skipped', 'This total score does not match the approved grading scale for the student\'s programme. Confirm the scores or ask Academic Records to check the grading scale.');
                continue;
            }

            $courseUnit = (int) ($course->unit ?? 0);
            $product = $courseUnit * (float) $grading->point;
            $previousApprovedResult = DB::table('results')
                ->where(['username' => $id, 'code' => $this->course, 'approve' => 'vc'])
                ->where('session', '<', $this->session)
                ->exists();
            $resultUnit = $previousApprovedResult ? 0 : $courseUnit;

            $records = [
                'grading' => $grading->ref,
                'username' => $id,
                'code' => $course->code,
                'ca' => $ca,
                'exam' => $exam,
                'total' => $total,
                'per' => $this->per,
                'grade' => $grading->grade,
                'remark' => $grading->remark,
                'ugp' => $product,
                'unit' => $resultUnit,
                'point' => $courseUnit > 0 ? $product / $courseUnit : 0,
                'semester' => $this->semester,
                'session' => $this->session,
                'level' => $course->level ?? null,
                'current_level' => $student->level,
                'final_level' => ((int) ($stdDuration ?? 0)) * 100,
                'program' => $course->program,
                'department' => $student->department,
                'lecturer' => session('username'),
                'updated_at' => now(),
            ];

            try {
                $transaction = DB::transaction(function () use ($id, $records, $previousSession, $stalePreviousRegistration) {
                    $existing = DB::table('results')
                        ->where([
                            'username' => $id,
                            'code' => $this->course,
                            'session' => $this->session,
                            'semester' => $this->semester,
                        ])
                        ->lockForUpdate()
                        ->first();

                    if ($existing && strtolower((string) ($existing->approve ?? 'system')) !== 'system') {
                        return ['operation' => 'protected', 'removed_registrations' => 0];
                    }

                    $removedRegistrations = 0;
                    if ($stalePreviousRegistration && $previousSession) {
                        $removedRegistrations = DB::table('student_course_registration')
                            ->where([
                                'username' => $id,
                                'code' => $this->course,
                                'session' => $previousSession,
                            ])
                            ->lockForUpdate()
                            ->delete();
                    }

                    if ($existing) {
                        DB::table('results')->where('id', $existing->id)->update($records);
                        return ['operation' => 'updated', 'removed_registrations' => $removedRegistrations];
                    }

                    DB::table('results')->insert($records);
                    return ['operation' => 'inserted', 'removed_registrations' => $removedRegistrations];
                });

                $operation = $transaction['operation'];
                if ($operation === 'protected') {
                    $this->addIssue($excelRow, $id, 'skipped', 'A result for this session and semester has already been submitted or approved. It cannot be replaced here; use the correction process if a change is needed.');
                    continue;
                }

                $this->report[$operation]++;
                $removedRegistrations = (int) ($transaction['removed_registrations'] ?? 0);
                if ($removedRegistrations > 0) {
                    $this->report['cleaned_registrations'] += $removedRegistrations;
                }
                if ($resultUnit === 0 && $previousApprovedResult) {
                    $this->report['resits']++;
                    $message = ucfirst($operation) . ' as a resit. An earlier approved attempt exists, so this attempt will not add the course units again.';
                } else {
                    $message = ucfirst($operation) . ' and saved for approval.';
                }
                if ($removedRegistrations > 0) {
                    $message .= " An old course-registration entry from the previous session was also removed because no result had been recorded.";
                }
                $this->addDetail($excelRow, $id, 'success', $message);
            } catch (QueryException $exception) {
                $this->addIssue($excelRow, $id, 'failed', 'The result could not be saved at this time. No change was made for this student. Try again; if it continues, contact Academic Records.');
            } catch (\Throwable $exception) {
                report($exception);
                $this->addIssue($excelRow, $id, 'failed', 'A temporary system problem prevented this result from being saved. No change was made. Try again or contact Academic Records.');
            }
        }
    }

    private function addIssue(int $row, ?string $username, string $status, string $message): void
    {
        if ($status === 'skipped') {
            $this->report['skipped']++;
        } else {
            $this->report['failed']++;
        }
        $this->addDetail($row, $username, $status, $message);
    }

    private function addDetail(int $row, ?string $username, string $status, string $message): void
    {
        $this->report['details'][] = [
            'row' => $row,
            'username' => $username ?: '—',
            'status' => $status,
            'message' => $message,
        ];
    }

    private function cell($row, int $index): mixed
    {
        if ($row instanceof Collection) {
            return $row->get($index);
        }
        if (is_array($row)) {
            return $row[$index] ?? null;
        }
        if ($row instanceof \ArrayAccess) {
            return $row->offsetExists($index) ? $row[$index] : null;
        }
        return null;
    }

    private function isEmptyRow($row): bool
    {
        foreach (range(0, 3) as $index) {
            if (trim((string) $this->cell($row, $index)) !== '') {
                return false;
            }
        }
        return true;
    }

    private function looksLikeHeader($row): bool
    {
        $text = strtolower(implode(' ', array_map(
            fn ($value) => trim((string) $value),
            array_map(fn ($index) => $this->cell($row, $index), range(0, 3))
        )));
        return (bool) preg_match('/matric|username|student|exam|score|ca/', $text);
    }

    private function normaliseId($value): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', trim((string) $value)));
    }

    private function score($value): ?float
    {
        $value = trim((string) $value);
        if ($value === '' || !is_numeric(str_replace(',', '', $value))) {
            return null;
        }
        return (float) str_replace(',', '', $value);
    }

    private function entryYear(string $username): string
    {
        $parts = explode('/', $username);
        return isset($parts[0]) && preg_match('/^\d{2}$/', $parts[0])
            ? '20' . $parts[0]
            : (string) date('Y');
    }

    private function previousSession(): ?string
    {
        if (!preg_match('/^(\d{4})\/(\d{4})$/', (string) $this->session, $matches)) {
            return null;
        }
        $start = (int) $matches[1] - 1;
        return $start . '/' . (int) $matches[1];
    }
}
