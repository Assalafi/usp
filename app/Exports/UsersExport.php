<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($q)
    {
        $this->query = $q;
    }
    public function collection()
    {
        return $this->query->map(function ($row) {
            if (!empty($row->date_of_birth) && $row->date_of_birth != '1970-01-01') {
                $row->date_of_birth = date('d/m/Y', strtotime($row->date_of_birth));
            } else {
                $row->date_of_birth = 'N/A';
            }
            return $row;
        });
    }

    public function headings(): array
    {
        return [
            'ID NO',
            'FULLNAME',
            'GENDER',
            'DATE OF BIRTH',
            'FACULTY',
            'DEPARTMENT',
            'PROGRAM',
            'LEVEL',
            'SESSION OF ENTRY',
            'JAMB NO',
            'STATE',
            'LGA',
            'NATIONALITY',
            'MARITAL STATUS',
            'PHONE',
            'EMAIL',
            'NEXT OF KIN NAME',
            'NEXT OF KIN PHONE',
        ];
    }
}
