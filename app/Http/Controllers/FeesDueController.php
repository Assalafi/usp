<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PaidStudentsExport;
use App\Exports\UnpaidStudentsExport;

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

            $data['data'] = DB::table($this->table)->where(['status' => 'Paid', 'session' => $selectedSession])->paginate(100)->withQueryString();


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
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
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
