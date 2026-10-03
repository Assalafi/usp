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
        $serviceTypeId = (string) config('services.remita.school_fees_key', '365039916');
        $sponsorFilter = '';

        if ($feesType === 'nelfund') {
            $sponsorFilter = " AND fees_type = 'nelfund'";
        } elseif ($feesType === 'others') {
            $sponsorFilter = " AND (fees_type <> 'nelfund' OR fees_type IS NULL)";
        }

        // Aggregate in MySQL instead of materialising every paid student in PHP.
        // The fee schedule selection mirrors paidRows(): first NEW row by id,
        // and the highest RETURNING amount for each programme and level.
        $query = "
            SELECT
                COUNT(*) AS students,
                COALESCE(SUM(summary.required_amount), 0) AS required_amount,
                COALESCE(SUM(summary.invoices_amount), 0) AS amount_paid
            FROM (
                SELECT
                    s.username,
                    s.faculty,
                    s.department,
                    s.program,
                    s.level,
                    s.session_of_entry,
                    CASE
                        WHEN s.session_of_entry = ? THEN COALESCE(new_fee.amount, 0)
                        ELSE COALESCE(returning_fee.amount, 0)
                    END AS required_amount,
                    paid.invoices_amount
                FROM students s
                INNER JOIN (
                    SELECT username, SUM(amount) AS invoices_amount
                    FROM invoices
                    WHERE session = ?
                      AND status = 'Paid'
                      AND serviceTypeId = ?
                      AND description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES'
                      {$sponsorFilter}
                    GROUP BY username
                ) paid ON paid.username = s.user_id
                LEFT JOIN (
                    SELECT first_new.program, first_new.level, sf.amount
                    FROM (
                        SELECT program, level, MIN(id) AS id
                        FROM school_fees
                        WHERE type = 'NEW'
                        GROUP BY program, level
                    ) first_new
                    INNER JOIN school_fees sf ON sf.id = first_new.id
                ) new_fee ON new_fee.program = s.program AND new_fee.level = s.level
                LEFT JOIN (
                    SELECT program, level, MAX(amount) AS amount
                    FROM school_fees
                    WHERE type = 'RETURNING'
                    GROUP BY program, level
                ) returning_fee ON returning_fee.program = s.program AND returning_fee.level = s.level
                GROUP BY
                    s.username,
                    s.faculty,
                    s.department,
                    s.program,
                    s.level,
                    s.session_of_entry,
                    new_fee.amount,
                    returning_fee.amount,
                    paid.invoices_amount
            ) summary
            WHERE summary.required_amount > 0
        ";

        $totals = DB::selectOne($query, [$session, $session, $serviceTypeId]);

        return [
            'students' => (int) ($totals->students ?? 0),
            'required_amount' => (float) ($totals->required_amount ?? 0),
            'amount_paid' => (float) ($totals->amount_paid ?? 0),
        ];
    }

    /**
     * Return the validated school-fee payments split by sponsor. The query
     * aggregates in MySQL and deliberately uses the same fee-schedule rules
     * as paidTotals() so the dashboard and exports never disagree.
     */
    public static function sponsorTotals(string $session): array
    {
        $serviceTypeId = (string) config('services.remita.school_fees_key', '365039916');

        $query = "
            SELECT sponsor,
                   COUNT(*) AS students,
                   COALESCE(SUM(required_amount), 0) AS required_amount,
                   COALESCE(SUM(amount_paid), 0) AS amount_paid,
                   COALESCE(SUM(GREATEST(required_amount - amount_paid, 0)), 0) AS outstanding_amount,
                   SUM(CASE WHEN amount_paid >= required_amount THEN 1 ELSE 0 END) AS fully_paid
            FROM (
                SELECT
                    CASE WHEN i.fees_type = 'nelfund' THEN 'nelfund' ELSE 'self' END AS sponsor,
                    s.username,
                    CASE
                        WHEN s.session_of_entry = ? THEN COALESCE(new_fee.amount, 0)
                        ELSE COALESCE(returning_fee.amount, 0)
                    END AS required_amount,
                    COALESCE(SUM(i.amount), 0) AS amount_paid
                FROM students s
                INNER JOIN invoices i ON i.username = s.user_id
                LEFT JOIN (
                    SELECT first_new.program, first_new.level, sf.amount
                    FROM (
                        SELECT program, level, MIN(id) AS id
                        FROM school_fees
                        WHERE type = 'NEW'
                        GROUP BY program, level
                    ) first_new
                    INNER JOIN school_fees sf ON sf.id = first_new.id
                ) new_fee ON new_fee.program = s.program AND new_fee.level = s.level
                LEFT JOIN (
                    SELECT program, level, MAX(amount) AS amount
                    FROM school_fees
                    WHERE type = 'RETURNING'
                    GROUP BY program, level
                ) returning_fee ON returning_fee.program = s.program AND returning_fee.level = s.level
                WHERE i.session = ?
                  AND i.status = 'Paid'
                  AND i.serviceTypeId = ?
                  AND i.description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES'
                GROUP BY
                    CASE WHEN i.fees_type = 'nelfund' THEN 'nelfund' ELSE 'self' END,
                    s.username,
                    s.session_of_entry,
                    new_fee.amount,
                    returning_fee.amount
            ) summary
            WHERE required_amount > 0
            GROUP BY sponsor
        ";

        $rows = DB::select($query, [$session, $session, $serviceTypeId]);
        $totals = [
            'nelfund' => ['students' => 0, 'required_amount' => 0.0, 'amount_paid' => 0.0, 'outstanding_amount' => 0.0, 'fully_paid' => 0],
            'self' => ['students' => 0, 'required_amount' => 0.0, 'amount_paid' => 0.0, 'outstanding_amount' => 0.0, 'fully_paid' => 0],
        ];

        foreach ($rows as $row) {
            $sponsor = $row->sponsor === 'nelfund' ? 'nelfund' : 'self';
            $totals[$sponsor] = [
                'students' => (int) $row->students,
                'required_amount' => (float) $row->required_amount,
                'amount_paid' => (float) $row->amount_paid,
                'outstanding_amount' => (float) $row->outstanding_amount,
                'fully_paid' => (int) $row->fully_paid,
            ];
        }

        return $totals;
    }
    /**
     * Validated programme-fee totals grouped by student level.
     */
    public static function levelTotals(string $session): array
    {
        $serviceTypeId = (string) config('services.remita.school_fees_key', '365039916');
        $query = "
            SELECT level,
                   COUNT(*) AS students,
                   COALESCE(SUM(required_amount), 0) AS required_amount,
                   COALESCE(SUM(amount_paid), 0) AS amount_paid,
                   SUM(CASE WHEN amount_paid >= required_amount THEN 1 ELSE 0 END) AS fully_paid
            FROM (
                SELECT
                    s.level,
                    s.username,
                    CASE
                        WHEN s.session_of_entry = ? THEN COALESCE(new_fee.amount, 0)
                        ELSE COALESCE(returning_fee.amount, 0)
                    END AS required_amount,
                    COALESCE(SUM(i.amount), 0) AS amount_paid
                FROM students s
                INNER JOIN invoices i ON i.username = s.user_id
                LEFT JOIN (
                    SELECT first_new.program, first_new.level, sf.amount
                    FROM (
                        SELECT program, level, MIN(id) AS id
                        FROM school_fees
                        WHERE type = 'NEW'
                        GROUP BY program, level
                    ) first_new
                    INNER JOIN school_fees sf ON sf.id = first_new.id
                ) new_fee ON new_fee.program = s.program AND new_fee.level = s.level
                LEFT JOIN (
                    SELECT program, level, MAX(amount) AS amount
                    FROM school_fees
                    WHERE type = 'RETURNING'
                    GROUP BY program, level
                ) returning_fee ON returning_fee.program = s.program AND returning_fee.level = s.level
                WHERE i.session = ?
                  AND i.status = 'Paid'
                  AND i.serviceTypeId = ?
                  AND i.description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES'
                GROUP BY s.level, s.username, s.session_of_entry, new_fee.amount, returning_fee.amount
            ) summary
            WHERE required_amount > 0
            GROUP BY level
            ORDER BY level
        ";

        return array_map(static function ($row): array {
            return [
                'level' => (string) ($row->level ?? 'Not set'),
                'students' => (int) ($row->students ?? 0),
                'required_amount' => (float) ($row->required_amount ?? 0),
                'amount_paid' => (float) ($row->amount_paid ?? 0),
                'fully_paid' => (int) ($row->fully_paid ?? 0),
            ];
        }, DB::select($query, [$session, $session, $serviceTypeId]));
    }
    public function collection()
    {
        $results = static::paidRows($this->session, $this->feesType);
        $totalRequired = (float) collect($results)->sum('required_amount');
        $totalPaid = (float) collect($results)->sum('amount_paid');

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
