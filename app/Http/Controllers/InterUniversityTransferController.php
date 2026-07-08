<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;

class InterUniversityTransferController extends Controller
{
    private $merchantId;
    private $apiKey;

    public function __construct()
    {
        $this->merchantId = env('REMITA_MERCHANT_ID');
        $this->apiKey = env('REMITA_API_KEY');
    }

    // ==========================================
    // REGISTRATION & AUTH (standalone pages)
    // ==========================================

    /**
     * Show registration form (standalone, no auth required)
     */
    public function showRegister()
    {
        if (session()->has('log')) {
            return redirect('/inter-university-transfer');
        }
        return view('InterUniversityTransfer.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,username',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $fullname = strtoupper($request->surname . ' ' . $request->first_name . ' ' . ($request->middle_name ?? ''));

        $userId = DB::table('users')->insertGetId([
            'username' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'accType' => 'Transfer',
            'name' => trim($fullname),
            'gender' => $request->gender ?? 'MALE',
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/')->with('success', 'Account created successfully! Please login with your email and password.');
    }

    // ==========================================
    // DASHBOARD (after login)
    // ==========================================

    /**
     * Transfer applicant dashboard
     */
    public function index()
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('inter_university_transfer')
            ->where('user_id', session('id'))
            ->where('session', session('system_session'))
            ->orderBy('id', 'desc')
            ->first();

        // Check if has paid
        $hasPaid = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->exists();

        $data = [
            'page' => 'inter university transfer',
            'application' => $application,
            'hasPaid' => $hasPaid,
        ];

        return view('main', $data);
    }

    // ==========================================
    // PAYMENT
    // ==========================================

    /**
     * Show payment page
     */
    public function paymentPage()
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Check if already paid
        $hasPaid = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->exists();

        if ($hasPaid) {
            return redirect('/inter-university-transfer')->with('info', 'Payment already completed.');
        }

        // Check for existing pending invoice
        $existingInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Pending')
            ->whereNotNull('rrr')
            ->first();

        $withinNigeriaFee = (float) SystemSettingsController::get('inter_university_transfer_fee_nigeria', 150000);
        $abroadFee = (float) SystemSettingsController::get('inter_university_transfer_fee_abroad', 250000);

        $data = [
            'page' => 'inter university transfer payment',
            'withinNigeriaFee' => $withinNigeriaFee,
            'abroadFee' => $abroadFee,
            'existingRrr' => $existingInvoice ? $existingInvoice->rrr : null,
            'existingAmount' => $existingInvoice ? (float) $existingInvoice->amount : null,
        ];

        return view('main', $data);
    }

    /**
     * Initialize Remita payment
     */
    public function initializePayment(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'transfer_type' => 'required|in:within_nigeria,abroad',
        ]);

        $amount = $request->transfer_type == 'within_nigeria'
            ? (float) SystemSettingsController::get('inter_university_transfer_fee_nigeria', 150000)
            : (float) SystemSettingsController::get('inter_university_transfer_fee_abroad', 250000);

        // Check for existing pending invoice with RRR
        $existingInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Pending')
            ->whereNotNull('rrr')
            ->first();

        if ($existingInvoice) {
            return response()->json([
                'success' => true,
                'rrr' => $existingInvoice->rrr,
                'amount' => (float) $existingInvoice->amount,
                'merchantId' => $this->merchantId,
                'existing' => true
            ]);
        }

        $user = DB::table('users')->where('id', session('id'))->first();
        $orderId = time() . rand(1000, 9999);
        $description = 'INTER-UNIVERSITY TRANSFER FEE';
        $serviceTypeId = env('REMITA_INTER_TRANSFER_KEY', '4430731');

        $paymentData = [
            'serviceTypeId' => $serviceTypeId,
            'amount' => $amount,
            'orderId' => $orderId,
            'payerName' => $user->name ?? session('username'),
            'payerEmail' => session('username'),
            'payerPhone' => '08000000000',
            'description' => $description,
        ];

