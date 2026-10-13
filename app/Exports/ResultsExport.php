<?php

namespace App\Exports;

use Generator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Export the final, VC-approved undergraduate results.
 *
 * The rows are read through a database cursor so an administrator can export
 * all faculties without re-running a large sorted query for every page.
 */
class ResultsExport implements FromGenerator, WithHeadings, WithMapping, WithEvents
{
    private array $filters;
    private int $serialNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = array_filter($filters, static function ($value): bool {
            return $value !== null && $value !== '' && $value !== 'all';
        });
    }

    public function query()
    {
        $query = DB::table('results')
            // Results keep programme/department/course codes. Resolve each
            // code to its human-readable title so the workbook remains useful
            // when it is shared outside the portal.
            ->leftJoin('program as result_program', 'result_program.code', '=', 'results.program')
            ->leftJoin('faculty as result_faculty', 'result_faculty.code', '=', 'result_program.faculty')
            ->leftJoin('department as result_department', 'result_department.code', '=', 'results.department')
            ->leftJoin('department as program_department', 'program_department.code', '=', 'result_program.department')
            ->leftJoin('course as result_course', 'result_course.code', '=', 'results.code')
            ->where('results.approve', 'vc')
            ->select([
                'result_faculty.code as faculty_code',
                'result_faculty.title as faculty_title',
                'result_program.faculty as program_faculty_code',
                'result_department.code as result_department_code',
                'result_department.title as result_department_title',
                'program_department.code as program_department_code',
                'program_department.title as program_department_title',
                'results.code as course_code',
                'result_course.title as course_title',
                'results.username as id_no',
                'results.ca',
                'results.exam',
                'results.total',
                'results.grade',
                'results.department',
                'results.level',
                'results.session',
                'results.semester',
            ]);

        foreach (['faculty', 'department', 'level', 'session', 'semester'] as $field) {
            if (!array_key_exists($field, $this->filters)) {
                continue;
            }

            if ($field === 'faculty') {
                $query->where('result_program.faculty', $this->filters[$field]);
            } elseif ($field === 'department') {
                $query->whereRaw(
                    "COALESCE(NULLIF(results.department, ''), result_program.department) = ?",
                    [$this->filters[$field]]
                );
            } else {
                $query->where('results.' . $field, $this->filters[$field]);
            }
        }

        // Keep the workbook in the requested academic cascade: faculty,
        // department, level, semester, session, then course and student.
        return $query
            ->orderByRaw("COALESCE(result_program.faculty, '') ASC")
            ->orderByRaw("COALESCE(NULLIF(results.department, ''), result_program.department, '') ASC")
            ->orderByRaw("CASE results.level WHEN '100' THEN 1 WHEN '200' THEN 2 WHEN '300' THEN 3 WHEN '400' THEN 4 WHEN '500' THEN 5 WHEN '600' THEN 6 WHEN '700' THEN 7 WHEN '800' THEN 8 ELSE 999999 END ASC")
            ->orderByRaw("CASE UPPER(results.semester) WHEN 'FIRST' THEN 1 WHEN 'SECOND' THEN 2 WHEN 'THIRD' THEN 3 WHEN 'RESIT' THEN 4 ELSE 999999 END ASC")
            ->orderBy('results.session', 'ASC')
            ->orderBy('results.code', 'ASC')
            ->orderBy('results.username', 'ASC');
    }

    /**
     * Read the sorted result set with one database cursor. The old query
     * export paged in 100-row chunks, repeating the expensive all-faculty
     * sort thousands of times before Excel could finish.
     */
    public function generator(): Generator
    {
        foreach ($this->query()->cursor() as $row) {
            yield $row;
        }
    }

    public function map($row): array
    {
        $this->serialNumber++;

        return [
            $this->serialNumber,
            $this->titleOrCode($row->faculty_title ?? null, $row->faculty_code ?? $row->program_faculty_code ?? null),
            $this->departmentName($row),
            (string) ($row->level ?? ''),
            (string) ($row->semester ?? ''),
            (string) ($row->session ?? ''),
            (string) ($row->course_code ?? ''),
            (string) ($row->course_title ?? $row->course_code ?? ''),
            (string) ($row->id_no ?? ''),
            $row->ca,
            $row->exam,
            $row->total,
            $row->grade,
            'Approved',
        ];
    }

    public function headings(): array
    {
        return [
            'SN',
            'FACULTY',
            'DEPARTMENT',
            'LEVEL',
            'SEMESTER',
            'SESSION',
            'COURSE CODE',
            'COURSE TITLE',
            'ID NO.',
            'CA',
            'EXAM',
            'TOTAL',
            'GRADE',
            'STATUS',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => static function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:N1');
                $sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '1769AA'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(16);
                $sheet->getColumnDimension('H')->setWidth(42);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('N')->setWidth(20);
            },
        ];
    }

    private function titleOrCode(?string $title, ?string $code): string
    {
        return trim((string) ($title ?: $code ?: ''));
    }

    private function departmentName(object $row): string
    {
        return $this->titleOrCode(
            $row->result_department_title ?? $row->program_department_title ?? null,
            $row->result_department_code ?? $row->program_department_code ?? $row->department ?? null
        );
    }
}
