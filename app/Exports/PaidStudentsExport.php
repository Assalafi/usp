<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PaidStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $session;
    protected $feesType;

    public function __construct($session, $feesType = '')
    {
        $this->session = $session;
        $this->feesType = $feesType;
    }

    public function collection()
    {
        $query = "
            SELECT
                username,
                faculty,
                department,
                program,
                level,
                required_amount,
                CASE
                    WHEN invoices_amount > required_amount THEN required_amount
                    ELSE invoices_amount
                END AS amount_paid,
                CASE
                    WHEN invoices_amount >= required_amount THEN 'Yes'
                    ELSE 'No'
                END AS full_payment
            FROM (
                SELECT
                    s.username,
                    s.faculty,
                    s.department,
                    s.program,
                    s.level,
                    s.session_of_entry,
                    CASE
                        WHEN s.session_of_entry = ? THEN
                            COALESCE((SELECT amount FROM school_fees
                             WHERE program = s.program AND level = s.level
                             AND type = 'NEW' LIMIT 1), 0)
                        ELSE
                            COALESCE((SELECT amount FROM school_fees
                             WHERE program = s.program AND level = s.level
                             AND type = 'RETURNING'
                             ORDER BY amount DESC LIMIT 1), 0)
                    END AS required_amount,
                    COALESCE(SUM(i.amount), 0) AS invoices_amount
                FROM
                    students s
                JOIN
                    invoices i ON s.user_id = i.username
                WHERE
                    i.session = ?
                    AND i.status = 'Paid'
                    AND i.serviceTypeId = 365039916
        ";

        $params = [$this->session, $this->session];

        // Add sponsor filter
        if ($this->feesType === 'nelfund') {
            $query .= " AND i.fees_type = 'nelfund'";
        } elseif ($this->feesType === 'others') {
            $query .= " AND (i.fees_type != 'nelfund' OR i.fees_type IS NULL)";
        }

        $query .= "
                GROUP BY
                    s.username,
                    s.faculty,
                    s.department,
                    s.program,
                    s.level,
                    s.session_of_entry
            ) AS subquery
            WHERE required_amount > 0
        ";

        $results = DB::select($query, $params);

        // Convert to array and format amounts
        return collect($results)->map(function ($item) {
            return [
                'username' => $item->username,
                'faculty' => $item->faculty,
                'department' => $item->department,
                'program' => $item->program,
                'level' => $item->level,
                'required_amount' => number_format($item->required_amount, 2),
                'amount_paid' => number_format($item->amount_paid, 2),
                'full_payment' => $item->full_payment,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Username',
            'Faculty',
            'Department',
            'Program',
            'Level',
            'Required Amount',
            'Amount Paid',
            'Full Payment',
        ];
    }
}
