<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PaidStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $session;
    protected $feesType;
    protected $filters = [];
    protected $summary = [];
    protected $noPaymentSectionRow = null;

    public function __construct($session, $feesType = '', array $filters = [])
    {
        $this->session = $session;
        $this->feesType = (string) ($feesType ?? '');
        $this->filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== 'all';
        });
    }

    /**
     * Return the validated paid-student rows used by both the export and the
     * Fees Due summary. Keeping this query in one place prevents the card and
     * downloaded report from showing different populations.
     */
    public static function paidRows(string $session, string $feesType = '', array $filters = []): array
    {
        $serviceTypeId = (string) config('services.remita.school_fees_key', '365039916');
        $academicFilterKeys = ['faculty', 'department', 'program', 'level'];
        $includeStudentsWithoutPayments = count(array_intersect(
            array_keys($filters),
            $academicFilterKeys
        )) > 0;

        $query = "
            SELECT
                username,
                faculty,
                department,
                program,
                level,
                required_amount,
                COALESCE(invoices_amount, 0) AS amount_paid,
                CASE
                    WHEN COALESCE(invoices_amount, 0) <= 0 THEN 'No payment'
                    WHEN invoices_amount > required_amount THEN 'Overpaid'
                    WHEN invoices_amount >= required_amount THEN 'Fully paid'
                    ELSE 'Partially paid'
                END AS payment_status
            FROM (
                SELECT
                    COALESCE(NULLIF(TRIM(s.username), ''), NULLIF(TRIM(account_lookup.username), '')) AS username,
                    COALESCE(faculty_lookup.title, s.faculty) AS faculty,
                    COALESCE(department_lookup.title, s.department) AS department,
                    COALESCE(program_lookup.title, s.program) AS program,
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
                    COALESCE(paid.invoices_amount, 0) AS invoices_amount
                FROM students s
                LEFT JOIN users account_lookup ON account_lookup.id = s.user_id
                LEFT JOIN faculty faculty_lookup ON faculty_lookup.code = s.faculty
                LEFT JOIN department department_lookup ON department_lookup.code = s.department
                LEFT JOIN program program_lookup ON program_lookup.code = s.program
                LEFT JOIN (
                    SELECT username, SUM(amount) AS invoices_amount
                    FROM invoices
                    WHERE session = ?
                      AND status = 'Paid'
                      AND serviceTypeId = ?
                      AND description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES'
";
        if ($feesType === 'nelfund') {
            $query .= " AND fees_type = 'nelfund'";
        } elseif ($feesType === 'others') {
            $query .= " AND (fees_type != 'nelfund' OR fees_type IS NULL)";
        }

        $query .= "
                    GROUP BY username
                ) paid ON paid.username = s.user_id
                WHERE 1 = 1
        ";

        $params = [$session, $session, $serviceTypeId];

        // Keep id_no = 0 records in the population, but only expose genuine
        // matric numbers. A valid UG username has exactly three slashes:
        // NN/NN/NN/NNNN.
        $query .= " AND TRIM(COALESCE(NULLIF(s.username, ''), NULLIF(account_lookup.username, ''))) REGEXP '^[0-9][0-9]/[0-9][0-9]/[0-9][0-9]/[0-9][0-9][0-9][0-9]$'";

        if (!$includeStudentsWithoutPayments) {
            $query .= " AND paid.username IS NOT NULL";
        }

        $filterColumns = [
            'faculty' => 's.faculty',
            'department' => 's.department',
            'program' => 's.program',
            'level' => 's.level',
        ];

        foreach ($filters as $key => $value) {
            if (isset($filterColumns[$key]) && $value !== null && $value !== '' && $value !== 'all') {
                $query .= ' AND ' . $filterColumns[$key] . ' = ?';
                $params[] = $value;
            }
        }

        $query .= "
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
        $results = static::paidRows($this->session, $this->feesType, $this->filters);
        $recordCount = count($results);
        $totalRequired = 0.0;
        $totalPaid = 0.0;
        $outstandingAmount = 0.0;
        $overpaidAmount = 0.0;
        $fullyPaid = 0;
        $partiallyPaid = 0;
        $noPayment = 0;
        $overpaid = 0;

        foreach ($results as $item) {
            $required = (float) ($item->required_amount ?? 0);
            $paid = (float) ($item->amount_paid ?? 0);
            $totalRequired += $required;
            $totalPaid += $paid;
            $outstandingAmount += max($required - $paid, 0);
            $overpaidAmount += max($paid - $required, 0);

            switch ((string) ($item->payment_status ?? 'No payment')) {
                case 'Fully paid':
                    $fullyPaid++;
                    break;
                case 'Partially paid':
                    $partiallyPaid++;
                    break;
                case 'Overpaid':
                    $overpaid++;
                    break;
                default:
                    $noPayment++;
                    break;
            }
        }

        $this->summary = [
            'records' => $recordCount,
            'paid_records' => $recordCount - $noPayment,
            'fully_paid' => $fullyPaid,
            'partially_paid' => $partiallyPaid,
            'no_payment' => $noPayment,
            'overpaid' => $overpaid,
            'with_balance' => $partiallyPaid + $noPayment,
            'required_amount' => $totalRequired,
            'amount_paid' => $totalPaid,
            'outstanding_amount' => $outstandingAmount,
            'overpaid_amount' => $overpaidAmount,
        ];

        $formatRow = function ($item): array {
            $required = (float) ($item->required_amount ?? 0);
            $paid = (float) ($item->amount_paid ?? 0);

            return [
                'username' => $item->username,
                'faculty' => $item->faculty,
                'department' => $item->department,
                'program' => $item->program,
                'level' => $item->level,
                'required_amount' => number_format($required, 2),
                'amount_paid' => number_format($paid, 2),
                'outstanding_amount' => number_format(max($required - $paid, 0), 2),
                'payment_status' => $item->payment_status,
            ];
        };

        $paidResults = collect($results)
            ->filter(fn ($item) => (float) ($item->amount_paid ?? 0) > 0)
            ->sortBy('username')
            ->values();

        $noPaymentResults = collect($results)
            ->filter(fn ($item) => (float) ($item->amount_paid ?? 0) <= 0)
            ->sortBy('username')
            ->values();

        $rows = $paidResults->map($formatRow);

        if ($noPaymentResults->isNotEmpty()) {
            // Keep students with no payment at the end under a visible
            // section heading so they cannot be mistaken for paid records.
            $this->noPaymentSectionRow = $rows->count() + 2;
            $rows->push([
                'username' => 'STUDENTS WITHOUT PAYMENT',
                'faculty' => '',
                'department' => '',
                'program' => '',
                'level' => '',
                'required_amount' => '',
                'amount_paid' => '',
                'outstanding_amount' => '',
                'payment_status' => '',
            ]);
            $rows = $rows->concat($noPaymentResults->map($formatRow));
        } else {
            $this->noPaymentSectionRow = null;
        }

        $rows->push([
            'username' => 'TOTAL',
            'faculty' => '',
            'department' => '',
            'program' => '',
            'level' => '',
            'required_amount' => number_format($totalRequired, 2),
            'amount_paid' => number_format($totalPaid, 2),
            'outstanding_amount' => number_format($outstandingAmount, 2),
            'payment_status' => '',
        ]);

        return $rows;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $summaryStart = $sheet->getHighestRow() + 2;
                $facultyFilter = $this->filters['faculty'] ?? null;
                $departmentFilter = $this->filters['department'] ?? null;
                $programFilter = $this->filters['program'] ?? null;
                $facultyLabel = $facultyFilter
                    ? (string) (DB::table('faculty')->where('code', $facultyFilter)->value('title') ?: $facultyFilter)
                    : 'All faculties';
                $departmentLabel = $departmentFilter
                    ? (string) (DB::table('department')->where('code', $departmentFilter)->value('title') ?: $departmentFilter)
                    : 'All departments';
                $programLabel = $programFilter
                    ? (string) (DB::table('program')->where('code', $programFilter)->value('title') ?: $programFilter)
                    : 'All programmes';
                $summaryRows = [
                    ['EXPORT SUMMARY', ''],
                    ['Session', (string) $this->session],
                    ['Sponsor', $this->feesType !== '' ? $this->feesType : 'All sponsors'],
                    ['Faculty', $facultyLabel],
                    ['Department', $departmentLabel],
                    ['Programme', $programLabel],
                    ['Level', $this->filters['level'] ?? 'All levels'],
                    ['Records', (int) ($this->summary['records'] ?? 0)],
                    ['Records with any payment', (int) ($this->summary['paid_records'] ?? 0)],
                    ['Fully paid records', (int) ($this->summary['fully_paid'] ?? 0)],
                    ['Partially paid records', (int) ($this->summary['partially_paid'] ?? 0)],
                    ['No payment records', (int) ($this->summary['no_payment'] ?? 0)],
                    ['Overpaid records', (int) ($this->summary['overpaid'] ?? 0)],
                    ['Required amount', (float) ($this->summary['required_amount'] ?? 0)],
                    ['Amount paid', (float) ($this->summary['amount_paid'] ?? 0)],
                    ['Outstanding amount', (float) ($this->summary['outstanding_amount'] ?? 0)],
                    ['Overpaid amount', (float) ($this->summary['overpaid_amount'] ?? 0)],
                ];
                if ($this->noPaymentSectionRow !== null) {
                    $sectionRow = $this->noPaymentSectionRow;
                    $sheet->mergeCells('A' . $sectionRow . ':I' . $sectionRow);
                    $sheet->getStyle('A' . $sectionRow . ':I' . $sectionRow)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'B45309']],
                        'alignment' => ['horizontal' => 'left'],
                    ]);
                }

                $sheet->fromArray($summaryRows, null, 'A' . $summaryStart);
                $sheet->getStyle('A' . $summaryStart)->getFont()->setBold(true);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Faculty',
            'Department',
            'Program',
            'Level',
            'Required Amount',
            'Amount Paid',
            'Outstanding Amount',
            'Payment Status',
        ];
    }
}
