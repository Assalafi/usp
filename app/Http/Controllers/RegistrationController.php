<?php

namespace App\Http\Controllers;

use App\Exports\CoursesExport;
use App\Exports\UsersExport;
use App\Imports\AdmittedImport;
use App\Imports\ApplicantImport;
use App\Imports\AdmitStudentUploadImport;
use App\Imports\StudentImport;
use App\Models\Admitted;
use App\Models\Applicant;
use App\Models\DEQualification;
use App\Models\DocumentUpload;
use App\Models\Jamb;
use App\Models\Ssce;
use App\Models\SsceResult;
use App\Models\Student;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class RegistrationController extends Controller
{
    /**
     * Save Personal Information section of the application form.
     */
    public function savePersonalInfo(Request $request)
    {
        // You may want to get applicant by session user or by hidden field
        $userId = session('id');
        $applicant = \App\Models\Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            return redirect()->back()->with('error', 'Applicant record not found.');
        }

        if ($request->ajax()) {
            $validator = \Validator::make($request->all(), [
                'surname' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'gender' => 'required|string|max:20',
                'dob' => 'required|date',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'nationality' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'city' => 'nullable|string|max:100',
                'address' => 'required|string|max:500',
                'lga' => 'required|string|max:100',
                'marital_status' => 'nullable|string|max:20',
                'religion' => 'nullable|string|max:50',
                'pob' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $validated = $validator->validated();
            $applicant->fill($validated);
            $applicant->save();
            return response()->json(['success' => 'Personal Information saved successfully.']);
        } else {
            $validated = $request->validate([
                'surname' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'gender' => 'required|string|max:20',
                'dob' => 'required|date',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'nationality' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'city' => 'nullable|string|max:100',
                'address' => 'required|string|max:500',
                'lga' => 'required|string|max:100',
                'marital_status' => 'nullable|string|max:20',
                'religion' => 'nullable|string|max:50',
                'pob' => 'nullable|string|max:255',
            ]);
            $applicant->fill($validated);
            $applicant->save();
            return redirect()->back()->with('success', 'Personal Information saved successfully.');
        }
    }

    /**
     * Save SSCE Information section of the application form.
     */
    public function saveSsceInfo(Request $request)
    {
        $userId = session('id');
        $applicant = \App\Models\Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Applicant record not found.'], 404);
            }
            return redirect()->back()->with('error', 'Applicant record not found.');
        }

        if ($request->ajax()) {
            $rules = [
                'ssce_type_1' => 'required|string|max:50',
                'ssce_year_1' => 'required|string|max:4',
                'ssce_reg_number_1' => 'required|string|max:100',
                'ssce_center_1' => 'required|string|max:255',
            ];

            // Add validation for first sitting subjects and grades (first 5 are required)
            for ($i = 1; $i <= 9; $i++) {
                if ($i <= 5) {
                    $rules["subject_1_{$i}"] = 'required|string|max:100';
                    $rules["grade_1_{$i}"] = 'required|string|max:5';
                } else {
                    $rules["subject_1_{$i}"] = 'nullable|string|max:100';
                    $rules["grade_1_{$i}"] = 'nullable|string|max:5';
                }
            }

            // Add validation for second sitting (all optional)
            $rules['ssce_type_2'] = 'nullable|string|max:50';
            $rules['ssce_year_2'] = 'nullable|string|max:4';
            $rules['ssce_reg_number_2'] = 'nullable|string|max:100';
            $rules['ssce_center_2'] = 'nullable|string|max:255';

            for ($i = 1; $i <= 9; $i++) {
                $rules["subject_2_{$i}"] = 'nullable|string|max:100';
                $rules["grade_2_{$i}"] = 'nullable|string|max:5';
            }

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $validated = $validator->validated();

            // Create or update first sitting SSCE record
            $ssce = \App\Models\Ssce::firstOrCreate(
                [
                    'user_id' => $userId,
                    'username' => $applicant->username,
                    'sitting' => 1
                ],
                [
                    'type' => $validated['ssce_type_1'],
                    'year' => $validated['ssce_year_1'],
                    'number' => $validated['ssce_reg_number_1'],
                    'center_name' => $validated['ssce_center_1']
                ]
            );

            // Update first sitting SSCE record
            $ssce->update([
                'type' => $validated['ssce_type_1'],
                'year' => $validated['ssce_year_1'],
                'number' => $validated['ssce_reg_number_1'],
                'center_name' => $validated['ssce_center_1']
            ]);

            // Delete existing first sitting results to avoid duplicates
            \App\Models\SsceResult::where('ssce_id', $ssce->id)
                ->where('sitting', 1)
                ->delete();

            // Save first sitting subject results
            for ($i = 1; $i <= 9; $i++) {
                $subjectKey = "subject_1_{$i}";
                $gradeKey = "grade_1_{$i}";

                if (!empty($validated[$subjectKey]) && !empty($validated[$gradeKey])) {
                    \App\Models\SsceResult::create([
                        'user_id' => $userId,
                        'username' => $applicant->username,
                        'ssce_id' => $ssce->id,
                        'subject' => $validated[$subjectKey],
                        'grade' => $validated[$gradeKey],
                        'sitting' => 1,
                        'remark' => 'First Sitting'
                    ]);
                }
            }

            // Handle second sitting if provided
            if (!empty($validated['ssce_type_2'])) {
                $ssce2 = \App\Models\Ssce::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'username' => $applicant->username,
                        'sitting' => 2
                    ],
                    [
                        'type' => $validated['ssce_type_2'],
                        'year' => $validated['ssce_year_2'],
                        'number' => $validated['ssce_reg_number_2'],
                        'center_name' => $validated['ssce_center_2']
                    ]
                );

                // Delete existing second sitting results
                \App\Models\SsceResult::where('ssce_id', $ssce2->id)
                    ->where('sitting', 2)
                    ->delete();

                // Save second sitting subject results
                for ($i = 1; $i <= 9; $i++) {
                    $subjectKey = "subject_2_{$i}";
                    $gradeKey = "grade_2_{$i}";

                    if (!empty($validated[$subjectKey]) && !empty($validated[$gradeKey])) {
                        \App\Models\SsceResult::create([
                            'user_id' => $userId,
                            'username' => $applicant->username,
                            'ssce_id' => $ssce2->id,
                            'subject' => $validated[$subjectKey],
                            'grade' => $validated[$gradeKey],
                            'sitting' => 2,
                            'remark' => 'Second Sitting'
                        ]);
                    }
                }
            }

            return response()->json(['success' => 'SSCE Information saved successfully.']);
        } else {
            // Handle normal form submission similarly
            $rules = [
                'ssce_type_1' => 'required|string|max:50',
                'ssce_year_1' => 'required|string|max:4',
                'ssce_reg_number_1' => 'required|string|max:100',
                'ssce_center_1' => 'required|string|max:255',
            ];

            for ($i = 1; $i <= 9; $i++) {
                if ($i <= 5) {
                    $rules["subject_1_{$i}"] = 'required|string|max:100';
                    $rules["grade_1_{$i}"] = 'required|string|max:5';
                } else {
                    $rules["subject_1_{$i}"] = 'nullable|string|max:100';
                    $rules["grade_1_{$i}"] = 'nullable|string|max:5';
                }
            }

            $validated = $request->validate($rules);

            // Create or update SSCE record (non-AJAX version)
            $ssce = \App\Models\Ssce::firstOrCreate(
                [
                    'user_id' => $userId,
                    'username' => $applicant->username
                ],
                [
                    'type' => $validated['ssce_type_1'],
                    'year' => $validated['ssce_year_1'],
                    'number' => $validated['ssce_reg_number_1'],
                    'center_name' => $validated['ssce_center_1']
                ]
            );

            $ssce->update([
                'type' => $validated['ssce_type_1'],
                'year' => $validated['ssce_year_1'],
                'number' => $validated['ssce_reg_number_1'],
                'center_name' => $validated['ssce_center_1']
            ]);

            \App\Models\SsceResult::where('ssce_id', $ssce->id)->delete();

            for ($i = 1; $i <= 9; $i++) {
                $subjectKey = "subject_1_{$i}";
                $gradeKey = "grade_1_{$i}";

                if (!empty($validated[$subjectKey]) && !empty($validated[$gradeKey])) {
                    \App\Models\SsceResult::create([
                        'user_id' => $userId,
                        'username' => $applicant->username,
                        'ssce_id' => $ssce->id,
                        'subject' => $validated[$subjectKey],
                        'grade' => $validated[$gradeKey],
                        'remark' => 'First Sitting'
                    ]);
                }
            }

            return redirect()->back()->with('success', 'SSCE Information saved successfully.');
        }
    }

    public function saveDirectEntryInfo(Request $request)
    {
        $userId = session('id');
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $applicant = \App\Models\Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant not found'], 404);
        }

        // Validate Direct Entry data
        $validated = $request->validate([
            'de_qualification' => 'required|string|max:50',
            'de_institution' => 'required|string|max:255',
            'de_grad_year' => 'required|string|max:4',
            'de_grade' => 'required|string|max:50',
            'de_reg_number' => 'required|string|max:50',
            'de_credentials' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',  // 2MB max
        ]);

        // Handle file upload if present
        $credentialsPath = null;
        if ($request->hasFile('de_credentials')) {
            $file = $request->file('de_credentials');
            $filename = time() . '_de_credentials_' . $userId . '.' . $file->getClientOriginalExtension();
            $credentialsPath = $file->storeAs('uploads/de_credentials', $filename, 'public');
        }

        // Save or update Direct Entry qualification
        $deQualification = \App\Models\DEQualification::updateOrCreate(
            [
                'user_id' => $userId,
                'applicant_id' => $applicant->id,
            ],
            array_filter([
                'qualification_type' => $validated['de_qualification'],
                'institution' => $validated['de_institution'],
                'grad_year' => $validated['de_grad_year'],
                'grade' => $validated['de_grade'],
                'reg_number' => $validated['de_reg_number'],
                'credentials_path' => $credentialsPath,  // Only update if file was uploaded
            ], function ($value) {
                return $value !== null;  // Only include non-null values
            })
        );

        return response()->json(['success' => 'Direct Entry information saved successfully.']);
    }

    public function saveNextOfKinInfo(Request $request)
    {
        $userId = session('id');
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $applicant = \App\Models\Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant not found'], 404);
        }

        // Validate Next of Kin data
        $validated = $request->validate([
            'nok_name' => 'required|string|max:255',
            'nok_relationship' => 'required|string|max:50',
            'nok_phone' => 'required|string|max:20',
            'nok_email' => 'nullable|email|max:255',
            'nok_address' => 'required|string|max:500',
        ]);

        // Update Next of Kin information in applicant record
        $applicant->update([
            'n_name' => $validated['nok_name'],
            'n_relationship' => $validated['nok_relationship'],
            'n_phone' => $validated['nok_phone'],
            'n_email' => $validated['nok_email'],
            'n_address' => $validated['nok_address'],
        ]);

        return response()->json(['success' => 'Next of Kin information saved successfully.']);
    }

    public function saveSponsorInfo(Request $request)
    {
        $userId = session('id');
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $applicant = \App\Models\Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant not found'], 404);
        }

        // Validate Sponsor data
        $validated = $request->validate([
            's_name' => 'required|string|max:255',
            's_phone' => 'required|string|max:20',
            's_address' => 'required|string|max:500',
        ]);

        // Update Sponsor information in applicant record
        $applicant->update([
            's_name' => $validated['s_name'],
            's_phone' => $validated['s_phone'],
            's_address' => $validated['s_address'],
        ]);

        return response()->json(['success' => 'Sponsor information saved successfully!']);
    }

    public function saveDocuments(Request $request)
    {
        try {
            // Check if user is logged in
            $userId = session('id');
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get applicant
            $applicant = Applicant::where('user_id', $userId)->first();
            if (!$applicant) {
                return response()->json(['error' => 'Applicant not found'], 404);
            }

            // Define required document types based on applicant mode
            $requiredDocs = ['passport_photo', 'jamb_result', 'ssce_result'];
            if (strtoupper($applicant->mode) === 'DE') {
                $requiredDocs[] = 'direct_entry_cert';
            }

            // Only validate if files are actually being uploaded
            if ($request->hasFile('documents')) {
                $validator = Validator::make($request->all(), [
                    'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:400',  // 400KB max
                    'doc_types.*' => 'required|string',
                    'doc_labels.*' => 'required|string',
                ], [
                    'documents.*.required' => 'Document file is required.',
                    'documents.*.file' => 'Invalid file format.',
                    'documents.*.mimes' => 'Document must be PDF, JPG, JPEG, or PNG format.',
                    'documents.*.max' => 'Document size must not exceed 400KB.',
                    'doc_types.*.required' => 'Document type is required.',
                    'doc_labels.*.required' => 'Document label is required.',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
            } else {
                // No files being uploaded, just return success if all required docs exist
                $existingDocs = DocumentUpload::where('user_id', $userId)
                    ->where('applicant_id', $applicant->id)
                    ->pluck('doc_type')
                    ->toArray();

                $missingRequired = array_diff($requiredDocs, $existingDocs);

                if (!empty($missingRequired)) {
                    $docLabels = [
                        'passport_photo' => 'Passport Photograph',
                        'jamb_result' => 'JAMB Result',
                        'ssce_result' => 'SSCE Result',
                        'direct_entry_cert' => 'Direct Entry Certificate',
                        'birth_certificate' => 'Birth Certificate'
                    ];

                    $missingLabels = array_map(function ($docType) use ($docLabels) {
                        return $docLabels[$docType] ?? $docType;
                    }, $missingRequired);

                    return response()->json([
                        'errors' => [
                            'required_documents' => 'Missing required documents: ' . implode(', ', $missingLabels)
                        ]
                    ], 422);
                } else {
                    return response()->json([
                        'success' => 'All required documents are already uploaded!'
                    ]);
                }
            }

            // Get existing documents for this applicant
            $existingDocs = DocumentUpload::where('user_id', $userId)
                ->where('applicant_id', $applicant->id)
                ->pluck('doc_type')
                ->toArray();

            // Get document types being uploaded in this request
            $uploadingDocs = $request->input('doc_types', []);

            // Combine existing and new document types
            $allDocTypes = array_unique(array_merge($existingDocs, $uploadingDocs));

            // Check if all required documents are covered
            $missingRequired = array_diff($requiredDocs, $allDocTypes);

            if (!empty($missingRequired)) {
                $docLabels = [
                    'passport_photo' => 'Passport Photograph',
                    'jamb_result' => 'JAMB Result',
                    'ssce_result' => 'SSCE Result',
                    'direct_entry_cert' => 'Direct Entry Certificate',
                    'birth_certificate' => 'Birth Certificate'
                ];

                $missingLabels = array_map(function ($docType) use ($docLabels) {
                    return $docLabels[$docType] ?? $docType;
                }, $missingRequired);

                return response()->json([
                    'errors' => [
                        'required_documents' => 'Missing required documents: ' . implode(', ', $missingLabels)
                    ]
                ], 422);
            }

            $documents = $request->file('documents');
            $docTypes = $request->input('doc_types', []);
            $docLabels = $request->input('doc_labels', []);
            $uploadedCount = 0;

            // If no documents to upload, we've already handled this case above
            if (!$documents || count($documents) === 0) {
                return response()->json(['success' => 'No new documents to upload.']);
            }

            // Process each document
            for ($i = 0; $i < count($documents); $i++) {
                $file = $documents[$i];
                $docType = $docTypes[$i];
                $docLabel = $docLabels[$i];

                // Additional file size check (400KB = 409600 bytes)
                if ($file->getSize() > 409600) {
                    return response()->json([
                        'errors' => [
                            'documents' => ["{$docLabel} exceeds 400KB limit. Current size: " . round($file->getSize() / 1024, 1) . 'KB']
                        ]
                    ], 422);
                }

                // Create dynamic filename based on label and timestamp
                $timestamp = now()->format('YmdHis');
                $extension = $file->getClientOriginalExtension();
                $sanitizedLabel = preg_replace('/[^A-Za-z0-9_-]/', '_', $docLabel);
                $fileName = "{$sanitizedLabel}_{$timestamp}.{$extension}";

                // Store file
                $filePath = $file->storeAs('uploads/documents', $fileName, 'public');

                if ($filePath) {
                    // Save or update document record
                    DocumentUpload::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'applicant_id' => $applicant->id,
                            'doc_type' => $docType
                        ],
                        [
                            'file_path' => $filePath,
                            'original_name' => $fileName,
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]
                    );

                    $uploadedCount++;
                }
            }

            if ($uploadedCount > 0) {
                return response()->json([
                    'success' => "Successfully uploaded {$uploadedCount} document(s)!"
                ]);
            } else {
                return response()->json(['error' => 'No documents were uploaded'], 400);
            }
        } catch (Exception $e) {
            Log::error('Document upload error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while uploading documents'], 500);
        }
    }

    public function saveJambInfo(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'jamb_subject1' => 'required|string|max:255',
                'jamb_score1' => 'required|numeric|min:0|max:100',
                'jamb_subject2' => 'required|string|max:255',
                'jamb_score2' => 'required|numeric|min:0|max:100',
                'jamb_subject3' => 'required|string|max:255',
                'jamb_score3' => 'required|numeric|min:0|max:100',
                'jamb_subject4' => 'required|string|max:255',
                'jamb_score4' => 'required|numeric|min:0|max:100',
            ], [
                'jamb_subject*.required' => 'All JAMB subjects are required.',
                'jamb_score*.required' => 'All JAMB scores are required.',
                'jamb_score*.numeric' => 'JAMB scores must be numeric.',
                'jamb_score*.min' => 'JAMB scores must be at least 0.',
                'jamb_score*.max' => 'JAMB scores must not exceed 100.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if user is logged in
            $userId = session('id');
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get applicant
            $applicant = Applicant::where('user_id', $userId)->first();
            if (!$applicant) {
                return response()->json(['error' => 'Applicant not found'], 404);
            }

            // Check for duplicate subjects
            $subjects = [
                $request->jamb_subject1,
                $request->jamb_subject2,
                $request->jamb_subject3,
                $request->jamb_subject4
            ];

            if (count($subjects) !== count(array_unique($subjects))) {
                return response()->json([
                    'errors' => [
                        'duplicate_subjects' => 'Duplicate subjects are not allowed. Please select different subjects.'
                    ]
                ], 422);
            }

            // Delete existing JAMB records for this user
            Jamb::where('user_id', $userId)->delete();

            // Save new JAMB records
            $savedCount = 0;
            for ($i = 1; $i <= 4; $i++) {
                $subject = $request->input("jamb_subject{$i}");
                $score = $request->input("jamb_score{$i}");

                if ($subject && $score !== null) {
                    Jamb::create([
                        'user_id' => $userId,
                        'username' => $applicant->username,
                        'subject' => $subject,
                        'score' => $score,
                    ]);
                    $savedCount++;
                }
            }

            return response()->json([
                'success' => "Successfully saved {$savedCount} JAMB subjects and scores!"
            ]);
        } catch (Exception $e) {
            Log::error('JAMB save error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while saving JAMB information'], 500);
        }
    }

    /**
     * Check completion status of all application sections
     */
    public function checkCompletion(Request $request)
    {
        try {
            $userId = session('id');
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Check applicant basic info
            $applicant = DB::table('applicants')->where('user_id', $userId)->first();
            if (!$applicant) {
                return response()->json(['error' => 'Applicant record not found'], 404);
            }

            // Check each section completion
            $completion = [
                'personal_info' => $this->checkPersonalInfoCompletion($applicant),
                'ssce_info' => $this->checkSsceCompletion($userId),
                'documents' => $this->checkDocumentCompletion($userId),
                'next_of_kin' => $this->checkNextOfKinCompletion($applicant),
                'sponsor' => $this->checkSponsorCompletion($applicant)
            ];

            // Check mode-specific sections
            if (strtoupper($applicant->mode) === 'DE') {
                $completion['direct_entry'] = $this->checkDirectEntryCompletion($userId);
            } else {
                $completion['jamb_info'] = $this->checkJambCompletion($userId);
            }

            return response()->json($completion);
        } catch (Exception $e) {
            Log::error('Check completion error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while checking completion'], 500);
        }
    }

    /**
     * Submit final application and change status to Submitted
     */
    public function finalSubmit(Request $request)
    {
        try {
            $userId = session('id');
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get applicant record
            $applicant = DB::table('applicants')->where('user_id', $userId)->first();
            if (!$applicant) {
                return response()->json(['error' => 'Applicant record not found'], 404);
            }

            // Check if already submitted
            if ($applicant->status === 'Submitted') {
                return response()->json(['error' => 'Application has already been submitted'], 400);
            }

            // Final validation - ensure all sections are complete
            $completion = [
                'personal_info' => $this->checkPersonalInfoCompletion($applicant),
                'ssce_info' => $this->checkSsceCompletion($userId),
                'documents' => $this->checkDocumentCompletion($userId),
                'next_of_kin' => $this->checkNextOfKinCompletion($applicant),
                'sponsor' => $this->checkSponsorCompletion($applicant)
            ];

            // Check mode-specific sections
            if (strtoupper($applicant->mode) === 'DE') {
                $completion['direct_entry'] = $this->checkDirectEntryCompletion($userId);
            } else {
                $completion['jamb_info'] = $this->checkJambCompletion($userId);
            }

            // Find incomplete sections
            $incompleteSections = [];
            foreach ($completion as $section => $isComplete) {
                if (!$isComplete) {
                    $incompleteSections[] = ucwords(str_replace('_', ' ', $section));
                }
            }

            if (!empty($incompleteSections)) {
                return response()->json([
                    'errors' => [
                        'incomplete_sections' => 'The following sections are incomplete: ' . implode(', ', $incompleteSections)
                    ]
                ], 422);
            }

            // Update applicant status to Submitted and set submission timestamp
            DB::table('applicants')
                ->where('user_id', $userId)
                ->update([
                    'status' => $applicant->status == 1 ? 'Admitted' : 'Submitted',
                    'submitted_at' => now(),
                    'updated_at' => now()
                ]);


            return response()->json([
                'success' => 'Your application has been successfully submitted! Reference ID: ' . $applicant->username
            ]);
        } catch (Exception $e) {
            Log::error('Final submission error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during final submission'], 500);
        }
    }

    /**
     * Helper methods to check section completion
     */
    private function checkPersonalInfoCompletion($applicant)
    {
        return !empty($applicant->dob) &&
            !empty($applicant->phone) &&
            !empty($applicant->email) &&
            !empty($applicant->address) &&
            !empty($applicant->lga);
    }

    private function checkSsceCompletion($userId)
    {
        $ssceCount = DB::table('ssce')->where('user_id', $userId)->count();
        $resultsCount = DB::table('ssce_results')->where('user_id', $userId)->count();

        return $ssceCount > 0 && $resultsCount >= 5;  // At least one SSCE record and 5 subjects
    }

    private function checkJambCompletion($userId)
    {
        $jambCount = DB::table('jamb')->where('user_id', $userId)->count();
        return $jambCount >= 4;  // All 4 JAMB subjects
    }

    private function checkDirectEntryCompletion($userId)
    {
        $deCount = DB::table('de_qualifications')->where('user_id', $userId)->count();
        return $deCount > 0;
    }

    private function checkDocumentCompletion($userId)
    {
        // Get applicant to check mode for DE-specific documents
        $applicant = DB::table('applicants')->where('user_id', $userId)->first();

        // Check for required documents based on mode
        $requiredDocs = ['passport_photo', 'jamb_result', 'ssce_result'];

        // Add Direct Entry certificate if DE mode
        if ($applicant && strtoupper($applicant->mode) === 'DE') {
            $requiredDocs[] = 'direct_entry_cert';
        }

        $uploadedDocs = DB::table('document_uploads')
            ->where('user_id', $userId)
            ->pluck('doc_type')
            ->toArray();

        // Check if all required documents are uploaded
        foreach ($requiredDocs as $doc) {
            if (!in_array($doc, $uploadedDocs)) {
                return false;
            }
        }

        return true;
    }

    private function checkNextOfKinCompletion($applicant)
    {
        return !empty($applicant->n_name) &&
            !empty($applicant->n_relationship) &&
            !empty($applicant->n_phone) &&
            !empty($applicant->n_address);
    }

    private function checkSponsorCompletion($applicant)
    {
        return !empty($applicant->s_name) &&
            !empty($applicant->s_phone) &&
            !empty($applicant->s_address);
    }

    /**
     * Show applicant dashboard
     */
    public function dashboard(Request $request)
    {
        $userId = session('id');
        if (!$userId) {
            return redirect('/')->with('error', 'Please log in to access your dashboard.');
        }

        // Get applicant data with all related information
        $applicant = Applicant::where('user_id', $userId)->first();
        if (!$applicant) {
            return redirect('/')->with('error', 'Applicant record not found.');
        }

        // Get JAMB information if UTME applicant
        $jambData = [];
        if (strtoupper($applicant->mode) === 'UTME') {
            $jambData = DB::table('jamb')->where('user_id', $userId)->get();
        }

        // Get DE qualification if DE applicant
        $deData = null;
        if (strtoupper($applicant->mode) === 'DE') {
            $deData = DB::table('de_qualifications')->where('user_id', $userId)->first();
        }

        // Get SSCE data
        $ssceData = DB::table('ssce')->where('user_id', $userId)->get();
        $ssceResults = [];
        foreach ($ssceData as $ssce) {
            $results = DB::table('ssce_results')->where('ssce_id', $ssce->id)->get();
            $ssceResults[$ssce->id] = $results;
        }

        // Get uploaded documents
        $documents = DB::table('document_uploads')
            ->where('user_id', $userId)
            ->where('applicant_id', $applicant->id)
            ->get();

        // Calculate application progress
        $progress = $this->calculateApplicationProgress($userId, $applicant);

        return view('dashboard', compact(
            'applicant',
            'jambData',
            'deData',
            'ssceData',
            'ssceResults',
            'documents',
            'progress'
        ));
    }

    // changeApplicationStatus
    public function changeApplicationStatus(Request $request)
    {
        $status = 'Pending';
        $userId = session('id');
        DB::table('applicants')->where('user_id', $userId)->update(['status' => $status]);
        return redirect('/application')->with('success', 'Application edit enabled.');
    }

    /**
     * Download admission letter for admitted applicants
     */
    public function downloadAdmissionLetter(Request $request)
    {
        $userId = session('id');
        if (!$userId) {
            return redirect('/')->with('error', 'Please log in to access this feature.');
        }

        $applicant = DB::table('applicants')->where('user_id', $userId)->first();
        if (!$applicant) {
            return redirect('/')->with('error', 'Applicant record not found.');
        }

        // Check if applicant is admitted
        if ($applicant->status !== 'Admitted') {
            return redirect('/dashboard')->with('error', 'Admission letter is only available for admitted applicants.');
        }

        // Generate and download admission letter as PDF
        return $this->generateAdmissionLetter($applicant, 'pdf');
    }

    /**
     * Calculate application completion progress
     */
    private function calculateApplicationProgress($userId, $applicant)
    {
        $totalSections = 6;  // Personal, SSCE, JAMB/DE, Next of Kin, Sponsor, Documents
        $completedSections = 0;

        // Check each section completion
        if ($this->checkPersonalInfoCompletion($applicant))
            $completedSections++;
        if ($this->checkSsceCompletion($userId))
            $completedSections++;
        if ($this->checkNextOfKinCompletion($applicant))
            $completedSections++;
        if ($this->checkSponsorCompletion($applicant))
            $completedSections++;
        if ($this->checkDocumentCompletion($userId))
            $completedSections++;

        // Check mode-specific section
        if (strtoupper($applicant->mode) === 'DE') {
            if ($this->checkDirectEntryCompletion($userId))
                $completedSections++;
        } else {
            if ($this->checkJambCompletion($userId))
                $completedSections++;
        }

        return [
            'completed' => $completedSections,
            'total' => $totalSections,
            'percentage' => round(($completedSections / $totalSections) * 100, 1)
        ];
    }

    /**
     * Generate admission letter PDF
     */
    private function generateAdmissionLetter($applicant, $format = 'html')
    {
        // Create admission letter content
        $letterContent = $this->buildAdmissionLetterContent($applicant);

        if ($format === 'pdf') {
            // Generate PDF using dompdf
            $options = new Options();
            $options->set('defaultFont', 'Times');
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('chroot', public_path());
            $options->set('tempDir', sys_get_temp_dir());

            $dompdf = new Dompdf($options);

            // Get HTML content
            $html = view('admission-letter-pdf', compact('applicant', 'letterContent'))->render();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Generate filename
            $filename = $applicant->username . '_ADMISSION_LETTER_' . date('Y-m-d') . '.pdf';
            $filename = str_replace(' ', '_', $filename);

            return $dompdf->stream($filename, ['Attachment' => false]);
        }

        // Return HTML view that can be printed as PDF
        return view('admission-letter', compact('applicant', 'letterContent'));
    }

    /**
     * Build admission letter content
     */
    private function buildAdmissionLetterContent($applicant)
    {
        // Get program details
        $program = DB::table('program')->where('code', $applicant->program)->first();
        $faculty = DB::table('faculty')->where('code', $applicant->faculty)->first();
        $department = DB::table('department')->where('code', $applicant->department)->first();

        return [
            'letterhead' => 'UNIVERSITY OF MAIDUGURI',
            'title' => 'ADMISSION LETTER',
            'session' => $this->session,
            'admission_number' => 'UNIMAID/' . date('Y') . '/' . str_pad($applicant->id, 6, '0', STR_PAD_LEFT),
            'date' => date('F j, Y'),
            'program' => $program,
            'faculty' => $faculty,
            'department' => $department,
        ];
    }

    //
    private $session;

    public function __construct(Request $req)
    {
        // Module Data - Use system session from settings
        $this->session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
    }

    /**
     * Admin method to download admission letter for any applicant
     */
    public function adminDownloadAdmissionLetter($id)
    {
        if (!session()->has('log')) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($id);

            // Check if applicant is admitted
            if ($applicant->status !== 'Admitted') {
                return redirect()->back()->with('error', 'Admission letter is only available for admitted applicants.');
            }

            // Generate and download admission letter as PDF
            return $this->generateAdmissionLetter($applicant, 'pdf');
        } catch (Exception $e) {
            Log::error('Error generating admin admission letter', [
                'applicant_id' => $id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the admission letter.');
        }
    }

    public function index(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //

        $session = '2024/2025';
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table('students');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            if (session('username') == 'su') {
                $data['data'] = $query->orderBy('id', 'DESC')->get();
            } else {
                $data['data'] = $query->orderBy('id', 'DESC')->get();
            }

            $data['dataq'] = $query->where($key, $value);
        } else {
            if (session('username') == 'su') {
                $data['data'] = DB::table('students')->where(['session_of_entry' => $session])->orderBy('id', 'DESC')->limit(100)->get();
            } else {
                $data['data'] = DB::table('students')->where(['session_of_entry' => $session])->orderBy('id', 'DESC')->limit(100)->get();
            }

            $data['dataq'] = DB::table('students')->where(['session_of_entry' => $session]);
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['admitted'] = DB::table('students')->where(['session_of_entry' => $session])->select('id')->orderBy('id', 'DESC')->count('id');
        $data['not_paid'] = DB::table('students')->where(['session_of_entry' => $session, 'id_no' => 0])->select('id')->orderBy('id', 'DESC')->count('id');
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        $data['sessions'] = $session;
        $data['page'] = 'registration';
        return view('main', $data);
    }

    public function applicant(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $defaultSession = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
        $perPage = 100;  // Items per page

        // Build base query
        $query = DB::table('applicants');

        // Apply filters if form is submitted
        if ($req->has('_token')) {
            $filters = $req->except(['_token', 'page']);
            $filteredData = array_filter($filters, function ($value) {
                return !is_null($value) && $value !== '';
            });

            foreach ($filteredData as $key => $value) {
                if ($key === 'search') {
                    // Global search across name fields
                    $query->where(function ($q) use ($value) {
                        $q
                            ->where('fullname', 'LIKE', "%{$value}%")
                            ->orWhere('surname', 'LIKE', "%{$value}%")
                            ->orWhere('first_name', 'LIKE', "%{$value}%")
                            ->orWhere('username', 'LIKE', "%{$value}%");
                    });
                } else {
                    $query->where($key, $value);
                }
            }
        } else {
            // Only use default session if no filters are applied
            $query->where(['session' => $defaultSession]);
        }

        // Determine the active session for statistics
        $session = isset($filters['session']) && !empty($filters['session']) ? $filters['session'] : $defaultSession;

        // Get paginated results
        $applicantsData = session('appointment') == 'DSO' ? $query->where('department', session('department'))->where('status', 'Admitted')->orderBy('id', 'DESC')->paginate($perPage) : $query->orderBy('id', 'DESC')->paginate($perPage);

        // Append query parameters to pagination links
        if ($req->has('_token')) {
            $applicantsData->appends($req->except(['_token']));
        }

        // Prepare data array
        // Get filters for dropdown population
        $filters = $req->except(['_token', 'page']);

        // Filter departments based on selected faculty
        $departments = DB::table('department')->where(['status' => '1']);
        if (!empty($filters['faculty'])) {
            $departments = $departments->where('faculty', $filters['faculty']);
        }
        $departments = $departments->select('code', 'title')->orderBy('title', 'ASC')->get();

        // Filter programs based on selected faculty and department
        $programs = DB::table('program')->where(['status' => '1']);
        if (!empty($filters['faculty'])) {
            $programs = $programs->where('faculty', $filters['faculty']);
        }
        if (!empty($filters['department'])) {
            $programs = $programs->where('department', $filters['department']);
        }
        $programs = $programs->select('code', 'title')->orderBy('title', 'ASC')->get();

        $data = [
            'data' => $applicantsData,
            'dataq' => $query,  // Keep for compatibility
            'faculty' => DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get(),
            'departments' => $departments,
            'programs' => $programs,
            'filters' => $filters,  // Pass filters to view for form repopulation
            'session' => DB::table('session')->select('title')->orderBy('title', 'ASC')->get(),
            'sessions' => $session,
            'page' => 'applicant',
            // Statistics
            'applicants' => DB::table('applicants')->where(['session' => $session]),
            'admitted' => DB::table('applicants')->where(['session' => $session, 'status' => 'Admitted']),
            'rejected' => DB::table('applicants')->where(['session' => $session, 'status' => 'Rejected']),
            'pending' => DB::table('applicants')->where(['session' => $session, 'status' => 'Pending']),
            'submitted' => DB::table('applicants')->where(['session' => $session, 'status' => 'Submitted']),
            'cleared' => DB::table('applicants')->where(['session' => $session, 'clearance' => 'Cleared']),
            // Filter values for form persistence
            'filters' => $req->except(['_token', 'page']),
        ];

        return view('main', $data);
    }

    public function admitted(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //

        $session = '2024/2025';
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = Admitted::orderBy('fullname');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            if (session('username') == 'su') {
                $data['data'] = $query->get();
            } else {
                $data['data'] = $query->get();
            }

            $data['dataq'] = $query->where($key, $value);
        } else {
            if (session('username') == 'su') {
                $data['data'] = Admitted::where(['session' => $session])->orderBy('id', 'DESC')->limit(100)->get();
            } else {
                $data['data'] = Admitted::where(['session' => $session])->orderBy('id', 'DESC')->limit(100)->get();
            }

            $data['dataq'] = DB::table('admitted')->where(['session' => $session]);
        }
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['session'] = DB::table('session')->select('title')->orderBy('title', 'ASC')->get();
        $data['sessions'] = $session;
        $data['page'] = 'admitted';
        return view('main', $data);
    }

    public function application(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $userId = session('id');
        $applicant = Applicant::where('user_id', $userId)->first();

        // Load existing SSCE data if available
        $ssceData = null;
        $ssceData2 = null;
        $ssceResults = [];
        $ssceResults2 = [];

        if ($applicant) {
            // Load first sitting data
            $ssce = \App\Models\Ssce::where('user_id', $userId)
                ->where('sitting', 1)
                ->first();
            if ($ssce) {
                $ssceData = $ssce;
                $firstSittingResults = \App\Models\SsceResult::where('ssce_id', $ssce->id)
                    ->where('sitting', 1)
                    ->orderBy('created_at')
                    ->get();
                $ssceResults = $firstSittingResults->values();
            }

            // Load second sitting data
            $ssce2 = \App\Models\Ssce::where('user_id', $userId)
                ->where('sitting', 2)
                ->first();
            if ($ssce2) {
                $ssceData2 = $ssce2;
                $secondSittingResults = \App\Models\SsceResult::where('ssce_id', $ssce2->id)
                    ->where('sitting', 2)
                    ->orderBy('created_at')
                    ->get();
                $ssceResults2 = $secondSittingResults->values();
            }
        }

        // Load existing Direct Entry data if available
        $deData = null;
        if ($applicant) {
            $deQualification = \App\Models\DEQualification::where('user_id', $userId)
                ->where('applicant_id', $applicant->id)
                ->first();
            if ($deQualification) {
                $deData = $deQualification;
            }
        }

        // Check if the applicant status is not pending, redirect to dashboard
        if ($applicant && $applicant->status !== 'Pending') {
            return redirect('/applicant-dashboard');
        }

        return view('application', compact('applicant', 'ssceData', 'ssceData2', 'ssceResults', 'ssceResults2', 'deData'));
    }

    public function createStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        User::updateOrCreate(
            ['username' => strtoupper($request->jamb_no)],
            [
                'password' => Hash::make(strtoupper($request->jamb_no)),
                'accType' => 'Student',
                'name' => strtoupper($request->first . ' ' . $request->surname . ' ' . $request->middle_name),
                'status' => '1'
            ]
        );
        $id = User::where('username', $request->jamb_no)->value('id');
        $f = DB::table('faculty')->where('code', $request->faculty)->value('no');
        $d = DB::table('department')->where('code', $request->department)->value('no');
        $session = DB::table('session')->where('status', '1')->value('title');
        Student::updateOrCreate(
            ['jamb_no' => strtoupper($request->jamb_no)],
            [
                'user_id' => strtoupper($id),
                'last_name' => strtoupper($request->surname),
                'first_name' => strtoupper($request->first_name),
                'other_name' => strtoupper($request->middle_name),
                'program' => strtoupper($request->program),
                'department' => strtoupper($request->department),
                'faculty' => strtoupper($request->faculty),
                'id_format' => '/' . $f . '/' . $d . '/',
                'gender' => strtoupper($request->gender),
                'session_of_entry' => $session,
                'mode_of_entry' => strtoupper($request->mode_of_entry),
                'level_of_entry' => strtoupper($request->level_of_entry),
                'level' => strtoupper($request->level_of_entry),
                'fullname' => strtoupper($request->first_name . ' ' . $request->surname . ' ' . $request->middle_name),
                'status' => '1'
            ]
        );
        return redirect()->back()->with('success', 'Done!!!');
    }

    public function updateStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if ($request->gender == 'M') {
            $gender = 'MALE';
        } elseif ($request->gender == 'F') {
            $gender = 'FEMALE';
        } else {
            return redirect()->back()->with('error', 'Gender Error');
        }
        if ($request->page == 'new') {
            Student::updateOrCreate(
                ['user_id' => strtoupper($request->id)],
                [
                    'last_name' => strtoupper($request->surname),
                    'first_name' => strtoupper($request->first_name),
                    'other_name' => strtoupper($request->middle_name),
                    'jamb_no' => strtoupper($request->jamb_no),
                    'gender' => strtoupper($request->gender),
                    'fullname' => strtoupper($request->first_name . ' ' . $request->surname . ' ' . $request->middle_name)
                ]
            );
            User::updateOrCreate(
                ['id' => strtoupper($request->id)],
                [
                    'username' => strtoupper($request->jamb_no),
                    'gender' => strtoupper($gender),
                ]
            );
        } else {
            Student::updateOrCreate(
                ['user_id' => strtoupper($request->id)],
                [
                    'last_name' => strtoupper($request->surname),
                    'first_name' => strtoupper($request->first_name),
                    'other_name' => strtoupper($request->middle_name),
                    'country' => strtoupper($request->country),
                    'lga_origin' => strtoupper($request->lga_origin),
                    'state_origin' => strtoupper($request->state_origin),
                    'kin_name' => strtoupper($request->kin_name),
                    'kin_phone' => strtoupper($request->kin_phone),
                    'gender' => strtoupper($request->gender),
                    'contact_phone' => strtoupper($request->contact_phone),
                    'level' => strtoupper($request->level),
                    'level_of_entry' => strtoupper($request->level_of_entry),
                    'fullname' => strtoupper($request->first_name . ' ' . $request->surname . ' ' . $request->middle_name),
                    'issue_date' => $request->issue_date,
                    'expire_date' => $request->expire_date,
                ]
            );
            $username = DB::table('students')->where('user_id', strtoupper($request->id))->value('username');
            DB::table('session_history')->where(['username' => $username, 'session' => session('system_session')])->update([
                'level' => strtoupper($request->level)
            ]);
            User::updateOrCreate(
                ['id' => strtoupper($request->id)],
                [
                    'gender' => strtoupper($gender),
                    'level' => strtoupper($request->level),
                ]
            );

            if ($request->file('passport_pic')) {
                $dot = $request->file('passport_pic')->getClientOriginalExtension();
                $request->file('passport_pic')->storeAs('passport_pic', $request->id . '.' . $dot, 'public');

                $applicant = Student::where(['user_id' => $request->id])->update([
                    'passport_pic' => $request->id . '.' . $dot
                ]);
            }

            if ($request->file('passport_sign')) {
                $dot = $request->file('passport_sign')->getClientOriginalExtension();
                $request->file('passport_sign')->storeAs('passport_sign', $request->id . '.' . $dot, 'public');

                $applicant = Student::where(['user_id' => $request->id])->update([
                    'passport_sign' => $request->id . '.' . $dot
                ]);
            }
        }
        return redirect()->back()->with('success', 'Done!!!');
    }

    public function updateProfile(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
            // return redirect()->back()->with('error', 'Temporary disabled!!!');
        if ($request->file('signiture')) {
            $dot = $request->file('signiture')->getClientOriginalExtension();
            $request->file('signiture')->storeAs('signature', session('id') . '.' . $dot, 'public');

            $applicant = Student::where(['id' => $request->id])->update([
                'signiture' => session('id') . '.' . $dot
            ]);
        }

        if ($request->file('picture')) {
            $dot = $request->file('picture')->getClientOriginalExtension();
            $request->file('picture')->storeAs('picture', session('id') . '.' . $dot, 'public');

            $applicant = Student::where(['id' => $request->id])->update([
                'picture' => session('id') . '.' . $dot
            ]);
        }

        Student::where('id', $request->id)->update(
            [
                'user_id' => session('id'),
                'last_name' => strtoupper($request->surname),
                'first_name' => strtoupper($request->first_name),
                'other_name' => strtoupper($request->other_name),
                'fullname' => strtoupper($request->first_name . ' ' . $request->surname . ' ' . $request->other_name),
                'level' => $request->level,
                'jamb_no' => $request->jamb_no,
                'date_of_birth' => strtoupper($request->date_of_birth),
                'place_of_birth' => strtoupper($request->place_of_birth),
                'country' => strtoupper($request->country),
                //'state_origin' => strtoupper($request->state_origin),
                'lga_origin' => $request->lga_origin,
                'marital_status' => strtoupper($request->marital_status),
                'maiden_name' => strtoupper($request->maiden_name),
                'religion' => strtoupper($request->religion),
                'nin' => strtoupper($request->nin),
                'health_status' => strtoupper($request->health_status),
                'physical_challenge' => strtoupper($request->physical_challenge),
                'hobbies' => strtoupper($request->hobbies),
                'blood_group' => strtoupper($request->blood_group),
                'room' => strtoupper($request->room),
                'hall' => strtoupper($request->hall),
                'games' => strtoupper($request->games),
                'genotype' => strtoupper($request->genotype),
                'highest_qualification' => strtoupper($request->highest_qualification),
                'home_address' => strtoupper($request->home_address),
                'home_phone' => strtoupper($request->home_phone),
                'home_email' => strtolower($request->home_email),
                'contact_address' => strtoupper($request->contact_address),
                'contact_phone' => strtoupper($request->contact_phone),
                'contact_email' => strtolower($request->contact_email),
                'kin_name' => strtoupper($request->kin_name),
                'kin_address' => strtoupper($request->kin_address),
                'kin_phone' => strtoupper($request->kin_phone),
                'kin_email' => strtolower($request->kin_email),
                'sponsor_type' => strtoupper($request->sponsor_type),
                'sponsor_name' => strtoupper($request->sponsor_name),
                'sponsor_address' => strtoupper($request->sponsor_address),
                'sponsor_phone' => strtoupper($request->sponsor_phone),
                'sponsor_email' => strtolower($request->sponsor_email),
                'mother_name' => strtoupper($request->mother_name),
                'mother_address' => strtoupper($request->mother_address),
                'mother_phone' => strtoupper($request->mother_phone),
                'mother_email' => strtolower($request->mother_email),
                'father_name' => strtoupper($request->father_name),
                'father_address' => strtoupper($request->father_address),
                'father_phone' => strtoupper($request->father_phone),
                'father_email' => strtolower($request->father_email),
                'update_profile' => 1
            ]
        );
        $request->session()->put('current_level', $request->level);
        $request->session()->put('level', $request->level);
        $request->session()->put('update_profile', 1);
        $request->session()->put('lga', $request->lga_origin);
        $request->session()->put('state', strtoupper($request->state_origin));
        DB::table('session_history')->where(['username' => session('id_number'), 'session' => session('system_session')])->update([
            'level' => strtoupper($request->level)
        ]);
        if (!empty($request->father_phone)) {
            $activeProfile = 1;
        } else {
            $activeProfile = 0;
        }
        $request->session()->put('activeProfile', $activeProfile);

        // return redirect('student-details-pdf');
        return redirect()->back()->with('success', 'Profile Updated Successfully!!!');
    }

    public function updateStudentLevel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        Student::where('user_id', session('id'))->update([
            'level' => $request->level,
            'level_flag' => 1,
        ]);
        $request->session()->put('level', $request->level);
        $request->session()->put('current_level', $request->level);
        $request->session()->put('level_flag', 1);
        // update session history if exists for current session, if not create new

        if (DB::table('session_history')->where(['username' => session('id_number'), 'session' => session('system_session')])->count() == 0) {
            DB::table('session_history')->insert([
                'username' => session('id_number'),
                'session' => session('system_session'),
                'level' => strtoupper($request->level),
                'program' => session('program'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('session_history')->where(['username' => session('id_number'), 'session' => session('system_session')])->update([
                'level' => strtoupper($request->level),
                'program' => session('program'),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Level Updated Successfully!!!');
    }

    public function deleteStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        Student::where('id', $request->id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully.');
    }

    public function resetStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        Student::where('id', $request->id)->update([
            'id_no' => '0',
            'username' => null,
        ]);
        return redirect()->back()->with('success', 'Reset successfully');
    }

    public function electionStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $status = DB::table('users')->where('id', $request->id)->select('status')->value('status');
        $id = DB::table('users')->where('id', $request->id)->select('id')->value('id');
        if ($id > 0) {
            if ($status == 1) {
                Student::where('user_id', $request->id)->update([
                    'vflag' => $request->vflag
                ]);
            } elseif ($status == 0) {
                return redirect()->back()->with('error', 'Inactive Student');
            } else {
                return redirect()->back()->with('error', 'Something went wrong');
            }
        } else {
            return redirect()->back()->with('error', 'Record Not Found!!!');
        }

        return redirect()->back()->with('success', 'Done!!!');
    }

    public function payment(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //
        $data['page'] = 'payment';
        $data['data'] = DB::table('invoices')->where(['username' => session('id')])->orderBy('session', 'DESC')->orderBy('updated_at', 'DESC')->get();
        return view('main', $data);
    }

    public function schoolFees(Request $request)
    {
        if (!session()->has('payment')) {
            return redirect('/');
        }

        $entry_session = DB::table('students')->where('user_id', session('id'))->select('session_of_entry')->value('session_of_entry');
        $system_session = DB::table('session')->where('status', '1')->value('title');
        if ($system_session == $entry_session) {
            return view('school fees');
        } else {
            return redirect('/dash');
        }
    }

    public function applicantFees(Request $request)
    {
        if (!session()->has('payment')) {
            return redirect('/');
        }

        $entry_session = DB::table('students')->where('user_id', session('id'))->select('session_of_entry')->value('session_of_entry');
        $system_session = DB::table('session')->where('status', '1')->value('title');
        $payment = DB::table('invoices')->where(['username' => session('id'), 'description' => 'POST UTME', 'session' => $system_session, 'status' => 'Paid'])->select('id')->value('id');
        // dd($payment);
        if ($payment > 0) {
            return redirect('/application');
        } else {
            return view('applicant fees');
        }
    }

    public function makePaymentt(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $flag = 0;
        // dd('test');
        $session = DB::table('session')->where('status', '1')->select('title')->value('title');
        $noo = Student::where(['user_id' => session('id'), 'session_of_entry' => $session, 'level_of_entry' => session('level_of_entry')])->select('id_no')->value('id_no');
        // dd(session('level_of_entry'));
        $std_s = Student::where(['user_id' => session('id'), 'session_of_entry' => $session, 'level_of_entry' => session('level_of_entry')])->select('session_of_entry')->value('session_of_entry');
        if ($noo == 0 && $std_s == $session) {
            // while($flag == 0){
            $data = Student::where(['user_id' => session('id'), 'session_of_entry' => $session, 'level_of_entry' => session('level_of_entry')])->select('id_format', 'department', 'session_of_entry')->get();
            foreach ($data as $row) {
                $id_format = $row->id_format;
                $department = $row->department;
                $ses = $row->session_of_entry;

                $lastId = Student::where(['session_of_entry' => $ses, 'department' => $department, 'level_of_entry' => session('level_of_entry')])->select('id_no')->orderBy('id_no', 'DESC')->limit(1)->value('id_no');
                if ($lastId < 1000 && session('level_of_entry') == '200') {
                    $lastId = 999;
                }
                $id_no = ++$lastId;
                $session = substr($ses, 2, 2);
                $lastId = str_pad($lastId, 4, '0', STR_PAD_LEFT);
                $id_number = $session . $id_format . $lastId;

                // echo $id_no;
                // die;
                $check = Student::where('username', $id_number)->select('id')->value('id');
                if ($check > 0) {
                    // dd($id_number);
                } else {
                    Student::where(['user_id' => session('id')])->where('id_no', 0)->update([
                        'username' => $id_number,
                        'id_no' => $id_no
                    ]);
                    $flag++;
                    $request->session()->put('id_no', $id_no);
                }
            }

            // }

            $request->session()->put('id_number', $id_number);
            return redirect('/dash')->with('success', $id_number . ' Just assigned to you as your ID Number');
        }
        return redirect('/dash')->with('error', 'Something Went Wrong With ID Number Generation');
    }

    public function makePayment(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $session = DB::table('session')->where('status', '1')->select('title')->value('title');
        $student = Student::where([
            'user_id' => session('id'),
            'session_of_entry' => $session,
        ])->first();

        // Skip processing if student is null
        if (!$student) {
            //dd('1');
            $request->session()->put('error', 'Record Not Found...');
            return redirect('/logout');  // or you could redirect with a message if preferred
        }

        // If student already has an ID, redirect
        if ($student->id_no != 0) {
            //dd('3');
            $request->session()->put('error', 'ID number already assigned');
            return redirect('/logout');
        }

        $data = Student::where([
            'user_id' => session('id'),
            'session_of_entry' => $session,
        ])->select('id_format', 'department', 'session_of_entry')->first();

        if (!$data) {
            //dd('2');
            $request->session()->put('error', 'Student record not found');
            return redirect('/logout');
        }

        $id_format = $data->id_format;
        $department = $data->department;
        $ses = $data->session_of_entry;

        // Get all used IDs for this department/session/level
        $usedIds = Student::where([
            'session_of_entry' => $ses,
            'department' => $department,
        ])->pluck('id_no')->toArray();

        // dd($usedIds);

        // Find the first available ID
        $id_no = 1;
        while (in_array($id_no, $usedIds)) {
            $id_no++;

            // Safety check to prevent infinite loop
            if ($id_no > 9999) {  // Adjust max limit as needed
            //dd('4');
            $request->session()->put('error', 'No available ID numbers');
                return redirect('/logout');
            }
        }

        // Format the ID number
        $sessionShort = substr($ses, 2, 2);
        $paddedId = str_pad($id_no, 4, '0', STR_PAD_LEFT);
        $id_number = $sessionShort . $id_format . $paddedId;

        // Verify ID is unique
        $exists = Student::where('username', $id_number)->exists();
        if ($exists) {
            //dd($id_number);
            $request->session()->put('error', 'Generated ID already exists ' . $id_number);
            return redirect('/logout');
        }

        // Update student record
        $updated = Student::where(['user_id' => session('id')])
            ->where('id_no', 0)
            ->update([
                'username' => $id_number,
                'id_no' => $id_no
            ]);

        if (!$updated) {
            //dd('6');
            $request->session()->put('error', 'Failed to update student record');
            return redirect('/logout');
        }

        // Store in session
        $request->session()->put('id_no', $id_no);
        $request->session()->put('id_number', $id_number);

        return redirect('/dash')->with('success', $id_number . ' assigned as your ID Number');
    }

    public function uploadStudent(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;
            $upload_type = $request->upload_type;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new StudentImport($faculty, $department, $program, $upload_type);
            Excel::import($import, $file);
            // dd(session('studentImportMsg'));
            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with(session('studentImportStatus'), session('studentImportMsg'));
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function uploadAdmitted(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;
            $upload_type = $request->upload_type;
            // dd($upload_type);

            // Load the uploaded file using Maatwebsite/Excel
            $import = new AdmittedImport($faculty, $department, $program, $upload_type);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function uploadApplicant(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;
            $session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
            // dd($upload_type);

            // Load the uploaded file using Maatwebsite/Excel
            $import = new ApplicantImport($faculty, $department, $program, $session);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function admitStudentUpload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $faculty = $request->faculty;
            $department = $request->department;
            $program = $request->program;
            $session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
            // dd($upload_type);

            // Load the uploaded file using Maatwebsite/Excel
            $import = new AdmitStudentUploadImport($faculty, $department, $program, $session);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'Admitted successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function exportUsers(Request $request)
    {
        $data = $request->all();
        unset($data['_token']);
        $filteredData = array_filter($data);
        // $query = DB::table('students')->select('username','fullname','faculty','program','state_origin','country','kin_name','kin_phone');

        $query = DB::table('students')->join('faculty', 'students.faculty', '=', 'faculty.code')->join('program', 'students.program', '=', 'program.code')->select('students.username', 'students.fullname', 'faculty.title as faculty_name', 'program.title as program_name', 'students.state_origin', 'students.country', 'students.kin_name', 'students.kin_phone');

        foreach ($filteredData as $key => $value) {
            $query->where('students.' . $key, $value);
        }

        $record = $query->get();
        return Excel::download(new UsersExport($record), 'student.xlsx');
    }

    public function exportCourses(Request $request)
    {
        $data = $request->all();
        unset($data['_token']);
        $code = $data['code'];
        // dd($code);

        $record = DB::table('student_course_registration')
            ->select('username', 'level')
            ->where('student_course_registration.code', $code)
            ->where('session', $data['session'])
            ->get();
        // dd($record);
        return Excel::download(new CoursesExport($record), $code . 'students.xlsx');
    }

    public function assignCourses()
    {
        // $student = DB::table('students')->select('program', 'level')->where(['user_id' => session('id')])->get();
        // foreach ($student as $row) {
        //     $data = DB::table('program_course_registration')->where(['program' => $this -> program, 'level' => $level])->get();
        //     foreach($data as $rows){
        //         $records['username'] = $ID;
        //         $records['code'] = $rows -> code;
        //         $records['unit'] = $rows -> unit;
        //         $records['semester'] = $rows -> semester;
        //         $records['session'] = $this -> session;
        //         $records['level'] = $rows -> level;
        //         $records['type'] = $rows -> type;
        //         try {
        //             DB::table('student_course_registration')->insert($records);
        //         } catch (QueryException $e) {
        //             if ($e->errorInfo[1] == 1062) {
        //                 DB::table('student_course_registration')->where(['username' => $ID, 'code' => $rows -> code, 'session' => $this -> session])->update($records);
        //             } else {

        //             }
        //         } catch (\Exception $e) {

        //         } finally {

        //         }
        //     }
        // }
    }

    public function add(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data = DB::table('program_course_registration')->where(['program' => $req->program, 'code' => $req->code])->limit(1)->get();
        $flag = 0;
        foreach ($data as $row) {
            $datass['username'] = $req->username;
            $datass['session'] = $req->session;
            $datass['semester'] = $row->semester;
            $datass['type'] = $row->type;
            $datass['code'] = $row->code;
            $datass['unit'] = $row->unit;
            $datass['level'] = $row->level;
            $flag = 1;
            DB::table('student_course_registration')->insert($datass);
            return redirect()->back()->with('success', 'Record Created!!!');
        }
        return redirect()->back()->with('error', 'Course record not found!!!');
    }

    public function drop(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table('student_course_registration')->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Deleted!!!');
    }

    /**
     * Admit an applicant
     */
    public function admitApplicant(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Validate input
        $request->validate([
            'applicant_id' => 'required|string|exists:applicants,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($request->applicant_id);

            // Check if applicant is eligible for admission
            if (!in_array($applicant->status, ['Rejected', 'Submitted'])) {
                return redirect()->back()->with('error', 'This applicant cannot be admitted. Current status: ' . $applicant->status);
            }

            // Update applicant status to Admitted
            $applicant->update([
                'status' => 'Admitted',
                'admission_date' => now(),
                'admission_remarks' => $request->remarks,
                'admitted_by' => session('username', 'Admin'),
            ]);

            // Log the admission action
            Log::info('Applicant admitted', [
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->fullname,
                'admitted_by' => session('username', 'Admin'),
                'remarks' => $request->remarks,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success',
                $applicant->fullname . ' has been successfully admitted to '
                    . DB::table('program')->where('code', $applicant->program)->value('title') . '!');
        } catch (Exception $e) {
            Log::error('Error admitting applicant', [
                'applicant_id' => $request->applicant_id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'An error occurred while processing the admission. Please try again.');
        }
    }

    /**
     * Clear an applicant
     */
    public function clearedApplicant(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Validate input
        $request->validate([
            'applicant_id' => 'required|string|exists:applicants,id',
            'program' => 'nullable|string|max:1000',
        ]);

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($request->applicant_id);

            // Check if applicant is eligible for admission
            if (!in_array($applicant->status, ['Admitted'])) {
                return redirect()->back()->with('error', 'This applicant cannot be admitted. Current status: ' . $applicant->status);
            }

            $user = User::updateOrCreate(
                ['username' => $applicant->username],
                [
                    'password' => Hash::make('umstad@2026'),
                    'accType' => 'Student',
                    'gender' => strtoupper($applicant->gender),
                    'name' => $applicant->fullname,
                    'status' => '0'
                ]
            );

            $f = $applicant->facultys->no;
            $d = $applicant->departments->no;
            $session = $applicant->session;

            Student::updateOrCreate(
                ['jamb_no' => $applicant->username],
                [
                    'user_id' => $user->id,
                    'last_name' => $applicant->surname,
                    'first_name' => $applicant->first_name,
                    'other_name' => $applicant->other_name,
                    'program' => $request->program,
                    'department' => $applicant->department,
                    'faculty' => $applicant->faculty,
                    'id_format' => '/' . str_pad($f, 2, '0', STR_PAD_LEFT) . '/' . str_pad($d, 2, '0', STR_PAD_LEFT) . '/',
                    'gender' => strtoupper($applicant->gender),
                    'session_of_entry' => $session,
                    'level_of_entry' => strtoupper($request->level),
                    'level' => strtoupper($request->level),
                    'mode_of_entry' => strtoupper($applicant->mode),
                    'state_origin' => $applicant->state,
                    'lga_origin' => $applicant->lga,
                    'contact_phone' => $applicant->phone,
                    'contact_email' => $applicant->email,
                    'contact_address' => $applicant->address,
                    'fullname' => $applicant->fullname,
                    'date_of_birth' => $applicant->dob,
                    'place_of_birth' => $applicant->pob,
                    'country' => $applicant->nationality,
                    'marital_status' => $applicant->marital_status,
                    'religion' => $applicant->religion,
                    'kin_name' => $applicant->n_name,
                    'kin_email' => $applicant->n_email,
                    'kin_phone' => $applicant->n_phone,
                    'kin_address' => $applicant->n_address,
                    'sponsor_name' => $applicant->s_name,
                    'sponsor_phone' => $applicant->s_phone,
                    'sponsor_address' => $applicant->s_address,
                    'status' => '1',
                    'school_fee' => '1'
                ]
            );

            // Update applicant status to Admitted
            $applicant->update([
                'clearance' => 'Cleared',
                'cleared_at' => now(),
                'cleared_by' => session('username', 'Admin'),
            ]);

            // Log the admission action
            Log::info('Applicant admitted', [
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->fullname,
                'admitted_by' => session('username', 'Admin'),
                'remarks' => $request->remarks,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success',
                $applicant->fullname . ' has been successfully cleared to '
                    . DB::table('program')->where('code', $request->program)->value('title') . '!');
        } catch (Exception $e) {
            Log::error('Error Clearing applicant', [
                'applicant_id' => $request->applicant_id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'An error occurred while processing the admission. Please try again.');
        }
    }

    /**
     * Reject an applicant
     */
    public function rejectClearingApplicant(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Validate input
        $request->validate([
            'applicant_id' => 'required|string|exists:applicants,id',
            'rejection_reason' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($request->applicant_id);

            // Check if applicant is eligible for rejection
            if (!in_array($applicant->status, ['Admitted', 'Submitted'])) {
                return redirect()->back()->with('error', 'This applicant cannot be rejected. Current status: ' . $applicant->status);
            }

            // Update applicant status to Rejected
            $applicant->update([
                'clearance' => 'Rejected',
                'rejection_date' => now(),
                'rejection_reason' => $request->rejection_reason,
                'rejection_remarks' => $request->remarks,
                'rejected_by' => session('username', 'Admin'),
            ]);

            // Log the rejection action
            Log::info('Applicant rejected', [
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->fullname,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => session('username', 'Admin'),
                'remarks' => $request->remarks,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success',
                $applicant->fullname . "'s application has been rejected. Reason: " . $request->rejection_reason);
        } catch (Exception $e) {
            Log::error('Error rejecting applicant', [
                'applicant_id' => $request->applicant_id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'An error occurred while processing the rejection. Please try again.');
        }
    }

    /**
     * Reject an applicant
     */
    public function rejectApplicant(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Validate input
        $request->validate([
            'applicant_id' => 'required|string|exists:applicants,id',
            'rejection_reason' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($request->applicant_id);

            // Check if applicant is eligible for rejection
            if (!in_array($applicant->status, ['Admitted', 'Submitted'])) {
                return redirect()->back()->with('error', 'This applicant cannot be rejected. Current status: ' . $applicant->status);
            }

            // Update applicant status to Rejected
            $applicant->update([
                'status' => 'Rejected',
                'rejection_date' => now(),
                'rejection_reason' => $request->rejection_reason,
                'rejection_remarks' => $request->remarks,
                'rejected_by' => session('username', 'Admin'),
            ]);

            // Log the rejection action
            Log::info('Applicant rejected', [
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->fullname,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => session('username', 'Admin'),
                'remarks' => $request->remarks,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success',
                $applicant->fullname . "'s application has been rejected. Reason: " . $request->rejection_reason);
        } catch (Exception $e) {
            Log::error('Error rejecting applicant', [
                'applicant_id' => $request->applicant_id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'An error occurred while processing the rejection. Please try again.');
        }
    }

    /**
     * View detailed applicant information
     */
    public function viewApplicantDetails($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            // Find the applicant
            $applicant = Applicant::findOrFail($id);

            // Get related data
            $jamb = Jamb::where('user_id', $applicant->user_id)->first();
            $deQualification = DEQualification::where('user_id', $applicant->user_id)->first();
            $ssce = Ssce::where('user_id', $applicant->user_id)->get();
            $ssceResults = SsceResult::where('user_id', $applicant->user_id)->get();
            $documents = DocumentUpload::where('user_id', $applicant->user_id)->get();

            // Get program details
            $program = DB::table('program')->where('code', $applicant->program)->first();
            $faculty = DB::table('faculty')->where('code', $applicant->faculty)->first();
            $department = DB::table('department')->where('code', $applicant->department)->first();

            $data = [
                'page' => 'applicant-details',
                'applicant' => $applicant,
                'jamb' => $jamb,
                'deQualification' => $deQualification,
                'ssce' => $ssce,
                'ssceResults' => $ssceResults,
                'documents' => $documents,
                'program' => $program,
                'faculty' => $faculty,
                'department' => $department
            ];

            return view('main', $data);
        } catch (Exception $e) {
            Log::error('Error viewing applicant details', [
                'applicant_id' => $id,
                'error' => $e->getMessage(),
                'admin' => session('username', 'Admin')
            ]);

            return redirect()->back()->with('error', 'Applicant not found or an error occurred.');
        }
    }

    /**
     * Download applicant details as PDF
     */
    public function downloadApplicantPdf($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            // Find the applicant and related data
            $applicant = Applicant::findOrFail($id);

            // Get JAMB information if UTME applicant
            $jambData = [];
            if (strtoupper($applicant->mode) === 'UTME') {
                $jambData = DB::table('jamb')->where('user_id', $applicant->user_id)->get();
            }

            // Get DE qualification if DE applicant
            $deQualification = null;
            if (strtoupper($applicant->mode) === 'DE') {
                $deQualification = DB::table('de_qualifications')->where('user_id', $applicant->user_id)->first();
            }

            // Get SSCE data - exactly matching dashboard method
            $ssceData = DB::table('ssce')->where('user_id', $applicant->user_id)->get();
            $ssceResults = [];
            \Log::info('SSCE Data for user ' . $applicant->user_id, ['count' => $ssceData->count(), 'data' => $ssceData->toArray()]);

            foreach ($ssceData as $ssce) {
                $results = DB::table('ssce_results')->where('ssce_id', $ssce->id)->get();
                $ssceResults[$ssce->id] = $results;
                \Log::info('SSCE Results for record ' . $ssce->id, ['count' => $results->count(), 'data' => $results->toArray()]);
            }

            $documents = DocumentUpload::where('user_id', $applicant->user_id)
                ->where('applicant_id', $applicant->id)
                ->get();

            // Get program, faculty, and department names
            $program = DB::table('program')->where('code', $applicant->program)->value('title');
            $faculty = DB::table('faculty')->where('code', $applicant->faculty)->value('title');
            $department = DB::table('department')->where('code', $applicant->department)->value('title');

            // Get logo as base64
            $logoPath = public_path('uploads/logo.png');
            $logoData = '';
            if (file_exists($logoPath)) {
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoData = 'data:image/' . $logoType . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            // Get applicant's passport photo if exists
            $passportPhoto = null;
            $passportDocument = $documents->firstWhere('doc_type', 'passport_photograph');
            if ($passportDocument) {
                $passportPhoto = storage_path('app/public/' . $passportDocument->file_path);
            }

            $data = [
                'applicant' => (object) array_merge($applicant->toArray(), [
                    'passport_photo' => $passportPhoto
                ]),
                'jambData' => $jambData,
                'deData' => $deQualification,
                'ssceData' => $ssceData,
                'ssceResults' => $ssceResults,
                'documents' => $documents,
                'program' => $program,
                'faculty' => $faculty,
                'department' => $department,
                'title' => 'Applicant Details - ' . $applicant->fullname,
                'logoData' => $logoData
            ];

            // Generate PDF
            $dompdf = new Dompdf();
            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'debugKeepTemp' => true,
                'chroot' => base_path('public'),
                'logOutputFile' => storage_path('logs/dompdf.html'),
                'tempDir' => storage_path('app/dompdf'),
                'fontCache' => storage_path('fonts/'),
                'fontDir' => storage_path('fonts/')
            ]);
            $dompdf->setOptions($options);

            // Set the protocol and host for relative URLs
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $dompdf->getOptions()->set('chroot', [
                public_path(),
                base_path('public'),
                base_path('resources/views'),
                base_path('storage')
            ]);

            // Load the view and render it as HTML
            $html = View::make('Admin.applicant-pdf', $data)->render();

            // Load HTML to Dompdf
            $dompdf->loadHtml($html);

            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the HTML as PDF
            $dompdf->render();

            // Generate a unique filename
            $filename = 'applicant_' . $applicant->username . '_' . now()->format('Ymd_His') . '.pdf';

            // Stream the PDF to the browser (open in browser instead of download)
            return $dompdf->stream($filename, [
                'Attachment' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating applicant PDF', [
                'applicant_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get applicant statistics for admin dashboard
     */
    public function getApplicantStats()
    {
        return [
            'total' => Applicant::count(),
            'pending' => Applicant::where('status', 'Pending')->count(),
            'submitted' => Applicant::where('status', 'Submitted')->count(),
            'admitted' => Applicant::where('status', 'Admitted')->count(),
            'rejected' => Applicant::where('status', 'Rejected')->count(),
        ];
    }

    /**
     * Display SIWES page for students
     */
    public function siwes()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'siwes']);
    }

    /**
     * Save SIWES information
     */
    public function saveSiwes(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'period_of_attachment_from' => 'required|date',
                'period_of_attachment_to' => 'required|date',
                'placement_of_address' => 'required|string',
                'bank_code' => 'required|string',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'sort_code' => 'nullable|string',
                'siwes_year' => 'required|string',
                'student_email_address' => 'required|email',
                'remarks' => 'nullable|string',
            ]);

            // Get student username from session
            $username = session('id_number');

            // Check if SIWES record already exists for this student
            $existing = DB::table('siwes')->where('username', $username)->first();

            if ($existing) {
                // Update existing record
                DB::table('siwes')->where('username', $username)->update([
                    'period_of_attachment_from' => $validated['period_of_attachment_from'],
                    'period_of_attachment_to' => $validated['period_of_attachment_to'],
                    'placement_of_address' => strtoupper($validated['placement_of_address']),
                    'bank_code' => $validated['bank_code'],
                    'bank_name' => strtoupper($validated['bank_name']),
                    'account_number' => $validated['account_number'],
                    'sort_code' => $validated['sort_code'],
                    'siwes_year' => $validated['siwes_year'],
                    'student_email_address' => strtolower($validated['student_email_address']),
                    'remarks' => strtoupper($validated['remarks']),
                    'updated_at' => now(),
                ]);
            } else {
                // Insert new record with foreign key to students table
                DB::table('siwes')->insert([
                    'username' => $username,
                    'period_of_attachment_from' => $validated['period_of_attachment_from'],
                    'period_of_attachment_to' => $validated['period_of_attachment_to'],
                    'placement_of_address' => strtoupper($validated['placement_of_address']),
                    'bank_code' => $validated['bank_code'],
                    'bank_name' => strtoupper($validated['bank_name']),
                    'account_number' => $validated['account_number'],
                    'sort_code' => $validated['sort_code'],
                    'siwes_year' => $validated['siwes_year'],
                    'student_email_address' => strtolower($validated['student_email_address']),
                    'remarks' => strtoupper($validated['remarks']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'SIWES information saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error saving SIWES information: ' . $e->getMessage());
        }
    }

    /**
     * Download SIWES information as PDF
     */
    public function downloadSiwes($id = null)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            if ($id) {
                // Admin downloading for a specific student
                $student = DB::table('students')->where('id', $id)->first();
                $siwesData = DB::table('siwes')->where('username', $student->username)->first();
            } else {
                // Student downloading their own SIWES
                $student = Student::where('user_id', session('id'))->first();
                $siwesData = DB::table('siwes')->where('username', $student->username ?? session('id_number'))->first();
            }

            if (!$siwesData) {
                return redirect()->back()->with('error', 'No SIWES information found.');
            }

            $data = [
                'student' => $student,
                'siwesData' => $siwesData,
            ];

            // Generate PDF using Dompdf
            $pdf = new \Dompdf\Dompdf();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $pdf->setOptions($options);

            // Set chroot to allow DomPDF to access public files
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $pdf->getOptions()->set('chroot', [
                public_path(),
                base_path('public'),
                base_path('resources/views'),
                base_path('storage')
            ]);

            $html = view('pdf.siwes-pdf', $data)->render();
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            $filename = 'SIWES_' . $student->username . '_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->stream($filename, ['Attachment' => false]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function adminSiwes(Request $request)
    {
        $facultyFilter = $request->get('faculty', '');
        $departmentFilter = $request->get('department', '');
        $levelFilter = $request->get('level', '');
        $programFilter = $request->get('program', '');
        $siwesYearFilter = $request->get('siwes_year', '');

        $query = DB::table('siwes')
            ->join('students', 'siwes.username', '=', DB::raw('students.username COLLATE utf8mb4_unicode_ci'))
            ->join('program', 'students.program', '=', DB::raw('program.code COLLATE utf8mb4_unicode_ci'))
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.other_name',
                'students.username as matric_number',
                'program.title as course_of_study',
                'students.level as level_of_study',
                'siwes.siwes_year',
                'siwes.student_email_address'
            );

        if ($facultyFilter && $facultyFilter != 'all') {
            $query->where('students.faculty', $facultyFilter);
        }
        if ($departmentFilter && $departmentFilter != 'all') {
            $query->where('students.department', $departmentFilter);
        }
        if ($levelFilter && $levelFilter != 'all') {
            $query->where('students.level', $levelFilter);
        }
        if ($programFilter && $programFilter != 'all') {
            $query->where('students.program', $programFilter);
        }
        if ($siwesYearFilter && $siwesYearFilter != 'all') {
            $query->where('siwes.siwes_year', $siwesYearFilter);
        }
        if(session('appointment') == 'SIWES DEPT'){
            $query->where('students.department', session('department'));
        }

        $data['data'] = $query->orderBy('students.last_name')->paginate(100);
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['siwesYears'] = DB::table('siwes')->select('siwes_year')->distinct()->orderBy('siwes_year', 'desc')->get();

        return view('main', ['page' => 'admin siwes', 'faculty' => $data['faculty'], 'data' => $data['data'], 'siwesYears' => $data['siwesYears']]);
    }

    public function adminSiwesExport(Request $request)
    {
        $facultyFilter = $request->get('faculty', '');
        $departmentFilter = $request->get('department', '');
        $levelFilter = $request->get('level', '');
        $programFilter = $request->get('program', '');
        $siwesYearFilter = $request->get('siwes_year', '');

        $query = DB::table('siwes')
            ->join('students', 'siwes.username', '=', DB::raw('students.username COLLATE utf8mb4_unicode_ci'))
            ->join('program', 'students.program', '=', DB::raw('program.code COLLATE utf8mb4_unicode_ci'))
            ->select(
                'students.first_name',
                'students.last_name',
                'students.other_name',
                'students.username as matric_number',
                'program.title as course_of_study',
                'students.level as level_of_study',
                'siwes.siwes_year',
                'siwes.student_email_address'
            );

        if ($facultyFilter && $facultyFilter != 'all' && session('appointment') != 'SIWES DEPT') {
            $query->where('students.faculty', $facultyFilter);
        }
        if ($departmentFilter && $departmentFilter != 'all' && session('appointment') != 'SIWES DEPT') {
            $query->where('students.department', $departmentFilter);
        }
        if ($levelFilter && $levelFilter != 'all') {
            $query->where('students.level', $levelFilter);
        }
        if ($programFilter && $programFilter != 'all' && session('appointment') != 'SIWES DEPT') {
            $query->where('students.program', $programFilter);
        }
        if ($siwesYearFilter && $siwesYearFilter != 'all') {
            $query->where('siwes.siwes_year', $siwesYearFilter);
        }
        if(session('appointment') == 'SIWES DEPT'){
            $query->where('students.department', session('department'));
        }

        $siwesData = $query->orderBy('students.last_name')->get();

        $filename = 'SIWES_Master_List_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($siwesData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S/NO', 'NAME_OF_STUDENT', 'MATRIC_NUMBER', 'COURSE_OF_STUDY', 'LEVEL_OF_STUDY', 'SIWES_YEAR', 'STUDENT_EMAIL_ADDRESS']);

            $counter = 1;
            foreach ($siwesData as $student) {
                fputcsv($file, [
                    $counter++,
                    strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name),
                    strtoupper($student->matric_number),
                    strtoupper($student->course_of_study),
                    $student->level_of_study,
                    $student->siwes_year,
                    $student->student_email_address
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminSiwesView($id)
    {
        $siwes = DB::table('siwes')
            ->join('students', 'siwes.username', '=', DB::raw('students.username COLLATE utf8mb4_unicode_ci'))
            ->join('program', 'students.program', '=', DB::raw('program.code COLLATE utf8mb4_unicode_ci'))
            ->where('students.id', $id)
            ->select(
                'siwes.*',
                'students.*',
                'program.title as course_of_study',
                'students.username as matric_number',
                'students.picture'
            )
            ->first();

        if (!$siwes) {
            return redirect()->back()->with('error', 'SIWES record not found');
        }

        $page = 'admin siwes view';
        return view('main', compact('page', 'siwes'));
    }
}
