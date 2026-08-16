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

        return [
            'username' => $row->username ?? 'N/A',
            'fullname' => $row->fullname ?? 'N/A',
            'gender' => $row->gender ?? 'N/A',
            'date_of_birth' => $dob,
            'faculty_name' => $row->faculty_name ?? $row->faculty ?? 'N/A',
            'department_name' => $row->department_name ?? $row->department ?? 'N/A',
            'program_name' => $row->program_name ?? $row->program ?? 'N/A',
            'level' => $row->level ?? 'N/A',
            'session_of_entry' => $row->session_of_entry ?? 'N/A',
            'jamb_no' => $row->jamb_no ?? 'N/A',
            'mode_of_entry' => $row->mode_of_entry ?? 'N/A',
            'state_origin' => $row->state_origin ?? 'N/A',
            'lga_origin' => $row->lga_origin ?? 'N/A',
            'country' => $row->country ?? 'N/A',
            'marital_status' => $row->marital_status ?? 'N/A',
            'religion' => $row->religion ?? 'N/A',
            'nin' => $row->nin ?? 'N/A',
            'blood_group' => $row->blood_group ?? 'N/A',
            'genotype' => $row->genotype ?? 'N/A',
            'highest_qualification' => $row->highest_qualification ?? 'N/A',
            'place_of_birth' => $row->place_of_birth ?? 'N/A',
            'contact_phone' => $row->contact_phone ?? 'N/A',
            'contact_email' => $row->contact_email ?? 'N/A',
            'contact_address' => $row->contact_address ?? 'N/A',
            'kin_name' => $row->kin_name ?? 'N/A',
            'kin_phone' => $row->kin_phone ?? 'N/A',
            'kin_address' => $row->kin_address ?? 'N/A',
            'kin_email' => $row->kin_email ?? 'N/A',
            'sponsor_type' => $row->sponsor_type ?? 'N/A',
            'sponsor_name' => $row->sponsor_name ?? 'N/A',
            'sponsor_phone' => $row->sponsor_phone ?? 'N/A',
            'father_name' => $row->father_name ?? 'N/A',
            'father_phone' => $row->father_phone ?? 'N/A',
            'mother_name' => $row->mother_name ?? 'N/A',
            'mother_phone' => $row->mother_phone ?? 'N/A',
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
