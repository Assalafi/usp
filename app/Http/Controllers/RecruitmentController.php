<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Session;
use Excel;

class RecruitmentController extends Controller
{
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $this->page = $contents;
        $this->table = str_replace(' ', '_', $this->page);
        $this->title = strtoupper($this->page);
    }

    /**
     * Display the recruitment page with applicants data
     */
    public function index(Request $request)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['page'] = $this->page;
        $data['title'] = $this->title;
        $data['filters'] = [
            'search' => '', 'status' => '', 'department' => '', 'post' => '',
            'state' => '', 'lga' => '', 'gender' => '', 'staff_type' => ''
        ];

        // Fetch counts for stats cards
        $apiUrl = 'https://employee.umstad.online/api/applicants';
        $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

        if ($apiKey) {
            try {
                $headers = ['X-API-Key' => $apiKey, 'Accept' => 'application/json'];

                // Fetch total count
                $totalResp = Http::withHeaders($headers)->withoutVerifying()->get($apiUrl, ['per_page' => 1]);
                if ($totalResp->successful() && ($totalResp->json()['success'] ?? false)) {
                    $data['totalCount'] = $totalResp->json()['data']['total_count'] ?? 0;
                }

                // Fetch submitted (NEW) count
                $submittedResp = Http::withHeaders($headers)->withoutVerifying()->get($apiUrl, ['per_page' => 1, 'status' => 'NEW']);
                if ($submittedResp->successful() && ($submittedResp->json()['success'] ?? false)) {
                    $data['submittedCount'] = $submittedResp->json()['data']['total_count'] ?? 0;
                }

                // Fetch draft count
                $draftResp = Http::withHeaders($headers)->withoutVerifying()->get($apiUrl, ['per_page' => 1, 'status' => 'DRAFT']);
                if ($draftResp->successful() && ($draftResp->json()['success'] ?? false)) {
                    $data['draftCount'] = $draftResp->json()['data']['total_count'] ?? 0;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to fetch stats: ' . $e->getMessage());
            }
        }

        return view('main', $data);
    }

    /**
     * Get data for server-side DataTables
     */
    public function data(Request $request)
    {
        $draw = intval($request->input('draw', 1));
        $emptyResponse = ['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];

        // Check if user is logged in
        if (!session()->has('log')) {
            return response()->json($emptyResponse);
        }

        // API configuration
        $apiUrl = 'https://employee.umstad.online/api/applicants';
        $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

        if (!$apiKey) {
            return response()->json($emptyResponse);
        }

        try {
            // DataTables sends start (offset) and length (per page)
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 100));
            if ($length < 0) $length = 10000; // "All" option
            $page = ($length > 0) ? floor($start / $length) + 1 : 1;

            // Build query parameters for API
            $queryParams = ['page' => $page, 'per_page' => $length];

            // Add filter parameters (only non-empty)
            $filterFields = ['search', 'status', 'department', 'post', 'state', 'lga', 'gender', 'staff_type'];
            foreach ($filterFields as $field) {
                $val = $request->input($field, '');
                if ($val !== '') {
                    $queryParams[$field] = $val;
                }
            }

            // Make API request with filters
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(30)->get($apiUrl, $queryParams);

            if (!$response->successful()) {
                \Log::error('Recruitment data API failed: ' . $response->status());
                return response()->json($emptyResponse);
            }

            $responseData = $response->json();
            if (!($responseData['success'] ?? false)) {
                return response()->json($emptyResponse);
            }

            $applications = $responseData['data']['applications'] ?? [];
            $totalCount = $responseData['data']['total_count'] ?? 0;

            // Format data for DataTables
            $data = [];
            $rowNum = $start;

            foreach ($applications as $application) {
                $rowNum++;
                $job = $application['job'] ?? null;
                $status = $application['status'] ?? '';
                $applicantId = $application['applicant_id'] ?? '';
                $statusColors = [
                    'DRAFT' => 'badge-warning', 'NEW' => 'badge-primary', 'SCREENING' => 'badge-info',
                    'SHORTLISTED' => 'badge-success', 'INTERVIEW_SCHEDULED' => 'badge-purple',
                    'INTERVIEWING' => 'badge-purple', 'INTERVIEW_COMPLETED' => 'badge-indigo',
                    'OFFER_PENDING' => 'badge-warning', 'OFFER_SENT' => 'badge-success',
                    'OFFER_ACCEPTED' => 'badge-success', 'OFFER_DECLINED' => 'badge-secondary',
                    'HIRED' => 'badge-success', 'REJECTED' => 'badge-danger', 'WITHDRAWN' => 'badge-secondary',
                ];

                $statusLabel = $status;
                if ($status === 'NEW') {
                    $statusLabel = 'SUBMITTED';
                } elseif ($status === 'DRAFT' && isset($application['current_step'])) {
                    $statusLabel = 'DRAFT (' . ($application['current_step'] + 1) . ')';
                }
                $statusBadge = '<span class="badge ' . ($statusColors[$status] ?? 'badge-secondary') . '">' . $statusLabel . '</span>';

                $firstName = $application['personal']['first_name'] ?? '';
                $lastName = $application['personal']['last_name'] ?? '';
                $appNumber = $application['application_number'] ?? 'N/A';
                $phone = $application['contact']['contact_phone'] ?? 'N/A';
                $email = $application['contact']['contact_email'] ?? 'N/A';
                $jobTitle = $job['title'] ?? 'Not Specified';
                $deptName = $job['department_name'] ?? 'N/A';
                $stateOrigin = $application['personal']['state_of_origin'] ?? 'N/A';
                $lga = $application['personal']['local_govt_of_origin'] ?? '';
                $gender = $application['personal']['gender'] ?? 'N/A';

                $actions = '<div class="btn-group btn-group-sm">';
                $actions .= '<a href="/recruitment/' . $applicantId . '" class="btn btn-outline-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';
                if ($status !== 'DRAFT') {
                    $actions .= '<a href="/recruitment/download-cv/' . $applicantId . '" class="btn btn-outline-success btn-sm" title="CV" target="_blank"><i class="fas fa-file-pdf"></i></a>';
                } else {
                    $actions .= '<button class="btn btn-outline-secondary btn-sm" title="CV not available for drafts" disabled><i class="fas fa-file-pdf"></i></button>';
                }
                $actions .= '</div>';

                $data[] = [
                    'row_number' => $rowNum,
                    'applicant' => '<strong>' . e($firstName . ' ' . $lastName) . '</strong><br><small class="text-muted">' . e($appNumber) . '</small>',
                    'contact' => '<small>' . e($phone) . '</small><br><small class="text-muted">' . e($email) . '</small>',
                    'position' => '<strong>' . e($jobTitle) . '</strong><br><small class="text-muted">' . e($deptName) . '</small>',
                    'location' => '<small>' . e($stateOrigin) . '</small>' . ($lga ? '<br><small class="text-muted">' . e($lga) . '</small>' : ''),
                    'gender' => '<small>' . e($gender) . '</small>',
                    'status' => $statusBadge,
                    'actions' => $actions
                ];
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $totalCount,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Recruitment data error: ' . $e->getMessage());
            return response()->json($emptyResponse);
        }
    }

    /**
     * Get specific applicant details
     */
    public function show(Request $request, $id)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // API configuration
        $apiUrl = "https://employee.umstad.online/api/applicants/{$id}";
        $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

        $data['page'] = 'recruitment show';
        $data['title'] = 'RECRUITMENT DETAILS';
        $data['applicant_id'] = $id;

        if (!$apiKey) {
            $data['error'] = 'API key not configured';
            return view('main', $data);
        }

        try {
            // Make API request (disable SSL verification for local development)
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->withoutVerifying()->get($apiUrl);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if ($responseData['success']) {
                    $data['applicant'] = $responseData['data'];
                } else {
                    $data['error'] = $responseData['message'] ?? 'Applicant not found';
                }
            } else {
                $errorMessage = 'API request failed';
                if ($response->status() === 401) {
                    $errorMessage = 'Unauthorized - Invalid API Key';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Applicant not found';
                } elseif ($response->status() >= 500) {
                    $errorMessage = 'Server error - Please try again later';
                }
                
                $data['error'] = $errorMessage;
            }
        } catch (\Exception $e) {
            $data['error'] = 'Connection error: ' . $e->getMessage();
        }

        return view('main', $data);
    }

    /**
     * Download file from recruitment API
     */
    public function downloadFile(Request $request, $filePath)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Construct file URL
        $fileUrl = "https://employee.umstad.online/storage/" . $filePath;

        try {
            // Stream the file
            return response()->stream(function () use ($fileUrl) {
                readfile($fileUrl);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }

    /**
     * Export applicants to PDF
     */
    public function exportPdf(Request $request)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get filtered applications (flat list)
        $applications = $this->getFilteredApplicants($request);
        
        // Prepare data for export
        $exportData = [];
        $serialNumber = 1;
        
        foreach ($applications as $application) {
            $exportData[] = [
                'sno' => $serialNumber++,
                'name' => ($application['personal']['first_name'] ?? '') . ' ' . 
                         ($application['personal']['middle_name'] ?? '') . ' ' . 
                         ($application['personal']['last_name'] ?? ''),
                'gender' => $application['personal']['gender'] ?? 'N/A',
                'date_of_birth' => !empty($application['personal']['date_of_birth']) ? 
                                 date('d/m/Y', strtotime($application['personal']['date_of_birth'])) : 'N/A',
                'state' => $application['personal']['state_of_origin'] ?? 'N/A',
                'lga' => $application['personal']['local_govt_of_origin'] ?? 'N/A',
                'qualification' => $this->getHighestQualification($application['education'] ?? []),
                'post_applied' => $application['job']['title'] ?? 'N/A',
                'department' => $application['job']['department_name'] ?? 'N/A',
                'gsm_no' => $application['contact']['contact_phone'] ?? $application['applicant_phone'] ?? 'N/A',
            ];
        }

        try {
            // Generate PDF using project's FPDF system
            $pdfContent = view('Admin.recruitment-pdf-fpdf', [
                'applicants' => $exportData,
                'filters' => $request->all()
            ])->render();
            
            // Evaluate the PDF content (it will output the PDF directly)
            eval('?>' . $pdfContent);
            exit;
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get applicants data from API
     */
    private function getApplicantsData()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => 'umstad_recruitment_api_OWVlNDBjNjNjYTlmNGRiNTI4ODRiMTk1'
            ])->get('https://employee.umstad.online/api/applicants');
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['applicants'] ?? [];
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error('API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Download applicant CV with all documents
     */
    public function downloadCV($id)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            // Get applicant data using same approach as show method
            $apiUrl = "https://employee.umstad.online/api/applicants/{$id}";
            $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

            if (!$apiKey) {
                return back()->with('error', 'API key not configured');
            }

            // Make API request
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->withoutVerifying()->get($apiUrl);

            if (!$response->successful()) {
                $errorMessage = 'API request failed';
                if ($response->status() === 401) {
                    $errorMessage = 'Unauthorized - Invalid API Key';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Applicant not found';
                } elseif ($response->status() >= 500) {
                    $errorMessage = 'Server error - Please try again later';
                }
                return back()->with('error', $errorMessage);
            }

            $responseData = $response->json();
            
            if (!$responseData['success']) {
                return back()->with('error', $responseData['message'] ?? 'Applicant not found');
            }

            $applicant = $responseData['data'];

            // Get the first application
            $application = null;
            if (!empty($applicant['applications'])) {
                $application = $applicant['applications'][0];
            }

            if (!$application) {
                return back()->with('error', 'No application found for this applicant');
            }

            // Check if application is a draft - don't generate CV for incomplete applications
            if (($application['status'] ?? '') === 'DRAFT') {
                return back()->with('error', 'CV cannot be generated for draft applications. Please submit the application first.');
            }

            // Ensure basic data exists for CV generation
            if (empty($application['personal']['first_name']) || empty($application['personal']['last_name'])) {
                return back()->with('error', 'Incomplete applicant information. Cannot generate CV.');
            }

            // Setup temp directory
            $tempDir = storage_path('app/temp/documents');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            $tempFiles = []; // track temp files for cleanup

            // ── Step 1: Download documents from API ──
            $apiBaseUrl = rtrim(config('app.api_url', 'https://employee.umstad.online'), '/');
            $downloadedDocs = [];

            if (!empty($application['documents'])) {
                foreach ($application['documents'] as $doc) {
                    if (empty($doc['file_path'])) continue;

                    $filePath = $doc['file_path'];
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $label = $doc['label'] ?? 'Document';

                    $urlsToTry = [
                        $apiBaseUrl . '/storage/' . $filePath,
                        $apiBaseUrl . '/' . $filePath,
                    ];

                    $fileContent = null;
                    foreach ($urlsToTry as $url) {
                        try {
                            $resp = Http::withoutVerifying()->timeout(15)->get($url);
                            if ($resp->successful() && strlen($resp->body()) > 100) {
                                $fileContent = $resp->body();
                                break;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    if ($fileContent) {
                        $tmpFile = $tempDir . '/' . uniqid('doc_') . '.' . $ext;
                        file_put_contents($tmpFile, $fileContent);
                        $tempFiles[] = $tmpFile;
                        $downloadedDocs[] = [
                            'label' => $label,
                            'ext' => $ext,
                            'path' => $tmpFile,
                            'content' => $fileContent,
                        ];
                    }
                }
            }

            // ── Step 2: Build CV HTML (embed images directly) ──
            $options = new Options();
            $options->set('chroot', public_path());
            $options->set('tempDir', sys_get_temp_dir());
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $html = view('Admin.recruitment-cv', [
                'applicant' => $applicant,
                'application' => $application
            ])->render();

            // Embed image documents into the HTML (insert before </body>)
            $imageDocs = [];
            foreach ($downloadedDocs as $ddoc) {
                if (in_array($ddoc['ext'], ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                    $mimeMap = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'bmp' => 'bmp'];
                    $mime = $mimeMap[$ddoc['ext']] ?? $ddoc['ext'];
                    $b64 = base64_encode($ddoc['content']);
                    $imageDocs[] = '<div style="text-align:center;page-break-after:always;"><img src="data:image/' . $mime . ';base64,' . $b64 . '" style="max-width:100%;max-height:750px;" /></div>';
                }
            }
            if (!empty($imageDocs)) {
                // Force page break after footer so images start on fresh page
                $html = str_replace('class="footer"', 'class="footer" style="page-break-after:always;"', $html);
                // Remove page-break-after from last image to avoid trailing blank page
                $lastIdx = count($imageDocs) - 1;
                $imageDocs[$lastIdx] = str_replace('page-break-after:always;', '', $imageDocs[$lastIdx]);
                $imageHtml = implode('', $imageDocs);
                $html = str_replace('</body>', $imageHtml . '</body>', $html);
            }

            // ── Step 3: Render the CV to PDF via DomPDF ──
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $cvPdfContent = $dompdf->output();

            // Save CV PDF to temp file
            $cvTmpFile = $tempDir . '/' . uniqid('cv_') . '.pdf';
            file_put_contents($cvTmpFile, $cvPdfContent);
            $tempFiles[] = $cvTmpFile;

            // ── Step 4: Collect PDF documents to merge ──
            $pdfDocsToMerge = [];
            foreach ($downloadedDocs as $ddoc) {
                if ($ddoc['ext'] === 'pdf') {
                    $pdfDocsToMerge[] = $ddoc;
                }
            }

            // ── Step 5: Merge using FPDI if there are PDF documents ──
            if (!empty($pdfDocsToMerge)) {
                $fpdi = new Fpdi();

                // Import CV pages
                $cvPageCount = $fpdi->setSourceFile($cvTmpFile);
                for ($p = 1; $p <= $cvPageCount; $p++) {
                    $tpl = $fpdi->importPage($p);
                    $size = $fpdi->getTemplateSize($tpl);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl);
                }

                // Import each PDF document
                foreach ($pdfDocsToMerge as $pdfDoc) {
                    try {
                        $docPageCount = $fpdi->setSourceFile($pdfDoc['path']);
                        for ($p = 1; $p <= $docPageCount; $p++) {
                            $tpl = $fpdi->importPage($p);
                            $size = $fpdi->getTemplateSize($tpl);
                            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $fpdi->useTemplate($tpl);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to merge PDF document: ' . $pdfDoc['label'] . ' - ' . $e->getMessage());
                    }
                }

                // Output merged PDF
                $mergedContent = $fpdi->Output('S');

                // Cleanup temp files
                foreach ($tempFiles as $f) {
                    @unlink($f);
                }

                // Generate filename
                $personal = $application['personal'] ?? [];
                $cleanFirstName = preg_replace('/[^A-Za-z0-9\-]/', '_', $personal['first_name'] ?? 'Applicant');
                $cleanLastName = preg_replace('/[^A-Za-z0-9\-]/', '_', $personal['last_name'] ?? '');
                $pdfName = sprintf('CV_%s_%s_%s.pdf', $cleanFirstName, $cleanLastName, date('Y-m-d_H-i-s'));
                $pdfName = str_replace('__', '_', $pdfName);

                return response($mergedContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $pdfName . '"',
                    'Content-Length' => strlen($mergedContent),
                ]);
            }

            // ── No PDF documents to merge — just return the CV ──
            // Cleanup temp files
            foreach ($tempFiles as $f) {
                @unlink($f);
            }

            $personal = $application['personal'] ?? [];
            $cleanFirstName = preg_replace('/[^A-Za-z0-9\-]/', '_', $personal['first_name'] ?? 'Applicant');
            $cleanLastName = preg_replace('/[^A-Za-z0-9\-]/', '_', $personal['last_name'] ?? '');
            $pdfName = sprintf('CV_%s_%s_%s.pdf', $cleanFirstName, $cleanLastName, date('Y-m-d_H-i-s'));
            $pdfName = str_replace('__', '_', $pdfName);

            return $dompdf->stream($pdfName, ['Attachment' => true]);
            
        } catch (\Exception $e) {
            \Log::error('CV Generation Error: ' . $e->getMessage());
            return back()->with('error', 'CV generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Export applicants to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        // Check if user is logged in
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Get filtered applications (flat list)
        $applications = $this->getFilteredApplicants($request);
        
        // Prepare data for export
        $exportData = [];
        $serialNumber = 1;
        
        foreach ($applications as $application) {
            $exportData[] = [
                'S/NO' => $serialNumber++,
                'NAME' => ($application['personal']['first_name'] ?? '') . ' ' . 
                        ($application['personal']['middle_name'] ?? '') . ' ' . 
                        ($application['personal']['last_name'] ?? ''),
                'GENDER' => $application['personal']['gender'] ?? 'N/A',
                'DATE OF BIRTH' => !empty($application['personal']['date_of_birth']) ? 
                                  date('d/m/Y', strtotime($application['personal']['date_of_birth'])) : 'N/A',
                'STATE' => $application['personal']['state_of_origin'] ?? 'N/A',
                'LGA' => $application['personal']['local_govt_of_origin'] ?? 'N/A',
                'QUALIFICATION' => $this->getHighestQualification($application['education'] ?? []),
                'POST APPLIED' => $application['job']['title'] ?? 'N/A',
                'DEPARTMENT' => $application['job']['department_name'] ?? 'N/A',
                'GSM NO' => $application['contact']['contact_phone'] ?? $application['applicant_phone'] ?? 'N/A',
            ];
        }

        // Generate filename with timestamp
        $filename = 'applicants_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Create CSV file
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Header row
            if (!empty($exportData)) {
                fputcsv($file, array_keys($exportData[0]));
            }
            
            // Data rows
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get filtered applicants based on request parameters
     */
    private function getFilteredApplicants(Request $request)
    {
        // API configuration
        $apiUrl = 'https://employee.umstad.online/api/applicants';
        $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

        try {
            // Build query params from export form
            $queryParams = ['per_page' => 10000, 'export' => 1]; // Fetch all with education for export
            if ($request->filled('status')) $queryParams['status'] = $request->input('status');
            if ($request->filled('department')) $queryParams['department'] = $request->input('department');
            if ($request->filled('post_applied')) $queryParams['post'] = $request->input('post_applied');
            if ($request->filled('state')) $queryParams['state'] = $request->input('state');
            if ($request->filled('lga')) $queryParams['lga'] = $request->input('lga');
            if ($request->filled('gender')) $queryParams['gender'] = $request->input('gender');
            if ($request->filled('staff_type')) $queryParams['staff_type'] = $request->input('staff_type');

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(60)->get($apiUrl, $queryParams);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData['success']) {
                    return $responseData['data']['applications'] ?? [];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Export API error: ' . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Get highest qualification from education array
     */
    private function getHighestQualification($education)
    {
        if (empty($education)) {
            return 'N/A';
        }
        
        // Define qualification hierarchy (highest first)
        $qualificationOrder = [
            'PHD' => 1, 'DOCTORATE' => 1, 'PhD' => 1,
            'MASTERS' => 2, 'MSC' => 2, 'MBA' => 2, 'M.Sc' => 2,
            'BACHELOR' => 3, 'BSC' => 3, 'BA' => 3, 'B.Sc' => 3, 'B.Eng' => 3,
            'HND' => 4, 'HIGHER NATIONAL DIPLOMA' => 4,
            'DIPLOMA' => 5, 'OND' => 5, 'NCE' => 5,
            'WAEC' => 6, 'NECO' => 6, 'SSCE' => 6, 'O LEVEL' => 6
        ];
        
        $highestQualification = '';
        $highestRank = 999;
        
        foreach ($education as $edu) {
            $degree = strtoupper($edu['degree'] ?? '');
            
            foreach ($qualificationOrder as $qual => $rank) {
                if (strpos($degree, $qual) !== false && $rank < $highestRank) {
                    $highestQualification = $edu['degree'] . ' in ' . ($edu['field_of_study'] ?? '');
                    $highestRank = $rank;
                }
            }
        }
        
        return $highestQualification ?: $education[0]['degree'] . ' in ' . ($education[0]['field_of_study'] ?? '');
    }

    /**
     * Filter applicants by department
     */
    private function filterByDepartment($applicants, $department)
    {
        return array_filter($applicants, function($applicant) use ($department) {
            foreach ($applicant['applications'] as $application) {
                if (isset($application['job']['department_name']) && 
                    strtolower($application['job']['department_name']) === strtolower($department)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter applicants by post applied
     */
    private function filterByPostApplied($applicants, $postApplied)
    {
        return array_filter($applicants, function($applicant) use ($postApplied) {
            foreach ($applicant['applications'] as $application) {
                if (isset($application['job']['title']) && 
                    strtolower($application['job']['title']) === strtolower($postApplied)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter applicants by state
     */
    private function filterByState($applicants, $state)
    {
        return array_filter($applicants, function($applicant) use ($state) {
            foreach ($applicant['applications'] as $application) {
                if (isset($application['personal']['state_of_origin']) && 
                    strtolower($application['personal']['state_of_origin']) === strtolower($state)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter applicants by LGA
     */
    private function filterByLga($applicants, $lga)
    {
        return array_filter($applicants, function($applicant) use ($lga) {
            foreach ($applicant['applications'] as $application) {
                if (isset($application['personal']['local_govt_of_origin']) && 
                    strtolower($application['personal']['local_govt_of_origin']) === strtolower($lga)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter applicants by gender
     */
    private function filterByGender($applicants, $gender)
    {
        return array_filter($applicants, function($applicant) use ($gender) {
            foreach ($applicant['applications'] as $application) {
                if (isset($application['personal']['gender']) && 
                    strtolower($application['personal']['gender']) === strtolower($gender)) {
                    return true;
                }
            }
            return false;
        });
    }
}
