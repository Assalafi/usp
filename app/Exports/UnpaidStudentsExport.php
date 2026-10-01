<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UnpaidStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected string $session,
        protected string $feesType = ''
    ) {
    }

    public function collection()
    {
        $programmeFee = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
        $sponsorSql = '';
        $params = [$this->session, $this->session, $programmeFee];

        if ($this->feesType === 'nelfund') {
            $sponsorSql = ' AND i.fees_type = ?';
            $params[] = 'nelfund';
        } elseif ($this->feesType === 'others') {
            $sponsorSql = ' AND (i.fees_type <> ? OR i.fees_type IS NULL)';
            $params[] = 'nelfund';
        }

        $params[] = $this->session;
        $params[] = $this->session;

        $query = "
            SELECT
                active.username,
                active.fullname,
                active.contact_phone,
                active.contact_email,
                active.faculty,
                active.department,
                active.program,
                active.level,
                active.session_of_entry,
                active.required_amount,
                active.amount_paid,
                GREATEST(active.required_amount - active.amount_paid, 0) AS outstanding_amount,
                active.result_count,
                active.history_count,
                active.account_status
            FROM (
                SELECT
                    s.username,
                    s.fullname,
                    s.contact_phone,
                    s.contact_email,
                    s.faculty,
                    s.department,
                    s.program,
                    s.level,
                    s.session_of_entry,
                    s.status AS account_status,
                    CASE
                        WHEN s.session_of_entry = ? THEN COALESCE(new_fees.amount, 0)
                        ELSE COALESCE(returning_fees.amount, 0)
                    END AS required_amount,
                    COALESCE(paid.amount_paid, 0) AS amount_paid,
                    COALESCE(results_activity.result_count, 0) AS result_count,
                    COALESCE(history_activity.history_count, 0) AS history_count
                FROM students s
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

                LEFT JOIN (
                    SELECT i.username, SUM(COALESCE(i.amount, 0)) AS amount_paid
                    FROM invoices i
                    WHERE i.session = ?
                      AND i.status = 'Paid'
                      AND i.description = ?
                      {$sponsorSql}
                    GROUP BY i.username
                ) paid ON paid.username = s.user_id
                LEFT JOIN (
                    SELECT username, COUNT(*) AS result_count
                    FROM results
                    WHERE session = ?
                    GROUP BY username
                ) results_activity ON results_activity.username = s.username
                LEFT JOIN (
                    SELECT username, COUNT(*) AS history_count
                    FROM session_history
                    WHERE session = ?
                    GROUP BY username
                ) history_activity ON history_activity.username = s.username
                WHERE results_activity.username IS NOT NULL
                   OR history_activity.username IS NOT NULL
            ) active
            WHERE active.required_amount > 0
              AND active.amount_paid < active.required_amount
            ORDER BY active.faculty, active.department, active.program, active.username
        ";

        return collect(DB::select($query, $params))->map(function ($student) {
            $resultCount = (int) $student->result_count;
            $historyCount = (int) $student->history_count;

            return [
                'username' => $student->username,
                'name' => trim((string) ($student->fullname ?? '')) ?: 'Not provided',
                'phone' => $student->contact_phone,
                'email' => $student->contact_email,
                'faculty' => $student->faculty,
                'department' => $student->department,
                'program' => $student->program,
                'level' => $student->level,
                'entry_session' => $student->session_of_entry,
                'required_amount' => number_format((float) $student->required_amount, 2),
                'amount_paid' => number_format((float) $student->amount_paid, 2),
                'outstanding_amount' => number_format((float) $student->outstanding_amount, 2),
                'activity_source' => match (true) {
                    $resultCount > 0 && $historyCount > 0 => 'Results and session history',
                    $resultCount > 0 => 'Results',
                    default => 'Session history',
                },
                'result_count' => $resultCount,
                'session_history_count' => $historyCount,
                'account_status' => (string) $student->account_status === '1' ? 'Active' : 'Inactive record',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Username',
            'Name',
            'Phone',
            'Email',
            'Faculty',
            'Department',
            'Programme',
            'Level',
            'Entry Session',
            'Required Amount',
            'Amount Paid',
            'Outstanding Amount',
            'Activity Source',
            'Result Count',
            'Session History Count',
            'Account Status',
        ];
    }
}
