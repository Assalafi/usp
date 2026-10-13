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
            // Results keep the programme/department codes, while faculty is
            // owned by the programme catalogue. Joining the catalogue keeps
            // faculty filters reliable even for older result rows.
            ->leftJoin('program as result_program', 'result_program.code', '=', 'results.program')
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
        // not exported, so the stable catalogue codes are used for faculty
        // and department ordering. This avoids joining two more catalogue
        // tables for every one of a large export's chunks.
        return $query
            ->orderByRaw("COALESCE(result_program.faculty, '') ASC")
            ->orderByRaw("COALESCE(NULLIF(results.department, ''), result_program.department, '') ASC")
            ->orderByRaw("CASE results.level WHEN '100' THEN 1 WHEN '200' THEN 2 WHEN '300' THEN 3 WHEN '400' THEN 4 WHEN '500' THEN 5 WHEN '600' THEN 6 WHEN '700' THEN 7 WHEN '800' THEN 8 ELSE 999999 END ASC")
            ->orderByRaw("CASE UPPER(results.semester) WHEN 'FIRST' THEN 1 WHEN 'SECOND' THEN 2 WHEN 'THIRD' THEN 3 WHEN 'RESIT' THEN 4 ELSE 999999 END ASC")
            ->orderBy('results.session', 'ASC')
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
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(20);
            },
        ];
    }
}
