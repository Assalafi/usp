<?php

namespace App\Http\Controllers;

use App\Imports\PaymentImport;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use FPDF;

class InvoicesController extends Controller
{
    //
    //

    /**
     * Generate a PDF receipt for a paid invoice
     *
     * @param string $rrr The RRR (Remita Retrieval Reference) number
     * @return \Illuminate\Http\Response
     */
    public function printReceipt($rrr)
    {
        // Find the invoice by RRR
        $invoice = Invoice::where('rrr', $rrr)->first();

        // Check if invoice exists and is paid
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found');
        }

        if ($invoice->status !== 'Paid') {
            return redirect()->back()->with('error', 'Receipt is only available for paid invoices');
        }

        // Get student information - try multiple lookup strategies
        $student = Student::where('user_id', $invoice->username)->first();
        if (!$student) {
            $student = Student::where('username', $invoice->username)->first();
        }
        $studentUsername = null;

        if ($student) {
            $invoice->name = $student->fullname;
            $invoice->program = $student->program;
            $studentUsername = $student->username;
        } else {
            // Try to extract clean name (remove appended ID like "NAME (ID)")
            $rawName = $invoice->name ?? 'N/A';
            if (preg_match('/^(.+?)\s*\((.+?)\)$/', $rawName, $m)) {
                $invoice->name = trim($m[1]);
                $studentUsername = trim($m[2]);
            } else {
                $invoice->name = $rawName;
                $studentUsername = 'N/A';
            }
            $invoice->program = $invoice->program ?? 'N/A';
        }

        // Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        // Separate the Naira and Kobo
        $naira = floor($invoice->amount);
        $kobo = round(($invoice->amount - $naira) * 100);

        // Convert Naira to words
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $nairaInWords = $formatter->format($naira);

        $amountInWords = ucfirst($nairaInWords) . ' Naira';

        // Add Kobo if it's not zero
        if ($kobo > 0) {
            $koboInWords = $formatter->format($kobo);
            $amountInWords .= ' and ' . ucfirst($koboInWords) . ' Kobo';
        }

