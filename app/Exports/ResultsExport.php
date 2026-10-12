<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Export the final, VC-approved undergraduate results.
 *
 * The query deliberately remains a query export instead of loading the whole
 * result set into memory. This matters when an administrator exports all
 * faculties and sessions at once.
 */
class ResultsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            // Results keep the programme/department codes, while faculty is
            // owned by the programme catalogue. Joining the catalogue keeps
            // faculty filters reliable even for older result rows.
            ->leftJoin('program as result_program', 'result_program.code', '=', 'results.program')
            ->leftJoin('faculty as result_faculty', 'result_faculty.code', '=', 'result_program.faculty')
            ->leftJoin('department as result_department', function ($join) {
                $join->on('result_department.code', '=', 'result_program.department')
                    ->on('result_department.faculty', '=', 'result_program.faculty');
            })
            ->where('results.approve', 'vc')
            ->select([
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

        // Keep the workbook in the requested academic cascade. Titles are
        // used for faculty/department ordering, while level and semester use
        // their natural academic order.
        return $query
            ->orderByRaw("COALESCE(result_faculty.title, result_program.faculty, '') ASC")
            ->orderByRaw("COALESCE(result_department.title, results.department, result_program.department, '') ASC")
            ->orderByRaw("CASE WHEN results.level REGEXP '^[0-9]+$' THEN CAST(results.level AS UNSIGNED) ELSE 999999 END ASC")
            ->orderByRaw("FIELD(UPPER(results.semester), 'FIRST', 'SECOND', 'THIRD', 'RESIT') ASC")
            ->orderBy('results.session', 'ASC')
            ->orderBy('results.username', 'ASC');
    }

    public function map($row): array
    {
        $this->serialNumber++;

        return [
            $this->serialNumber,
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
                $sheet->setAutoFilter('A1:G1');
                $sheet->getStyle('A1:G1')->applyFromArray([
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
                $sheet->getStyle('A:G')->getAlignment()->setVertical('center');
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(20);
            },
        ];
    }
}
