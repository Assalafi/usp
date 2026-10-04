<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class UnpaidStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected string $session;
    protected string $feesType;
    protected array $filters = [];
    protected array $summary = [];

    public function __construct(string $session, string $feesType = '', array $filters = [])
    {
        $this->session = $session;
        $this->feesType = $feesType;
        $this->filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== 'all';
        });
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

        $filterSql = '';
        $filterParams = [];
        $filterColumns = [
            'faculty' => 'active.faculty',
            'department' => 'active.department',
            'program' => 'active.program',
            'level' => 'active.level',
        ];
        foreach ($this->filters as $key => $value) {
            if (isset($filterColumns[$key]) && $value !== null && $value !== '' && $value !== 'all') {
                $filterSql .= ' AND ' . $filterColumns[$key] . ' = ?';
                $filterParams[] = $value;
            }
        }

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
                    SELECT first_new.program, first_new.level, sf.amount
                    FROM (
                        SELECT program, level, MIN(id) AS id
                        FROM school_fees
                        WHERE type = 'NEW'
                        GROUP BY program, level
                    ) first_new
                    INNER JOIN school_fees sf ON sf.id = first_new.id
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
              {$filterSql}
            ORDER BY active.faculty, active.department, active.program, active.username
        ";

        $params = array_merge($params, $filterParams);
        $rows = collect(DB::select($query, $params))->map(function ($student) {
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

        $recordCount = $rows->count();
        $totalRequired = (float) $rows->sum(function ($row) {
            return (float) str_replace(',', '', $row['required_amount']);
        });
        $totalPaid = (float) $rows->sum(function ($row) {
            return (float) str_replace(',', '', $row['amount_paid']);
        });
        $totalOutstanding = (float) $rows->sum(function ($row) {
            return (float) str_replace(',', '', $row['outstanding_amount']);
        });

        $this->summary = [
            'records' => $recordCount,
            'required_amount' => $totalRequired,
            'amount_paid' => $totalPaid,
            'outstanding_amount' => $totalOutstanding,
        ];

        $rows->push([
            'username' => 'TOTAL',
            'name' => '',
            'phone' => '',
            'email' => '',
            'faculty' => '',
            'department' => '',
            'program' => '',
            'level' => '',
            'entry_session' => '',
            'required_amount' => number_format($totalRequired, 2),
            'amount_paid' => number_format($totalPaid, 2),
            'outstanding_amount' => number_format($totalOutstanding, 2),
            'activity_source' => '',
            'result_count' => $rows->sum('result_count'),
            'session_history_count' => $rows->sum('session_history_count'),
            'account_status' => '',
        ]);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $summaryStart = $sheet->getHighestRow() + 2;
                $summaryRows = [
                    ['EXPORT SUMMARY', ''],
                    ['Session', (string) $this->session],
                    ['Sponsor', $this->feesType !== '' ? $this->feesType : 'All sponsors'],
                    ['Faculty', $this->filters['faculty'] ?? 'All faculties'],
                    ['Department', $this->filters['department'] ?? 'All departments'],
                    ['Programme', $this->filters['program'] ?? 'All programmes'],
                    ['Level', $this->filters['level'] ?? 'All levels'],
                    ['Records', (int) ($this->summary['records'] ?? 0)],
                    ['Required amount', (float) ($this->summary['required_amount'] ?? 0)],
                    ['Amount paid', (float) ($this->summary['amount_paid'] ?? 0)],
                    ['Outstanding amount', (float) ($this->summary['outstanding_amount'] ?? 0)],
                ];
                $sheet->fromArray($summaryRows, null, 'A' . $summaryStart);
                $sheet->getStyle('A' . $summaryStart)->getFont()->setBold(true);
            },
        ];
    }
    /**
     * Fast, read-only summary of active students whose programme fee is not
     * fully paid. Results and session history are both treated as activity,
     * matching the unpaid export definition.
     */
    public static function summaryByLevel(string $session, string $feesType = ''): array
    {
        $programmeFee = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
        $sponsorSql = '';
        $params = [$session, $session, $programmeFee];

        if ($feesType === 'nelfund') {
            $sponsorSql = ' AND i.fees_type = ?';
            $params[] = 'nelfund';
        } elseif ($feesType === 'others') {
            $sponsorSql = ' AND (i.fees_type <> ? OR i.fees_type IS NULL)';
            $params[] = 'nelfund';
        }

        $params[] = $session;
        $params[] = $session;

        $query = "
            SELECT active.level,
                   COUNT(*) AS students,
                   COALESCE(SUM(active.required_amount), 0) AS required_amount,
                   COALESCE(SUM(active.amount_paid), 0) AS amount_paid,
                   COALESCE(SUM(GREATEST(active.required_amount - active.amount_paid, 0)), 0) AS outstanding_amount
            FROM (
                SELECT
                    s.level,
                    CASE
                        WHEN s.session_of_entry = ? THEN COALESCE(new_fees.amount, 0)
                        ELSE COALESCE(returning_fees.amount, 0)
                    END AS required_amount,
                    COALESCE(paid.amount_paid, 0) AS amount_paid
                FROM students s
                LEFT JOIN (
                    SELECT first_new.program, first_new.level, sf.amount
                    FROM (
                        SELECT program, level, MIN(id) AS id
                        FROM school_fees
                        WHERE type = 'NEW'
                        GROUP BY program, level
                    ) first_new
                    INNER JOIN school_fees sf ON sf.id = first_new.id
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
            GROUP BY active.level
            ORDER BY active.level
        ";

        return array_map(static function ($row): array {
            return [
                'level' => (string) ($row->level ?? 'Not set'),
                'students' => (int) ($row->students ?? 0),
                'required_amount' => (float) ($row->required_amount ?? 0),
                'amount_paid' => (float) ($row->amount_paid ?? 0),
                'outstanding_amount' => (float) ($row->outstanding_amount ?? 0),
            ];
        }, DB::select($query, $params));
    }

    public static function summaryTotals(string $session, string $feesType = ''): array
    {
        $rows = static::summaryByLevel($session, $feesType);

        return [
            'students' => (int) collect($rows)->sum('students'),
            'required_amount' => (float) collect($rows)->sum('required_amount'),
            'amount_paid' => (float) collect($rows)->sum('amount_paid'),
            'outstanding_amount' => (float) collect($rows)->sum('outstanding_amount'),
        ];
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
