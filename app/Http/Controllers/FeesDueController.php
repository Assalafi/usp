<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\PaidStudentsExport;
use App\Exports\UnpaidStudentsExport;
use App\Services\RemitaPaymentService;

class FeesDueController extends Controller
{
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = $contents;
        $this->table = 'invoices';
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        $selectedSession = $req->input('session') ?: SystemSettingsController::getSchoolFeesSession();
        if ($req->has('_token')) {
            $data = $req->all();
            $start = $req -> start;
            $end = $req -> end;
            $fac = $req -> faculty;
            $status = $req -> status;
            $rrr = $req -> rrr;
            $studentId = trim((string) $req->input('student_id', ''));

            // RRR remains the primary search; Student ID can narrow the matching invoice records.
            if ($rrr) {
                $query = DB::table($this->table)
                    ->where('invoices.rrr', 'LIKE', '%' . $rrr . '%');
                if ($studentId !== '') {
                    // invoices.username stores the students.user_id, not the matric number.
                    $query->whereIn('invoices.username', function ($studentQuery) use ($studentId) {
                        $studentQuery->select('user_id')
                            ->from('students')
                            ->where('username', $studentId);
                    });
                }
                $data['data'] = $query->paginate(100)->withQueryString();
            } else {
                if($fac == 'none'){
                    unset($data['faculty']);
                }
                unset($data['_token']);
                unset($data['start']);
                unset($data['end']);
                unset($data['rrr']);
                unset($data['student_id']);
                $filteredData = array_filter($data);
                $query = DB::table($this->table)->where('invoices.session', $selectedSession);
                if ($studentId !== '') {
                    // invoices.username stores the students.user_id, not the matric number.
                    $query->whereIn('invoices.username', function ($studentQuery) use ($studentId) {
                        $studentQuery->select('user_id')
                            ->from('students')
                            ->where('username', $studentId);
                    });
                }
                foreach ($filteredData as $key => $value) {
                    $query->where('invoices.' . $key, $value);
                }
                if($fac == 'none'){
                    $data['data'] = $query->where('invoices.status', 'Paid')->paginate(100)->withQueryString();
                }else{
                    if($status == 'Pending'){
                        $data['data'] = $query->paginate(100)->withQueryString();
                    }else{
                        $data['data'] = $query->whereBetween('invoices.updated_at', [$start,$end])->paginate(100)->withQueryString();
                    }

                }
            }



        }else{
            // Keep the transaction table empty on first load. It can contain
            // a very large invoice history, so records are fetched only after
            // the administrator submits the filter form.
            $data['data'] = collect();

        }
        $schoolDescription = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
        $schoolTotals = PaidStudentsExport::paidTotals($selectedSession);
        $sponsorTotals = PaidStudentsExport::sponsorTotals($selectedSession);
        $paidLevelSummary = PaidStudentsExport::levelTotals($selectedSession);
        $unpaidLevelSummary = UnpaidStudentsExport::summaryByLevel($selectedSession);
        $levelMap = [];
        foreach ($paidLevelSummary as $level) {
            $key = (string) ($level['level'] ?: 'Not set');
            $levelMap[$key] = [
                'level' => $key,
                'paid_students' => (int) $level['fully_paid'],
                'paid_amount' => (float) $level['amount_paid'],
                'unpaid_students' => 0,
                'outstanding_amount' => 0.0,
            ];
        }
        foreach ($unpaidLevelSummary as $level) {
            $key = (string) ($level['level'] ?: 'Not set');
            $levelMap[$key] ??= [
                'level' => $key,
                'paid_students' => 0,
                'paid_amount' => 0.0,
                'unpaid_students' => 0,
                'outstanding_amount' => 0.0,
            ];
            $levelMap[$key]['unpaid_students'] = (int) $level['students'];
            $levelMap[$key]['outstanding_amount'] = (float) $level['outstanding_amount'];
        }
        $levelSummary = collect($levelMap)->sortBy(function ($row) {
            return is_numeric($row['level']) ? (int) $row['level'] : 9999;
        })->values();
        $unpaidTotals = [
            'students' => (int) collect($unpaidLevelSummary)->sum('students'),
            'required_amount' => (float) collect($unpaidLevelSummary)->sum('required_amount'),
            'amount_paid' => (float) collect($unpaidLevelSummary)->sum('amount_paid'),
            'outstanding_amount' => (float) collect($unpaidLevelSummary)->sum('outstanding_amount'),
        ];

