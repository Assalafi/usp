<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class ChangeOfCourseController extends Controller
{
    private $merchantId;
    private $apiKey;
    
    public function __construct()
    {
        $this->merchantId = env('REMITA_MERCHANT_ID');
        $this->apiKey = env('REMITA_API_KEY');
    }

    /**
     * Student: View change of course application form
     * Payment MUST be completed first before accessing the form
     */
    public function index()
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get student data
        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        if (!$student) {
            return redirect('/')->with('error', 'Student record not found');
        }

        // Get ALL applications for this student in current session
        $applications = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'))
            ->orderBy('id', 'desc')
            ->get();

        // Get RRRs already used by applications
        $usedRrrs = $applications->whereNotNull('rrr')->pluck('rrr')->toArray();

        // Check for unused paid invoice (paid but not linked to any application)
        $unusedPaidInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->when(!empty($usedRrrs), function ($q) use ($usedRrrs) {
                return $q->whereNotIn('rrr', $usedRrrs);
            })
            ->first();

        $hasUnusedPayment = $unusedPaidInvoice ? true : false;

        // If no applications and no unused payment, show payment page
        if ($applications->isEmpty() && !$hasUnusedPayment) {
            // Check for existing pending invoice with RRR
            $existingInvoice = DB::table('invoices')
                ->where('username', session('id'))
                ->where('description', 'CHANGE OF COURSE FEE')
                ->where('session', session('system_session'))
                ->where('status', 'Pending')
                ->whereNotNull('rrr')
                ->first();

            $data = [
                'page' => 'change of course payment',
                'student' => $student,
                'voluntaryFee' => (float) SystemSettingsController::get('change_of_course_fee_voluntary', 100000),
                'obligatoryFee' => (float) SystemSettingsController::get('change_of_course_fee_obligatory', 50000),
                'existingRrr' => $existingInvoice ? $existingInvoice->rrr : null,
                'existingInvoiceAmount' => $existingInvoice ? (float) $existingInvoice->amount : null,
            ];
            return view('main', $data);
        }

        // Get faculties and departments for dropdowns
        $faculties = DB::table('faculty')
            ->where('status', '1')
            ->orderBy('title', 'ASC')
            ->get();

        $data = [
            'page' => 'change of course',
            'student' => $student,
            'applications' => $applications,
            'application' => $applications->first(), // latest for backward compat
            'faculties' => $faculties,
            'hasPaid' => $hasUnusedPayment,
        ];

        return view('main', $data);
    }

    /**
     * Student: Start a new application (after previous was Approved/Rejected)
     */
    public function newApplication()
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        if (!$student) {
            return redirect('/')->with('error', 'Student record not found');
        }

        // Get RRRs already used by existing applications
        $usedRrrs = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'))
            ->whereNotNull('rrr')
            ->pluck('rrr')
            ->toArray();

        // Check for unused paid invoice
        $unusedPaidInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->when(!empty($usedRrrs), function ($q) use ($usedRrrs) {
                return $q->whereNotIn('rrr', $usedRrrs);
            })
            ->first();

        // If there's an unused paid invoice, go straight to application form
        if ($unusedPaidInvoice) {
            return redirect()->route('change-of-course.index')
                ->with('success', 'You have an unused payment. Please submit your application.');
        }

        // Check for existing pending invoice with RRR
        $existingInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Pending')
            ->whereNotNull('rrr')
            ->first();

        $data = [
            'page' => 'change of course payment',
            'student' => $student,
            'voluntaryFee' => (float) SystemSettingsController::get('change_of_course_fee_voluntary', 100000),
            'obligatoryFee' => (float) SystemSettingsController::get('change_of_course_fee_obligatory', 50000),
            'existingRrr' => $existingInvoice ? $existingInvoice->rrr : null,
            'existingInvoiceAmount' => $existingInvoice ? (float) $existingInvoice->amount : null,
            'isNewApplication' => true,
        ];
        return view('main', $data);
    }

    /**
     * Get departments by faculty (AJAX)
     */
    public function getDepartments(Request $request)
    {
        $departments = DB::table('department')
            ->where('faculty', $request->faculty)
            ->where('status', '1')
            ->orderBy('title', 'ASC')
            ->get();

        return response()->json($departments);
    }

    /**
     * Get programs by department (AJAX)
     */
    public function getPrograms(Request $request)
    {
        $programs = DB::table('program')
            ->where('department', $request->department)
            ->where('status', '1')
            ->orderBy('title', 'ASC')
            ->get();

        return response()->json($programs);
    }

    /**
     * Upload JAMB result for existing applicants
     */
    public function uploadJambResult(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'jamb_result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'jamb_score' => 'nullable|integer|min:0|max:400',
            'application_id' => 'nullable|integer',
        ]);

        $query = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'));

        if ($request->application_id) {
            $query->where('id', $request->application_id);
        }

        $application = $query->first();

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

        DB::table('change_of_course')->where('id', $application->id)->update($updateData);

        return redirect()->route('change-of-course.index')->with('success', 'JAMB information updated successfully!');
    }

    /**
     * Student: Submit application (payment must be completed first)
     */
    public function store(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get student data
        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student record not found');
        }

        // Get RRRs already used by existing applications
        $usedRrrs = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'))
            ->whereNotNull('rrr')
            ->pluck('rrr')
            ->toArray();

        // Find an unused paid invoice
        $invoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->when(!empty($usedRrrs), function ($q) use ($usedRrrs) {
                return $q->whereNotIn('rrr', $usedRrrs);
            })
            ->first();

        if (!$invoice) {
            return redirect()->route('change-of-course.index')
                ->with('error', 'Please complete payment before submitting your application');
        }

        $request->validate([
            'new_faculty' => 'required',
            'new_department' => 'required',
            'new_program' => 'required',
            'reason_for_change' => 'required|min:20',
            'admission_type' => 'required|in:UTME,DE',
            'jamb_score' => 'required_if:admission_type,UTME|nullable|integer|min:0|max:400',
            'jamb_result_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Validate JAMB number - must be at least 7 digits
        if (empty($student->jamb_no) || strlen(preg_replace('/\D/', '', $student->jamb_no)) < 7) {
            return redirect()->back()->with('error', 'You must have a valid JAMB number before applying for change of course.');
        }

        // Check if same department AND same program (only block if both are the same)
        if ($student->department == $request->new_department && $student->program == $request->new_program) {
            return redirect()->back()->with('error', 'You cannot transfer to the same department and same program. Please select a different program or department.');
        }

        // Handle JAMB result file upload
        $jambResultFile = null;
        if ($request->hasFile('jamb_result_file')) {
            $file = $request->file('jamb_result_file');
            $filename = 'jamb_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/transfer_documents'), $filename);
            $jambResultFile = $filename;
        }

        // Generate unique application number
        $jambNo = $student->jamb_no ?? 'UNKNOWN';
        $appCount = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'))
            ->count();
        $applicationNo = 'UM/COC/' . $jambNo . ($appCount > 0 ? '/' . ($appCount + 1) : '');

        // Create application
        $applicationId = DB::table('change_of_course')->insertGetId([
            'username' => session('id_number'),
            'user_id' => session('id'),
            'application_no' => $applicationNo,
            'student_name' => $student->fullname,
            'admission_type' => $request->admission_type,
            'jamb_score' => $request->admission_type === 'UTME' ? $request->jamb_score : null,
            'jamb_result_file' => $jambResultFile,
            'current_faculty' => $student->faculty,
            'current_department' => $student->department,
            'current_program' => $student->program,
            'current_level' => $student->level,
            'new_faculty' => $request->new_faculty,
            'new_department' => $request->new_department,
            'new_program' => $request->new_program,
            'reason_for_change' => $request->reason_for_change,
            'transfer_type' => $invoice->amount >= SystemSettingsController::get('change_of_course_fee_voluntary', 100000) ? 'voluntary' : 'obligatory',
            'student_applied_date' => date('Y-m-d'),
            'rrr' => $invoice->rrr ?? null,
            'payment_status' => 'Paid',
            'payment_date' => date('Y-m-d'),
            'amount' => (float) ($invoice->amount ?? 0),
            'status' => $this->isMedicalOrDentalProgram($request->new_program) ? 'Awaiting Provost' : 'Awaiting New HOD',
            'session' => session('system_session'),
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);

        return redirect()->route('change-of-course.index')
            ->with('success', 'Application submitted successfully! Your application is now being processed.');
    }

    /**
     * Student: Payment page
     */
    public function payment($id)
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('change_of_course')
            ->where('id', $id)
            ->where('username', session('id_number'))
            ->first();

        if (!$application) {
            return redirect()->route('change-of-course.index')
                ->with('error', 'Application not found');
        }

        // Check if already paid
        if ($application->payment_status == 'Paid') {
            return redirect()->route('change-of-course.index')
                ->with('info', 'Payment already completed');
        }

        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        // Check for existing pending invoice with RRR
        $existingInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Pending')
            ->whereNotNull('rrr')
            ->first();

        $data = [
            'page' => 'change of course payment',
            'application' => $application,
            'student' => $student,
            'existingRrr' => $existingInvoice ? $existingInvoice->rrr : null,
        ];

        return view('main', $data);
    }

    /**
     * Initialize Remita payment for initial fee (before application)
     */
    public function initializeInitialPayment(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student record not found'], 404);
        }

        // Determine fee based on transfer type
        $transferType = $request->input('transfer_type', 'voluntary');
        $voluntaryFee = (float) SystemSettingsController::get('change_of_course_fee_voluntary', 100000);
        $obligatoryFee = (float) SystemSettingsController::get('change_of_course_fee_obligatory', 50000);
        $requestedAmount = ($transferType == 'obligatory') ? $obligatoryFee : $voluntaryFee;

        // Get RRRs already used by existing applications
        $usedRrrs = DB::table('change_of_course')
            ->where('username', session('id_number'))
            ->where('session', session('system_session'))
            ->whereNotNull('rrr')
            ->pluck('rrr')
            ->toArray();

        // Block if there's an unused paid invoice already
        $unusedPaidInvoice = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Paid')
            ->when(!empty($usedRrrs), function ($q) use ($usedRrrs) {
                return $q->whereNotIn('rrr', $usedRrrs);
            })
            ->first();

        if ($unusedPaidInvoice) {
            return response()->json(['error' => 'You already have an unused paid invoice. Please submit your application first before making another payment.'], 400);
        }

        // Check for existing pending invoices with RRR
        $existingPendingInvoices = DB::table('invoices')
            ->where('username', session('id'))
            ->where('description', 'CHANGE OF COURSE FEE')
            ->where('session', session('system_session'))
            ->where('status', 'Pending')
            ->whereNotNull('rrr')
            ->get();

        foreach ($existingPendingInvoices as $existingInvoice) {
            $existingType = ((float) $existingInvoice->amount >= $voluntaryFee) ? 'voluntary' : 'obligatory';

            if ($existingType === $transferType) {
                // Same type pending - return existing RRR (don't create duplicate)
                return response()->json([
                    'success' => true,
                    'rrr' => $existingInvoice->rrr,
                    'amount' => (float) $existingInvoice->amount,
                    'merchantId' => $this->merchantId,
                    'existing' => true
                ]);
            }
        }

        // If switching type, cancel ALL pending invoices for this description/session
        if ($existingPendingInvoices->isNotEmpty()) {
            DB::table('invoices')
                ->where('username', session('id'))
                ->where('description', 'CHANGE OF COURSE FEE')
                ->where('session', session('system_session'))
                ->where('status', 'Pending')
                ->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d')]);
        }
        if ($transferType == 'obligatory') {
            $amount = (float) SystemSettingsController::get('change_of_course_fee_obligatory', 50000);
        } else {
            $amount = (float) SystemSettingsController::get('change_of_course_fee_voluntary', 100000);
        }
        $orderId = time() . rand(1000, 9999);
        $description = 'CHANGE OF COURSE FEE';
        $serviceTypeId = env('REMITA_INTER_TRANSFER_KEY', '4430731');

        // Sanitize strings - remove non-ASCII chars (Remita Java backend rejects them)
        $payerName = trim(preg_replace('/[^\x20-\x7E]/', '', $student->fullname)) ?: 'STUDENT';
        $payerEmail = trim(preg_replace('/[^\x20-\x7E]/', '', $student->contact_email ?? 'student@unimaid.edu.ng'));
        $payerPhone = trim(preg_replace('/[^0-9+]/', '', $student->contact_phone ?? '08000000000'));

        // Generate RRR using Remita API
        $paymentData = [
            'serviceTypeId' => $serviceTypeId,
            'amount' => $amount,
            'orderId' => $orderId,
            'payerName' => $payerName,
            'payerEmail' => $payerEmail,
            'payerPhone' => $payerPhone,
            'description' => $description,
        ];

        $apiHash = hash('sha512', 
            $this->merchantId . $serviceTypeId . $orderId . $amount . $this->apiKey
        );

        $remitaBaseUrl = \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remitaBaseUrl . '/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $apiHash,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log debugging information
        \Log::info('Remita Payment Debug:', [
            'merchantId' => $this->merchantId,
            'serviceTypeId' => $serviceTypeId,
            'orderId' => $orderId,
            'amount' => $amount,
            'apiHash' => $apiHash,
            'httpCode' => $httpCode,
            'curlError' => $curlError,
            'response' => $response,
            'paymentData' => $paymentData
        ]);

        if ($httpCode == 200) {
            // Handle JSONP response format
            $cleanResponse = str_replace('jsonp (', '', $response);
            $cleanResponse = str_replace(')', '', $cleanResponse);
            $result = json_decode($cleanResponse, true);
            
            if (isset($result['RRR'])) {
                $rrr = $result['RRR'];

                // Create invoice record
                DB::table('invoices')->insert([
                    'username' => session('id'),
                    'name' => $student->fullname,
                    'phone' => $student->contact_phone,
                    'email' => $student->contact_email,
                    'description' => $description,
                    'amount' => $amount,
                    'rrr' => $rrr,
                    'orderId' => $orderId,
                    'serviceTypeId' => $serviceTypeId,
                    'faculty' => $student->faculty,
                    'department' => $student->department,
                    'program' => $student->program,
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
            } else {
                // Log the actual response for debugging
                \Log::error('Remita API Response Error:', [
                    'response' => $response,
                    'decoded' => $result,
                    'httpCode' => $httpCode
                ]);
                
                return response()->json([
                    'error' => 'Payment initialization failed: ' . ($result['message'] ?? 'Unknown error'),
                    'debug' => $result ?? []
                ], 500);
            }
        }

        return response()->json([
            'error' => 'Payment initialization failed. HTTP Code: ' . $httpCode . '. Error: ' . $curlError,
            'debug' => [
                'httpCode' => $httpCode,
                'curlError' => $curlError,
                'response' => $response
            ]
        ], 500);
    }

    /**
     * Initialize Remita payment (legacy - for applications that already exist)
     */
    public function initializePayment(Request $request, $id)
    {
        if (!session()->has('log') || session('accType') != 'Student') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')
            ->where('id', $id)
            ->where('username', session('id_number'))
            ->first();

        if (!$application || $application->payment_status == 'Paid') {
            return response()->json(['error' => 'Invalid application'], 400);
        }

        $student = DB::table('students')
            ->where('user_id', session('id'))
            ->first();

        // Prepare Remita payment data
        $amount = $application->amount;
        $orderId = time() . rand(1000, 9999);
        $description = 'CHANGE OF COURSE FEE';
        $serviceTypeId = env('REMITA_CHANGE_OF_COURSE_KEY', '4430731');

        // Sanitize strings - remove non-ASCII chars (Remita Java backend rejects them)
        $payerName = trim(preg_replace('/[^\x20-\x7E]/', '', $student->fullname)) ?: 'STUDENT';
        $payerEmail = trim(preg_replace('/[^\x20-\x7E]/', '', $student->contact_email ?? 'student@unimaid.edu.ng'));
        $payerPhone = trim(preg_replace('/[^0-9+]/', '', $student->contact_phone ?? '08000000000'));

        // Generate RRR using Remita API
        $paymentData = [
            'serviceTypeId' => $serviceTypeId,
            'amount' => $amount,
            'orderId' => $orderId,
            'payerName' => $payerName,
            'payerEmail' => $payerEmail,
            'payerPhone' => $payerPhone,
            'description' => $description,
        ];

        $apiHash = hash('sha512', 
            $this->merchantId . $serviceTypeId . $orderId . $amount . $this->apiKey
        );

        $remitaBaseUrl = \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remitaBaseUrl . '/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $apiHash,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            
            if (isset($result['RRR'])) {
                $rrr = $result['RRR'];
                
                // Save RRR to application
                DB::table('change_of_course')
                    ->where('id', $id)
                    ->update([
                        'rrr' => $rrr,
                        'updated_at' => date('Y-m-d'),
                    ]);

                // Create invoice record
                DB::table('invoices')->insert([
                    'username' => session('id'),
                    'name' => $student->fullname,
                    'phone' => $student->contact_phone,
                    'email' => $student->contact_email,
                    'description' => $description,
                    'amount' => $amount,
                    'rrr' => $rrr,
                    'orderId' => $orderId,
                    'serviceTypeId' => $serviceTypeId,
                    'faculty' => $student->faculty,
                    'department' => $student->department,
                    'program' => $student->program,
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
        }

        return response()->json([
            'error' => 'Payment initialization failed. Please try again.'
        ], 500);
    }

    /**
     * Verify payment callback
     */
    public function verifyPayment(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Session expired');
        }

        $rrr = $request->rrr;
        
        if (!$rrr) {
            return redirect()->route('change-of-course.index')
                ->with('error', 'Invalid payment reference');
        }

        // Verify payment with Remita
        $apiHash = hash('sha512', $rrr . $this->apiKey . $this->merchantId);
        $remitaBaseUrl = \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remitaBaseUrl . "/remita/exapp/api/v1/send/api/echannelsvc/$this->merchantId/$rrr/$apiHash/status.reg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $apiHash,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Debug logging
        \Log::info('Remita Verify Response', [
            'rrr' => $rrr,
            'url' => $remitaBaseUrl . "/remita/exapp/api/v1/send/api/echannelsvc/$this->merchantId/$rrr/$apiHash/status.reg",
            'httpCode' => $httpCode,
            'response' => $response,
            'curlError' => $curlError
        ]);

        $result = json_decode($response, true);

        // Check for successful payment (status 00 or 01 means paid)
        if (isset($result['status']) && in_array($result['status'], ['00', '01'])) {
            // Payment successful - update invoice
            DB::table('invoices')
                ->where('rrr', $rrr)
                ->update([
                    'status' => 'Paid',
                    'updated_at' => date('Y-m-d'),
                ]);

            // Check if this is for an existing application or initial payment
            $application = DB::table('change_of_course')
                ->where('rrr', $rrr)
                ->first();

            if ($application) {
                // Update existing application
                DB::table('change_of_course')
                    ->where('rrr', $rrr)
                    ->update([
                        'payment_status' => 'Paid',
                        'payment_date' => date('Y-m-d'),
                        'status' => $this->isMedicalOrDentalProgram($application->new_program) ? 'Awaiting Provost' : 'Awaiting New HOD',
                        'updated_at' => date('Y-m-d'),
                    ]);
            }

            return redirect()->route('change-of-course.index')
                ->with('success', 'Payment successful! You can now proceed to fill the application form.');
        }

        $errorMsg = 'Payment verification failed.';
        if (isset($result['message'])) {
            $errorMsg .= ' ' . $result['message'];
        } elseif ($curlError) {
            $errorMsg .= ' Connection error: ' . $curlError;
        }
        
        return redirect()->route('change-of-course.index')
            ->with('error', $errorMsg);
    }

    /**
     * Admin/Staff: View applications (for HOD, Dean, Registrar)
     */
    public function adminIndex(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Check if Admin or Staff with appropriate appointment
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

        $query = DB::table('change_of_course');

        // Filter based on role - Staff see only relevant applications
        if ($isHOD) {
            // HOD sees applications where their department is involved (current or new)
            $query->where(function($q) {
                $q->where('current_department', session('department'))
                  ->orWhere('new_department', session('department'));
            });
        } elseif ($isDean) {
            // Dean sees applications where their faculty is involved (current or new)
            $query->where(function($q) {
                $q->where('current_faculty', session('faculty'))
                  ->orWhere('new_faculty', session('faculty'));
            });
        } elseif ($isProvost) {
            // Provost sees applications where either faculty is a college (college = 1)
            $query->where(function($q) {
                $q->whereExists(function($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('faculty')
                        ->whereRaw('faculty.code COLLATE utf8mb4_unicode_ci = change_of_course.new_faculty COLLATE utf8mb4_unicode_ci')
                        ->where('faculty.college', 1);
                })
                ->orWhereExists(function($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('faculty')
                        ->whereRaw('faculty.code COLLATE utf8mb4_unicode_ci = change_of_course.current_faculty COLLATE utf8mb4_unicode_ci')
                        ->where('faculty.college', 1);
                });
            });
        }
        // Registrar, Admin, and VC see all applications

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by session
        if ($request->has('session') && $request->session != '') {
            $query->where('session', $request->session);
        }

        $applications = $query->orderBy('id', 'desc')->paginate(50);

        $data = [
            'page' => 'change of course admin',
            'applications' => $applications,
            'userRole' => $isAdmin ? 'Admin' : ($isCOC ? 'COC' : ($isHOD ? 'HOD' : ($isDean ? 'Dean' : ($isRegistrar ? 'Registrar' : 'VC')))),
        ];

        return view('main', $data);
    }

    /**
     * Admin/Staff: View single application details
     */
    public function show($id)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Check if Admin or Staff with appropriate appointment
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

        $application = DB::table('change_of_course')->where('id', $id)->first();

        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        // Check if staff can view this application
        if ($isHOD && $application->current_department != session('department') && $application->new_department != session('department')) {
            return redirect()->back()->with('error', 'You are not authorized to view this application');
        }
        if ($isDean && $application->current_faculty != session('faculty') && $application->new_faculty != session('faculty')) {
            return redirect()->back()->with('error', 'You are not authorized to view this application');
        }
        if ($isProvost) {
            // Provost can only view applications where either faculty is a college
            $currentFaculty = DB::table('faculty')->where('code', $application->current_faculty)->first();
            $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
            
            if ((!$currentFaculty || $currentFaculty->college != 1) && (!$newFaculty || $newFaculty->college != 1)) {
                return redirect()->back()->with('error', 'You are not authorized to view this application');
            }
        }

        // Get faculty/department/program titles
        $currentFacultyTitle = DB::table('faculty')->where('code', $application->current_faculty)->value('title');
        $currentDepartmentTitle = DB::table('department')->where('code', $application->current_department)->value('title');
        $currentProgramTitle = DB::table('program')->where('code', $application->current_program)->value('title');
        
        $newFacultyTitle = DB::table('faculty')->where('code', $application->new_faculty)->value('title');
        $newDepartmentTitle = DB::table('department')->where('code', $application->new_department)->value('title');
        $newProgramTitle = DB::table('program')->where('code', $application->new_program)->value('title');

        // Get student's results
        $results = DB::table('results')
            ->where('username', $application->username)
            ->orderBy('session', 'asc')
            ->orderBy('semester', 'asc')
            ->get();

        // Determine what actions this user can take
        $canApproveNewHOD = $this->canApproveAsHOD($application->new_department);
        $canApproveNewDean = $this->canApproveAsDean($application->new_faculty);
        $canApproveProvost = $this->canApproveAsProvost();
        $canApproveCurrentHOD = $this->canApproveAsHOD($application->current_department);
        $canApproveCurrentDean = $this->canApproveAsDean($application->current_faculty);
        $canApproveRegistrar = $this->canApproveAsRegistrar();

        $data = [
            'page' => 'change of course details',
            'application' => $application,
            'currentFacultyTitle' => $currentFacultyTitle,
            'currentDepartmentTitle' => $currentDepartmentTitle,
            'currentProgramTitle' => $currentProgramTitle,
            'newFacultyTitle' => $newFacultyTitle,
            'newDepartmentTitle' => $newDepartmentTitle,
            'newProgramTitle' => $newProgramTitle,
            'results' => $results,
            'userRole' => $isAdmin ? 'Admin' : ($isCOC ? 'COC' : ($isHOD ? 'HOD' : ($isDean ? 'Dean' : ($isProvost ? 'Provost' : ($isRegistrar ? 'Registrar' : 'VC'))))),
            'canApproveVC' => $this->canApproveAsVC(),
            'canApproveNewHOD' => $canApproveNewHOD,
            'canApproveNewDean' => $canApproveNewDean,
            'canApproveProvost' => $canApproveProvost,
            'canApproveCurrentHOD' => $canApproveCurrentHOD,
            'canApproveCurrentDean' => $canApproveCurrentDean,
            'canApproveRegistrar' => $canApproveRegistrar,
        ];

        return view('main', $data);
    }

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
        
        // Fallback to users table
        $user = DB::table('users')->where('username', session('username'))->first();
        return $user ? $user->name : session('username');
    }

    /**
     * Check if user can approve as HOD for a specific department
     */
    private function canApproveAsHOD($department)
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'HOD' && session('department') == $department) return true;
        return false;
    }

    /**
     * Check if user can approve as DEAN for a specific faculty
     */
    private function canApproveAsDean($faculty)
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'DEAN' && session('faculty') == $faculty) return true;
        return false;
    }

    /**
     * Check if user can approve as Provost (for colleges)
     */
    private function canApproveAsProvost()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'PROVOST') return true;
        return false;
    }

    /**
     * Check if user can approve as Registrar
     */
    private function canApproveAsRegistrar()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'REGISTRAR') return true;
        return false;
    }

    private function canApproveAsVC()
    {
        if (session('accType') == 'Admin') return true;
        if (session('accType') == 'Staff' && session('appointment') == 'VC') return true;
        return false;
    }

    /**
     * HOD: Approve/Reject from new department
     */
    public function newHodAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Check authorization: Admin or HOD of the new department
        if (!$this->canApproveAsHOD($application->new_department)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'recommended_level' => 'required_if:decision,Yes',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'new_hod_willing' => $request->decision,
            'new_hod_name' => $approverName,
            'new_hod_remarks' => $request->remarks,
            'new_hod_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ];

        if ($request->decision == 'Yes') {
            $updateData['new_hod_recommended_level'] = $request->recommended_level;
            $updateData['status'] = 'Awaiting New Dean';
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Your decision has been recorded successfully.'
        ]);
    }

    /**
     * Dean: Approve/Reject from new faculty
     */
    public function newDeanAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Check authorization: Admin or Dean of the new faculty
        if (!$this->canApproveAsDean($application->new_faculty)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'new_dean_recommendation' => $request->decision,
            'new_dean_name' => $approverName,
            'new_dean_remarks' => $request->remarks,
            'new_dean_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ];

        if ($request->decision == 'Yes') {
            // If current program is MBBS/DBS, skip Current HOD/Dean → go to Provost
            if ($this->isMedicalOrDentalProgram($application->current_program)) {
                $updateData['status'] = 'Awaiting Provost';
            } else {
                $updateData['status'] = 'Awaiting Current HOD';
            }
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Your recommendation has been recorded successfully.'
        ]);
    }

    /**
     * HOD: Release from current department
     */
    public function currentHodAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Check authorization: Admin or HOD of the current department
        if (!$this->canApproveAsHOD($application->current_department)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'reason' => 'required_if:decision,No',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'current_hod_willing' => $request->decision,
            'current_hod_name' => $approverName,
            'current_hod_reason' => $request->reason,
            'current_hod_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ];

        if ($request->decision == 'Yes') {
            $updateData['status'] = 'Awaiting Current Dean';
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Your decision has been recorded successfully.'
        ]);
    }

    /**
     * Dean: Approve release from current faculty
     */
    public function currentDeanAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Check authorization: Admin or Dean of the current faculty
        if (!$this->canApproveAsDean($application->current_faculty)) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Yes,No',
            'remarks' => 'nullable',
        ]);

        $approverName = $this->getApproverName();

        $updateData = [
            'current_dean_recommendation' => $request->decision,
            'current_dean_name' => $approverName,
            'current_dean_remarks' => $request->remarks,
            'current_dean_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ];

        if ($request->decision == 'Yes') {
            // If Provost already acted (new program was MBBS/DBS), skip Provost → Registrar
            if ($application->provost_recommendation !== 'Pending') {
                $updateData['status'] = 'Awaiting Registrar';
            } else {
                // Check if either faculty has college = 1, then route to Provost
                $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
                $currentFaculty = DB::table('faculty')->where('code', $application->current_faculty)->first();
                if (($newFaculty && $newFaculty->college == 1) || ($currentFaculty && $currentFaculty->college == 1)) {
                    $updateData['status'] = 'Awaiting Provost';
                } else {
                    $updateData['status'] = 'Awaiting Registrar';
                }
            }
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Your recommendation has been recorded successfully.'
        ]);
    }

    /**
     * Provost: Approve for college faculties (Registrar level)
     */
    public function provostAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Check authorization: Admin or Provost
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
            'provost_name' => $approverName,
            'provost_remarks' => $request->remarks,
            'provost_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ];

        if ($request->decision == 'Yes') {
            // Auto-fill MBBS/DBS HOD/Dean fields that Provost is handling
            if ($this->isMedicalOrDentalProgram($application->new_program)) {
                $updateData['new_hod_willing'] = 'Yes';
                $updateData['new_hod_name'] = $approverName . ' (Provost)';
                $updateData['new_hod_date'] = date('Y-m-d');
                if ($request->recommended_level) {
                    $updateData['new_hod_recommended_level'] = $request->recommended_level;
                }
                $updateData['new_dean_recommendation'] = 'Yes';
                $updateData['new_dean_name'] = $approverName . ' (Provost)';
                $updateData['new_dean_date'] = date('Y-m-d');
            }
            if ($this->isMedicalOrDentalProgram($application->current_program)) {
                $updateData['current_hod_willing'] = 'Yes';
                $updateData['current_hod_name'] = $approverName . ' (Provost)';
                $updateData['current_hod_date'] = date('Y-m-d');
                $updateData['current_dean_recommendation'] = 'Yes';
                $updateData['current_dean_name'] = $approverName . ' (Provost)';
                $updateData['current_dean_date'] = date('Y-m-d');
            }

            // Determine next status
            if ($this->isMedicalOrDentalProgram($application->new_program) && !$this->isMedicalOrDentalProgram($application->current_program)) {
                // Scenario 1: Normal → MBBS/DBS - Provost handled new side, current side is normal
                $updateData['status'] = 'Awaiting Current HOD';
            } else {
                // Scenario 2 & 3: Provost handled current side (or both) → go to Registrar
                $updateData['status'] = 'Awaiting Registrar';
            }
        } else {
            $updateData['status'] = 'Rejected';
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Your recommendation has been recorded successfully.'
        ]);
    }

    /**
     * Registrar: Final decision
     */
    public function registrarAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check authorization: Admin or Registrar
        if (!$this->canApproveAsRegistrar()) {
            return response()->json(['error' => 'You are not authorized to approve this application'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'remarks' => 'nullable',
            'recommended_level' => 'required_if:decision,Approved|in:100,200,300',
        ]);

        $application = DB::table('change_of_course')->where('id', $id)->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $approverName = $this->getApproverName();

        $updateData = [
            'registrar_decision' => $request->decision,
            'registrar_name' => $approverName,
            'registrar_remarks' => $request->remarks,
            'registrar_date' => date('Y-m-d'),
            'status' => $request->decision == 'Approved' ? 'Awaiting VC' : 'Rejected',
            'updated_at' => date('Y-m-d'),
        ];

        // If approved, update the recommended level (registrar can override HOD's recommendation)
        if ($request->decision == 'Approved' && $request->recommended_level) {
            $updateData['new_hod_recommended_level'] = $request->recommended_level;
        }

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Forwarded to VC for final approval.'
        ]);
    }

    /**
     * VC: Final decision
     */
    public function vcAction(Request $request, $id)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->canApproveAsVC()) {
            return response()->json(['error' => 'You are not authorized as VC'], 403);
        }
        $request->validate(['decision' => 'required|in:Approved,Rejected', 'remarks' => 'nullable']);

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $approverName = $this->getApproverName();
        DB::table('change_of_course')->where('id', $id)->update([
            'vc_decision' => $request->decision,
            'vc_name' => $approverName,
            'vc_remarks' => $request->remarks,
            'vc_date' => date('Y-m-d'),
            'status' => $request->decision,
            'updated_at' => date('Y-m-d'),
        ]);

        if ($request->decision == 'Approved') {
            // Get current student record for reference data
            $oldStudent = DB::table('students')->where('username', $application->username)->first();
            
            if ($oldStudent) {
                // Get new faculty/department info for ID format
                $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
                $newDepartment = DB::table('department')->where('code', $application->new_department)->first();
                
                // Generate new ID format
                $f = $newFaculty ? $newFaculty->no : '00';
                $d = $newDepartment ? $newDepartment->no : '00';
                $newIdFormat = '/' . str_pad($f, 2, '0', STR_PAD_LEFT) . '/' . str_pad($d, 2, '0', STR_PAD_LEFT) . '/';
                
                // Create NEW user record with application_no as username
                $newUserId = DB::table('users')->insertGetId([
                    'username' => $application->application_no,
                    'password' => \Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                    'accType' => 'Student',
                    'gender' => $oldStudent->gender == 'F' ? 'FEMALE' : 'MALE',
                    'name' => $oldStudent->fullname,
                    'status' => '0',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Create NEW student record
                DB::table('students')->insert([
                    'user_id' => $newUserId,
                    'jamb_no' => $oldStudent->jamb_no,
                    'last_name' => $oldStudent->last_name,
                    'first_name' => $oldStudent->first_name,
                    'other_name' => $oldStudent->other_name,
                    'fullname' => $oldStudent->fullname,
                    'program' => $application->new_program,
                    'department' => $application->new_department,
                    'faculty' => $application->new_faculty,
                    'id_format' => $newIdFormat,
                    'gender' => $oldStudent->gender,
                    'session_of_entry' => $application->session,
                    'level_of_entry' => $application->new_hod_recommended_level ?? $oldStudent->level,
                    'level' => $application->new_hod_recommended_level ?? $oldStudent->level,
                    'mode_of_entry' => $oldStudent->mode_of_entry,
                    'state_origin' => $oldStudent->state_origin,
                    'lga_origin' => $oldStudent->lga_origin,
                    'contact_phone' => $oldStudent->contact_phone,
                    'contact_email' => $oldStudent->contact_email,
                    'status' => '1',
                    'school_fee' => '1',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Log the change
                \Log::info('Change of Course Approved - New Student Record Created', [
                    'application_id' => $id,
                    'application_no' => $application->application_no,
                    'old_username' => $application->username,
                    'new_username' => $application->application_no,
                    'new_user_id' => $newUserId,
                    'old_program' => $oldStudent->program,
                    'new_program' => $application->new_program,
                    'approved_by' => session('id'),
                    'approved_date' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Final decision has been recorded and student record updated.'
        ]);
    }

    /**
     * Admin: Bulk edit application
     */
    public function bulkEdit($id)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        // Get all faculties, departments, and programs for dropdowns
        $faculties = DB::table('faculty')->orderBy('title')->get();
        $departments = DB::table('department')->orderBy('title')->get();
        $programs = DB::table('program')->orderBy('title')->get();

        // Get current titles
        $currentFacultyTitle = DB::table('faculty')->where('code', $application->current_faculty)->value('title');
        $currentDepartmentTitle = DB::table('department')->where('code', $application->current_department)->value('title');
        $currentProgramTitle = DB::table('program')->where('code', $application->current_program)->value('title');
        $newFacultyTitle = DB::table('faculty')->where('code', $application->new_faculty)->value('title');
        $newDepartmentTitle = DB::table('department')->where('code', $application->new_department)->value('title');
        $newProgramTitle = DB::table('program')->where('code', $application->new_program)->value('title');

        $data = [
            'page' => 'change of course bulk-edit',
            'application' => $application,
            'faculties' => $faculties,
            'departments' => $departments,
            'programs' => $programs,
            'currentFacultyTitle' => $currentFacultyTitle,
            'currentDepartmentTitle' => $currentDepartmentTitle,
            'currentProgramTitle' => $currentProgramTitle,
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

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        $request->validate([
            'student_name' => 'required|string|max:255',
            'new_faculty' => 'required|string',
            'new_department' => 'required|string',
            'new_program' => 'required|string',
            'current_faculty' => 'required|string',
            'current_department' => 'required|string',
            'current_program' => 'required|string',
            'status' => 'required|string',
            'payment_status' => 'required|string|in:Paid,Pending',
            'admission_type' => 'nullable|string',
            'jamb_score' => 'nullable|integer|min:0|max:400',
            'reason_for_change' => 'required|string',
        ]);

        $updateData = [
            'student_name' => $request->student_name,
            'new_faculty' => $request->new_faculty,
            'new_department' => $request->new_department,
            'new_program' => $request->new_program,
            'current_faculty' => $request->current_faculty,
            'current_department' => $request->current_department,
            'current_program' => $request->current_program,
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'admission_type' => $request->admission_type,
            'jamb_score' => $request->jamb_score,
            'reason_for_change' => $request->reason_for_change,
            'updated_at' => now(),
        ];

        // Update officer actions if provided
        if ($request->has('new_hod_willing')) {
            $updateData['new_hod_willing'] = $request->new_hod_willing ?: 'Pending';
            $updateData['new_hod_recommended_level'] = $request->new_hod_recommended_level;
            $updateData['new_hod_remarks'] = $request->new_hod_remarks;
            $updateData['new_hod_name'] = $request->new_hod_name ?: 'Admin Override';
            $updateData['new_hod_date'] = $request->new_hod_date ?: date('Y-m-d');
        }

        if ($request->has('new_dean_recommendation')) {
            $updateData['new_dean_recommendation'] = $request->new_dean_recommendation ?: 'Pending';
            $updateData['new_dean_remarks'] = $request->new_dean_remarks;
            $updateData['new_dean_name'] = $request->new_dean_name ?: 'Admin Override';
            $updateData['new_dean_date'] = $request->new_dean_date ?: date('Y-m-d');
        }

        if ($request->has('current_hod_willing')) {
            $updateData['current_hod_willing'] = $request->current_hod_willing ?: 'Pending';
            $updateData['current_hod_reason'] = $request->current_hod_reason;
            $updateData['current_hod_name'] = $request->current_hod_name ?: 'Admin Override';
            $updateData['current_hod_date'] = $request->current_hod_date ?: date('Y-m-d');
        }

        if ($request->has('current_dean_recommendation')) {
            $updateData['current_dean_recommendation'] = $request->current_dean_recommendation ?: 'Pending';
            $updateData['current_dean_remarks'] = $request->current_dean_remarks;
            $updateData['current_dean_name'] = $request->current_dean_name ?: 'Admin Override';
            $updateData['current_dean_date'] = $request->current_dean_date ?: date('Y-m-d');
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

        DB::table('change_of_course')->where('id', $id)->update($updateData);

        // Log the admin action
        \Log::info('Change of Course Bulk Updated by Admin', [
            'application_id' => $id,
            'application_no' => $application->application_no,
            'admin_id' => session('id'),
            'updated_fields' => array_keys($updateData),
            'updated_date' => date('Y-m-d H:i:s')
        ]);

        return response()->json(['success' => true, 'message' => 'Application updated successfully']);
    }

    /**
     * Admin: Resubmit application (reset all approval levels)
     */
    public function resubmitApplication(Request $request, $id)
    {
        // Log 1: Method entry
        \Log::info('RESUBMIT: Method started', ['id' => $id, 'user' => session('username')]);
        
        if (!session()->has('log') || session('accType') != 'Admin') {
            \Log::error('RESUBMIT: Unauthorized access attempt', ['session' => session()->all()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        \Log::info('RESUBMIT: Authorization passed');

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            \Log::error('RESUBMIT: Application not found', ['id' => $id]);
            return response()->json(['error' => 'Application not found'], 404);
        }
        
        \Log::info('RESUBMIT: Application found', ['app_no' => $application->application_no]);

        try {
            // Delete the existing application entirely so student can create a fresh one
            // This is the cleanest approach - it's like they never submitted
            DB::table('change_of_course')->where('id', $id)->delete();
            \Log::info('RESUBMIT: Application deleted successfully', ['application_id' => $id]);
            
        } catch (\Exception $e) {
            \Log::error('RESUBMIT: Failed to delete application', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to reset application: ' . $e->getMessage()], 500);
        }

        try {
            // Log the action for audit purposes
            \Log::info('RESUBMIT: Attempting to log activity');
            $logData = [
                'user_id' => session('id'),
                'username' => session('username'),
                'action' => 'Resubmitted Change of Course Application',
                'description' => "Application {$application->application_no} was resubmitted and all approval levels were reset",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ];
            
            \Log::info('RESUBMIT: Log data prepared', ['log_data' => $logData]);
            
            // Try to insert into activity_logs, but don't fail if table doesn't exist
            try {
                DB::table('activity_logs')->insert($logData);
                \Log::info('RESUBMIT: Activity logged successfully');
            } catch (\Exception $logEx) {
                \Log::warning('RESUBMIT: Could not log to activity_logs table', ['error' => $logEx->getMessage()]);
                // Continue even if logging fails
            }
        } catch (\Exception $e) {
            \Log::warning('RESUBMIT: Logging section failed', ['error' => $e->getMessage()]);
            // Continue even if logging fails
        }

        \Log::info('RESUBMIT: Method completed successfully');
        
        return response()->json([
            'success' => true, 
            'message' => 'Application has been successfully reset! The student can now submit a fresh application.'
        ]);
    }

    /**
     * Check if a program is MBBS or DBS (no HOD/Dean required)
     */
    private function isMedicalOrDentalProgram($programCode)
    {
        $medicalPrograms = ['MBBS', 'DBS'];
        return in_array($programCode, $medicalPrograms);
    }

    /**
     * Check if either current or new program is medical/dental (requires special workflow)
     */
    private function requiresSpecialWorkflow($application)
    {
        return $this->isMedicalOrDentalProgram($application->new_program) || 
               $this->isMedicalOrDentalProgram($application->current_program);
    }

    /**
     * Admin: Generate admission letter PDF for approved COC application
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

        $application = DB::table('change_of_course')->where('id', $id)->first();
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found');
        }

        if ($application->status != 'Approved') {
            return redirect()->back()->with('error', 'Admission letter is only available for approved applications.');
        }

        // Track the download
        $this->trackDownload($id, 'single');

        $currentFacultyTitle = DB::table('faculty')->where('code', $application->current_faculty)->value('title');
        $currentDepartmentTitle = DB::table('department')->where('code', $application->current_department)->value('title');
        $currentProgramTitle = DB::table('program')->where('code', $application->current_program)->value('title');
        $jambNo = DB::table('students')->where('username', $application->username)->value('jamb_no');

        $options = new Options();
        $options->set('defaultFont', 'Times');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('chroot', public_path());
        $options->set('tempDir', sys_get_temp_dir());

        $dompdf = new Dompdf($options);

        $html = view('admission-letter-coc-pdf', compact('application', 'currentFacultyTitle', 'currentDepartmentTitle', 'currentProgramTitle', 'jambNo'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $application->application_no . '_ADMISSION_LETTER_' . date('Y-m-d') . '.pdf';
        $filename = str_replace(['/', ' '], '_', $filename);

        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Admin: Download management page (admin only)
     */
    public function downloadManagement(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get base query - ONLY approved applications for download management
        $query = DB::table('change_of_course')->where('status', 'Approved');
        
        // Apply filters
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Order and paginate
        $applications = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get status counts (all applications for context)
        $statusCounts = [
            'processing' => DB::table('change_of_course')->whereNotIn('status',['Payment Pending','Approved','Rejected'])->count(),
            'approved' => DB::table('change_of_course')->where('status','Approved')->count(),
            'rejected' => DB::table('change_of_course')->where('status','Rejected')->count(),
        ];
        
        // Get download statistics for approved applications only
        $approvedIds = DB::table('change_of_course')->where('status','Approved')->pluck('id');
        $downloadStats = [];
        if($approvedIds->isNotEmpty()) {
            $downloads = DB::table('change_of_course_downloads')
                ->whereIn('application_id', $approvedIds)
                ->selectRaw('application_id, COUNT(*) as download_count, MAX(downloaded_at) as last_downloaded')
                ->groupBy('application_id')
                ->get()
                ->keyBy('application_id');
            $downloadStats = $downloads->toArray();
        }
        
        // Calculate download status counts
        $downloadStatusCounts = [
            'awaiting_download' => 0,
            'downloaded' => 0,
            'redownloaded' => 0
        ];
        
        // Count from all approved applications (not just paginated)
        $allApprovedApplications = DB::table('change_of_course')->where('status', 'Approved')->get();
        foreach($allApprovedApplications as $app) {
            if(isset($downloadStats[$app->id])) {
                if($downloadStats[$app->id]->download_count == 1) {
                    $downloadStatusCounts['downloaded']++;
                } else {
                    $downloadStatusCounts['redownloaded']++;
                }
            } else {
                $downloadStatusCounts['awaiting_download']++;
            }
        }
        
        $data = [
            'page' => 'change of course downloads',
            'applications' => $applications, 
            'statusCounts' => $statusCounts, 
            'downloadStats' => $downloadStats, 
            'downloadStatusCounts' => $downloadStatusCounts
        ];
        
        return view('main', $data);
    }

    /**
     * Admin: Bulk download admission letters for approved applications
     */
    public function bulkDownload(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isAdmin = session('accType') == 'Admin';
        $isStaff = session('accType') == 'Staff';
        if (!$isAdmin && !$isStaff) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $downloadOption = $request->input('download_option', 'separate');
        $namingOption = $request->input('naming_option', 'appno');
        $markAsDownloaded = $request->input('mark_as_downloaded', true);
        $isRedownload = $request->input('is_redownload', false);
        $selectedIds = $request->input('application_ids', []);

        // Get applications to download
        $query = DB::table('change_of_course')->where('status', 'Approved');
        
        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }
        
        $approvedApplications = $query->orderBy('application_no', 'ASC')->get();

        if ($approvedApplications->isEmpty()) {
            return response()->json(['error' => 'No approved applications found'], 404);
        }

        try {
            if ($downloadOption === 'combined') {
                // Create combined PDF
                return $this->createCombinedPDF($approvedApplications, $namingOption, $markAsDownloaded);
            } else {
                // Create separate PDFs
                return $this->createSeparatePDFs($approvedApplications, $namingOption, $markAsDownloaded);
            }
        } catch (\Exception $e) {
            \Log::error('Bulk download error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDFs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create separate PDF files for bulk download
     */
    private function createSeparatePDFs($applications, $namingOption, $markAsDownloaded)
    {
        $files = [];
        $tempDir = storage_path('app/temp_downloads');
        
        // Ensure temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($applications as $application) {
            try {
                $filename = $this->generateSinglePDF($application, $tempDir, $namingOption);
                
                if ($filename) {
                    $files[] = [
                        'url' => url('storage/app/temp_downloads/' . $filename),
                        'filename' => $filename,
                        'application_id' => $application->id
                    ];

                    // Track download
                    if ($markAsDownloaded) {
                        $this->trackDownload($application->id, $isRedownload ? 'bulk_redownload' : 'bulk');
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error generating PDF for application {$application->id}: " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            'success' => true,
            'files' => $files,
            'message' => 'Generated ' . count($files) . ' PDF files'
        ]);
    }

    /**
     * Create combined PDF file for bulk download
     */
    private function createCombinedPDF($applications, $namingOption, $markAsDownloaded)
    {
        $options = new Options();
        $options->set('defaultFont', 'Times');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        
        // Generate combined HTML
        $html = '<div style="page-break-after: always;">';
        $html .= '<h2 style="text-align: center; margin-bottom: 30px;">BULK ADMISSION LETTERS - CHANGE OF COURSE</h2>';
        $html .= '<p style="text-align: center; margin-bottom: 30px;">Generated on: ' . date('d M Y H:i:s') . '</p>';
        $html .= '<p style="text-align: center; margin-bottom: 30px;">Total Applications: ' . count($applications) . '</p>';
        $html .= '</div>';

        foreach ($applications as $index => $application) {
            try {
                $currentFacultyTitle = DB::table('faculty')->where('code', $application->current_faculty)->value('title');
                $currentDepartmentTitle = DB::table('department')->where('code', $application->current_department)->value('title');
                $currentProgramTitle = DB::table('program')->where('code', $application->current_program)->value('title');
                $jambNo = DB::table('students')->where('username', $application->username)->value('jamb_no');

                $html .= view('admission-letter-coc-pdf', compact('application', 'currentFacultyTitle', 'currentDepartmentTitle', 'currentProgramTitle', 'jambNo'))->render();
                
                // Add page break except for last page
                if ($index < count($applications) - 1) {
                    $html .= '<div style="page-break-after: always;"></div>';
                }

                // Track download
                if ($markAsDownloaded) {
                    $this->trackDownload($application->id, $isRedownload ? 'bulk_redownload' : 'bulk');
                }
            } catch (\Exception $e) {
                \Log::error("Error adding application {$application->id} to combined PDF: " . $e->getMessage());
                continue;
            }
        }

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'BULK_ADMISSION_LETTERS_COC_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Save to temporary file
        $tempPath = storage_path('app/temp_downloads/' . $filename);
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        file_put_contents($tempPath, $dompdf->output());

        return response()->json([
            'success' => true,
            'download_url' => url('storage/app/temp_downloads/' . $filename),
            'filename' => $filename,
            'message' => 'Combined PDF generated successfully'
        ]);
    }

    /**
     * Generate single PDF file
     */
    private function generateSinglePDF($application, $tempDir, $namingOption)
    {
        $currentFacultyTitle = DB::table('faculty')->where('code', $application->current_faculty)->value('title');
        $currentDepartmentTitle = DB::table('department')->where('code', $application->current_department)->value('title');
        $currentProgramTitle = DB::table('program')->where('code', $application->current_program)->value('title');
        $jambNo = DB::table('students')->where('username', $application->username)->value('jamb_no');

        $options = new Options();
        $options->set('defaultFont', 'Times');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $html = view('admission-letter-coc-pdf', compact('application', 'currentFacultyTitle', 'currentDepartmentTitle', 'currentProgramTitle', 'jambNo'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate filename based on naming option
        if ($namingOption === 'student') {
            $studentName = preg_replace('/[^a-zA-Z0-9]/', '_', $application->student_name);
            $filename = 'Admission_Letter_' . $studentName . '_' . $application->application_no . '.pdf';
        } else {
            $filename = 'Admission_Letter_' . $application->application_no . '.pdf';
        }

        // Save to temporary file
        $tempPath = $tempDir . '/' . $filename;
        file_put_contents($tempPath, $dompdf->output());

        return $filename;
    }

    /**
     * Track download activity
     */
    private function trackDownload($applicationId, $downloadType)
    {
        try {
            $application = DB::table('change_of_course')->where('id', $applicationId)->first();
            if (!$application) {
                return;
            }

            DB::table('change_of_course_downloads')->insert([
                'application_id' => $applicationId,
                'application_no' => $application->application_no,
                'downloaded_by' => session('id'),
                'downloaded_by_name' => session('name') ?? session('fullname') ?? 'Unknown',
                'download_type' => $downloadType,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'downloaded_at' => now(),
                'session_id' => session()->getId(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error tracking download: ' . $e->getMessage());
        }
    }

    /**
     * Get selected applications details for bulk download modal
     */
    public function getSelectedApplications(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $applicationIds = $request->input('application_ids', []);
        
        if (empty($applicationIds)) {
            return response()->json(['error' => 'No application IDs provided'], 400);
        }

        // Get applications with download statistics
        $applications = DB::table('change_of_course')
            ->whereIn('id', $applicationIds)
            ->get();

        // Get download counts for these applications
        $downloadStats = [];
        if ($applications->isNotEmpty()) {
            $downloads = DB::table('change_of_course_downloads')
                ->whereIn('application_id', $applicationIds)
                ->selectRaw('application_id, COUNT(*) as download_count')
                ->groupBy('application_id')
                ->get()
                ->keyBy('application_id');
            $downloadStats = $downloads->toArray();
        }

        // Format response
        $formattedApplications = $applications->map(function($app) use ($downloadStats) {
            return [
                'id' => $app->id,
                'application_no' => $app->application_no,
                'student_name' => $app->student_name,
                'status' => $app->status,
                'download_count' => isset($downloadStats[$app->id]) ? $downloadStats[$app->id]->download_count : 0
            ];
        });

        return response()->json([
            'success' => true,
            'applications' => $formattedApplications
        ]);
    }

    /**
     * Track single download (AJAX endpoint)
     */
    public function trackSingleDownload(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $applicationId = $request->input('application_id');
        $downloadType = $request->input('download_type', 'single');

        if (!$applicationId) {
            return response()->json(['error' => 'Application ID required'], 400);
        }

        $this->trackDownload($applicationId, $downloadType);

        return response()->json(['success' => true]);
    }

    /**
     * Export selected applications data
     */
    public function exportSelectedApplications(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $applicationIds = $request->input('ids', []);
        
        if (empty($applicationIds)) {
            return redirect()->back()->with('error', 'No applications selected for export');
        }

        // Get applications with download statistics
        $applications = DB::table('change_of_course')
            ->whereIn('id', $applicationIds)
            ->orderBy('application_no', 'ASC')
            ->get();

        // Get download statistics
        $downloadStats = [];
        if ($applications->isNotEmpty()) {
            $downloads = DB::table('change_of_course_downloads')
                ->whereIn('application_id', $applicationIds)
                ->selectRaw('application_id, COUNT(*) as download_count, MAX(downloaded_at) as last_downloaded')
                ->groupBy('application_id')
                ->get()
                ->keyBy('application_id');
            $downloadStats = $downloads->toArray();
        }

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['Application No', 'Student Name', 'Username', 'From Department', 'To Department', 'Status', 'Download Count', 'Last Downloaded', 'Application Date'];

        foreach ($applications as $app) {
            $fromDept = DB::table('department')->where('code', $app->current_department)->value('title');
            $toDept = DB::table('department')->where('code', $app->new_department)->value('title');
            
            $downloadCount = isset($downloadStats[$app->id]) ? $downloadStats[$app->id]->download_count : 0;
            $lastDownloaded = isset($downloadStats[$app->id]) ? $downloadStats[$app->id]->last_downloaded : null;

            $csvData[] = [
                $app->application_no,
                $app->student_name,
                $app->username,
                $fromDept,
                $toDept,
                $app->status,
                $downloadCount,
                $lastDownloaded ? date('Y-m-d H:i:s', strtotime($lastDownloaded)) : 'Never',
                date('Y-m-d', strtotime($app->created_at))
            ];
        }

        // Generate CSV
        $filename = 'change_of_course_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