        $dompdf = new Dompdf($options);
        $html = view('Student.receipt-pdf', ['invoice' => $invoice, 'amountInWords' => $amountInWords, 'studentUsername' => $studentUsername])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return the PDF as a download
        // Return the PDF to be viewed in the browser
        return $dompdf->stream('receipt-' . $rrr . '.pdf', [
            'Attachment' => false
        ]);
    }

    /**
     * Admin: Receipts page with filters
     */
    public function adminReceipts(Request $request)
    {
        if (!session()->has('log') || !in_array(session('accType'), ['Admin', 'Staff'])) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $data['faculty'] = DB::table('faculty')->where('status', '1')->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = 'receipts';
        $data['title'] = 'RECEIPTS';

        $query = DB::table('invoices');

        $hasFilter = false;

        if ($request->filled('faculty')) {
            $query->where('invoices.faculty', $request->faculty);
            $hasFilter = true;
        }
        if ($request->filled('department')) {
            $query->where('invoices.department', $request->department);
            $hasFilter = true;
        }
        if ($request->filled('program')) {
            $query->where('invoices.program', $request->program);
            $hasFilter = true;
        }
        if ($request->filled('status')) {
            $query->where('invoices.status', $request->status);
            $hasFilter = true;
        }
        if ($request->filled('fees_type')) {
            if ($request->fees_type === 'nelfund') {
                $query->where('invoices.fees_type', 'nelfund');
            } else {
                $query->where(function ($q) {
                    $q->where('invoices.fees_type', '!=', 'nelfund')
                      ->orWhereNull('invoices.fees_type');
                });
            }
            $hasFilter = true;
        }
        if ($request->filled('session')) {
            $query->where('invoices.session', $request->session);
            $hasFilter = true;
        }
        if ($request->filled('description')) {
            $query->where('invoices.description', $request->description);
            $hasFilter = true;
        }

        if ($hasFilter) {
            $data['data'] = $query->orderBy('invoices.updated_at', 'DESC')->get();
        } else {
            $data['data'] = collect();
        }

        return view('main', $data);
    }

    /**
     * Admin: Download all filtered paid receipts as ZIP of individual PDFs
     */
    public function downloadAllReceipts(Request $request)
    {
        if (!session()->has('log') || !in_array(session('accType'), ['Admin', 'Staff'])) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        set_time_limit(600);
        ini_set('memory_limit', '256M');

        $query = DB::table('invoices')->where('status', 'Paid');

        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }
        if ($request->filled('fees_type')) {
            if ($request->fees_type === 'nelfund') {
                $query->where('fees_type', 'nelfund');
            } else {
                $query->where(function ($q) {
                    $q->where('fees_type', '!=', 'nelfund')
                      ->orWhereNull('fees_type');
                });
            }
        }
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }
        if ($request->filled('description')) {
            $query->where('description', $request->description);
        }

        $invoices = $query->orderBy('updated_at', 'DESC')->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No paid invoices found for the selected filters.');
        }

        // Process in chunks to avoid memory issues
        $chunkSize = 50;
        $invoiceChunks = $invoices->chunk($chunkSize);

        $zipFilename = 'receipts-bulk-' . date('Y-m-d-His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFilename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return redirect()->back()->with('error', 'Failed to create ZIP file');
        }

        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

        foreach ($invoiceChunks as $chunk) {
            // Batch-load students for this chunk
            $userIds = $chunk->pluck('username')->unique()->filter()->values();
            $studentsByUserId = Student::whereIn('user_id', $userIds)->get()->keyBy('user_id');
            $studentsByUsername = Student::whereIn('username', $userIds)->get()->keyBy('username');

            // Batch-load programs for this chunk
            $programCodes = $chunk->pluck('program')->unique()->filter()->values();
            $programTitles = DB::table('program')->whereIn('code', $programCodes)->pluck('title', 'code');

            foreach ($chunk as $invoice) {
                $student = $studentsByUserId->get($invoice->username)
                        ?? $studentsByUsername->get($invoice->username);

                $receipt = new \stdClass();
                $receipt->id = $invoice->id;
                $receipt->rrr = $invoice->rrr;
                $receipt->amount = $invoice->amount;
                $receipt->description = $invoice->description;
                $receipt->session = $invoice->session;
                $receipt->fees_type = $invoice->fees_type ?? null;
                $receipt->updated_at = $invoice->updated_at;

                if ($student) {
                    $receipt->name = $student->fullname;
                    $receipt->studentUsername = $student->username;
                    $receipt->programTitle = $programTitles->get($student->program, 'N/A');
                } else {
                    $rawName = $invoice->name ?? 'N/A';
                    if (preg_match('/^(.+?)\s*\((.+?)\)$/', $rawName, $m)) {
                        $receipt->name = trim($m[1]);
                        $receipt->studentUsername = trim($m[2]);
                    } else {
                        $receipt->name = $rawName;
                        $receipt->studentUsername = 'N/A';
                    }
                    $receipt->programTitle = $programTitles->get($invoice->program, $invoice->program ?? 'N/A');
                }

                // Amount in words
                $naira = floor($invoice->amount);
                $kobo = round(($invoice->amount - $naira) * 100);
                $nairaInWords = $formatter->format($naira);
                $receipt->amountInWords = ucfirst($nairaInWords) . ' Naira';
                if ($kobo > 0) {
                    $koboInWords = $formatter->format($kobo);
                    $receipt->amountInWords .= ' and ' . ucfirst($koboInWords) . ' Kobo';
                }

                // Generate single receipt PDF
                $html = view('Admin.receipts-bulk-pdf', ['receipts' => [$receipt]])->render();

                $options = new Options();
                $options->set('isRemoteEnabled', true);
                $options->set('chroot', public_path());
                $options->set('defaultFont', 'Helvetica');

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $pdfContent = $dompdf->output();
                $pdfFilename = 'receipt_' . $receipt->rrr . '.pdf';
                $zip->addFromString($pdfFilename, $pdfContent);

                // Free memory
                unset($dompdf);
                unset($html);
                unset($pdfContent);
                gc_collect_cycles();
            }

            // Free chunk memory
            unset($studentsByUserId);
            unset($studentsByUsername);
            unset($programTitles);
            gc_collect_cycles();
        }

        $zip->close();

        // Stream ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment;filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: max-age=0');

        readfile($zipPath);

        // Delete temp file
        unlink($zipPath);

        exit;
    }

    /**
     * Admin: Export paid students to Excel
     */
    public function exportPaidStudents(Request $request)
    {
        if (!session()->has('log') || !in_array(session('accType'), ['Admin', 'Staff'])) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $session = $request->input('session');
        if (!$session) {
            return redirect()->back()->with('error', 'Session is required');
        }

        $feesType = $request->input('fees_type', '');

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $export = new \App\Exports\PaidStudentsExport($session, $feesType);
        $filename = 'paid_students_' . str_replace('/', '-', $session) . '_' . ($feesType ? $feesType : 'all') . '_' . date('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace('create ', '', $contents);
        $contents = str_replace('upload ', '', $contents);
        $contents = str_replace('download ', '', $contents);
        $contents = str_replace('update ', '', $contents);
        $contents = str_replace('delete ', '', $contents);
        $this->page = $contents;
        $this->table = str_replace(' ', '_', $this->page);
        $this->title = strtoupper($this->page);
        $this->merchantId = env('REMITA_MERCHANT_ID');
        $this->apiKey = env('REMITA_API_KEY');
    }

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table($this->table)->get();
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function initialize(Request $req)
    {
        $x = 0;
        $data = DB::table('students')->select('fullname', 'contact_phone', 'contact_email', 'faculty', 'department', 'program', 'level', 'level_of_entry')->where('user_id', session('id'))->get();
        foreach ($data as $row) {
            $name = $datas['name'] = $row->fullname;
            $phone = $datas['phone'] = $row->contact_phone;
            $email = $datas['email'] = strtolower($req->email);
            $datas['faculty'] = $row->faculty;
            $datas['department'] = $row->department;
            $datas['program'] = $row->program;
            $datas['level'] = $row->level;
            $datas['created_at'] = date('Y-m-d');
            $datas['updated_at'] = date('Y-m-d');
            $x++;
        }

        $datas['session'] = \App\Http\Controllers\SystemSettingsController::getSchoolFeesSession();
        $x++;
        if ($x != 2) {
            return redirect()->back()->with('error', 'Your Personal Record Not Found in Current Uploaded Data, Report to Student Affairs.');
        }

        if ($req->page == 'hostel') {
            $datas['session'] = \App\Http\Controllers\SystemSettingsController::getHostelFeesSession();
            $name = $datas['name'] = $name . ' (' . session('id_number') . ')';
            $phone = $datas['phone'] = $req->phone;
            $email = $datas['email'] = strtolower($req->email);

            $description = env('REMITA_HOSTEL_DESCRIPTION');
            $serviceTypeId = env('REMITA_HOSTEL_KEY');
            $amount = DB::table('hostel')->where('occupant', session('id_number'))->select('amount')->value('amount');
            $p_level = DB::table('program')->where('code', $row->program)->value('duration');
            $p_level = $p_level * 100;
            // return $p_level.$row -> level;
            if ($row->level >= $p_level) {
                return redirect()->back()->with('error', 'Graduated or Spilling Students are not ELIGIBLE for Bed Space!!!');
            }
            if ($amount > 0) {
            } else {
                return redirect()->back()->with('error', 'No Bed Space Reservation Assign to Your ID NO.');
            }
        } else if ($req->page == 'school-fees') {
            $amounts = DB::table('school_fees')->where(['program' => $row->program, 'level' => $row->level_of_entry, 'type' => 'NEW'])->select('amount')->value('amount');
            $amount = $req->amount;
            $half = $amounts * 0.5;
            $full = $amounts;
            if (($amount == $half || $amount == $full) && $req->try == '1') {
                $description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
                $serviceTypeId = env('REMITA_SCHOOL_FEES_KEY');
                $name = $datas['name'] = $name . ' (' . session('username') . ')';
                $amount = $amount;
            } else if ($req->try == 'second') {
                $description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
                $serviceTypeId = env('REMITA_SCHOOL_FEES_KEY');
                $name = $datas['name'] = $name . ' (' . session('username') . ')';
                $amountPay = 0;
                $amountPaid = 0;

                $schoolFeesSession = \App\Http\Controllers\SystemSettingsController::getSchoolFeesSession();
                if ($schoolFeesSession == session('student_session')) {
                    $amountPaid = DB::table('invoices')
                        ->where([
                            'username' => session('id'),
                            'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES',
                            'status' => 'Paid',
                        ])
                        ->orderBy('id', 'ASC')
                        ->sum('amount');
                    $amountPay = DB::table('school_fees')
                        ->where(['program' => $row->program, 'level' => $row->level_of_entry, 'type' => 'NEW'])
                        ->select('amount')
                        ->value('amount');
                    // dd($amountPaid, $amountPay, $amount);
                } else {
                    $returnData = DB::table('session_history')
                        ->where(['username' => session('id_number')])
                        ->get();
                    foreach ($returnData as $return) {
                        // Check if this is a fresh student with no results yet
                        if ($return->session == session('student_session')) {
                            $amountPay += DB::table('school_fees')
                                ->where(['program' => $row->program, 'level' => $return->level, 'type' => 'NEW'])
                                ->select('amount')
                                ->value('amount');
                        } else {
                            $amountPay += DB::table('school_fees')
                                ->where(['program' => $row->program, 'level' => $return->level, 'type' => 'RETURNING'])
                                ->select('amount')
                                ->value('amount');
                        }

                        $amountPaid += DB::table('invoices')
                            ->where([
                                'username' => session('id'),
                                'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES',
                                'status' => 'Paid',
                                'session' => $return->session,
                            ])
                            ->orderBy('id', 'ASC')
                            ->sum('amount');
                    }
                }

                $remain = $amountPay - $amountPaid;
                if ($remain < 0) {
                    $remain = 0;
                }
                $amount = (float) $amount;
                $half = $amount * 0.5;
                $full = (float) $amount;
                $rhalf = $remain * 0.5;
                $rfull = (float) $remain;
                // dd($amountPay,$amount,$rhalf, $rfull);
                if ($rhalf == $amount || $rfull == $amount) {
                } else {
                    return redirect()->back()->with('error', 'Invalid Amount...');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid Amount.');
            }
        } else if ($req->page == 'id-card') {
            $amount = 2000;
            $description = 'ID CARDS';
            $serviceTypeId = '767553585';
            $name = $datas['name'] = $name . ' (' . session('username') . ')';
            $phone = $datas['phone'] = $req->phone;
            $email = $datas['email'] = strtolower($req->email);
            $datas['fees_type'] = 'Self Sponsor';
            $datas['amount_type'] = 'Student';
        } else {
            return redirect()->back()->with('error', 'Undefined Payment Description');
        }

        $baseUrl = env('REMITA_BASE_URL') . 'remita/exapp/api/v1/send/api';
        $merchantId = $this->merchantId;
        $apiKey = $this->apiKey;
        // echo $description.' '.$serviceTypeId;
        // die;

        // $baseUrl = 'https://remitademo.net/remita/exapp/api/v1/send/api';
        // $merchantId = 2547916;
        // $apiKey = 1946;
        // $serviceTypeId = 4430731;
        $amount = $amount;
        $orderId = rand(92343459459, 93438458488);
        $user_ip_address = $_SERVER['REMOTE_ADDR'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => '' . $baseUrl . '/echannelsvc/merchant/api/paymentinit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,  // Set 15-second timeout
            CURLOPT_CONNECTTIMEOUT => 10,  // Set 10-second connection timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "serviceTypeId": "' . $serviceTypeId . '",
                "amount": "' . $amount . '",
                "orderId": "' . $orderId . '",
                "payerName": "' . $name . '",
                "payerEmail": "' . $email . '",
                "payerPhone": "' . $phone . '",
                "description": "' . $description . '"
                }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . hash('sha512', $merchantId . '' . $serviceTypeId . '' . $orderId . '' . $amount . '' . $apiKey) . ''
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $substr = substr($response, 7, -1);
        $obj = json_decode($substr, true);

        if (isset($obj['RRR'])) {
            $rrr = $obj['RRR'];
        } else {
            return redirect()->back()->with('error', 'Failed to Connect to REMITA. Try Again');
        }

        if ($rrr == NULL) {
            return redirect()->back()->with('error', 'There is an error from the REMITA site. Try Again');
        } else {
            $datas['username'] = session('id');
            $datas['description'] = $description;
            $datas['amount'] = $amount;
            $datas['orderId'] = $orderId;
            $datas['serviceTypeId'] = $serviceTypeId;
            $datas['rrr'] = $rrr;
            try {
                DB::table($this->table)->insert($datas);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return redirect()->back()->with('error', 'You already have pending invoice for this service');
                } else {
                    return redirect()->back()->with('error', 'Something went Wrong...');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went Wrong ');
            } finally {
            }

            return redirect()->back()->with('success', 'Payment Generated');
        }
    }

    public function initializeApplicant(Request $req)
    {
        $x = 0;
        $data = DB::table('applicants')->select('fullname', 'phone', 'email', 'faculty', 'department', 'program')->where('user_id', session('id'))->get();
        foreach ($data as $row) {
            $name = $datas['name'] = $row->fullname;
            $phone = $datas['phone'] = $req->phone;
            $email = $datas['email'] = strtolower($req->email);
            $datas['faculty'] = $row->faculty;
            $datas['department'] = $row->department;
            $datas['program'] = $row->program;
            $datas['level'] = '100';
            $datas['created_at'] = date('Y-m-d');
            $datas['updated_at'] = date('Y-m-d');
            $x++;
        }

        $datas['session'] = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
        $x++;
        if ($x != 2) {
            return redirect()->back()->with('error', 'Your Personal Record Not Found in Current Uploaded Data, Report to Student Affairs.');
        }
        $amount = (float) \App\Http\Controllers\SystemSettingsController::get('post_utme_fee', 2000);
        $description = env('REMITA_POST_UTME_DESCRIPTION');
        $serviceTypeId = env('REMITA_POST_UTME_KEY');
        $name = $datas['name'] = $name . ' (' . session('username') . ')';

        $baseUrl = env('REMITA_BASE_URL') . 'remita/exapp/api/v1/send/api';
        $merchantId = $this->merchantId;
        $apiKey = $this->apiKey;
        // echo $description.' '.$serviceTypeId;
        // die;
        $orderId = rand(92343459459, 93438458488);
        $user_ip_address = $_SERVER['REMOTE_ADDR'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => '' . $baseUrl . '/echannelsvc/merchant/api/paymentinit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,  // Set 15-second timeout
            CURLOPT_CONNECTTIMEOUT => 10,  // Set 10-second connection timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "serviceTypeId": "' . $serviceTypeId . '",
                "amount": "' . $amount . '",
                "orderId": "' . $orderId . '",
                "payerName": "' . $name . '",
                "payerEmail": "' . $email . '",
                "payerPhone": "' . $phone . '",
                "description": "' . $description . '"
                }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . hash('sha512', $merchantId . '' . $serviceTypeId . '' . $orderId . '' . $amount . '' . $apiKey) . ''
            ),
        ));

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Check for cURL errors (including timeouts)
        if ($response === false || !empty($curl_error)) {
            $error_msg = 'Invoice generation failed. ';
            if (strpos($curl_error, 'timeout') !== false || strpos($curl_error, 'Operation timed out') !== false) {
                $error_msg = 'Invoice generation timed out. Please try again in a moment.';
            } elseif (!empty($curl_error)) {
                $error_msg .= 'Connection error occurred.';
            } else {
                $error_msg .= 'Unable to connect to payment gateway.';
            }
            return redirect()->back()->with('error', $error_msg);
        }

        // Check HTTP response code
        if ($http_code !== 200) {
            return redirect()->back()->with('error', 'Payment service unavailable (HTTP ' . $http_code . '). Please try again later.');
        }

        $substr = substr($response, 7, -1);
        $obj = json_decode($substr, true);
        // dd($response);

        if (isset($obj['RRR'])) {
            $rrr = $obj['RRR'];
        } else {
            return redirect()->back()->with('error', 'Failed to Connect to REMITA. Try Again');
        }

        if ($rrr == NULL) {
            return redirect()->back()->with('error', 'There is an error from the REMITA site. Try Again');
        } else {
            $datas['username'] = session('id');
            $datas['description'] = $description;
            $datas['amount'] = $amount;
            $datas['orderId'] = $orderId;
            $datas['serviceTypeId'] = $serviceTypeId;
            $datas['rrr'] = $rrr;
            try {
                DB::table('invoices')->insert($datas);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return redirect()->back()->with('error', 'You already have pending invoice for this service');
                } else {
                    dd($e);
                    return redirect()->back()->with('error', 'Something went Wrong...');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went Wrong ');
            } finally {
            }

            return redirect()->back()->with('success', 'Payment Generated');
        }
    }

    public function create(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();
        return redirect()->back()->with('success', 'Record Delete!!!');
    }

    public function invoice($rrr)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = $check = DB::table('invoices')->where('rrr', $rrr)->get();
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);
        $html = View::make('print invoice', $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('document.pdf', array('Attachment' => 0));
        die;
        // Storage::put('public/pdf/'.session('id').'.pdf', );
    }

    public function verify(Request $req)
    {
        $rrr = $req->rrr;
        $merchantId = $this->merchantId;
        $apiKey = $this->apiKey;
        $apiHash = hash('sha512', $rrr . '' . $apiKey . '' . $merchantId);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('REMITA_CURLOPT_URL') . $merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,  // Set 15-second timeout
            CURLOPT_CONNECTTIMEOUT => 10,  // Set 10-second connection timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $apiHash . ''
            ),
        ));

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Check for cURL errors (including timeouts)
        if ($response === false || !empty($curl_error)) {
            $error_msg = 'Payment verification failed. ';
            if (strpos($curl_error, 'timeout') !== false || strpos($curl_error, 'Operation timed out') !== false) {
                $error_msg = 'Payment verification timed out. Please try again in a moment.';
            } elseif (!empty($curl_error)) {
                $error_msg .= 'Connection error occurred.';
            } else {
                $error_msg .= 'Unable to connect to payment gateway.';
            }
            return redirect()->back()->with('error', $error_msg);
        }

        // Check HTTP response code
        if ($http_code !== 200) {
            return redirect()->back()->with('error', 'Payment verification service unavailable (HTTP ' . $http_code . '). Please try again later.');
        }

        $obj = json_decode($response, true);
        if (!is_array($obj) || !isset($obj['status'])) {
            return redirect()->back()->with('error', 'Invalid response from payment gateway. Please try again.');
        }

        $status = $obj['status'];
        // dd($obj);
        $datas['rrr_status'] = $status;
        if ($status == '021') {
            // echo '<div class="alert alert-warning">Transaction Pending</div>';
            return redirect()->back()->with('error', 'Transaction Pending');
        } elseif ($status == '023') {
            // echo '<div class="alert alert-danger">Invalid RRR</div>';
            return redirect()->back()->with('error', 'Invalid RRR');
        } elseif ($status == '01') {
            // Update invoice status to Paid
            $datas['status'] = 'Paid';
            $datas['rrr_status'] = $status;
            $datas['updated_at'] = now();

            // Get invoice descriptions for this payment
            $invoices = DB::table('invoices')
                ->where(['rrr' => $rrr, 'username' => session('id')])
                ->select('description', 'username')
                ->get();

            $hasPostUtme = false;
            foreach ($invoices as $invoice) {
                if ($invoice->description == 'HOSTEL-MAINTENANCE/FEES') {
                    // Update hostel payment status
                    $username = DB::table('students')
                        ->where('user_id', $invoice->username)
                        ->value('username');

                    DB::table('hostel')
                        ->where('occupant', $username)
                        ->update(['hostel_payment' => '1']);
                    // dd($invoice, $username);
                } elseif ($invoice->description == 'UNIVERSITY OF MAIDUGURI-1000127 FEES') {
                    // Handle ID number assignment
                    $session = DB::table('session')->where('status', '1')->value('title');

                    $student = Student::where([
                        'user_id' => session('id'),
                        'session_of_entry' => $session,
                    ])->first();

                    // Skip if student not found
                    if (!$student) {
                        continue;
                    }

                    // Only assign ID if student doesn't have one
                    if ($student->id_no == 0) {
                        $data = Student::where(['user_id' => session('id')])
                            ->select('id_format', 'department', 'session_of_entry')
                            ->first();

                        if (!$data) {
                            continue;
                        }

                        // Get all used IDs for this department/session/level
                        $usedIds = Student::where([
                            'session_of_entry' => $data->session_of_entry,
                            'department' => $data->department,
                        ])->pluck('id_no')->toArray();

                        // Find first available ID
                        $id_no = 1;
                        while (in_array($id_no, $usedIds)) {
                            $id_no++;

                            // Safety check
                            if ($id_no > 9999) {
                                break;
                            }
                        }

                        // Format the ID
                        $sessionShort = substr($data->session_of_entry, 2, 2);
                        $paddedId = str_pad($id_no, 4, '0', STR_PAD_LEFT);
                        $id_number = $sessionShort . $data->id_format . $paddedId;

                        // Verify ID is unique
                        if (!Student::where('username', $id_number)->exists()) {
                            Student::where(['user_id' => session('id')])
                                ->where('id_no', 0)
                                ->update([
                                    'username' => $id_number,
                                    'id_no' => $id_no
                                ]);

                            $req->session()->put('id_number', $id_number);
                            $req->session()->put('log', '1');

                            return redirect()
                                ->back()
                                ->with('success', $id_number . ' assigned as your ID Number');
                        }
                    }
                } elseif ($invoice->description == 'POST UTME') {
                    $hasPostUtme = true;
                }
            }

            // Update invoice status
            DB::table('invoices')->where('rrr', $rrr)->update($datas);
            if ($hasPostUtme) {
                // return to application route
                $req->session()->put('log', '1');
                return redirect('/application')->with('success', 'Successfully Paid');
            }
            return redirect()->back()->with('success', 'Successfully Paid');
        } else {
            // echo $response;
            return redirect()->back()->with('error', 'Error from Remita site. Status code: ' . $status);
        }
    }

    public function uploadPayment(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $upload_type = $request->upload_type;
            $session = $request->session;
            $sponsor = $request->sponsor;
            $service = $request->service;
            // dd($upload_type, $session, $sponsor, $service);

            // Load the uploaded file using Maatwebsite/Excel
            $import = new PaymentImport($upload_type, $session, $sponsor, $service);
            Excel::import($import, $file);
            // dd(session('studentImportMsg'));
            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with(session('studentImportStatus'), session('studentImportMsg'));
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    /**
     * Show the bulk verify progress page
     */
    public function showBulkVerifyProgress(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $serviceTypeId = $request->input('serviceTypeId');
        if (!$serviceTypeId) {
            return redirect()->back()->with('error', 'Please select a service type.');
        }

        $data['page'] = 'invoices/bulk_verify_progress';
        $data['title'] = 'Bulk Verify RRR';
        $data['serviceTypeId'] = $serviceTypeId;
        return view('main', $data);
    }

    /**
     * Stream the progress of bulk RRR verification
     */
    public function bulkVerifyStream(Request $request)
    {
        if (!session()->has('log')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $serviceTypeId = $request->input('serviceTypeId');
        if (!$serviceTypeId) {
            return response()->json(['error' => 'Service type ID is required'], 400);
        }

        $response = new StreamedResponse(function() use ($serviceTypeId) {
            // Set headers for Server-Sent Events
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering

            // Set time and memory limits to prevent timeout
            set_time_limit(0); // Unlimited execution time
            ini_set('memory_limit', '512M'); // Increase memory limit
            ignore_user_abort(true); // Continue script even if user disconnects

            // Send initial status
            $this->sendSseMessage('status', [
                'message' => "Starting bulk verification for service type {$serviceTypeId}..."
            ]);

            try {
                // Get the highest session for this serviceTypeId
                $highestSession = DB::table('invoices')
                    ->where('serviceTypeId', $serviceTypeId)
                    ->whereNotNull('rrr')
                    ->whereRaw('CHAR_LENGTH(rrr) = 12')
                    ->max('session');

                if (!$highestSession) {
                    $this->sendSseMessage('finished', [
                        'message' => "No invoices found for service type {$serviceTypeId} with valid 12-digit RRR."
                    ]);
                    return;
                }

                // Get all invoices for the highest session with 12-digit RRR
                $allInvoices = DB::table('invoices')
                    ->select('rrr', 'username', 'session', 'description')
                    ->where('serviceTypeId', $serviceTypeId)
                    ->where('session', $highestSession)
                    ->whereNotNull('rrr')
                    ->whereRaw('CHAR_LENGTH(rrr) = 12')
                    ->get();

                $totalInvoices = $allInvoices->count();
                $processedCount = 0;
                $successCount = 0;
                $errorCount = 0;

                if ($totalInvoices === 0) {
                    $this->sendSseMessage('finished', [
                        'message' => "No invoices found for service type {$serviceTypeId} in session {$highestSession}."
                    ]);
                    return;
                }

                $this->sendSseMessage('status', [
                    'message' => "Found {$totalInvoices} invoices to verify for highest session: {$highestSession}..."
                ]);

                $this->sendSseMessage('status', [
                    'message' => "Processing {$totalInvoices} invoices..."
                ]);

                foreach ($allInvoices as $invoice) {
                    // Add connection check
                    if (connection_aborted()) {
                        break;
                    }

                    try {
                        // Verify this RRR
                        $result = $this->verifySingleRrr($invoice->rrr, $invoice->username);

                        if ($result['success']) {
                            $successCount++;
                            $this->sendSseMessage('progress', [
                                'message' => "✓ Verified {$invoice->rrr} - {$invoice->username}",
                                'progress' => round((++$processedCount / $totalInvoices) * 100)
                            ]);
                        } else {
                            $errorCount++;
                            $this->sendSseMessage('progress', [
                                'message' => "✗ Failed {$invoice->rrr} - {$invoice->username}: {$result['message']}",
                                'progress' => round((++$processedCount / $totalInvoices) * 100)
                            ]);
                        }

                    } catch (\Exception $e) {
                        $errorCount++;
                        \Log::error('Error verifying RRR in bulk', [
                            'rrr' => $invoice->rrr ?? 'unknown',
                            'username' => $invoice->username ?? 'unknown',
                            'serviceTypeId' => $serviceTypeId,
                            'error' => $e->getMessage()
                        ]);
                        $this->sendSseMessage('progress', [
                            'message' => "✗ Error verifying {$invoice->rrr}: " . $e->getMessage(),
                            'progress' => round((++$processedCount / $totalInvoices) * 100)
                        ]);
                    }

                    // Flush output to send messages immediately
                    if (ob_get_level()) ob_flush();
                    flush();

                    // Small delay to prevent overwhelming the connection
                    usleep(10000); // 10ms delay
                }

                // Send final status
                $finalMessage = "Bulk verification completed! Success: {$successCount}, Errors: {$errorCount}";
                $this->sendSseMessage('finished', [
                    'message' => $finalMessage
                ]);

            } catch (\Exception $e) {
                $this->sendSseMessage('finished', [
                    'message' => 'Process failed: ' . $e->getMessage()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Helper method to verify a single RRR
     */
    private function verifySingleRrr($rrr, $username)
    {
        $merchantId = $this->merchantId;
        $apiKey = $this->apiKey;
        $apiHash = hash('sha512', $rrr . '' . $apiKey . '' . $merchantId);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('REMITA_CURLOPT_URL') . $merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $apiHash . ''
            ),
        ));

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Check for cURL errors
        if ($response === false || !empty($curl_error)) {
            if (strpos($curl_error, 'timeout') !== false) {
                return ['success' => false, 'message' => 'Verification timed out'];
            } elseif (!empty($curl_error)) {
                return ['success' => false, 'message' => 'Connection error'];
            } else {
                return ['success' => false, 'message' => 'Unable to connect to payment gateway'];
            }
        }

        // Check HTTP response code
        if ($http_code !== 200) {
            return ['success' => false, 'message' => 'Service unavailable (HTTP ' . $http_code . ')'];
        }

        $obj = json_decode($response, true);
        if (!is_array($obj) || !isset($obj['status'])) {
            return ['success' => false, 'message' => 'Invalid response from payment gateway'];
        }

        $status = $obj['status'];

        // Update invoice with current status
        $datas['rrr_status'] = $status;
        $datas['updated_at'] = now();

        if ($status == '01') {
            // Payment successful
            $datas['status'] = 'Paid';

            // Perform post-payment actions
            $this->performPostPaymentActions($rrr, $username);

            $statusMessage = 'Payment verified and marked as Paid';
            $success = true;
        } elseif ($status == '021') {
            // Transaction pending
            $datas['status'] = 'Pending';
            $statusMessage = 'Payment is Pending';
            $success = false;
        } elseif ($status == '023') {
            // Invalid RRR
            $datas['status'] = 'Invalid';
            $statusMessage = 'Invalid RRR';
            $success = false;
        } else {
            // Other statuses
            $datas['status'] = 'Unpaid';
            $statusMessage = 'Payment not successful (Status: ' . $status . ')';
            $success = false;
        }

        DB::table('invoices')->where('rrr', $rrr)->update($datas);

        return ['success' => $success, 'message' => $statusMessage];
    }

    /**
     * Perform post-payment actions based on invoice description
     */
    private function performPostPaymentActions($rrr, $username)
    {
        try {
            // Get invoice descriptions for this payment
            $invoices = DB::table('invoices')
                ->where('rrr', $rrr)
                ->select('description', 'username', 'session')
                ->get();

            foreach ($invoices as $invoice) {
                if ($invoice->description == 'HOSTEL-MAINTENANCE/FEES') {
                    // Update hostel payment status
                    $studentUsername = DB::table('students')
                        ->where('user_id', $invoice->username)
                        ->value('username');

                    if ($studentUsername) {
                        DB::table('hostel')
                            ->where('occupant', $studentUsername)
                            ->update(['hostel_payment' => '1']);
                    }
                } elseif ($invoice->description == 'UNIVERSITY OF MAIDUGURI-1000127 FEES') {
                    // Handle ID number assignment
                    $session = DB::table('session')->where('status', '1')->value('title');

                    $student = Student::where([
                        'user_id' => $invoice->username,
                        'session_of_entry' => $session,
                    ])->first();

                    // Skip if student not found
                    if (!$student) {
                        continue;
                    }

                    // Only assign ID if student doesn't have one
                    if ($student->id_no == 0) {
                        $data = Student::where(['user_id' => $invoice->username])
                            ->select('id_format', 'department', 'session_of_entry')
                            ->first();

                        if (!$data) {
                            continue;
                        }

                        // Get all used IDs for this department/session
                        $usedIds = Student::where([
                            'session_of_entry' => $data->session_of_entry,
                            'department' => $data->department,
                        ])->pluck('id_no')->toArray();

                        // Find first available ID
                        $id_no = 1;
                        while (in_array($id_no, $usedIds)) {
                            $id_no++;

                            // Safety check
                            if ($id_no > 9999) {
                                break;
                            }
                        }

                        // Format the ID
                        $sessionShort = substr($data->session_of_entry, 2, 2);
                        $paddedId = str_pad($id_no, 4, '0', STR_PAD_LEFT);
                        $id_number = $sessionShort . $data->id_format . $paddedId;

                        // Verify ID is unique
                        if (!Student::where('username', $id_number)->exists()) {
                            Student::where(['user_id' => $invoice->username])
                                ->where('id_no', 0)
                                ->update([
                                    'username' => $id_number,
                                    'id_no' => $id_no
                                ]);
                        }
                    }
                }
                // POST UTME handling is not needed in bulk verification since it requires redirect
            }
        } catch (\Exception $e) {
            \Log::error('Error performing post-payment actions', [
                'rrr' => $rrr,
                'username' => $username,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method to send SSE messages
     */
    private function sendSseMessage($type, $data)
    {
        echo "event: {$type}\n";
        echo "data: " . json_encode($data) . "\n\n";
    }
}