        // Keep the description summary useful for reconciliation while using
        // the validated school-fee total for the programme-fee line.
        $summaryRows = DB::table($this->table)
            ->where('session', $selectedSession)
            ->where('status', 'Paid')
            ->selectRaw("COALESCE(NULLIF(TRIM(description), ''), 'Other payments') AS description, COUNT(*) AS payment_count, SUM(amount) AS total_amount")
            ->groupByRaw("COALESCE(NULLIF(TRIM(description), ''), 'Other payments')")
            ->orderByDesc('total_amount')
            ->get();

        $paymentSummary = collect();
        foreach ($summaryRows as $summaryRow) {
            $description = trim((string) $summaryRow->description);
            if (strcasecmp($description, $schoolDescription) === 0 || strcasecmp($description, 'REFUND') === 0) {
                continue;
            }
            $paymentSummary->push([
                'description' => $description !== '' ? $description : 'Other payments',
                'filter' => $description !== '' ? $description : 'Other payments',
                'amount' => (float) $summaryRow->total_amount,
                'count' => (int) $summaryRow->payment_count,
            ]);
        }
        if (($schoolTotals['amount_paid'] ?? 0) > 0) {
            $paymentSummary->push([
                'description' => 'SCHOOL FEES',
                'filter' => $schoolDescription,
                'amount' => (float) $schoolTotals['amount_paid'],
                'count' => (int) ($schoolTotals['students'] ?? 0),
            ]);
        }
        if (($schoolTotals['surcharge_paid'] ?? 0) > 0) {
            $paymentSummary->push([
                'description' => 'SCHOOL FEES ADDITIONAL CHARGES',
                'filter' => $schoolDescription,
                'amount' => (float) $schoolTotals['surcharge_paid'],
                'count' => (int) ($schoolTotals['students'] ?? 0),
            ]);
        }
        $hostelPinCount = DB::table('hostel_pin')
            ->where('flag', 1)
            ->where('username', '!=', 'Awaiting')
            ->count();
        $hostelPinSummary = [
            'count' => (int) $hostelPinCount,
            'amount' => (float) $hostelPinCount * 2000,
        ];
        $paymentSummary = $paymentSummary->sortByDesc('amount')->values();
        $data['payment_summary'] = $paymentSummary;
        $data['hostel_pin_summary'] = $hostelPinSummary;
        $data['payment_summary_total'] = (float) $paymentSummary->sum('amount') + (float) $hostelPinSummary['amount'];
        $data['school_fee_totals'] = $schoolTotals;
        $data['fee_sponsor_summary'] = $sponsorTotals;
        $data['fee_unpaid_summary'] = $unpaidTotals;
        $data['fee_level_summary'] = $levelSummary;
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['fees_type'] = DB::table('fees_type')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
        // Export and filtering must support historical sessions, not only the active one.
            $data['session'] = DB::table('session')->select('title')->orderByDesc('title')->get();
            $data['fees_session'] = $selectedSession;
            $data['page'] = $this->page;
            $data['title'] = $this->title;
        return view('main',$data);
    }

    /**
     * Show the administrator payment workspace. This is separate from the
     * student payment page so an administrator can create a payment for a
     * payer who is not a portal student.
     */
    public function adminPaymentPage(Request $request)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can open this payment page.');
        }

        $services = collect(SystemSettingsController::getRemitaServiceCatalog())
            ->filter(function (array $service) {
                return !empty($service['active'])
                    && trim((string) ($service['service_type_id'] ?? '')) !== '';
            })
            ->values();

        // Keep the payment desk useful after an invoice is created: administrators
        // should be able to find every invoice without leaving this workspace.
        // The query is deliberately server-side and paginated because the invoices
        // table contains the complete payment history.
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $sessionFilter = trim((string) $request->input('payment_session', ''));
        $descriptionFilter = trim((string) $request->input('description', ''));
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));

        $paymentQuery = DB::table('invoices')
            ->leftJoin('users', 'users.id', '=', 'invoices.username')
            ->leftJoin('students', 'students.user_id', '=', 'invoices.username')
            // The admin payment desk must not mix student-generated invoices
            // into its records. Admin invoices are marked at creation time.
            ->where('invoices.fees_type', 'Admin initiated');

        if ($search !== '') {
            $paymentQuery->where(function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where('invoices.name', 'LIKE', $like)
                    ->orWhere('invoices.email', 'LIKE', $like)
                    ->orWhere('invoices.phone', 'LIKE', $like)
                    ->orWhere('invoices.rrr', 'LIKE', $like)
                    ->orWhere('invoices.orderId', 'LIKE', $like)
                    ->orWhere('invoices.description', 'LIKE', $like)
                    ->orWhere('students.username', 'LIKE', $like)
                    ->orWhere('users.name', 'LIKE', $like);
            });
        }
        if ($status !== '' && strtolower($status) !== 'all') {
            $paymentQuery->where('invoices.status', $status);
        }
        if ($sessionFilter !== '' && strtolower($sessionFilter) !== 'all') {
            $paymentQuery->where('invoices.session', $sessionFilter);
        }
        if ($descriptionFilter !== '' && strtolower($descriptionFilter) !== 'all') {
            $paymentQuery->where('invoices.description', $descriptionFilter);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $paymentQuery->whereDate('invoices.created_at', '>=', $from);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $paymentQuery->whereDate('invoices.created_at', '<=', $to);
        }

        $payments = (clone $paymentQuery)
            ->select([
                'invoices.id', 'invoices.username', 'invoices.name', 'invoices.email',
                'invoices.phone', 'invoices.description', 'invoices.amount',
                'invoices.base_amount', 'invoices.surcharge_amount', 'invoices.session',
                'invoices.rrr', 'invoices.orderId', 'invoices.status',
                'invoices.payment_method', 'invoices.created_at', 'invoices.updated_at',
                'users.name as account_name', 'students.username as student_id',
            ])
            ->orderByDesc('invoices.created_at')
            ->orderByDesc('invoices.id')
            ->paginate(25, ['*'], 'payments_page')
            ->withQueryString();

        $paymentMetrics = [
            'records' => (int) (clone $paymentQuery)->count('invoices.id'),
            'paid_count' => (int) (clone $paymentQuery)->where('invoices.status', 'Paid')->count('invoices.id'),
            'paid_amount' => (float) (clone $paymentQuery)->where('invoices.status', 'Paid')->sum('invoices.amount'),
            'pending_count' => (int) (clone $paymentQuery)->whereIn('invoices.status', ['Pending', 'Processing'])->count('invoices.id'),
            'pending_amount' => (float) (clone $paymentQuery)->whereIn('invoices.status', ['Pending', 'Processing'])->sum('invoices.amount'),
            'failed_count' => (int) (clone $paymentQuery)->whereIn('invoices.status', ['Failed', 'Cancelled', 'Rejected'])->count('invoices.id'),
        ];

        $paymentDescriptions = DB::table('invoices')
            ->where('fees_type', 'Admin initiated')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('description')
            ->distinct()
            ->orderBy('description')
            ->limit(250)
            ->pluck('description');

        return view('main', [
            'page' => 'admin make payment',
            'title' => 'ADMIN PAYMENTS',
            'paymentServices' => $services,
            'paymentSessions' => DB::table('session')->select('title')->orderByDesc('title')->get(),
            'paymentSession' => SystemSettingsController::getSchoolFeesSession(),
            'payments' => $payments,
            'paymentMetrics' => $paymentMetrics,
            'paymentDescriptions' => $paymentDescriptions,
            'paymentFilters' => [
                'q' => $search,
                'status' => $status,
                'payment_session' => $sessionFilter,
                'description' => $descriptionFilter,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    /**
     * Generate a Remita invoice for an administrator-entered payer using the
     * same preparation, split allocation and invoice transaction as students.
     */
    public function createAdminPayment(Request $request)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can create a payment.');
        }

        $validated = $request->validate([
            'payer_name' => 'required|string|max:160',
            'email' => 'required|email:rfc,dns|max:160',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s-]{7,30}$/'],
            'description' => 'required|string|max:180',
            'service_type' => 'required|string|max:80',
            'amount' => 'nullable|numeric|min:1|max:999999999.99',
            'session' => 'required|string|max:30',
            'primary_account_name' => 'required|string|max:160',
            'primary_account_number' => ['required', 'string', 'regex:/^\d{10}$/'],
            'primary_bank_code' => ['required', 'string', 'regex:/^\d{3,12}$/'],
        ], [
            'primary_account_number.regex' => 'Enter the exact 10-digit account number that Remita has whitelisted for this merchant profile.',
            'primary_bank_code.regex' => 'Enter a numeric bank code or routing code between 3 and 12 digits.',
        ]);

        $service = collect(SystemSettingsController::getRemitaServiceCatalog())
            ->filter(fn (array $item) => !empty($item['active']) && trim((string) ($item['service_type_id'] ?? '')) !== '')
            ->firstWhere('key', trim((string) $validated['service_type']));

        if (!is_array($service)) {
            return redirect()->back()->withInput()->with('error', 'Select a valid payment service from the list.');
        }

        $baseAmount = (float) ($validated['amount'] ?? 0);
        if ($baseAmount <= 0) {
            $baseAmount = (float) ($service['amount'] ?? 0);
        }
        if ($baseAmount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Enter the amount to collect for this service.');
        }

        $orderId = (string) random_int(92343459459, 93438458488);
        $payer = [
            'name' => trim($validated['payer_name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
        ];
        $description = trim($validated['description']);

        try {
            $prepared = RemitaPaymentService::prepare(
                (string) $service['key'],
                $baseAmount,
                $orderId,
                $payer,
                $description,
                [], [
                    'label' => trim($validated['primary_account_name']),
                    'account_number' => trim($validated['primary_account_number']),
                    'bank_code' => trim($validated['primary_bank_code']),
                ]
            );
        } catch (\Throwable $exception) {
            Log::error('UG administrator payment preparation failed', [
                'service_key' => $service['key'] ?? null,
                'message' => $exception->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'This payment service is not configured correctly. Please review the payment settings.');
        }

        $result = RemitaPaymentService::createReference($prepared, 'UG administrator payment');
        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', 'Remita could not create the payment reference: ' . $result['message']);
        }

        try {
            $invoiceId = RemitaPaymentService::storeInvoice([
                'username' => session('id'),
                'name' => $payer['name'],
                'phone' => $payer['phone'],
                'email' => $payer['email'],
                'session' => trim($validated['session']),
                'description' => $description,
                'amount' => $prepared['total_amount'],
                'orderId' => $orderId,
                'serviceTypeId' => $prepared['service_type_id'],
                'rrr' => $result['rrr'],
                'status' => 'Pending',
                'payment_method' => 'Remita',
                'fees_type' => 'Admin initiated',
                'amount_type' => 'External payer',
                'created_at' => now(),
                'updated_at' => now(),
            ], $prepared);
        } catch (\Throwable $exception) {
            Log::error('UG administrator invoice storage failed', [
                'rrr' => $result['rrr'] ?? null,
                'message' => $exception->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'The payment reference was created, but the invoice could not be saved. Do not create another payment until this is reviewed.');
        }

        return redirect()->route('admin.make-payment')->with([
            'success' => 'Payment invoice created successfully.',
            'adminPaymentResult' => [
                'invoice_id' => $invoiceId,
                'rrr' => $result['rrr'],
                'order_id' => $orderId,
                'payer_name' => $payer['name'],
                'description' => $description,
                'service_label' => $service['label'] ?? $service['key'],
                'base_amount' => $prepared['base_amount'],
                'surcharge_amount' => $prepared['surcharge_amount'],
                'total_amount' => $prepared['total_amount'],
            ],
        ]);
    }

    /**
     * Verify an admin-created Remita invoice without applying student-only
     * side effects such as ID assignment or hostel settlement.
     */
    public function verifyAdminPayment(Request $request, $id)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can verify this payment.');
        }

        $invoice = DB::table('invoices')
            ->where('id', $id)
            ->where('fees_type', 'Admin initiated')
            ->first(['id', 'rrr', 'status']);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Admin payment record not found.');
        }
        if (in_array(strtolower((string) $invoice->status), ['paid', 'successful', 'success'], true)) {
            return redirect()->back()->with('success', 'This payment is already marked as paid.');
        }
        if (trim((string) $invoice->rrr) === '') {
            return redirect()->back()->with('error', 'This invoice has no Remita reference to verify.');
        }

        $merchantId = trim((string) config('services.remita.merchant_id'));
        $apiKey = trim((string) config('services.remita.api_key'));
        if ($merchantId === '' || $apiKey === '') {
            return redirect()->back()->with('error', 'Remita verification is not configured.');
        }

        $rrr = trim((string) $invoice->rrr);
        $apiHash = hash('sha512', $rrr . $apiKey . $merchantId);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => SystemSettingsController::getRemitaBaseUrl() . '/remita/exapp/api/v1/send/api/echannelsvc/' . $merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $apiHash,
            ],
        ]);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $curlError !== '') {
            return redirect()->back()->with('error', 'Payment verification could not connect to Remita. Please try again.');
        }
        if ($httpCode !== 200) {
            return redirect()->back()->with('error', 'Remita verification is temporarily unavailable (HTTP ' . $httpCode . ').');
        }

        $payload = json_decode($response, true);
        $remitaStatus = is_array($payload) ? trim((string) ($payload['status'] ?? '')) : '';
        if ($remitaStatus === '') {
            return redirect()->back()->with('error', 'Remita returned an incomplete verification response.');
        }

        $newStatus = in_array($remitaStatus, ['00', '01'], true)
            ? 'Paid'
            : (in_array($remitaStatus, ['021'], true) ? 'Pending' : 'Failed');

        DB::table('invoices')
            ->where('id', $invoice->id)
            ->where('fees_type', 'Admin initiated')
            ->update([
                'status' => $newStatus,
                'rrr_status' => $remitaStatus,
                'updated_at' => now(),
            ]);

        if ($newStatus === 'Paid') {
            return redirect()->back()->with('success', 'Payment verified successfully and marked as paid.');
        }
        if ($newStatus === 'Pending') {
            return redirect()->back()->with('error', 'Remita still reports this payment as pending.');
        }

        return redirect()->back()->with('error', 'Remita did not confirm this payment (status ' . $remitaStatus . ').');
    }

    /** Cancel a pending admin-created invoice while keeping its audit trail. */
    public function cancelAdminPayment(Request $request, $id)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can cancel this payment.');
        }

        $updated = DB::table('invoices')
            ->where('id', $id)
            ->where('fees_type', 'Admin initiated')
            ->whereIn('status', ['Pending', 'Processing'])
            ->update([
                'status' => 'Cancelled',
                'rrr_status' => 'CANCELLED',
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Only a pending admin payment can be cancelled.');
        }

        return redirect()->back()->with('success', 'Pending admin payment cancelled successfully.');
    }

    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can update payment transactions.');
        }
        $validated = $req->validate([
            'id' => 'required|integer',
            'session' => 'required|string|max:20',
            'base_amount' => 'required|numeric|min:0',
        ]);

        $invoice = DB::table($this->table)
            ->where('id', $validated['id'])
            ->first(['amount', 'base_amount', 'surcharge_amount']);

        if (!$invoice) {
            return redirect()->back()->withErrors(['id' => 'Payment transaction not found.']);
        }

        $baseAmount = round((float) $validated['base_amount'], 2);
        $surchargeAmount = round((float) ($invoice->surcharge_amount ?? 0), 2);

        DB::table($this->table)
            ->where('id', $validated['id'])
            ->update([
                'session' => strtoupper(trim($validated['session'])),
                'base_amount' => $baseAmount,
                'amount' => round($baseAmount + $surchargeAmount, 2),
            ]);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log') || session('accType') !== 'Admin') {
            return redirect('/')->with('error', 'Only administrators can delete payment transactions.');
        }
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
    public function searchApplicants(Request $req)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $query = trim($req->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        $results = [];
        $session = session('system_session');

        // Search users table only (fast, indexed on id)
        $users = DB::table('users')
            ->whereIn('accType', ['Student', 'Transfer'])
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%");
                if (is_numeric($query)) {
                    $q->orWhere('id', $query);
                }
            })
            ->select('id', 'name', 'username', 'accType')
            ->limit(20)
            ->get();

        if ($users->isEmpty()) {
            return response()->json([]);
        }

        // Batch: get all user IDs and check payments in one query
        $userIds = $users->pluck('id')->toArray();

        $paidInvoices = DB::table('invoices')
            ->whereIn('username', $userIds)
            ->whereIn('description', ['CHANGE OF COURSE FEE', 'INTER-UNIVERSITY TRANSFER FEE'])
            ->where('session', $session)
            ->where('status', 'Paid')
            ->select('username', 'description')
            ->get();

        $paidMap = [];
        foreach ($paidInvoices as $inv) {
            $paidMap[$inv->username . '|' . $inv->description] = true;
        }

        // Batch: get student details for student users
        $studentIds = $users->where('accType', 'Student')->pluck('id')->toArray();
        $studentDetails = [];
        if ($studentIds) {
            $stuRows = DB::table('students')->whereIn('user_id', $studentIds)->select('user_id', 'faculty', 'department')->get();
            foreach ($stuRows as $s) {
                $studentDetails[$s->user_id] = $s;
            }
        }

        foreach ($users as $u) {
            $stu = $studentDetails[$u->id] ?? null;
            $results[] = [
                'user_id' => $u->id,
                'name' => $u->name,
                'identifier' => $u->username,
                'faculty' => $stu->faculty ?? null,
                'department' => $stu->department ?? null,
                'type' => $u->accType == 'Student' ? 'student' : 'transfer',
                'type_label' => $u->accType == 'Student' ? 'Student' : 'Transfer Applicant',
                'has_paid_coc' => isset($paidMap[$u->id . '|CHANGE OF COURSE FEE']),
                'has_paid_iut' => isset($paidMap[$u->id . '|INTER-UNIVERSITY TRANSFER FEE']),
            ];
        }

        return response()->json($results);
    }

    public function confirmPayment(Request $req)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $req->validate([
            'user_id' => 'required|integer',
            'fee_type' => 'required|in:coc_voluntary,coc_obligatory,iut_nigeria,iut_abroad',
            'payment_reference' => 'required|string|max:100',
            'payment_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = DB::table('users')->where('id', $req->user_id)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Determine description and amount based on fee type
        switch ($req->fee_type) {
            case 'coc_voluntary':
                $description = 'CHANGE OF COURSE FEE';
                $amount = (float) SystemSettingsController::get('change_of_course_fee_voluntary', 100000);
                break;
            case 'coc_obligatory':
                $description = 'CHANGE OF COURSE FEE';
                $amount = (float) SystemSettingsController::get('change_of_course_fee_obligatory', 50000);
                break;
            case 'iut_nigeria':
                $description = 'INTER-UNIVERSITY TRANSFER FEE';
                $amount = (float) SystemSettingsController::get('inter_university_transfer_fee_nigeria', 150000);
                break;
            case 'iut_abroad':
                $description = 'INTER-UNIVERSITY TRANSFER FEE';
                $amount = (float) SystemSettingsController::get('inter_university_transfer_fee_abroad', 250000);
                break;
        }

        // Check if already has paid invoice for this
        $exists = DB::table('invoices')
            ->where('username', $user->id)
            ->where('description', $description)
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'This user already has a paid ' . $description . ' for this session'], 400);
        }

        // Handle receipt upload
        $receiptPath = null;
        if ($req->hasFile('payment_receipt')) {
            $file = $req->file('payment_receipt');
            $filename = 'offline_' . $req->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/payment_receipts'), $filename);
            $receiptPath = 'uploads/payment_receipts/' . $filename;
        }

        // Create paid invoice
        DB::table('invoices')->insert([
            'username' => $user->id,
            'name' => $user->name,
            'amount' => $amount,
            'rrr' => 'OFFLINE-' . $req->payment_reference,
            'orderId' => 'OFFLINE-' . time() . rand(100,999),
            'description' => $description,
            'payment_method' => 'Offline',
            'status' => 'Paid',
            'session' => session('system_session'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $feeLabels = [
            'coc_voluntary' => 'Change of Course (Voluntary)',
            'coc_obligatory' => 'Change of Course (Obligatory)',
            'iut_nigeria' => 'Inter-University Transfer (Within Nigeria)',
            'iut_abroad' => 'Inter-University Transfer (Abroad)',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Payment created for ' . $user->name . ' — ' . $feeLabels[$req->fee_type] . ' (₦' . number_format($amount, 2) . ')',
        ]);
    }
}
