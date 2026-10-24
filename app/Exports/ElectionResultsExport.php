<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ElectionResultsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->category ?? '',
                $row->position ?? '',
                $row->candidate ?? '',
                $row->name ?? '',
                $row->entity_label ?? '',
                $row->level ?? '',
                (int) ($row->vote ?? 0),
                $row->remark ?? 'Not elected',
            ];
        });
    }

    public function headings(): array
    {
        return ['S/N', 'Category', 'Office', 'ID No.', 'Candidate name', 'Faculty / hostel / LGA', 'Level', 'Total votes scored', 'Remark'];
    }
}
