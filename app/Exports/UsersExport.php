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
        //return Student::all();
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID NO',
            'FULLNAME',
            'FACULTY',
            'PROGRAM',
            'STATE',
            'NATIONALITY',
            'NEXT OF KIN NAME',
            'NEXT OF KIN PHONE',
        ];
    }
}
