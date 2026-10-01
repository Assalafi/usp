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

    /**
     * Return the validated paid-student rows used by both the export and the
     * Fees Due summary. Keeping this query in one place prevents the card and
     * downloaded report from showing different populations.
     */
    public static function paidRows(string $session, string $feesType = ''): array
    {
        $serviceTypeId = (string) config('services.remita.school_fees_key', '365039916');

        $query = "
            SELECT
                username,
                faculty,
                department,
                program,
                level,
                required_amount,
                invoices_amount AS amount_paid,
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
                        WHEN s.session_of_entry = ? THEN COALESCE(MAX(new_fees.amount), 0)
                        ELSE COALESCE(MAX(returning_fees.amount), 0)
                    END AS required_amount,
                    COALESCE(SUM(i.amount), 0) AS invoices_amount
                FROM
                    students s
                JOIN
                    invoices i ON s.user_id = i.username
                LEFT JOIN (
                    SELECT program, level, MAX(amount) AS amount
                    FROM school_fees
                    WHERE type = 'NEW'
                    GROUP BY program, level
                ) new_fees ON new_fees.program = s.program AND new_fees.level = s.level
                LEFT JOIN (
                    SELECT program, level, MAX(amount) AS amount
                    FROM school_fees
                    WHERE type = 'RETURNING'
                    GROUP BY program, level
                ) returning_fees ON returning_fees.program = s.program AND returning_fees.level = s.level
                WHERE
                    i.session = ?
                    AND i.status = 'Paid'
                    AND i.serviceTypeId = ?
                    AND i.description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES'
        ";

        $params = [$session, $session, $serviceTypeId];

        // Add sponsor filter
        if ($feesType === 'nelfund') {
            $query .= " AND i.fees_type = 'nelfund'";
        } elseif ($feesType === 'others') {
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

        return DB::select($query, $params);
    }

    /**
     * Totals for the same validated population used in the report.
     */
    public static function paidTotals(string $session, string $feesType = ''): array
    {
        $rows = collect(static::paidRows($session, $feesType));

        return [
            'students' => $rows->count(),
            'required_amount' => (float) $rows->sum('required_amount'),
            'amount_paid' => (float) $rows->sum('invoices_amount'),
        ];
    }

    public function collection()
    {
        $results = static::paidRows($this->session, $this->feesType);
        $totalRequired = (float) collect($results)->sum('required_amount');
        $totalPaid = (float) collect($results)->sum('invoices_amount');

        // Convert to array and format amounts
        $rows = collect($results)->map(function ($item) {
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

        // Keep the summary in the final row so it is visible in Excel without
        // requiring a separate calculation.
        $rows->push([
            'username' => 'TOTAL',
            'faculty' => '',
            'department' => '',
            'program' => '',
            'level' => '',
            'required_amount' => number_format($totalRequired, 2),
            'amount_paid' => number_format($totalPaid, 2),
            'full_payment' => '',
        ]);

        return $rows;
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
