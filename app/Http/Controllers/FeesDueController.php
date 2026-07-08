<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($req->has('_token')) {
            $data = $req->all();
            $start = $req -> start;
            $end = $req -> end;
            $fac = $req -> faculty;
            $status = $req -> status;
            $rrr = $req -> rrr;

            // If RRR is provided, search only by RRR and ignore other filters
            if ($rrr) {
                $query = DB::table($this->table)->where('rrr', 'LIKE', '%' . $rrr . '%');
                $data['data'] = $query->get();
            } else {
                if($fac == 'none'){
                    unset($data['faculty']);
                }
                unset($data['_token']);
                unset($data['start']);
                unset($data['end']);
                unset($data['rrr']);
                $filteredData = array_filter($data);
                $query = DB::table($this->table)->where(['session' => $req->session]);
                foreach ($filteredData as $key => $value) {
                    $query->where($key, $value);
                }
                if($fac == 'none'){
                    $data['data'] = $query->where(['status' => 'Paids'])->get();
                }else{
                    if($status == 'Pending'){
                        $data['data'] = $query->get();
                    }else{
                        $data['data'] = $query->whereBetween('updated_at', [$start,$end])->get();
                    }

                }
            }


            $data['hostel'] = DB::table($this->table)->where(['status' => 'Paid', 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => '2024/2025'])->whereBetween('updated_at', [$start,$end])->sum('amount');
            $data['school'] = DB::table($this->table)->where(['status' => 'Paid', 'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES', 'session' => '2024/2025'])->whereBetween('updated_at', [$start,$end])->sum('amount');
        }else{

            $data['data'] = DB::table($this->table)->where(['status' => 'Paids'])->get();

            $data['hostel'] = DB::table($this->table)->where(['status' => 'Paid', 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => '2024/2025'])->sum('amount');
            $data['school'] = DB::table($this->table)->where(['status' => 'Paid', 'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES', 'session' => '2024/2025'])->sum('amount');
        }
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['fees_type'] = DB::table('fees_type')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
            $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
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
