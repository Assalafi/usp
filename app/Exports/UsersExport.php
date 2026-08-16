<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $query;
    protected $selectedColumns;

    public function __construct($q, $selectedColumns = [])
    {
        $this->query = $q;
        $this->selectedColumns = $selectedColumns;
        if (empty($this->selectedColumns)) {
            $this->selectedColumns = array_keys(self::columns());
        }
    }

    /**
     * Available export columns: key => label
     */
    public static function columns(): array
    {
        return [
            'username' => 'ID No.',
            'fullname' => 'Full Name',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'faculty_name' => 'Faculty',
            'department_name' => 'Department',
            'program_name' => 'Program',
            'level' => 'Level',
            'session_of_entry' => 'Session of Entry',
            'jamb_no' => 'JAMB No.',
            'mode_of_entry' => 'Mode of Entry',
            'state_origin' => 'State',
            'lga_origin' => 'LGA',
            'country' => 'Nationality',
            'marital_status' => 'Marital Status',
            'religion' => 'Religion',
            'nin' => 'NIN',
            'blood_group' => 'Blood Group',
            'genotype' => 'Genotype',
            'highest_qualification' => 'Highest Qualification',
            'place_of_birth' => 'Place of Birth',
            'contact_phone' => 'Phone',
            'contact_email' => 'Email',
            'contact_address' => 'Home Address',
            'kin_name' => 'Next of Kin Name',
            'kin_phone' => 'Next of Kin Phone',
            'kin_address' => 'Next of Kin Address',
            'kin_email' => 'Next of Kin Email',
            'sponsor_type' => 'Sponsor Type',
            'sponsor_name' => 'Sponsor Name',
            'sponsor_phone' => 'Sponsor Phone',
            'father_name' => 'Father Name',
            'father_phone' => 'Father Phone',
            'mother_name' => 'Mother Name',
            'mother_phone' => 'Mother Phone',
        ];
    }

    protected function formatRow($row)
    {
        $dob = !empty($row->date_of_birth) && $row->date_of_birth != '1970-01-01'
            ? date('d/m/Y', strtotime($row->date_of_birth))
            : 'N/A';

        // Escape values that look like spreadsheet formulas (formula-injection / PhpSpreadsheet error protection)
        $esc = function ($value) {
            $value = (string) ($value ?? 'N/A');
            if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'])) {
                return "'" . $value;
            }
            return $value;
        };

        return [
            'username' => $esc($row->username ?? null),
            'fullname' => $esc($row->fullname ?? null),
            'gender' => $esc($row->gender ?? null),
            'date_of_birth' => $dob,
            'faculty_name' => $esc($row->faculty_name ?? $row->faculty ?? null),
            'department_name' => $esc($row->department_name ?? $row->department ?? null),
            'program_name' => $esc($row->program_name ?? $row->program ?? null),
            'level' => $esc($row->level ?? null),
            'session_of_entry' => $esc($row->session_of_entry ?? null),
            'jamb_no' => $esc($row->jamb_no ?? null),
            'mode_of_entry' => $esc($row->mode_of_entry ?? null),
            'state_origin' => $esc($row->state_origin ?? null),
            'lga_origin' => $esc($row->lga_origin ?? null),
            'country' => $esc($row->country ?? null),
            'marital_status' => $esc($row->marital_status ?? null),
            'religion' => $esc($row->religion ?? null),
            'nin' => $esc($row->nin ?? null),
            'blood_group' => $esc($row->blood_group ?? null),
            'genotype' => $esc($row->genotype ?? null),
            'highest_qualification' => $esc($row->highest_qualification ?? null),
            'place_of_birth' => $esc($row->place_of_birth ?? null),
            'contact_phone' => $esc($row->contact_phone ?? null),
            'contact_email' => $esc($row->contact_email ?? null),
            'contact_address' => $esc($row->contact_address ?? null),
            'kin_name' => $esc($row->kin_name ?? null),
            'kin_phone' => $esc($row->kin_phone ?? null),
            'kin_address' => $esc($row->kin_address ?? null),
            'kin_email' => $esc($row->kin_email ?? null),
            'sponsor_type' => $esc($row->sponsor_type ?? null),
            'sponsor_name' => $esc($row->sponsor_name ?? null),
            'sponsor_phone' => $esc($row->sponsor_phone ?? null),
            'father_name' => $esc($row->father_name ?? null),
            'father_phone' => $esc($row->father_phone ?? null),
            'mother_name' => $esc($row->mother_name ?? null),
            'mother_phone' => $esc($row->mother_phone ?? null),
        ];
    }

    public function collection()
    {
        $selected = array_flip($this->selectedColumns);

        return $this->query->map(function ($row) use ($selected) {
            return array_intersect_key($this->formatRow($row), $selected);
        });
    }

    public function headings(): array
    {
        $labels = self::columns();

        return array_values(array_intersect_key($labels, array_flip($this->selectedColumns)));
    }
}