        $apiHash = hash('sha512',
            $this->merchantId . $serviceTypeId . $orderId . $amount . $this->apiKey
        );

        $remitaBaseUrl = SystemSettingsController::getRemitaBaseUrl();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remitaBaseUrl . '/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $apiHash,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        \Log::info('Inter-University Transfer Payment Debug:', [
            'httpCode' => $httpCode,
            'curlError' => $curlError,
            'response' => $response,
        ]);

        if ($httpCode == 200) {
            $cleanResponse = str_replace('jsonp (', '', $response);
            $cleanResponse = str_replace(')', '', $cleanResponse);
            $result = json_decode($cleanResponse, true);

            if (isset($result['RRR'])) {
                $rrr = $result['RRR'];

                DB::table('invoices')->insert([
                    'username' => session('id'),
                    'name' => $user->name ?? session('username'),
                    'phone' => '08000000000',
                    'email' => session('username'),
                    'description' => $description,
                    'amount' => $amount,
                    'rrr' => $rrr,
                    'orderId' => $orderId,
                    'serviceTypeId' => $serviceTypeId,
                    'session' => session('system_session'),
                    'status' => 'Pending',
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                return response()->json([
                    'success' => true,
                    'rrr' => $rrr,
                    'amount' => $amount,
                    'merchantId' => $this->merchantId,
                ]);
            }

            return response()->json([
                'error' => 'Payment initialization failed: ' . ($result['message'] ?? 'Unknown error'),
            ], 500);
        }

        return response()->json([
            'error' => 'Payment initialization failed. HTTP Code: ' . $httpCode . '. Error: ' . $curlError,
        ], 500);
    }

    /**
     * Verify Remita payment
     */
    public function verifyPayment(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized');
        }

        $rrr = $request->query('rrr');
        if (!$rrr) {
            return redirect('/inter-university-transfer/payment')->with('error', 'RRR is required for verification');
        }

        $remitaBaseUrl = SystemSettingsController::getRemitaBaseUrl();
        $apiHash = hash('sha512', $rrr . $this->apiKey . $this->merchantId);
        $url = $remitaBaseUrl . '/remita/exapp/api/v1/send/api/echannelsvc/' . $this->merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $apiHash,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $cleanResponse = str_replace('jsonp (', '', $response);
        $cleanResponse = rtrim($cleanResponse, ')');
        $result = json_decode($cleanResponse, true);

        \Log::info('Inter-University Transfer Verify:', ['rrr' => $rrr, 'result' => $result]);

        if (isset($result['status']) && in_array($result['status'], ['00', '01'])) {
            DB::table('invoices')->where('rrr', $rrr)->update([
                'status' => 'Paid',
                'updated_at' => date('Y-m-d'),
            ]);

            return redirect('/inter-university-transfer')
                ->with('success', 'Payment verified successfully! You can now fill your application.');
        }

        $errorMsg = 'Payment verification failed.';
        if (isset($result['message'])) $errorMsg .= ' ' . $result['message'];
        return redirect('/inter-university-transfer/payment')->with('error', $errorMsg);
    }

    // ==========================================
    // APPLICATION FORM
    // ==========================================

    /**
     * Show application form
     */
    public function applicationForm()
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Check payment
        $hasPaid = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->exists();

        if (!$hasPaid) {
            return redirect('/inter-university-transfer/payment')->with('error', 'Please complete payment first.');
        }

        // Check if already submitted
        $application = DB::table('inter_university_transfer')
            ->where('user_id', session('id'))
            ->where('session', session('system_session'))
            ->first();

        if ($application && $application->status != 'Draft') {
            return redirect('/inter-university-transfer')->with('info', 'Application already submitted.');
        }

        $faculties = DB::table('faculty')->where('status', '1')->orderBy('title', 'ASC')->get();

        // Get invoice to determine transfer type
        $invoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->first();

        $data = [
            'page' => 'inter university transfer form',
            'application' => $application,
            'faculties' => $faculties,
            'invoice' => $invoice,
        ];

        return view('main', $data);
    }

    /**
     * Store/update application
     */
    public function store(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'first_name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string',
            'postal_address' => 'required|string',
            'present_institution' => 'required|string',
            'registration_number' => 'required|string',
            'year_of_study' => 'required|string',
            'new_faculty' => 'required',
            'new_department' => 'required',
            'new_program' => 'required',
            'reason_for_transfer' => 'required|string|min:20',
            'transfer_type' => 'required|in:within_nigeria,abroad',
            'admission_type' => 'required|in:UTME,DE',
            'jamb_score' => 'required_if:admission_type,UTME|nullable|integer|min:0|max:400',
            'jamb_result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $invoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'INTER-UNIVERSITY TRANSFER FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Payment not found.');
        }

        $applicationNo = 'UM/IUT/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Handle JAMB result file upload
        $jambResultFile = $existing->jamb_result_file ?? null;
        if ($request->hasFile('jamb_result_file')) {
            $file = $request->file('jamb_result_file');
            $filename = 'jamb_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/transfer_documents'), $filename);
            $jambResultFile = $filename;
        }

        $applicationData = [
            'user_id' => session('id'),
            'application_no' => $applicationNo,
            'first_name' => strtoupper($request->first_name),
            'middle_name' => strtoupper($request->middle_name ?? ''),
            'surname' => strtoupper($request->surname),
            'admission_type' => $request->admission_type,
            'jamb_score' => $request->admission_type === 'UTME' ? $request->jamb_score : null,
            'jamb_result_file' => $jambResultFile,
            'date_of_birth' => $request->date_of_birth,
            'nationality' => strtoupper($request->nationality),
            'postal_address' => strtoupper($request->postal_address),
            'phone' => $request->phone,
            'email' => session('username'),
            'present_institution' => strtoupper($request->present_institution),
            'registration_number' => strtoupper($request->registration_number),
            'year_of_study' => $request->year_of_study,
            'transfer_type' => $request->transfer_type,
            'new_faculty' => $request->new_faculty,
            'new_department' => $request->new_department,
            'new_program' => $request->new_program,
            'reason_for_transfer' => $request->reason_for_transfer,
            'qualifications_wasc' => $request->qualifications_wasc,
            'qualifications_tc2' => $request->qualifications_tc2,
            'qualifications_gce' => $request->qualifications_gce,
            'qualifications_ijmb' => $request->qualifications_ijmb,
            'qualifications_nce' => $request->qualifications_nce,
            'qualifications_others' => $request->qualifications_others,
            'rrr' => $invoice->rrr,
            'payment_status' => 'Paid',
            'amount' => $invoice->amount,
            'payment_date' => date('Y-m-d'),
            'status' => 'Awaiting Documents',
            'session' => session('system_session'),
            'updated_at' => now(),
        ];

        $existing = DB::table('inter_university_transfer')
            ->where('user_id', session('id'))
            ->where('session', session('system_session'))
            ->first();

        if ($existing) {
            DB::table('inter_university_transfer')->where('id', $existing->id)->update($applicationData);
        } else {
            $applicationData['created_at'] = now();
            DB::table('inter_university_transfer')->insert($applicationData);
        }

        return redirect('/inter-university-transfer')->with('success', 'Application saved! Please upload your documents.');
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('inter_university_transfer')
            ->where('user_id', session('id'))
            ->where('session', session('system_session'))
            ->first();

        if (!$application) {
            return redirect('/inter-university-transfer')->with('error', 'Application not found.');
        }

        $updateData = ['updated_at' => now()];

        if ($request->hasFile('certificates_upload')) {
            $request->validate(['certificates_upload' => 'file|mimes:pdf,jpg,jpeg,png|max:5120']);
            $file = $request->file('certificates_upload');
            $filename = 'certificates_' . session('id') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inter_transfer'), $filename);
            $updateData['certificates_upload'] = 'uploads/inter_transfer/' . $filename;
        }

        if ($request->hasFile('present_institution_approval')) {
            $request->validate(['present_institution_approval' => 'file|mimes:pdf,jpg,jpeg,png|max:5120']);
            $file = $request->file('present_institution_approval');
            $filename = 'approval_' . session('id') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inter_transfer'), $filename);
            $updateData['present_institution_approval'] = 'uploads/inter_transfer/' . $filename;
        }

        if ($request->hasFile('transcript_upload')) {
            $request->validate(['transcript_upload' => 'file|mimes:pdf,jpg,jpeg,png|max:5120']);
            $file = $request->file('transcript_upload');
            $filename = 'transcript_' . session('id') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inter_transfer'), $filename);
            $updateData['transcript_upload'] = 'uploads/inter_transfer/' . $filename;
        }

        DB::table('inter_university_transfer')->where('id', $application->id)->update($updateData);

        // Check if all required docs are uploaded - if so, move to approval
        $updated = DB::table('inter_university_transfer')->where('id', $application->id)->first();
        if ($updated->certificates_upload && $updated->present_institution_approval) {
            // MBBS/DBS programs skip HOD/Dean, go directly to Provost
            $nextStatus = $this->isMedicalOrDentalProgram($updated->new_program) ? 'Awaiting Provost' : 'Awaiting UNIMAID HOD';
            DB::table('inter_university_transfer')->where('id', $application->id)->update([
                'status' => $nextStatus,
            ]);
        }

        return redirect('/inter-university-transfer')->with('success', 'Documents uploaded successfully!');
    }

    /**
     * Upload JAMB result for existing applicants
     */
    public function uploadJambResult(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Transfer') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'jamb_result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'jamb_score' => 'nullable|integer|min:0|max:400',
        ]);

        $application = DB::table('inter_university_transfer')
            ->where('user_id', session('id'))
            ->where('session', session('system_session'))
            ->first();

        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }

        $updateData = ['updated_at' => now()];

        // Handle file upload
        if ($request->hasFile('jamb_result_file')) {
            $file = $request->file('jamb_result_file');
            $filename = 'jamb_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/transfer_documents'), $filename);
            $updateData['jamb_result_file'] = $filename;
        }

        // Save JAMB score if provided (for UTME applicants)
        if ($request->filled('jamb_score') && $application->admission_type == 'UTME') {
            $updateData['jamb_score'] = $request->jamb_score;
        }

        if (count($updateData) <= 1) {
            return redirect()->back()->with('error', 'Please provide JAMB result file or score.');
        }

        DB::table('inter_university_transfer')->where('id', $application->id)->update($updateData);

        return redirect('/inter-university-transfer')->with('success', 'JAMB information updated successfully!');
    }

    // ==========================================
    // AJAX helpers (same as departmental)
    // ==========================================

    public function getDepartments(Request $request)
    {
        $departments = DB::table('department')
            ->where('faculty', $request->faculty)
            ->where('status', '1')
            ->orderBy('title', 'ASC')
            ->get();
        return response()->json($departments);
    }

    public function getPrograms(Request $request)
    {
        $programs = DB::table('program')
            ->where('department', $request->department)
            ->where('status', '1')
            ->orderBy('title', 'ASC')
            ->get();
        return response()->json($programs);
    }

    // ==========================================
    // ADMIN / STAFF APPROVAL
    // ==========================================

    /**
     * Get approver name from staff table or Admin
     */
    private function getApproverName()
    {
        if (session('accType') == 'Admin') {
            return 'Admin (' . session('username') . ')';
        }
        $staff = DB::table('staff')->where('username', session('username'))->first();
        if ($staff && isset($staff->name)) {
            return $staff->name;
        }
        $user = DB::table('users')->where('username', session('username'))->first();
        return $user ? $user->name : session('username');
    }

    private function canApproveAsHOD($department)
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'HOD' && session('department') == $department) return true;
        return false;
    }

    private function canApproveAsDean($faculty)
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'DEAN' && session('faculty') == $faculty) return true;
        return false;
    }

    private function canApproveAsProvost()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'PROVOST') return true;
        return false;
    }

    private function canApproveAsRegistrar()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'REGISTRAR') return true;
        return false;
    }

    /**
     * Check if a program is MBBS or DBS (no HOD/Dean required)
     */
    private function isMedicalOrDentalProgram($programCode)
    {
        $medicalPrograms = ['MBBS', 'DBS'];
        return in_array($programCode, $medicalPrograms);
    }

    private function canApproveAsVC()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'VC') return true;
        return false;
    }

    /**
     * Admin/Staff: View all applications
     */
    public function adminIndex(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $isAdmin = session('accType') == 'Admin';
        $isHOD = session('accType') == 'Staff' && session('appointment') == 'HOD';
        $isDean = session('accType') == 'Staff' && session('appointment') == 'DEAN';
        $isProvost = session('accType') == 'Staff' && session('appointment') == 'PROVOST';
        $isRegistrar = session('accType') == 'Staff' && session('appointment') == 'REGISTRAR';
        $isVC = session('accType') == 'Staff' && session('appointment') == 'VC';
        $isCOC = session('accType') == 'Staff' && session('appointment') == 'COC';

        if (!$isAdmin && !$isHOD && !$isDean && !$isProvost && !$isRegistrar && !$isVC && !$isCOC) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $query = DB::table('inter_university_transfer')
            ->whereNotIn('status', ['Draft', 'Payment Pending', 'Awaiting Documents']);

        if ($isHOD) {
            $query->where('new_department', session('department'));
        } elseif ($isDean) {
            $query->where('new_faculty', session('faculty'));
        } elseif ($isProvost) {
            // Provost sees applications where new faculty is a college (college = 1)
            $query->whereExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('faculty')
                    ->whereRaw('faculty.code COLLATE utf8mb4_unicode_ci = inter_university_transfer.new_faculty COLLATE utf8mb4_unicode_ci')
                    ->where('faculty.college', 1);
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        if ($request->has('session') && $request->session != '') {
            $query->where('session', $request->session);
        }

        $applications = $query->orderBy('id', 'desc')->paginate(50);

        $data = [
            'page' => 'inter university transfer admin',
            'applications' => $applications,
            'userRole' => $isAdmin ? 'Admin' : ($isCOC ? 'COC' : ($isHOD ? 'HOD' : ($isDean ? 'Dean' : ($isRegistrar ? 'Registrar' : 'VC')))),
        ];

        return view('main', $data);
    }

    /**
     * Admin/Staff: View single application
     */
    public function show($id)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $isAdmin = session('accType') == 'Admin';
        $isHOD = session('accType') == 'Staff' && session('appointment') == 'HOD';
        $isDean = session('accType') == 'Staff' && session('appointment') == 'DEAN';
        $isProvost = session('accType') == 'Staff' && session('appointment') == 'PROVOST';
        $isRegistrar = session('accType') == 'Staff' && session('appointment') == 'REGISTRAR';
        $isVC = session('accType') == 'Staff' && session('appointment') == 'VC';
        $isCOC = session('accType') == 'Staff' && session('appointment') == 'COC';

        if (!$isAdmin && !$isHOD && !$isDean && !$isProvost && !$isRegistrar && !$isVC && !$isCOC) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        $newFacultyTitle = DB::table('faculty')->where('code', $application->new_faculty)->value('title');
        $newDepartmentTitle = DB::table('department')->where('code', $application->new_department)->value('title');
        $newProgramTitle = DB::table('program')->where('code', $application->new_program)->value('title');

        $canApproveHOD = $this->canApproveAsHOD($application->new_department);
        $canApproveDean = $this->canApproveAsDean($application->new_faculty);
        $canApproveProvost = $this->canApproveAsProvost();
        $canApproveRegistrar = $this->canApproveAsRegistrar();
        $canApproveVC = $this->canApproveAsVC();

        $data = [
            'page' => 'inter university transfer details',
            'application' => $application,
            'newFacultyTitle' => $newFacultyTitle,
            'newDepartmentTitle' => $newDepartmentTitle,
            'newProgramTitle' => $newProgramTitle,
            'userRole' => $isAdmin ? 'Admin' : ($isCOC ? 'COC' : ($isHOD ? 'HOD' : ($isDean ? 'Dean' : ($isProvost ? 'Provost' : ($isRegistrar ? 'Registrar' : 'VC'))))),
            'canApproveHOD' => $canApproveHOD,
            'canApproveDean' => $canApproveDean,
            'canApproveProvost' => $canApproveProvost,
            'canApproveRegistrar' => $canApproveRegistrar,
            'canApproveVC' => $canApproveVC,
        ];

        return view('main', $data);
    }

    /**
     * UNIMAID HOD Action
     */
    public function hodAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        if (!$this->canApproveAsHOD($application->new_department)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'unimaid_hod_recommendation' => $request->decision,
            'unimaid_hod_remarks' => $request->remarks,
            'unimaid_hod_name' => $approverName,
            'unimaid_hod_date' => date('Y-m-d'),
            'updated_at' => now(),
        ];

        if ($request->decision == 'Yes') {
            $updateData['status'] = 'Awaiting UNIMAID Dean';
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        return response()->json(['success' => true, 'message' => 'Your recommendation has been recorded.']);
    }

    /**
     * UNIMAID Dean Action
     */
    public function deanAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        if (!$this->canApproveAsDean($application->new_faculty)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'unimaid_dean_recommendation' => $request->decision,
            'unimaid_dean_remarks' => $request->remarks,
            'unimaid_dean_name' => $approverName,
            'unimaid_dean_date' => date('Y-m-d'),
            'updated_at' => now(),
        ];

        if ($request->decision == 'Yes') {
            // Check if new faculty has college = 1, then route to Provost
            $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
            if ($newFaculty && $newFaculty->college == 1) {
                $updateData['status'] = 'Awaiting Provost';
            } else {
                $updateData['status'] = 'Awaiting Registrar';
            }
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        return response()->json(['success' => true, 'message' => 'Your recommendation has been recorded.']);
    }

    /**
     * Provost Action (for colleges and MBBS/DBS)
     */
    public function provostAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        if (!$this->canApproveAsProvost()) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'provost_recommendation' => $request->decision,
            'provost_remarks' => $request->remarks,
            'provost_name' => $approverName,
            'provost_date' => date('Y-m-d'),
            'updated_at' => now(),
        ];

        if ($request->decision == 'Yes') {
            // Auto-fill MBBS/DBS HOD/Dean fields that Provost is handling
            if ($this->isMedicalOrDentalProgram($application->new_program)) {
                $updateData['unimaid_hod_recommendation'] = 'Yes';
                $updateData['unimaid_hod_name'] = $approverName . ' (Provost)';
                $updateData['unimaid_hod_date'] = date('Y-m-d');
                $updateData['unimaid_dean_recommendation'] = 'Yes';
                $updateData['unimaid_dean_name'] = $approverName . ' (Provost)';
                $updateData['unimaid_dean_date'] = date('Y-m-d');
            }
            $updateData['status'] = 'Awaiting Registrar';
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        return response()->json(['success' => true, 'message' => 'Your recommendation has been recorded.']);
    }

    /**
     * Registrar Action
     */
    public function registrarAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->canApproveAsRegistrar()) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'remarks' => 'nullable',
        ]);

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $approverName = $this->getApproverName();

        $updateData = [
            'registrar_decision' => $request->decision,
            'registrar_remarks' => $request->remarks,
            'registrar_name' => $approverName,
            'registrar_date' => date('Y-m-d'),
            'status' => $request->decision == 'Approved' ? 'Awaiting VC' : 'Rejected',
            'updated_at' => now(),
        ];

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        return response()->json(['success' => true, 'message' => 'Forwarded to VC for final approval.']);
    }

    /**
     * VC Action (Final Approval)
     */
    public function vcAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->canApproveAsVC()) {
            return response()->json(['error' => 'You are not authorized as VC'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'remarks' => 'nullable',
        ]);

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $approverName = $this->getApproverName();

        $updateData = [
            'vc_decision' => $request->decision,
            'vc_remarks' => $request->remarks,
            'vc_name' => $approverName,
            'vc_date' => date('Y-m-d'),
            'status' => $request->decision,
            'updated_at' => now(),
        ];

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        if ($request->decision == 'Approved') {
            $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
            $newDepartment = DB::table('department')->where('code', $application->new_department)->first();
            $f = $newFaculty ? $newFaculty->no : '00';
            $d = $newDepartment ? $newDepartment->no : '00';
            $newIdFormat = '/' . str_pad($f, 2, '0', STR_PAD_LEFT) . '/' . str_pad($d, 2, '0', STR_PAD_LEFT) . '/';
            $fullname = strtoupper(trim($application->surname . ' ' . $application->first_name . ' ' . $application->middle_name));

            $newUserId = DB::table('users')->insertGetId([
                'username' => $application->application_no,
                'password' => \Hash::make(SystemSettingsController::get('default_student_password', 'umstad@2026')),
                'accType' => 'Student',
                'gender' => 'MALE',
                'name' => $fullname,
                'status' => '0',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('students')->insert([
                'user_id' => $newUserId,
                'last_name' => $application->surname,
                'first_name' => $application->first_name,
                'other_name' => $application->middle_name,
                'fullname' => $fullname,
                'program' => $application->new_program,
                'department' => $application->new_department,
                'faculty' => $application->new_faculty,
                'id_format' => $newIdFormat,
                'session_of_entry' => $application->session,
                'level_of_entry' => '100',
                'level' => '100',
                'mode_of_entry' => 'TRANSFER',
                'transfer_from' => $application->present_institution,
                'country' => $application->nationality,
                'contact_phone' => $application->phone,
                'contact_email' => $application->email,
                'status' => '1',
                'school_fee' => '1',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Log::info('Inter-University Transfer VC Approved - Student Record Created', [
                'application_id' => $id,
                'application_no' => $application->application_no,
                'new_user_id' => $newUserId,
                'approved_by' => session('username'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'VC decision recorded successfully.']);
    }

    /**
     * Admin: Bulk edit application
     */
    public function bulkEdit($id)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        // Get all faculties, departments, and programs for dropdowns
        $faculties = DB::table('faculty')->orderBy('title')->get();
        $departments = DB::table('department')->orderBy('title')->get();
        $programs = DB::table('program')->orderBy('title')->get();

        // Get current titles
        $newFacultyTitle = DB::table('faculty')->where('code', $application->new_faculty)->value('title');
        $newDepartmentTitle = DB::table('department')->where('code', $application->new_department)->value('title');
        $newProgramTitle = DB::table('program')->where('code', $application->new_program)->value('title');

        $data = [
            'page' => 'inter university transfer bulk-edit',
            'application' => $application,
            'faculties' => $faculties,
            'departments' => $departments,
            'programs' => $programs,
            'newFacultyTitle' => $newFacultyTitle,
            'newDepartmentTitle' => $newDepartmentTitle,
            'newProgramTitle' => $newProgramTitle,
        ];

        return view('main', $data);
    }

    /**
     * Admin: Bulk update application
     */
    public function bulkUpdate(Request $request, $id)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'new_faculty' => 'required|string',
            'new_department' => 'required|string',
            'new_program' => 'required|string',
            'status' => 'required|string',
            'admission_type' => 'nullable|string',
            'jamb_score' => 'nullable|integer|min:0|max:400',
            'reason_for_transfer' => 'required|string',
            'present_institution' => 'required|string',
            'registration_number' => 'required|string',
            'year_of_study' => 'required|string',
            'transfer_type' => 'required|string|in:within_nigeria,from_abroad',
        ]);

        $updateData = [
            'surname' => $request->surname,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'new_faculty' => $request->new_faculty,
            'new_department' => $request->new_department,
            'new_program' => $request->new_program,
            'status' => $request->status,
            'admission_type' => $request->admission_type,
            'jamb_score' => $request->jamb_score,
            'reason_for_transfer' => $request->reason_for_transfer,
            'present_institution' => $request->present_institution,
            'registration_number' => $request->registration_number,
            'year_of_study' => $request->year_of_study,
            'transfer_type' => $request->transfer_type,
            'updated_at' => now(),
        ];

        // Update officer actions if provided
        if ($request->has('unimaid_hod_recommendation')) {
            $updateData['unimaid_hod_recommendation'] = $request->unimaid_hod_recommendation ?: 'Pending';
            $updateData['unimaid_hod_remarks'] = $request->unimaid_hod_remarks;
            $updateData['unimaid_hod_name'] = $request->unimaid_hod_name ?: 'Admin Override';
            $updateData['unimaid_hod_date'] = $request->unimaid_hod_date ?: date('Y-m-d');
        }

        if ($request->has('unimaid_dean_recommendation')) {
            $updateData['unimaid_dean_recommendation'] = $request->unimaid_dean_recommendation ?: 'Pending';
            $updateData['unimaid_dean_remarks'] = $request->unimaid_dean_remarks;
            $updateData['unimaid_dean_name'] = $request->unimaid_dean_name ?: 'Admin Override';
            $updateData['unimaid_dean_date'] = $request->unimaid_dean_date ?: date('Y-m-d');
        }

        if ($request->has('provost_recommendation')) {
            $updateData['provost_recommendation'] = $request->provost_recommendation ?: 'Pending';
            $updateData['provost_remarks'] = $request->provost_remarks;
            $updateData['provost_name'] = $request->provost_name ?: 'Admin Override';
            $updateData['provost_date'] = $request->provost_date ?: date('Y-m-d');
        }

        if ($request->has('registrar_decision')) {
            $updateData['registrar_decision'] = $request->registrar_decision ?: 'Pending';
            $updateData['registrar_remarks'] = $request->registrar_remarks;
            $updateData['registrar_name'] = $request->registrar_name ?: 'Admin Override';
            $updateData['registrar_date'] = $request->registrar_date ?: date('Y-m-d');
        }

        if ($request->has('vc_decision')) {
            $updateData['vc_decision'] = $request->vc_decision ?: 'Pending';
            $updateData['vc_remarks'] = $request->vc_remarks;
            $updateData['vc_name'] = $request->vc_name ?: 'Admin Override';
            $updateData['vc_date'] = $request->vc_date ?: date('Y-m-d');
        }

        DB::table('inter_university_transfer')->where('id', $id)->update($updateData);

        // Log the admin action
        \Log::info('Inter-University Transfer Bulk Updated by Admin', [
            'application_id' => $id,
            'application_no' => $application->application_no,
            'admin_id' => session('id'),
            'updated_fields' => array_keys($updateData),
            'updated_date' => date('Y-m-d H:i:s')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application has been successfully updated.'
        ]);
    }

    /**
     * Admin: Generate admission letter PDF for approved IUT application
     */
    public function generateAdmissionLetter($id)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $isAdmin = session('accType') == 'Admin';
        $isStaff = session('accType') == 'Staff';
        if (!$isAdmin && !$isStaff) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('inter_university_transfer')->where('id', $id)->first();
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        if ($application->status != 'Approved') {
            return redirect()->back()->with('error', 'Admission letter is only available for approved applications.');
        }

        $options = new Options();
        $options->set('defaultFont', 'Times');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('chroot', public_path());
        $options->set('tempDir', sys_get_temp_dir());

        $dompdf = new Dompdf($options);

        $html = view('admission-letter-iut-pdf', compact('application'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $application->application_no . '_ADMISSION_LETTER_' . date('Y-m-d') . '.pdf';
        $filename = str_replace(['/', ' '], '_', $filename);

        return $dompdf->stream($filename, ['Attachment' => false]);
    }
}
