@php
    use Illuminate\Support\Facades\DB;

    // Get current applicant data
    $applicant = DB::table('applicants')->where('user_id', session('id'))->first();

    if (!$applicant) {
        return redirect('/')->with('error', 'Applicant record not found.');
    }
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'UNIMAID') }} - Application Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="University Application Form" />
    <meta name="keywords" content="application, university, form">
    <meta name="author" content="UNIMAID" />
    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .application-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .application-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .application-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .application-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .application-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .application-body {
            padding: 2rem;
        }

        .section-title {
            color: #2d3748;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            position: relative;
        }

        .section-title::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .form-section {
            margin-bottom: 3rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
            width: 100%;
        }

        /* Desktop and tablet styles */
        @media (min-width: 768px) {
            .form-row {
                flex-direction: row;
                gap: 20px;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-label.required::after {
            content: ' *';
            color: #e53e3e;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .readonly-field {
            background-color: #f7fafc;
            border-color: #cbd5e0;
            color: #4a5568;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background-color: #f0fff4;
            color: #22543d;
            border-left: 4px solid #38a169;
        }

        .alert-danger {
            background-color: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }

        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        }

        .ssce-sitting:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .sitting-title {
            color: #495057;
            font-weight: 600;
            margin: 0;
        }

        .results-sitting {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
            margin-top: 15px;
        }

        .result-row {
            transition: all 0.2s ease;
        }

        .result-row:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
            transform: translateX(2px);
        }

        /* Enhanced button styling */
        .btn-outline-primary {
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-danger {
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        /* JAMB Subject Layout */
        .jamb-subject-select {
            font-size: 0.95rem;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .form-section {
                padding: 15px;
                margin-bottom: 15px;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .results-sitting {
                padding: 10px;
            }

            .ssce-sitting {
                padding: 15px;
            }

            .result-row {
                margin-bottom: 10px;
                padding: 5px;
                border-radius: 6px;
                border: 1px solid #e9ecef;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .search-filter {
                font-size: 0.85rem;
                padding: 8px;
            }

            /* Stack form elements on mobile */
            .form-row {
                gap: 10px;
            }

            .choices {
                // Choices.js will handle appearance

                font-size: 0.9rem;
            }
        }

        /* Small mobile devices */
        @media (max-width: 576px) {
            .container {
                padding: 5px;
            }

            .form-section {
                padding: 10px;
            }

            .section-title {
                font-size: 1.1rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }

        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 4px;
            border-radius: 2px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .application-container {
                padding: 0 0.5rem;
                width: 100% !important;
                max-width: 100vw !important;
            }

            .application-card {
                padding: 0;
                width: 100% !important;
                max-width: 100vw !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .container {
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 100% !important;
                max-width: 100vw !important;
            }

            body {
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }

            html {
                overflow-x: hidden !important;
            }

            .application-body {
                padding: 1rem;
            }

            .form-section {
                padding: 0.5rem;
            }
        }

        @media (max-width: 576px) {

            .ssce-sitting .table,
            .form-group .table {
                font-size: 0.92rem;
            }

            .ssce-sitting .table th,
            .ssce-sitting .table td,
            .form-group .table th,
            .form-group .table td {
                padding: 0.35rem 0.35rem;
                font-size: 0.92rem;
            }

            .ssce-sitting .form-control,
            .form-group .form-control,
            .ssce-sitting .form-select,
            .form-group .form-select {
                font-size: 0.95rem;
                padding: 0.25rem 0.5rem;
                height: 2.1rem;
            }

            .ssce-sitting .form-label,
            .form-group .form-label {
                font-size: 0.95rem;
            }

            .ssce-sitting .section-title,
            .form-group .section-title {
                font-size: 1.1rem;
            }

            .ssce-sitting .result-row,
            .form-group .result-row {
                margin-bottom: 0.3rem;
            }

            .ssce-sitting,
            .form-group {
                margin-bottom: 0.7rem;
            }
        }

        @media (max-width: 576px) {

            .results-sitting .row,
            .results-sitting .form-control,
            .results-sitting label,
            .results-sitting .form-control-plaintext {
                font-size: 0.85rem !important;
            }

            .results-sitting .form-control,
            .results-sitting .form-control-plaintext {
                height: 1.7rem !important;
                padding: 0.15rem 0.3rem !important;
            }

            .results-sitting label {
                margin-bottom: 0.1rem !important;
            }

            .results-sitting .row.mb-3 {
                margin-bottom: 0.3rem !important;
            }
        }

        /* Document Upload Mobile Optimizations */
        .document-upload-container {
            padding: 0;
        }

        @media (max-width: 768px) {
            .document-upload-container .form-group {
                margin-bottom: 1rem;
            }

            .document-upload-container .card {
                border-radius: 10px;
            }

            .document-upload-container .file-info {
                min-width: 0;
                flex: 1;
            }

            .document-upload-container .file-actions {
                flex-shrink: 0;
                margin-left: auto;
            }

            .document-upload-container .btn-sm {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
                min-width: 28px;
                height: 28px;
            }

            .document-upload-container .file-icon i {
                font-size: 16px !important;
            }

            .document-upload-container .card-body {
                padding: 0.75rem !important;
            }

            .document-upload-container .img-fluid {
                max-height: 100px !important;
            }

            /* Better touch targets for mobile */
            .document-upload-container .form-control {
                font-size: 16px;
                /* Prevents zoom on iOS */
                padding: 0.75rem;
            }

            .document-upload-container .form-label {
                font-size: 0.95rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            /* Responsive flex layout for file preview */
            .document-upload-container .d-flex.align-items-center {
                gap: 0.5rem;
            }

            /* Stack file actions vertically on very small screens */
            @media (max-width: 480px) {
                .document-upload-container .file-actions {
                    flex-direction: column;
                    gap: 0.25rem;
                }

                .document-upload-container .file-actions .btn {
                    width: 100%;
                    min-width: 40px;
                }
            }
        }

        /* Improved tooltips on mobile */
        @media (max-width: 768px) {
            [data-bs-toggle="tooltip"] {
                position: relative;
            }
        }
    </style>

</head>

<body class="bg-light bg-gradient">
    <!-- Hero Header -->
    <div class="hero-header mb-2 p-0 bg-transparent">
        <div class="container">
            <div class="card shadow-lg rounded-4 border-0 overflow-hidden bg-white position-relative"
                style="background: linear-gradient(135deg, #f8fafc 60%, #e7e9fb 100%);">
                <div class="d-flex flex-column flex-md-row align-items-center p-2 p-md-4 gap-1 gap-md-3">
                    <div class="flex-shrink-0 text-center mb-1 mb-md-0">
                        <img src="{{ url('uploads/logo.png') }}" alt="UNIMAID Logo"
                            class="rounded-circle shadow bg-white"
                            style="width: 80px; height: 80px; border: 3px solid #fff;">
                    </div>
                    <div class="flex-grow-1 text-center text-md-start">
                        <h3 class="display-6 fw-bold mb-1 text-center" style="color:#4e54c8;letter-spacing:0.01em;">
                            UNIMAID POST-UTME</h3>
                    </div>
                    <div class="flex-shrink-0 align-self-md-start align-self-center mt-1 mt-md-0">
                        <a href="/logout"
                            class="btn btn-outline-danger btn-sm px-3 py-1 rounded-pill fw-semibold d-inline-flex align-items-center">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
                <!-- SVG Decorative Wave -->
                <div style="height:25px;overflow:hidden;line-height:0;">
                    <svg viewBox="0 0 1440 90" fill="none" xmlns="http://www.w3.org/2000/svg" width="100%"
                        height="25">
                        <path fill="#f8fafc" d="M0,0 C480,90 960,0 1440,90 L1440,0 L0,0 Z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div
                    class="application-card shadow-lg rounded-4 bg-white mb-4 p-4 p-md-5 animate__animated animate__fadeIn">
                    <div class="text-center mb-4">
                        <div class="application-body">
                            <!-- Display Messages -->
                            @if (session('success'))
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form action="/application/personal-info" method="POST" enctype="multipart/form-data"
                                id="personalInfoForm">
                                @csrf
                                <div class="form-section">
                                    <h3 class="section-title"><i class="fas fa-user me-2"></i>Personal Information</h3>

                                    <!-- Pre-filled Data Display -->
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> Some information below has been pre-filled from your
                                        uploaded data.
                                        Please review and complete the missing fields.
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="surname">Surname</label>
                                            <input type="text" class="form-control readonly-field" id="surname"
                                                name="surname" value="{{ $applicant->surname }}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="first_name">First Name</label>
                                            <input type="text" class="form-control readonly-field" id="first_name"
                                                name="first_name" value="{{ $applicant->first_name }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="other_name">Other Names</label>
                                            <input type="text" class="form-control readonly-field" id="other_name"
                                                name="other_name" value="{{ $applicant->other_name }}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="gender">Gender</label>
                                            <input type="text" class="form-control readonly-field" id="gender"
                                                name="gender" value="{{ $applicant->gender }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                value="{{ old('phone', $applicant->phone ?? '') }}"
                                                placeholder="+234.." required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ old('email', $applicant->email ?? '') }}"
                                                placeholder="your.email@example.com" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="dob">Date of Birth</label>
                                            <input type="date" class="form-control" id="dob" name="dob"
                                                value="{{ old('dob', $applicant->dob ?? '') }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="pob">Place of Birth</label>
                                            <input type="text" class="form-control" id="pob" name="pob"
                                                value="{{ old('pob', $applicant->pob ?? '') }}"
                                                placeholder="Enter place of birth">
                                        </div>
                                    </div>

                                    <div class="form-row">

                                        <div class="form-group">
                                            <label class="form-label" for="marital_status">Marital Status</label>
                                            <select class="form-control" id="marital_status" name="marital_status">
                                                <option value="">Select status</option>
                                                <option value="Single"
                                                    {{ old('marital_status', $applicant->marital_status ?? '') == 'Single' ? 'selected' : '' }}>
                                                    Single</option>
                                                <option value="Married"
                                                    {{ old('marital_status', $applicant->marital_status ?? '') == 'Married' ? 'selected' : '' }}>
                                                    Married</option>
                                                <option value="Divorced"
                                                    {{ old('marital_status', $applicant->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>
                                                    Divorced</option>
                                                <option value="Widowed"
                                                    {{ old('marital_status', $applicant->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>
                                                    Widowed</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="religion">Religion</label>
                                            <select class="form-control" id="religion" name="religion">
                                                <option value="">Select religion</option>
                                                <option value="Christianity"
                                                    {{ old('religion', $applicant->religion ?? '') == 'Christianity' ? 'selected' : '' }}>
                                                    Christianity</option>
                                                <option value="Islam"
                                                    {{ old('religion', $applicant->religion ?? '') == 'Islam' ? 'selected' : '' }}>
                                                    Islam</option>
                                                <option value="Traditional"
                                                    {{ old('religion', $applicant->religion ?? '') == 'Traditional' ? 'selected' : '' }}>
                                                    Traditional</option>
                                                <option value="Other"
                                                    {{ old('religion', $applicant->religion ?? '') == 'Other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="nationality">Nationality</label>
                                            <select class="form-control" id="nationality" name="nationality">
                                                <option value="Nigerian" selected>Nigerian</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="state">State of Origin</label>
                                            <input type="text" class="form-control readonly-field" id="state"
                                                name="state" value="{{ $applicant->state }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="lga">Local Government
                                                Area</label>
                                            <input type="text" class="form-control" id="lga" name="lga"
                                                value="{{ old('lga', $applicant->lga ?? '') }}"
                                                placeholder="Enter LGA" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                value="{{ old('city', $applicant->city ?? '') }}"
                                                placeholder="Enter city">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label required" for="address">Permanent Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"
                                            placeholder="Enter your full permanent address" required>{{ old('address', $applicant->address ?? '') }}</textarea>
                                    </div>
                                    <!-- Personal Information Save Button -->
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-success" id="savePersonalBtn">
                                            <i class="fas fa-save me-1"></i> Save Personal Information
                                        </button>
                                    </div>
                            </form>

                            <!-- Remaining Application Sections -->
                            <form action="/submit-application" method="POST" enctype="multipart/form-data"
                                id="applicationForm">
                                @csrf

                                <!-- Section 2: Academic Information -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="fas fa-graduation-cap me-2"></i>Academic
                                        Information</h3>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="faculty">Faculty</label>
                                            <input type="text" class="form-control readonly-field" id="faculty"
                                                name="faculty"
                                                value="{{ DB::table('faculty')->where('code', $applicant->faculty)->value('title') ?? $applicant->faculty }}"
                                                readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="department">Department</label>
                                            <input type="text" class="form-control readonly-field" id="department"
                                                name="department"
                                                value="{{ DB::table('department')->where('code', $applicant->department)->value('title') ?? $applicant->department }}"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="program">Program</label>
                                            <input type="text" class="form-control readonly-field" id="program"
                                                name="program"
                                                value="{{ DB::table('program')->where('code', $applicant->program)->value('title') ?? $applicant->program }}"
                                                readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required" for="mode_of_entry">Mode of
                                                Entry</label>
                                            <input type="text" class="form-control readonly-field"
                                                value="{{ $applicant->mode }}" readonly>
                                        </div>
                                    </div>
                                    <!-- SSCE and other sections remain unchanged. Place DE section after SSCE. -->
                                </div>

                                <!-- Section 3: JAMB Information -->
                                @if (strtoupper($applicant->mode) !== 'DE')
                                    @php
                                        // Load existing JAMB data
                                        $jambRecords = \App\Models\Jamb::where('user_id', session('id'))->get();
                                        $jambSubjects = [];
                                        $jambScores = [];
                                        foreach ($jambRecords as $index => $record) {
                                            $jambSubjects[$index + 1] = $record->subject;
                                            $jambScores[$index + 1] = $record->score;
                                        }
                                    @endphp
                                    <div class="form-section">
                                        <h3 class="section-title"><i class="fas fa-clipboard-list me-2"></i>JAMB
                                            Information</h3>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label class="form-label required" for="jamb_reg_number">JAMB
                                                    Reg Number</label>
                                                <input type="text" style="text-align: center;"
                                                    class="form-control readonly-field" id="jamb_reg_number"
                                                    name="jamb_reg_number" value="{{ $applicant->username }}"
                                                    readonly required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label required" for="jamb_year">JAMB Year</label>
                                                <input type="text" style="text-align: center;"
                                                    class="form-control readonly-field" id="jamb_year"
                                                    name="jamb_year" value="{{ $applicant->session }}" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="jamb_score">JAMB Score</label>
                                                <input type="text" style="text-align: center;"
                                                    class="form-control readonly-field" id="jamb_score"
                                                    name="jamb_score" value="{{ $applicant->score }}" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label required">JAMB Subjects & Scores</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 45px;">S/N</th>
                                                            <th>Subject</th>
                                                            <th style="width: 120px;">Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for ($i = 1; $i <= 4; $i++)
                                                            <tr>
                                                                <td class="text-center fw-bold">{{ $i }}
                                                                </td>
                                                                <td>
                                                                    {{ $jambSubjects[$i] }}
                                                                </td>
                                                                <td>
                                                                    {{ $jambScores[$i] }}
                                                                </td>
                                                            </tr>
                                                        @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- SSCE Information Form -->
                                <form action="/application/ssce-info" method="POST" enctype="multipart/form-data"
                                    id="ssceInfoForm">
                                    @csrf

                                    <!-- Section 4: SSCE Information -->
                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-certificate me-2"></i>SSCE Information
                                            <small class="text-muted ms-2">(You can add up to 2 sittings)</small>
                                        </h3>

                                        <div id="ssce-sittings-container">
                                            <!-- First Sitting -->
                                            <div class="ssce-sitting" data-sitting="1">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="sitting-title"><i class="fas fa-scroll me-1"></i>First
                                                        Sitting</h5>
                                                </div>

                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label class="form-label required" for="ssce_type_1">SSCE
                                                            Type</label>
                                                        <select class="form-control" id="ssce_type_1"
                                                            name="ssce_type_1" required>
                                                            <option value="">Select SSCE Type</option>
                                                            <option value="WAEC"
                                                                {{ $ssceData && $ssceData->type == 'WAEC' ? 'selected' : '' }}>
                                                                WAEC</option>
                                                            <option value="NECO"
                                                                {{ $ssceData && $ssceData->type == 'NECO' ? 'selected' : '' }}>
                                                                NECO</option>
                                                            <option value="NABTEB"
                                                                {{ $ssceData && $ssceData->type == 'NABTEB' ? 'selected' : '' }}>
                                                                NABTEB</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label required" for="ssce_year_1">SSCE
                                                            Year</label>
                                                        <select class="form-control" id="ssce_year_1"
                                                            name="ssce_year_1" required>
                                                            <option value="">Select Year</option>
                                                            @for ($year = date('Y'); $year >= 2015; $year--)
                                                                <option value="{{ $year }}"
                                                                    {{ $ssceData && $ssceData->year == $year ? 'selected' : '' }}>
                                                                    {{ $year }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label class="form-label required"
                                                            for="ssce_reg_number_1">SSCE Registration Number</label>
                                                        <input type="text" class="form-control"
                                                            id="ssce_reg_number_1" name="ssce_reg_number_1"
                                                            placeholder="Enter SSCE registration number"
                                                            value="{{ $ssceData ? $ssceData->number : '' }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label required"
                                                            for="ssce_center_1">Examination Center</label>
                                                        <input type="text" class="form-control" id="ssce_center_1"
                                                            name="ssce_center_1"
                                                            placeholder="Enter examination center"
                                                            value="{{ $ssceData ? $ssceData->center_name : '' }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-outline-primary"
                                                id="add-sitting-btn" onclick="addSsceSitting()">
                                                <i class="fas fa-plus me-2"></i>Add Second Sitting
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Section 5: SSCE Results -->
                                    <div class="form-section">
                                        <h3 class="section-title"><i class="fas fa-list-alt me-2"></i>SSCE Results
                                        </h3>
                                        <p class="text-muted mb-3">Enter your SSCE subject results (minimum 5 subjects
                                            with C6 and above)</p>

                                        <div id="ssce-results-container">
                                            <!-- Results for First Sitting -->
                                            <div class="results-sitting" data-sitting="1">
                                                <h5 class="mb-3"><i class="fas fa-list me-1"></i>First Sitting
                                                    Results</h5>

                                                <div class="row mb-3">
                                                    <div class="col-2 col-md-1">
                                                        <label
                                                            class="form-label text-center"><strong>S/N</strong></label>
                                                    </div>
                                                    <div class="col-6 col-md-8">
                                                        <label class="form-label"><strong>Subject</strong></label>
                                                    </div>
                                                    <div class="col-4 col-md-3">
                                                        <label class="form-label"><strong>Grade</strong></label>
                                                    </div>
                                                </div>

                                                @for ($i = 1; $i <= 9; $i++)
                                                    <div class="row mb-3 result-row"
                                                        id="result-row-1-{{ $i }}">
                                                        <div class="col-2 col-md-1">
                                                            <div class="form-control-plaintext text-center fw-bold">
                                                                {{ $i }}</div>
                                                        </div>
                                                        <div class="col-6 col-md-8">
                                                            <select class="form-control tom-select"
                                                                id="subject_1_{{ $i }}"
                                                                name="subject_1_{{ $i }}"
                                                                {{ $i <= 5 ? 'required' : '' }}>
                                                                @php
                                                                    $savedResult = isset($ssceResults[$i - 1])
                                                                        ? $ssceResults[$i - 1]
                                                                        : null;
                                                                    $savedSubject = $savedResult
                                                                        ? $savedResult->subject
                                                                        : '';
                                                                @endphp
                                                                <option value="">Select Subject</option>
                                                                <option value="English Language"
                                                                    {{ $savedSubject == 'English Language' ? 'selected' : '' }}>
                                                                    English Language</option>
                                                                <option value="Mathematics"
                                                                    {{ $savedSubject == 'Mathematics' ? 'selected' : '' }}>
                                                                    Mathematics</option>
                                                                <option value="Physics"
                                                                    {{ $savedSubject == 'Physics' ? 'selected' : '' }}>
                                                                    Physics</option>
                                                                <option value="Chemistry"
                                                                    {{ $savedSubject == 'Chemistry' ? 'selected' : '' }}>
                                                                    Chemistry</option>
                                                                <option value="Biology"
                                                                    {{ $savedSubject == 'Biology' ? 'selected' : '' }}>
                                                                    Biology</option>
                                                                <option value="Geography"
                                                                    {{ $savedSubject == 'Geography' ? 'selected' : '' }}>
                                                                    Geography</option>
                                                                <option value="Economics"
                                                                    {{ $savedSubject == 'Economics' ? 'selected' : '' }}>
                                                                    Economics</option>
                                                                <option value="Government"
                                                                    {{ $savedSubject == 'Government' ? 'selected' : '' }}>
                                                                    Government</option>
                                                                <option value="Literature in English"
                                                                    {{ $savedSubject == 'Literature in English' ? 'selected' : '' }}>
                                                                    Literature in English</option>
                                                                <option value="History"
                                                                    {{ $savedSubject == 'History' ? 'selected' : '' }}>
                                                                    History</option>
                                                                <option value="Agricultural Science"
                                                                    {{ $savedSubject == 'Agricultural Science' ? 'selected' : '' }}>
                                                                    Agricultural Science</option>
                                                                <option value="Further Mathematics"
                                                                    {{ $savedSubject == 'Further Mathematics' ? 'selected' : '' }}>
                                                                    Further Mathematics</option>
                                                                <option value="Computer Studies"
                                                                    {{ $savedSubject == 'Computer Studies' ? 'selected' : '' }}>
                                                                    Computer Studies</option>
                                                                <option value="Technical Drawing"
                                                                    {{ $savedSubject == 'Technical Drawing' ? 'selected' : '' }}>
                                                                    Technical Drawing</option>
                                                                <option value="Food and Nutrition"
                                                                    {{ $savedSubject == 'Food and Nutrition' ? 'selected' : '' }}>
                                                                    Food and Nutrition</option>
                                                                <option value="Christian Religious Studies"
                                                                    {{ $savedSubject == 'Christian Religious Studies' ? 'selected' : '' }}>
                                                                    Christian Religious Studies</option>
                                                                <option value="Islamic Religious Studies"
                                                                    {{ $savedSubject == 'Islamic Religious Studies' ? 'selected' : '' }}>
                                                                    Islamic Religious Studies</option>
                                                                <option value="Civic Education"
                                                                    {{ $savedSubject == 'Civic Education' ? 'selected' : '' }}>
                                                                    Civic Education</option>
                                                                <option value="Commerce"
                                                                    {{ $savedSubject == 'Commerce' ? 'selected' : '' }}>
                                                                    Commerce</option>
                                                                <option value="Accounting"
                                                                    {{ $savedSubject == 'Accounting' ? 'selected' : '' }}>
                                                                    Accounting</option>
                                                                <option value="Marketing"
                                                                    {{ $savedSubject == 'Marketing' ? 'selected' : '' }}>
                                                                    Marketing</option>
                                                                <option value="Office Practice"
                                                                    {{ $savedSubject == 'Office Practice' ? 'selected' : '' }}>
                                                                    Office Practice</option>
                                                                <option value="Data Processing"
                                                                    {{ $savedSubject == 'Data Processing' ? 'selected' : '' }}>
                                                                    Data Processing</option>
                                                                <option value="Fine Arts"
                                                                    {{ $savedSubject == 'Fine Arts' ? 'selected' : '' }}>
                                                                    Fine Arts</option>
                                                                <option value="Music"
                                                                    {{ $savedSubject == 'Music' ? 'selected' : '' }}>
                                                                    Music</option>
                                                                <option value="Hausa"
                                                                    {{ $savedSubject == 'Hausa' ? 'selected' : '' }}>
                                                                    Hausa</option>
                                                                <option value="Yoruba"
                                                                    {{ $savedSubject == 'Yoruba' ? 'selected' : '' }}>
                                                                    Yoruba</option>
                                                                <option value="Igbo"
                                                                    {{ $savedSubject == 'Igbo' ? 'selected' : '' }}>
                                                                    Igbo</option>
                                                                <option value="French"
                                                                    {{ $savedSubject == 'French' ? 'selected' : '' }}>
                                                                    French</option>
                                                                <option value="Arabic"
                                                                    {{ $savedSubject == 'Arabic' ? 'selected' : '' }}>
                                                                    Arabic</option>
                                                                <option value="Fisheries"
                                                                    {{ $savedSubject == 'Fisheries' ? 'selected' : '' }}>
                                                                    Fisheries</option>
                                                                <option value="Forestry"
                                                                    {{ $savedSubject == 'Forestry' ? 'selected' : '' }}>
                                                                    Forestry</option>
                                                                <option value="Animal Husbandry"
                                                                    {{ $savedSubject == 'Animal Husbandry' ? 'selected' : '' }}>
                                                                    Animal Husbandry</option>
                                                                <option value="Auto Mechanics"
                                                                    {{ $savedSubject == 'Auto Mechanics' ? 'selected' : '' }}>
                                                                    Auto Mechanics</option>
                                                                <option value="Building Construction"
                                                                    {{ $savedSubject == 'Building Construction' ? 'selected' : '' }}>
                                                                    Building Construction</option>
                                                                <option value="Electrical Installation"
                                                                    {{ $savedSubject == 'Electrical Installation' ? 'selected' : '' }}>
                                                                    Electrical Installation</option>
                                                                <option value="Electronics"
                                                                    {{ $savedSubject == 'Electronics' ? 'selected' : '' }}>
                                                                    Electronics</option>
                                                                <option value="Metal Work"
                                                                    {{ $savedSubject == 'Metal Work' ? 'selected' : '' }}>
                                                                    Metal Work</option>
                                                                <option value="Wood Work"
                                                                    {{ $savedSubject == 'Wood Work' ? 'selected' : '' }}>
                                                                    Wood Work</option>
                                                                <option value="Clothing and Textile"
                                                                    {{ $savedSubject == 'Clothing and Textile' ? 'selected' : '' }}>
                                                                    Clothing and Textile</option>
                                                                <option value="Home Management"
                                                                    {{ $savedSubject == 'Home Management' ? 'selected' : '' }}>
                                                                    Home Management</option>
                                                                <option value="Catering Craft Practice"
                                                                    {{ $savedSubject == 'Catering Craft Practice' ? 'selected' : '' }}>
                                                                    Catering Craft Practice</option>
                                                                <option value="Tourism"
                                                                    {{ $savedSubject == 'Tourism' ? 'selected' : '' }}>
                                                                    Tourism</option>
                                                                <option value="Mining"
                                                                    {{ $savedSubject == 'Mining' ? 'selected' : '' }}>
                                                                    Mining</option>
                                                                <option value="Store Management"
                                                                    {{ $savedSubject == 'Store Management' ? 'selected' : '' }}>
                                                                    Store Management</option>
                                                                <option value="Insurance"
                                                                    {{ $savedSubject == 'Insurance' ? 'selected' : '' }}>
                                                                    Insurance</option>
                                                                <option value="Refrigeration"
                                                                    {{ $savedSubject == 'Refrigeration' ? 'selected' : '' }}>
                                                                    Refrigeration</option>
                                                                <option value="Air Conditioning"
                                                                    {{ $savedSubject == 'Air Conditioning' ? 'selected' : '' }}>
                                                                    Air Conditioning</option>
                                                                <option value="Other"
                                                                    {{ $savedSubject == 'Other' ? 'selected' : '' }}>
                                                                    Other (Specify)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4 col-md-3">
                                                            <select class="form-control"
                                                                id="grade_1_{{ $i }}"
                                                                name="grade_1_{{ $i }}"
                                                                {{ $i <= 5 ? 'required' : '' }}>
                                                                @php $savedGrade = $savedResult ? $savedResult->grade : ''; @endphp
                                                                <option value="">Select Grade</option>
                                                                <option value="A1"
                                                                    {{ $savedGrade == 'A1' ? 'selected' : '' }}>A1 -
                                                                    Excellent</option>
                                                                <option value="B2"
                                                                    {{ $savedGrade == 'B2' ? 'selected' : '' }}>B2 -
                                                                    Very Good</option>
                                                                <option value="B3"
                                                                    {{ $savedGrade == 'B3' ? 'selected' : '' }}>B3 -
                                                                    Good</option>
                                                                <option value="C4"
                                                                    {{ $savedGrade == 'C4' ? 'selected' : '' }}>C4 -
                                                                    Credit</option>
                                                                <option value="C5"
                                                                    {{ $savedGrade == 'C5' ? 'selected' : '' }}>C5 -
                                                                    Credit</option>
                                                                <option value="C6"
                                                                    {{ $savedGrade == 'C6' ? 'selected' : '' }}>C6 -
                                                                    Credit</option>
                                                                <option value="D7"
                                                                    {{ $savedGrade == 'D7' ? 'selected' : '' }}>D7 -
                                                                    Pass</option>
                                                                <option value="E8"
                                                                    {{ $savedGrade == 'E8' ? 'selected' : '' }}>E8 -
                                                                    Pass</option>
                                                                <option value="F9"
                                                                    {{ $savedGrade == 'F9' ? 'selected' : '' }}>F9 -
                                                                    Fail</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <!-- SSCE Information Save Button -->
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-success" id="saveSsceBtn">
                                                <i class="fas fa-save me-1"></i> Save SSCE Information
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Section 5: Direct Entry Qualification Details (for DE applicants only) -->
                                @if (strtoupper($applicant->mode) === 'DE')
                                    @php
                                        $qualTypes = [
                                            'ND' => 'National Diploma (ND)',
                                            'HND' => 'Higher National Diploma (HND)',
                                            'NCE' => 'Nigeria Certificate in Education (NCE)',
                                            'IJMB' => 'IJMB A-Level',
                                            'JUPEB' => 'JUPEB A-Level',
                                            'GCE' => 'GCE/Cambridge A-Level',
                                            'DIPLOMA' => 'University Diploma/Certificate',
                                            'NABTEB' => 'NABTEB A-Level',
                                            'DEGREE' => 'Degree (B.Sc., B.A., etc.)',
                                            'OTHER' => 'Other Equivalent',
                                        ];
                                        $gradeOptions = [
                                            'ND' => ['Distinction', 'Upper Credit', 'Lower Credit', 'Pass'],
                                            'HND' => ['Distinction', 'Upper Credit', 'Lower Credit', 'Pass'],
                                            'NCE' => ['Distinction', 'Credit', 'Merit', 'Pass'],
                                            'IJMB' => [
                                                '14 Points',
                                                '13 Points',
                                                '12 Points',
                                                '11 Points',
                                                '10 Points',
                                                '9 Points',
                                                '8 Points',
                                                '7 Points',
                                                '6 Points',
                                                'A',
                                                'B',
                                                'C',
                                                'D',
                                                'E',
                                                'F',
                                            ],
                                            'JUPEB' => [
                                                '16 Points',
                                                '15 Points',
                                                '14 Points',
                                                '13 Points',
                                                '12 Points',
                                                '11 Points',
                                                '10 Points',
                                                '9 Points',
                                                '8 Points',
                                                'A',
                                                'B',
                                                'C',
                                                'D',
                                                'E',
                                                'F',
                                            ],
                                            'GCE' => ['A', 'B', 'C', 'D', 'E', 'F'],
                                            'DIPLOMA' => ['Distinction', 'Credit', 'Merit', 'Pass'],
                                            'NABTEB' => ['A', 'B', 'C', 'D', 'E', 'F'],
                                            'DEGREE' => [
                                                'First Class',
                                                'Second Class Upper',
                                                'Second Class Lower',
                                                'Third Class',
                                                'Pass',
                                            ],
                                            'OTHER' => [
                                                'Distinction',
                                                'Credit',
                                                'Merit',
                                                'Pass',
                                                'A',
                                                'B',
                                                'C',
                                                'D',
                                                'E',
                                                'F',
                                                'First Class',
                                                'Second Class Upper',
                                                'Second Class Lower',
                                                'Third Class',
                                            ],
                                        ];
                                        $selectedQual = $deData ? $deData->qualification_type ?? '' : '';
                                        $selectedGrade = $deData ? $deData->grade ?? '' : '';
                                    @endphp
                                    <div class="form-section mt-4">
                                        <h3 class="section-title"><i class="fas fa-user-graduate me-2"></i>Direct
                                            Entry Qualification Details</h3>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label class="form-label required"
                                                    for="de_qualification">Qualification Type</label>
                                                <select class="form-control" id="de_qualification"
                                                    name="de_qualification">
                                                    <option value="">Select Qualification</option>
                                                    @foreach ($qualTypes as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @if ($selectedQual == $key || $selectedQual == $label) selected @endif>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="form-label required" for="de_institution">Institution
                                                    Attended</label>
                                                <input type="text" class="form-control" id="de_institution"
                                                    name="de_institution" value="{{ $deData->institution ?? '' }}"
                                                    placeholder="Enter institution name" required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label class="form-label required" for="de_grad_year">Year of
                                                    Graduation</label>
                                                <input type="number" class="form-control" id="de_grad_year"
                                                    name="de_grad_year" value="{{ $deData->grad_year ?? '' }}"
                                                    placeholder="e.g. 2023" min="1990" max="2030" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="form-label required" for="de_grade">Grade/Result</label>
                                                <select class="form-control" id="de_grade" name="de_grade">
                                                    <option value="">Select Grade</option>
                                                    @if ($selectedQual && isset($gradeOptions[$selectedQual]))
                                                        @foreach ($gradeOptions[$selectedQual] as $grade)
                                                            <option value="{{ $grade }}"
                                                                @if ($selectedGrade == $grade) selected @endif>
                                                                {{ $grade }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <label class="form-label required" for="de_reg_number">DE Registration
                                                    Number</label>
                                                <input type="text" class="form-control" id="de_reg_number"
                                                    name="de_reg_number"
                                                    value="{{ $deData ? $deData->reg_number ?? '' : '' }}"
                                                    placeholder="Enter DE registration number" required>
                                            </div>
                                        </div>

                                        <!-- Direct Entry Save Button -->
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-success" id="saveDirectEntryBtn">
                                                <i class="fas fa-save me-1"></i> Save Direct Entry Information
                                            </button>
                                        </div>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Define makeSelectSearchable function globally
                                            function makeSelectSearchable(selector) {
                                                const selects = document.querySelectorAll(selector);
                                                selects.forEach(select => {
                                                    // Simple searchable functionality
                                                    select.addEventListener('focus', function() {
                                                        console.log('Select focused:', this);
                                                    });
                                                });
                                            }

                                            // Make function globally available
                                            window.makeSelectSearchable = makeSelectSearchable;

                                            // Direct Entry Credentials Preview Function
                                            function previewDeCredentials(input) {
                                                const previewContainer = document.getElementById('de_credentials_preview');
                                                const fileIcon = document.getElementById('de_file_icon');
                                                const fileName = document.getElementById('de_file_name');
                                                const fileSize = document.getElementById('de_file_size');
                                                const imagePreview = document.getElementById('de_image_preview');
                                                const previewImg = document.getElementById('de_preview_img');

                                                if (input.files && input.files[0]) {
                                                    const file = input.files[0];
                                                    const fileType = file.type;
                                                    const fileSizeKB = Math.round(file.size / 1024);
                                                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

                                                    // Show preview container
                                                    previewContainer.style.display = 'block';

                                                    // Update file info
                                                    fileName.textContent = file.name;
                                                    fileSize.textContent = fileSizeMB > 1 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;

                                                    // Update file icon based on type
                                                    if (fileType.includes('pdf')) {
                                                        fileIcon.className = 'fas fa-file-pdf text-danger';
                                                    } else if (fileType.includes('image')) {
                                                        fileIcon.className = 'fas fa-file-image text-success';

                                                        // Show image preview
                                                        const reader = new FileReader();
                                                        reader.onload = function(e) {
                                                            previewImg.src = e.target.result;
                                                            imagePreview.style.display = 'block';
                                                        }
                                                        reader.readAsDataURL(file);
                                                    } else {
                                                        fileIcon.className = 'fas fa-file-alt text-primary';
                                                        imagePreview.style.display = 'none';
                                                    }

                                                    // Hide action buttons for new files (not yet uploaded)
                                                    const fileActions = previewContainer.querySelector('.file-actions');
                                                    if (fileActions) {
                                                        fileActions.style.display = 'none';
                                                    }
                                                } else {
                                                    // Hide preview if no file selected
                                                    previewContainer.style.display = 'none';
                                                }
                                            }

                                            // Make preview function globally available
                                            window.previewDeCredentials = previewDeCredentials;

                                            // Document Upload Preview Function
                                            function previewDocument(input, docId) {
                                                const previewContainer = document.getElementById(`preview_${docId}`);
                                                const fileIcon = document.getElementById(`icon_${docId}`);
                                                const fileName = document.getElementById(`name_${docId}`);
                                                const fileSize = document.getElementById(`size_${docId}`);
                                                const imagePreview = document.getElementById(`img_preview_${docId}`);
                                                const previewImg = document.getElementById(`img_${docId}`);
                                                const maxSizeKB = parseInt(input.getAttribute('data-max-size')) || 400;
                                                const docLabel = input.getAttribute('data-doc-label') || 'Document';

                                                if (input.files && input.files[0]) {
                                                    const file = input.files[0];
                                                    const fileType = file.type;
                                                    const fileSizeKB = Math.round(file.size / 1024);
                                                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

                                                    // Check file size (400KB max)
                                                    if (fileSizeKB > maxSizeKB) {
                                                        Swal.fire({
                                                            title: 'File Too Large',
                                                            text: `${docLabel} must be ${maxSizeKB}KB or smaller. Your file is ${fileSizeKB}KB.`,
                                                            icon: 'error',
                                                            confirmButtonText: 'Choose Another File',
                                                            confirmButtonColor: '#dc3545'
                                                        });
                                                        input.value = ''; // Clear the input
                                                        previewContainer.style.display = 'none';
                                                        return;
                                                    }

                                                    // Show preview container
                                                    previewContainer.style.display = 'block';

                                                    // Create dynamic filename based on label
                                                    const timestamp = new Date().getTime();
                                                    const fileExt = file.name.split('.').pop();
                                                    const dynamicName = `${docLabel.replace(/\s+/g, '_')}_${timestamp}.${fileExt}`;

                                                    // Update file info
                                                    fileName.textContent = dynamicName;
                                                    fileSize.textContent = fileSizeMB > 1 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;

                                                    // Update file icon based on type
                                                    if (fileType.includes('pdf')) {
                                                        fileIcon.className = 'fas fa-file-pdf text-danger';
                                                        imagePreview.style.display = 'none';
                                                    } else if (fileType.includes('image')) {
                                                        fileIcon.className = 'fas fa-file-image text-success';

                                                        // Show image preview
                                                        const reader = new FileReader();
                                                        reader.onload = function(e) {
                                                            previewImg.src = e.target.result;
                                                            imagePreview.style.display = 'block';
                                                        }
                                                        reader.readAsDataURL(file);
                                                    } else {
                                                        fileIcon.className = 'fas fa-file-alt text-primary';
                                                        imagePreview.style.display = 'none';
                                                    }

                                                    // Hide action buttons for new files (not yet uploaded)
                                                    const fileActions = previewContainer.querySelector('.file-actions');
                                                    if (fileActions) {
                                                        fileActions.style.display = 'none';
                                                    }
                                                } else {
                                                    // Hide preview if no file selected
                                                    previewContainer.style.display = 'none';
                                                }
                                            }

                                            // Make document preview function globally available
                                            window.previewDocument = previewDocument;

                                            // Initialize existing file preview
                                            const existingFile = '{{ $deData->credentials_path ?? '' }}';
                                            if (existingFile) {
                                                const fileIcon = document.getElementById('de_file_icon');
                                                const fileExt = existingFile.split('.').pop().toLowerCase();

                                                if (fileExt === 'pdf') {
                                                    fileIcon.className = 'fas fa-file-pdf text-danger';
                                                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                                                    fileIcon.className = 'fas fa-file-image text-success';
                                                } else {
                                                    fileIcon.className = 'fas fa-file-alt text-primary';
                                                }
                                            }

                                            const qualSelect = document.getElementById('de_qualification');
                                            const gradeSelect = document.getElementById('de_grade');
                                            if (!qualSelect || !gradeSelect) return;
                                            const gradeOptions = @json($gradeOptions);
                                            qualSelect.addEventListener('change', function() {
                                                const sel = qualSelect.value;
                                                gradeSelect.innerHTML = '<option value="">Select Grade</option>';
                                                if (gradeOptions[sel]) {
                                                    gradeOptions[sel].forEach(function(opt) {
                                                        gradeSelect.innerHTML += `<option value="${opt}">${opt}</option>`;
                                                    });
                                                }
                                            });
                                        });
                                    </script>
                                @endif
                                <!-- Section 6: Next of Kin -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="fas fa-users me-2"></i>Next of Kin Information
                                    </h3>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="nok_name">Full Name</label>
                                            <input type="text" class="form-control" id="nok_name"
                                                name="nok_name"
                                                value="{{ old('nok_name', $applicant->n_name ?? '') }}"
                                                placeholder="Enter next of kin full name" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label required"
                                                for="nok_relationship">Relationship</label>
                                            <select class="form-control" id="nok_relationship"
                                                name="nok_relationship" required>
                                                <option value="">Select Relationship</option>
                                                <option value="Father"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Father' ? 'selected' : '' }}>
                                                    Father</option>
                                                <option value="Mother"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Mother' ? 'selected' : '' }}>
                                                    Mother</option>
                                                <option value="Sibling"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Sibling' ? 'selected' : '' }}>
                                                    Sibling</option>
                                                <option value="Spouse"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Spouse' ? 'selected' : '' }}>
                                                    Spouse</option>
                                                <option value="Guardian"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Guardian' ? 'selected' : '' }}>
                                                    Guardian</option>
                                                <option value="Uncle"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Uncle' ? 'selected' : '' }}>
                                                    Uncle</option>
                                                <option value="Aunt"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Aunt' ? 'selected' : '' }}>
                                                    Aunt</option>
                                                <option value="Other"
                                                    {{ old('nok_relationship', $applicant->n_relationship ?? '') == 'Other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label required" for="nok_phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="nok_phone"
                                                name="nok_phone"
                                                value="{{ old('nok_phone', $applicant->n_phone ?? '') }}"
                                                placeholder="+234.." required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="nok_email">Email Address</label>
                                            <input type="email" class="form-control" id="nok_email"
                                                name="nok_email"
                                                value="{{ old('nok_email', $applicant->n_email ?? '') }}"
                                                placeholder="Next of kin email (optional)">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label required" for="nok_address">Address</label>
                                        <textarea class="form-control" id="nok_address" name="nok_address" rows="3"
                                            placeholder="Enter next of kin address" required>{{ old('nok_address', $applicant->n_address ?? '') }}</textarea>
                                    </div>

                                    <!-- Next of Kin Save Button -->
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-success" id="saveNextOfKinBtn">
                                            <i class="fas fa-save me-1"></i> Save Next of Kin Information
                                        </button>
                                    </div>
                                </div>

                                <!-- Section 7: Sponsor Information -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="fas fa-hand-holding-usd me-2"></i>Sponsor
                                        Information</h3>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="form-label required" for="s_name">Sponsor Name</label>
                                            <input type="text" class="form-control" id="s_name" name="s_name"
                                                value="{{ old('s_name', $applicant->s_name ?? '') }}"
                                                placeholder="Enter sponsor's full name" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="form-label required" for="s_phone">Sponsor Phone</label>
                                            <input type="tel" class="form-control" id="s_phone" name="s_phone"
                                                value="{{ old('s_phone', $applicant->s_phone ?? '') }}"
                                                placeholder="Enter sponsor's phone number" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-12">
                                            <label class="form-label required" for="s_address">Sponsor Address</label>
                                            <textarea class="form-control" id="s_address" name="s_address" rows="2" placeholder="Enter sponsor's address"
                                                required>{{ old('s_address', $applicant->s_address ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Sponsor Save Button -->
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-success" id="saveSponsorBtn">
                                            <i class="fas fa-save me-1"></i> Save Sponsor Information
                                        </button>
                                    </div>
                                </div>

                                <!-- Section 7: Document Upload -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="fas fa-file-upload me-2"></i>Document Upload
                                    </h3>
                                    <p class="text-muted mb-3">Upload required documents (PDF, JPG, or PNG format, max
                                        400KB each)</p>

                                    @php
                                        $documentTypes = [
                                            [
                                                'id' => 'passport_photo',
                                                'label' => 'Passport Photograph',
                                                'required' => true,
                                                'accept' => 'image/*',
                                                'description' => 'Upload a recent passport-size photo',
                                            ],
                                            [
                                                'id' => 'jamb_result',
                                                'label' => 'JAMB Result',
                                                'required' => true,
                                                'accept' => '.pdf,.jpg,.jpeg,.png',
                                                'description' => 'Upload JAMB result slip',
                                            ],
                                            [
                                                'id' => 'ssce_result',
                                                'label' => 'SSCE Result (1st Sitting)',
                                                'required' => true,
                                                'accept' => '.pdf,.jpg,.jpeg,.png',
                                                'description' => 'Upload WAEC/NECO/NABTEB result for 1st sitting',
                                            ],
                                            [
                                                'id' => 'ssce_result_2',
                                                'label' => 'SSCE Result (2nd Sitting)',
                                                'required' => false,
                                                'accept' => '.pdf,.jpg,.jpeg,.png',
                                                'description' => 'Upload WAEC/NECO/NABTEB result for 2nd sitting',
                                                'conditional' => true,
                                            ],
                                            [
                                                'id' => 'birth_certificate',
                                                'label' => 'Birth Certificate',
                                                'required' => false,
                                                'accept' => '.pdf,.jpg,.jpeg,.png',
                                                'description' => 'Upload birth certificate (optional)',
                                            ],
                                        ];

                                        // Add Direct Entry certificate if applicant mode is DE
                                        if (isset($applicant->mode) && strtoupper($applicant->mode) === 'DE') {
                                            $documentTypes[] = [
                                                'id' => 'direct_entry_cert',
                                                'label' => 'Direct Entry Certificate',
                                                'required' => true,
                                                'accept' => '.pdf,.jpg,.jpeg,.png',
                                                'description' => 'Upload DE qualification certificate',
                                            ];
                                        }

                                        // Load existing documents
                                        $existingDocs = [];
                                        if ($applicant) {
                                            $documents = \App\Models\DocumentUpload::where('user_id', session('id'))
                                                ->where('applicant_id', $applicant->id)
                                                ->get();
                                            foreach ($documents as $doc) {
                                                $existingDocs[$doc->doc_type] = $doc;
                                            }
                                        }
                                    @endphp

                                    <div id="documentContainer" class="document-upload-container">
                                        @foreach ($documentTypes as $index => $docType)
                                            <div class="form-group mb-3 mb-md-4"
                                                data-doc-type="{{ $docType['id'] }}"
                                                @if(isset($docType['conditional']) && $docType['conditional']) 
                                                    data-conditional="true"
                                                @endif>
                                                <label
                                                    class="form-label {{ $docType['required'] ? 'required' : '' }}"
                                                    for="{{ $docType['id'] }}">{{ $docType['label'] }}</label>
                                                <input type="file" class="form-control document-upload"
                                                    id="{{ $docType['id'] }}" name="{{ $docType['id'] }}"
                                                    accept="{{ $docType['accept'] }}" data-max-size="400"
                                                    data-doc-label="{{ $docType['label'] }}"
                                                    {{ $docType['required'] ? 'required' : '' }}
                                                    onchange="previewDocument(this, '{{ $docType['id'] }}')"> <small
                                                    class="text-muted d-block mt-1">{{ $docType['description'] }}
                                                    (Max: 400KB)
                                                </small>

                                                <!-- File Preview Area -->
                                                <div id="preview_{{ $docType['id'] }}" class="mt-2 mt-md-3"
                                                    style="{{ isset($existingDocs[$docType['id']]) ? '' : 'display: none;' }}">
                                                    <div class="card border-light shadow-sm">
                                                        <div class="card-body p-2 p-md-3">
                                                            <div
                                                                class="d-flex align-items-center flex-wrap flex-md-nowrap">
                                                                <div class="file-icon me-2 mb-2 mb-md-0">
                                                                    <i id="icon_{{ $docType['id'] }}"
                                                                        class="fas fa-file-alt text-primary"
                                                                        style="font-size: 18px;"></i>
                                                                </div>
                                                                <div class="file-info flex-grow-1 me-2">
                                                                    <div id="name_{{ $docType['id'] }}"
                                                                        class="fw-bold text-dark"
                                                                        style="font-size: 13px; word-break: break-word;">
                                                                        {{ isset($existingDocs[$docType['id']]) ? $existingDocs[$docType['id']]->original_name : 'No file uploaded' }}
                                                                    </div>
                                                                    <div id="size_{{ $docType['id'] }}"
                                                                        class="text-muted" style="font-size: 11px;">
                                                                        {{ isset($existingDocs[$docType['id']]) ? number_format($existingDocs[$docType['id']]->size / 1024, 1) . ' KB' : '' }}
                                                                    </div>
                                                                </div>
                                                                @if (isset($existingDocs[$docType['id']]))
                                                                    <div class="file-actions d-flex gap-1">
                                                                        <a href="{{ asset('storage/' . $existingDocs[$docType['id']]->file_path) }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-outline-primary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="View Document">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <a href="{{ asset('storage/' . $existingDocs[$docType['id']]->file_path) }}"
                                                                            download
                                                                            class="btn btn-sm btn-outline-success"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Download Document">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Image Preview -->
                                                            <div id="img_preview_{{ $docType['id'] }}"
                                                                class="mt-2" style="display: none;">
                                                                <div class="text-center">
                                                                    <img id="img_{{ $docType['id'] }}"
                                                                        src="" alt="Preview"
                                                                        class="img-fluid rounded border"
                                                                        style="max-height: 120px; max-width: 100%; object-fit: contain;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Document Upload Save Button -->
                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-success" id="saveDocumentsBtn">
                                            <i class="fas fa-save me-1"></i> Save Documents
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary" id="submitApplicationBtn">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <style>
                    /* Hide conditional document upload fields by default */
                    [data-doc-type][data-conditional="true"] {
                        display: none;
                    }
                </style>
                <!-- Bootstrap JS -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <!-- SweetAlert2 -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <!-- Tom Select JS -->
                <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

                <script>
                    // Variables to track sittings
                    let sittingCount = 1;
                    let maxSittings = 2;
                    // Tom Select initialization for all subject selects
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.tom-select').forEach(function(select) {
                            new TomSelect(select, {
                                dropdownParent: 'body',
                                create: false,
                                allowEmptyOption: true,
                                placeholder: 'Type to search...'
                            });
                        });
                    });

                    // Function to add second SSCE sitting
                    function addSsceSitting() {
                        if (sittingCount >= maxSittings) {
                            Swal.fire({
                                title: 'Maximum Sittings',
                                text: 'You can only add up to 2 SSCE sittings.',
                                icon: 'info',
                                confirmButtonColor: '#667eea'
                            });
                            return;
                        }

                        sittingCount++;
                        const currentYear = new Date().getFullYear();

                        // Create second sitting HTML
                        const secondSittingHtml = `
                    <div class="ssce-sitting mt-4" data-sitting="2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="sitting-title"><i class="fas fa-scroll me-1"></i>Second Sitting</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSsceSitting(2)">
                                <i class="fas fa-times me-1"></i>Remove
                            </button>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required" for="ssce_type_2">SSCE Type</label>
                                <select class="form-control" id="ssce_type_2" name="ssce_type_2" required>
                                    <option value="">Select SSCE Type</option>
                                    <option value="WAEC" {{ $ssceData && $ssceData->type == 'WAEC' ? 'selected' : '' }}>WAEC</option>
                                    <option value="NECO" {{ $ssceData && $ssceData->type == 'NECO' ? 'selected' : '' }}>NECO</option>
                                    <option value="NABTEB" {{ $ssceData && $ssceData->type == 'NABTEB' ? 'selected' : '' }}>NABTEB</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required" for="ssce_year_2">SSCE Year</label>
                                <select class="form-control" id="ssce_year_2" name="ssce_year_2" required>
                                    <option value="">Select Year</option>`;

                        let yearOptions = '';
                        for (let year = currentYear; year >= 2015; year--) {
                            yearOptions += `<option value="${year}">${year}</option>`;
                        }

                        const secondSittingHtml2 = yearOptions + `
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required" for="ssce_reg_number_2">SSCE Registration Number</label>
                                <input type="text" class="form-control" id="ssce_reg_number_2" name="ssce_reg_number_2" 
                                   placeholder="Enter SSCE registration number" 
                                   value="{{ $ssceData ? $ssceData->number : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required" for="ssce_center_2">Examination Center</label>
                                <input type="text" class="form-control" id="ssce_center_2" name="ssce_center_2" 
                                   placeholder="Enter examination center" 
                                   value="{{ $ssceData ? $ssceData->center_name : '' }}" required>
                            </div>
                        </div>
                    </div>`;

                        // Add to container
                        document.getElementById('ssce-sittings-container').insertAdjacentHTML('beforeend', secondSittingHtml +
                            secondSittingHtml2);

                        // Show the second SSCE document upload field
                        const secondSsceDocField = document.querySelector('[data-doc-type="ssce_result_2"]');
                        if (secondSsceDocField) {
                            secondSsceDocField.style.display = 'block';
                            // Make the field required
                            const input = secondSsceDocField.querySelector('input[type="file"]');
                            if (input) {
                                input.required = true;
                            }
                        }

                        // Add second sitting results
                        addSecondSittingResults();

                        // Hide add button
                        document.getElementById('add-sitting-btn').style.display = 'none';

                        // Initialize search for new selects
                        setTimeout(() => {
                            makeSelectSearchable('.ssce-subject-select');
                        }, 100);
                    }

                    // Function to add second sitting results
                    function addSecondSittingResults() {
                        const subjectOptions = `
                    <option value="">Select Subject</option>
                    <option value="English Language">English Language</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Physics">Physics</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Biology">Biology</option>
                    <option value="Geography">Geography</option>
                    <option value="Economics">Economics</option>
                    <option value="Government">Government</option>
                    <option value="Literature in English">Literature in English</option>
                    <option value="History">History</option>
                    <option value="Agricultural Science">Agricultural Science</option>
                    <option value="Further Mathematics">Further Mathematics</option>
                    <option value="Computer Studies">Computer Studies</option>
                    <option value="Technical Drawing">Technical Drawing</option>
                    <option value="Food and Nutrition">Food and Nutrition</option>
                    <option value="Christian Religious Studies">Christian Religious Studies</option>
                    <option value="Islamic Religious Studies">Islamic Religious Studies</option>
                    <option value="Civic Education">Civic Education</option>
                    <option value="Commerce">Commerce</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Office Practice">Office Practice</option>
                    <option value="Data Processing">Data Processing</option>
                    <option value="Fine Arts">Fine Arts</option>
                    <option value="Music">Music</option>
                    <option value="Hausa">Hausa</option>
                    <option value="Yoruba">Yoruba</option>
                    <option value="Igbo">Igbo</option>
                    <option value="French">French</option>
                    <option value="Arabic">Arabic</option>
                    <option value="Fisheries">Fisheries</option>
                    <option value="Forestry">Forestry</option>
                    <option value="Animal Husbandry">Animal Husbandry</option>
                    <option value="Auto Mechanics">Auto Mechanics</option>
                    <option value="Building Construction">Building Construction</option>
                    <option value="Electrical Installation">Electrical Installation</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Metal Work">Metal Work</option>
                    <option value="Wood Work">Wood Work</option>
                    <option value="Clothing and Textile">Clothing and Textile</option>
                    <option value="Home Management">Home Management</option>
                    <option value="Catering Craft Practice">Catering Craft Practice</option>
                    <option value="Tourism">Tourism</option>
                    <option value="Mining">Mining</option>
                    <option value="Store Management">Store Management</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Refrigeration">Refrigeration</option>
                    <option value="Air Conditioning">Air Conditioning</option>
                    <option value="Other">Other (Specify)</option>
                `;

                        const gradeOptions = `
                    <option value="">Select Grade</option>
                    <option value="A1">A1 - Excellent</option>
                    <option value="B2">B2 - Very Good</option>
                    <option value="B3">B3 - Good</option>
                    <option value="C4">C4 - Credit</option>
                    <option value="C5">C5 - Credit</option>
                    <option value="C6">C6 - Credit</option>
                    <option value="D7">D7 - Pass</option>
                    <option value="E8">E8 - Pass</option>
                    <option value="F9">F9 - Fail</option>
                `;

                        let secondSittingResultsHtml = `
                    <div class="results-sitting mt-4" data-sitting="2">
                        <h5 class="mb-3"><i class="fas fa-list me-1"></i>Second Sitting Results</h5>
                        
                        <div class="row mb-3">
                            <div class="col-2 col-md-1"><label class="form-label text-center"><strong>S/N</strong></label></div>
                            <div class="col-6 col-md-8"><label class="form-label"><strong>Subject</strong></label></div>
                            <div class="col-4 col-md-3"><label class="form-label"><strong>Grade</strong></label></div>
                        </div>`;

                        for (let i = 1; i <= 9; i++) {
                            secondSittingResultsHtml += `
                        <div class="row mb-3 result-row" id="result-row-2-${i}">
                            <div class="col-2 col-md-1"><div class="form-control-plaintext text-center fw-bold">${i}</div></div>
                            <div class="col-6 col-md-8">
                                <select class="form-control ssce-subject-select" id="subject_2_${i}" name="subject_2_${i}">
                                    ${subjectOptions}
                                </select>
                            </div>
                            <div class="col-4 col-md-3">
                                <select class="form-control" id="grade_2_${i}" name="grade_2_${i}">
                                    ${gradeOptions}
                                </select>
                            </div>
                        </div>`;
                        }

                        secondSittingResultsHtml += '</div>';

                        document.getElementById('ssce-results-container').insertAdjacentHTML('beforeend', secondSittingResultsHtml);
                        // Ensure Tom Select is initialized for all new SSCE subject selects (second sitting)
                        document.querySelectorAll('.ssce-subject-select').forEach(function(select) {
                            if (!select.tomselect) {
                                new TomSelect(select, {
                                    dropdownParent: 'body',
                                    create: false,
                                    allowEmptyOption: true,
                                    placeholder: 'Type to search...'
                                });
                            }
                        });
                    }

                    // Function to remove SSCE sitting
                    function removeSsceSitting(sittingNumber) {
                        // Remove the sitting element
                        const sittingElement = document.querySelector(`.ssce-sitting[data-sitting="${sittingNumber}"]`);
                        if (sittingElement) {
                            sittingElement.remove();
                        }
                        
                        // If removing the second sitting, hide and clear the second SSCE document upload
                        if (sittingNumber === 2) {
                            const secondSsceDocField = document.querySelector('[data-doc-type="ssce_result_2"]');
                            if (secondSsceDocField) {
                                secondSsceDocField.style.display = 'none';
                                // Make the field not required and clear it
                                const input = secondSsceDocField.querySelector('input[type="file"]');
                                if (input) {
                                    input.required = false;
                                    input.value = ''; // Clear the file input
                                    // Also clear the preview if it exists
                                    const previewContainer = document.getElementById('preview_ssce_result_2');
                                    if (previewContainer) {
                                        previewContainer.style.display = 'none';
                                        document.getElementById('name_ssce_result_2').textContent = 'No file uploaded';
                                        document.getElementById('size_ssce_result_2').textContent = '';
                                        const imgPreview = document.getElementById('img_preview_ssce_result_2');
                                        if (imgPreview) imgPreview.style.display = 'none';
                                    }
                                }
                            }
                        }

                        // Remove sitting results section
                        const resultsDiv = document.querySelector(`[data-sitting="${sittingNumber}"].results-sitting`);
                        if (resultsDiv) {
                            resultsDiv.remove();
                        }

                        sittingCount--;

                        // Show add button again
                        document.getElementById('add-sitting-btn').style.display = 'inline-block';
                    }

                    // Initialize search functionality on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        // Make JAMB and SSCE subject selects searchable
                        makeSelectSearchable('.jamb-subject-select');
                        makeSelectSearchable('.ssce-subject-select');
                    });
                </script>

                <script>
                    // Enhanced form validation and submission
                    document.getElementById('applicationForm').addEventListener('submit', function(e) {
                        e.preventDefault();

                        // Comprehensive validation
                        let isValid = true;
                        let errorMessage = '';

                        // Basic required fields validation
                        const requiredFields = this.querySelectorAll('[required]');

                        requiredFields.forEach(field => {
                            if (!field.value.trim()) {
                                isValid = false;
                                field.classList.add('is-invalid');
                            } else {
                                field.classList.remove('is-invalid');
                            }
                        });

                        // Email validation
                        const email = document.getElementById('email');
                        if (email && email.value) {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email.value)) {
                                isValid = false;
                                email.classList.add('is-invalid');
                                errorMessage = 'Please enter a valid email address.';
                            }
                        }

                        // Phone validation
                        const phone = document.getElementById('phone');
                        if (phone && phone.value) {
                            const phoneRegex = /^\+?[1-9]\d{10,14}$/;
                            if (!phoneRegex.test(phone.value.replace(/\s/g, ''))) {
                                isValid = false;
                                phone.classList.add('is-invalid');
                                errorMessage = 'Please enter a valid phone number.';
                            }
                        }

                        // JAMB scores validation
                        for (let i = 1; i <= 4; i++) {
                            const score = document.getElementsByName(`jamb_score${i}`)[0];
                            if (score && score.value) {
                                const scoreValue = parseInt(score.value);
                                if (scoreValue < 0 || scoreValue > 100) {
                                    isValid = false;
                                    score.classList.add('is-invalid');
                                    errorMessage = `JAMB Score ${i} must be between 0 and 100.`;
                                }
                            }
                        }

                        // SSCE Results validation for multiple sittings
                        let validSubjects = 0;
                        let creditCount = 0;
                        let hasEnglish = false;
                        let hasMaths = false;
                        let englishCredit = false;
                        let mathsCredit = false;

                        // Check all sittings
                        for (let sitting = 1; sitting <= sittingCount; sitting++) {
                            for (let i = 1; i <= 9; i++) {
                                const subject = document.getElementById(`subject_${sitting}_${i}`);
                                const grade = document.getElementById(`grade_${sitting}_${i}`);

                                if (subject && grade && subject.value && grade.value) {
                                    validSubjects++;

                                    // Check for credit level grades (A1 to C6)
                                    if (['A1', 'B2', 'B3', 'C4', 'C5', 'C6'].includes(grade.value)) {
                                        creditCount++;
                                    }

                                    // Check for English and Mathematics
                                    if (subject.value === 'English Language') {
                                        hasEnglish = true;
                                        if (['A1', 'B2', 'B3', 'C4', 'C5', 'C6'].includes(grade.value)) {
                                            englishCredit = true;
                                        }
                                    }

                                    if (subject.value === 'Mathematics') {
                                        hasMaths = true;
                                        if (['A1', 'B2', 'B3', 'C4', 'C5', 'C6'].includes(grade.value)) {
                                            mathsCredit = true;
                                        }
                                    }
                                }
                            }
                        }

                        // Validate minimum requirements
                        if (validSubjects < 5) {
                            isValid = false;
                            errorMessage = 'Please provide at least 5 SSCE subjects.';
                        } else if (creditCount < 5) {
                            isValid = false;
                            errorMessage = 'You must have at least 5 credits (C6 and above) in your SSCE results.';
                        } else if (!hasEnglish) {
                            isValid = false;
                            errorMessage = 'English Language is a required subject.';
                        } else if (!hasMaths) {
                            isValid = false;
                            errorMessage = 'Mathematics is a required subject.';
                        } else if (!englishCredit) {
                            isValid = false;
                            errorMessage = 'English Language must have a credit grade (C6 and above).';
                        } else if (!mathsCredit) {
                            isValid = false;
                            errorMessage = 'Mathematics must have a credit grade (C6 and above).';
                        }

                        // File upload validation
                        const requiredFiles = ['passport_photo', 'jamb_result', 'ssce_result'];
                        for (let field of requiredFiles) {
                            const element = document.getElementById(field);
                            if (element && element.files.length > 0) {
                                const file = element.files[0];
                                if (file.size > 2 * 1024 * 1024) { // 2MB limit
                                    isValid = false;
                                    element.classList.add('is-invalid');
                                    errorMessage = `${field.replace('_', ' ')} file size must be less than 2MB.`;
                                } else {
                                    element.classList.remove('is-invalid');
                                }
                            }
                        }

                        if (isValid) {
                            Swal.fire({
                                title: 'Submit Application?',
                                text: 'Are you sure you want to submit your application? You cannot modify it after submission.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#667eea',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Yes, Submit!',
                                cancelButtonText: 'Review Again'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Show loading state
                                    Swal.fire({
                                        title: 'Submitting Application...',
                                        text: 'Please wait while we process your application.',
                                        icon: 'info',
                                        allowOutsideClick: false,
                                        showConfirmButton: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    this.submit();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Form Validation Error',
                                text: errorMessage ||
                                    'Please fill all required fields and correct any errors before submitting.',
                                icon: 'warning',
                                confirmButtonColor: '#667eea'
                            });
                        }
                    });

                    // File size validation on change
                    document.querySelectorAll('input[type="file"]').forEach(input => {
                        input.addEventListener('change', function() {
                            if (this.files.length > 0) {
                                const file = this.files[0];
                                if (file.size > 2 * 1024 * 1024) {
                                    this.value = '';
                                    this.classList.add('is-invalid');
                                    Swal.fire({
                                        title: 'File Too Large',
                                        text: 'Please select a file smaller than 2MB.',
                                        icon: 'warning',
                                        confirmButtonColor: '#667eea'
                                    });
                                } else {
                                    this.classList.remove('is-invalid');
                                }
                            }
                        });
                    });
                </script>

                <!-- Success/Error Messages -->
                @if (session('success'))
                    <script>
                        Swal.fire({
                            title: 'Success!',
                            text: '{{ session('success') }}',
                            icon: 'success',
                            confirmButtonColor: '#667eea'
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        Swal.fire({
                            title: 'Error!',
                            text: '{{ session('error') }}',
                            icon: 'error',
                            confirmButtonColor: '#667eea'
                        });
                    </script>
                @endif
</body>
<!-- SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-load second sitting if it exists in database
        const ssceData2 = @json($ssceData2 ?? null);
        const ssceResults2 = @json($ssceResults2 ?? []);

        if (ssceData2) {
            console.log('Second sitting data found, auto-loading...', ssceData2);
            // Call the existing addSsceSitting function if it exists
            if (typeof addSsceSitting === 'function') {
                addSsceSitting();

                // Populate second sitting fields after Tom Select initialization
                setTimeout(() => {
                    populateSecondSitting(ssceData2, ssceResults2);
                }, 300);
            }
        }

        // Function to populate second sitting fields with saved data
        function populateSecondSitting(ssceData2, ssceResults2) {
            console.log('Populating second sitting with:', ssceData2, ssceResults2);

            // Populate SSCE basic info (these are regular inputs/selects)
            const ssceType2 = document.getElementById('ssce_type_2');
            const ssceYear2 = document.getElementById('ssce_year_2');
            const ssceRegNumber2 = document.getElementById('ssce_reg_number_2');
            const ssceCenter2 = document.getElementById('ssce_center_2');

            if (ssceType2) ssceType2.value = ssceData2.type || '';
            if (ssceYear2) ssceYear2.value = ssceData2.year || '';
            if (ssceRegNumber2) ssceRegNumber2.value = ssceData2.number || '';
            if (ssceCenter2) ssceCenter2.value = ssceData2.center_name || '';

            // Wait for select elements to be enhanced by makeSelectSearchable
            setTimeout(() => {
                console.log('Populating subjects and grades after select enhancement...');

                // Check if Tom Select instances are ready
                const allSubjectsReady = ssceResults2.every((_, index) => {
                    const subjectField = document.getElementById(`subject_2_${index + 1}`);
                    return !subjectField || subjectField.tomselect;
                });

                console.log('All Tom Select instances ready:', allSubjectsReady);

                ssceResults2.forEach((result, index) => {
                    const fieldIndex = index + 1;
                    const subjectField = document.getElementById(`subject_2_${fieldIndex}`);
                    const gradeField = document.getElementById(`grade_2_${fieldIndex}`);

                    console.log(`Field ${fieldIndex}:`, {
                        subject: result.subject,
                        grade: result.grade,
                        subjectField: subjectField,
                        gradeField: gradeField
                    });

                    // Set grade (regular select)
                    if (gradeField && result.grade) {
                        gradeField.value = result.grade;
                        console.log(`Set grade ${fieldIndex}:`, result.grade);
                    }

                    // Set subject (Tom Select enhanced - use Tom Select API)
                    if (subjectField && result.subject) {
                        console.log(`Attempting to set subject ${fieldIndex}:`, result.subject);

                        // Check if Tom Select instance exists
                        const tomSelectInstance = subjectField.tomselect;

                        if (tomSelectInstance) {
                            // Use Tom Select API
                            console.log(`Using Tom Select API for subject ${fieldIndex}`);
                            tomSelectInstance.setValue(result.subject);
                        } else {
                            // Fallback to regular select methods
                            console.log(
                                `No Tom Select instance found for subject ${fieldIndex}, using fallback`
                            );
                            subjectField.value = result.subject;

                            const option = subjectField.querySelector(
                                `option[value="${result.subject}"]`);
                            if (option) {
                                option.selected = true;
                                subjectField.dispatchEvent(new Event('change'));
                            }
                        }

                        console.log(`Subject ${fieldIndex} set to:`, subjectField.value);
                    }
                });

                console.log('Second sitting populated successfully');
            }, 800); // Wait 800ms for select enhancement
        }

        const personalForm = document.getElementById('personalInfoForm');
        const saveBtn = document.getElementById('savePersonalBtn');
        const csrfToken = document.querySelector('input[name="_token"]');

        if (!personalForm || !saveBtn || !csrfToken || typeof Swal === 'undefined') {
            console.error('Personal Info AJAX: Required elements not found');
            return;
        }

        saveBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            // Visual feedback
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            // Show loading popup
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait while we save your personal information',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData(personalForm);

            try {
                const response = await fetch(personalForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                let data;
                const contentType = response.headers.get('content-type');

                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    data = await response.text();
                }

                // Close loading popup and show result
                Swal.close();

                if (response.ok && typeof data === 'object' && data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.success,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    });
                } else if (typeof data === 'object' && data.errors) {
                    let errorMessages = [];
                    const fieldNames = {
                        'dob': 'Date of Birth',
                        'phone': 'Phone Number',
                        'email': 'Email Address',
                        'address': 'Permanent Address',
                        'lga': 'Local Government Area',
                        'surname': 'Surname',
                        'first_name': 'First Name'
                    };
                    for (let field in data.errors) {
                        const fieldLabel = fieldNames[field] || field.toUpperCase();
                        if (Array.isArray(data.errors[field])) {
                            data.errors[field].forEach(error => {
                                errorMessages.push(
                                    `• ${fieldLabel}: ${error.replace('The ' + field + ' field', 'This field')}`
                                );
                            });
                        } else {
                            errorMessages.push(
                                `• ${fieldLabel}: ${data.errors[field].replace('The ' + field + ' field', 'This field')}`
                            );
                        }
                    }
                    Swal.fire({
                        title: 'Validation Errors',
                        html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                            errorMessages.join('<br>') + '</div>',
                        icon: 'error',
                        confirmButtonText: 'Fix Errors',
                        confirmButtonColor: '#dc3545',
                        width: '500px'
                    });
                } else if (typeof data === 'object' && data.error) {
                    Swal.fire({
                        title: 'Error',
                        text: data.error,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } else if (typeof data === 'string') {
                    Swal.fire({
                        title: 'Server Error',
                        text: 'Server returned HTML instead of JSON. Please check console for details.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } else {
                    Swal.fire({
                        title: 'Unknown Error',
                        text: 'Unknown response format received from server.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                }

            } catch (err) {
                console.error('Personal Info AJAX Error:', err);
                Swal.fire({
                    title: 'Network Error',
                    text: 'Network error: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                // Reset button
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Personal Information';
            }
        });

        // Direct Entry Information AJAX Form
        const directEntryForm = document.getElementById('applicationForm');
        const directEntryBtn = document.getElementById('saveDirectEntryBtn');

        if (directEntryBtn && directEntryForm) {
            directEntryBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Visual feedback
                directEntryBtn.disabled = true;
                directEntryBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                // Show loading popup
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your Direct Entry information',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Collect Direct Entry fields manually
                const formData = new FormData();
                const csrfToken = directEntryForm.querySelector('input[name="_token"]');
                if (csrfToken) formData.append('_token', csrfToken.value);

                // Add Direct Entry specific fields
                const deFields = ['de_qualification', 'de_institution', 'de_grad_year', 'de_grade',
                    'de_reg_number'
                ];
                deFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && field.value) {
                        formData.append(fieldName, field.value);
                    }
                });

                // Add file upload if present
                const credentialsFile = document.getElementById('de_credentials');
                if (credentialsFile && credentialsFile.files.length > 0) {
                    formData.append('de_credentials', credentialsFile.files[0]);
                    console.log('DE credentials file added:', credentialsFile.files[0].name);
                }

                try {
                    const response = await fetch('/application/direct-entry-info', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.success,
                            icon: 'success',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#198754'
                        });
                    } else if (data.errors) {
                        // Handle validation errors
                        const errorMessages = [];
                        const fieldNames = {
                            'de_qualification': 'Qualification Type',
                            'de_institution': 'Institution',
                            'de_grad_year': 'Graduation Year',
                            'de_grade': 'Grade/Result',
                            'de_reg_number': 'Registration Number'
                        };

                        for (let field in data.errors) {
                            const fieldLabel = fieldNames[field] || field.toUpperCase();
                            if (Array.isArray(data.errors[field])) {
                                data.errors[field].forEach(error => {
                                    errorMessages.push(`• ${fieldLabel}: ${error}`);
                                });
                            } else {
                                errorMessages.push(`• ${fieldLabel}: ${data.errors[field]}`);
                            }
                        }

                        Swal.fire({
                            title: 'Validation Errors',
                            html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                                errorMessages.join('<br>') + '</div>',
                            icon: 'error',
                            confirmButtonText: 'Fix Errors',
                            confirmButtonColor: '#dc3545',
                            width: '500px'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'Unknown error occurred',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (err) {
                    console.error('Direct Entry AJAX Error:', err);
                    Swal.fire({
                        title: 'Network Error',
                        text: 'Network error: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } finally {
                    // Reset button
                    directEntryBtn.disabled = false;
                    directEntryBtn.innerHTML =
                        '<i class="fas fa-save me-1"></i> Save Direct Entry Information';
                }
            });
        }

        // Next of Kin Information AJAX Form
        const nextOfKinForm = document.getElementById('applicationForm');
        const nextOfKinBtn = document.getElementById('saveNextOfKinBtn');

        if (nextOfKinBtn && nextOfKinForm) {
            nextOfKinBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Visual feedback
                nextOfKinBtn.disabled = true;
                nextOfKinBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                // Show loading popup
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your Next of Kin information',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Collect Next of Kin fields manually
                const formData = new FormData();
                const csrfToken = nextOfKinForm.querySelector('input[name="_token"]');
                if (csrfToken) formData.append('_token', csrfToken.value);

                // Add Next of Kin specific fields
                const nokFields = ['nok_name', 'nok_relationship', 'nok_phone', 'nok_email',
                    'nok_address'
                ];
                nokFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && field.value.trim()) {
                        formData.append(fieldName, field.value.trim());
                    } else if (fieldName === 'nok_email') {
                        // Email is optional, can be empty
                        formData.append(fieldName, field ? field.value.trim() : '');
                    }
                });

                try {
                    const response = await fetch('/application/next-of-kin-info', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.success,
                            icon: 'success',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#198754'
                        });
                    } else if (data.errors) {
                        // Handle validation errors
                        const errorMessages = [];
                        const fieldNames = {
                            'nok_name': 'Full Name',
                            'nok_relationship': 'Relationship',
                            'nok_phone': 'Phone Number',
                            'nok_email': 'Email Address',
                            'nok_address': 'Address'
                        };

                        for (let field in data.errors) {
                            const fieldLabel = fieldNames[field] || field.toUpperCase();
                            if (Array.isArray(data.errors[field])) {
                                data.errors[field].forEach(error => {
                                    errorMessages.push(`• ${fieldLabel}: ${error}`);
                                });
                            } else {
                                errorMessages.push(`• ${fieldLabel}: ${data.errors[field]}`);
                            }
                        }

                        Swal.fire({
                            title: 'Validation Errors',
                            html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                                errorMessages.join('<br>') + '</div>',
                            icon: 'error',
                            confirmButtonText: 'Fix Errors',
                            confirmButtonColor: '#dc3545',
                            width: '500px'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'Unknown error occurred',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (err) {
                    console.error('Next of Kin AJAX Error:', err);
                    Swal.fire({
                        title: 'Network Error',
                        text: 'Network error: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } finally {
                    // Reset button
                    nextOfKinBtn.disabled = false;
                    nextOfKinBtn.innerHTML =
                        '<i class="fas fa-save me-1"></i> Save Next of Kin Information';
                }
            });
        }

        // Sponsor Information AJAX Form
        const sponsorForm = document.getElementById('applicationForm');
        const sponsorBtn = document.getElementById('saveSponsorBtn');

        if (sponsorBtn && sponsorForm) {
            sponsorBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Visual feedback
                sponsorBtn.disabled = true;
                sponsorBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                // Show loading popup
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your Sponsor information',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Collect Sponsor fields manually
                const formData = new FormData();
                const csrfToken = sponsorForm.querySelector('input[name="_token"]');
                if (csrfToken) formData.append('_token', csrfToken.value);

                // Add Sponsor specific fields
                const sponsorFields = ['s_name', 's_phone', 's_address'];
                sponsorFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && field.value.trim()) {
                        formData.append(fieldName, field.value.trim());
                    }
                });

                try {
                    const response = await fetch('/application/sponsor-info', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.success,
                            icon: 'success',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#198754'
                        });
                    } else if (data.errors) {
                        // Handle validation errors
                        const errorMessages = [];
                        const fieldNames = {
                            's_name': 'Sponsor Name',
                            's_phone': 'Sponsor Phone',
                            's_address': 'Sponsor Address'
                        };

                        for (let field in data.errors) {
                            const fieldLabel = fieldNames[field] || field.toUpperCase();
                            if (Array.isArray(data.errors[field])) {
                                data.errors[field].forEach(error => {
                                    errorMessages.push(`• ${fieldLabel}: ${error}`);
                                });
                            } else {
                                errorMessages.push(`• ${fieldLabel}: ${data.errors[field]}`);
                            }
                        }

                        Swal.fire({
                            title: 'Validation Errors',
                            html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                                errorMessages.join('<br>') + '</div>',
                            icon: 'error',
                            confirmButtonText: 'Fix Errors',
                            confirmButtonColor: '#dc3545',
                            width: '500px'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'Unknown error occurred',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (err) {
                    console.error('Sponsor AJAX Error:', err);
                    Swal.fire({
                        title: 'Network Error',
                        text: 'Network error: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } finally {
                    // Reset button
                    sponsorBtn.disabled = false;
                    sponsorBtn.innerHTML =
                        '<i class="fas fa-save me-1"></i> Save Sponsor Information';
                }
            });
        }

        // Document Upload AJAX Form
        const docForm = document.getElementById('applicationForm');
        const docBtn = document.getElementById('saveDocumentsBtn');

        if (docBtn && docForm) {
            docBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Visual feedback
                docBtn.disabled = true;
                docBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';

                // Show loading popup
                Swal.fire({
                    title: 'Uploading Documents...',
                    text: 'Please wait while we upload your documents',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Collect document files
                const formData = new FormData();
                const csrfToken = docForm.querySelector('input[name="_token"]');
                if (csrfToken) formData.append('_token', csrfToken.value);

                // Get all document upload inputs
                const documentInputs = document.querySelectorAll('.document-upload');
                let hasFiles = false;
                let totalSize = 0;
                const maxSizeKB = 400;
                const missingRequired = [];
                const fileSizeErrors = [];

                // First pass: Check for required documents and file size issues
                documentInputs.forEach(input => {
                    const isRequired = input.hasAttribute('required');
                    const docLabel = input.getAttribute('data-doc-label');
                    const hasFile = input.files && input.files[0];
                    const docType = input.id;

                    // Check if this document already exists (uploaded previously)
                    const previewContainer = document.getElementById(`preview_${docType}`);
                    const hasExistingFile = previewContainer && previewContainer.style
                        .display !== 'none' &&
                        previewContainer.querySelector('.file-actions');

                    // Check if required document is missing (no current file AND no existing file)
                    if (isRequired && !hasFile && !hasExistingFile) {
                        missingRequired.push(docLabel);
                    }

                    // Check file size if file is present (only for NEW uploads)
                    if (hasFile) {
                        const file = input.files[0];
                        const fileSizeKB = Math.round(file.size / 1024);

                        if (fileSizeKB > maxSizeKB) {
                            fileSizeErrors.push(
                                `${docLabel}: ${fileSizeKB}KB (max ${maxSizeKB}KB)`);
                        }
                    }
                });

                // Show validation errors if any
                if (missingRequired.length > 0) {
                    Swal.fire({
                        title: 'Required Documents Missing',
                        html: `<div style="text-align: left; font-size: 14px;"><strong>Please upload the following required documents marked with *:</strong><br><br>` +
                            missingRequired.map(doc => `• ${doc}`).join('<br>') + '</div>',
                        icon: 'warning',
                        confirmButtonText: 'Upload Required Documents',
                        confirmButtonColor: '#ffc107',
                        width: '500px'
                    });
                    docBtn.disabled = false;
                    docBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Documents';
                    return;
                }

                if (fileSizeErrors.length > 0) {
                    Swal.fire({
                        title: 'File Size Errors',
                        html: `<div style="text-align: left; font-size: 14px;"><strong>The following files exceed the 400KB limit:</strong><br><br>` +
                            fileSizeErrors.map(error => `• ${error}`).join('<br>') +
                            '</div>',
                        icon: 'error',
                        confirmButtonText: 'Fix File Sizes',
                        confirmButtonColor: '#dc3545',
                        width: '500px'
                    });
                    docBtn.disabled = false;
                    docBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Documents';
                    return;
                }

                // Second pass: Collect valid files for upload
                documentInputs.forEach(input => {
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        const fileSizeKB = Math.round(file.size / 1024);

                        totalSize += fileSizeKB;
                        hasFiles = true;

                        // Append file with doc type info
                        formData.append('documents[]', file);
                        formData.append('doc_types[]', input.id);
                        formData.append('doc_labels[]', input.getAttribute(
                            'data-doc-label'));
                    }
                });

                // Check if we have new files to upload OR if all required documents are already satisfied
                let hasExistingRequiredDocs = true;
                documentInputs.forEach(input => {
                    const isRequired = input.hasAttribute('required');
                    if (isRequired) {
                        const docType = input.id;
                        const previewContainer = document.getElementById(
                            `preview_${docType}`);
                        const hasExistingFile = previewContainer && previewContainer.style
                            .display !== 'none' &&
                            previewContainer.querySelector('.file-actions');
                        const hasNewFile = input.files && input.files[0];

                        if (!hasExistingFile && !hasNewFile) {
                            hasExistingRequiredDocs = false;
                        }
                    }
                });

                // Allow save if we have new files OR if just updating and all required docs exist
                if (!hasFiles && !hasExistingRequiredDocs) {
                    Swal.fire({
                        title: 'No Files Selected',
                        text: 'Please select at least one document to upload.',
                        icon: 'warning',
                        confirmButtonText: 'Select Files',
                        confirmButtonColor: '#ffc107'
                    });
                    docBtn.disabled = false;
                    docBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Documents';
                    return;
                }

                // If no new files but all required documents exist, show different message
                if (!hasFiles && hasExistingRequiredDocs) {
                    Swal.fire({
                        title: 'No New Files',
                        text: 'No new files selected for upload. All required documents are already uploaded.',
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#17a2b8'
                    });
                    docBtn.disabled = false;
                    docBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Documents';
                    return;
                }

                try {
                    const response = await fetch('/application/documents', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Upload Successful!',
                            text: data.success,
                            icon: 'success',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            // Optionally refresh the page to show updated file previews
                            // location.reload();

                            // Or update UI to show uploaded files
                            documentInputs.forEach(input => {
                                if (input.files && input.files[0]) {
                                    const previewContainer = document
                                        .getElementById(`preview_${input.id}`);
                                    const fileActions = previewContainer
                                        ?.querySelector('.file-actions');
                                    if (fileActions) {
                                        fileActions.style.display = 'flex';
                                    }
                                }
                            });
                        });
                    } else if (data.errors) {
                        // Handle validation errors
                        const errorMessages = [];

                        if (typeof data.errors === 'object') {
                            for (let field in data.errors) {
                                if (Array.isArray(data.errors[field])) {
                                    data.errors[field].forEach(error => {
                                        errorMessages.push(`• ${error}`);
                                    });
                                } else {
                                    errorMessages.push(`• ${data.errors[field]}`);
                                }
                            }
                        } else {
                            errorMessages.push(`• ${data.errors}`);
                        }

                        Swal.fire({
                            title: 'Upload Errors',
                            html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                                errorMessages.join('<br>') + '</div>',
                            icon: 'error',
                            confirmButtonText: 'Fix Errors',
                            confirmButtonColor: '#dc3545',
                            width: '500px'
                        });
                    } else {
                        Swal.fire({
                            title: 'Upload Error',
                            text: data.error || 'Unknown error occurred during upload',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (err) {
                    console.error('Document Upload AJAX Error:', err);
                    Swal.fire({
                        title: 'Network Error',
                        text: 'Network error during upload: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                } finally {
                    // Reset button
                    docBtn.disabled = false;
                    docBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Documents';
                }
            });
        }

        // SSCE Information AJAX Form with retry mechanism
        function initSsceAjax(attempt = 1) {
            console.log(`SSCE Init Attempt ${attempt}:`);

            const ssceForm = document.getElementById(
                'applicationForm'); // Use main form since ssceInfoForm doesn't exist
            const ssceSaveBtn = document.getElementById('saveSsceBtn');
            const ssceToken = ssceForm ? ssceForm.querySelector('input[name="_token"]') : null;

            console.log('FIXED: Using applicationForm instead of missing ssceInfoForm');

            console.log('- SSCE Form:', ssceForm);
            console.log('- SSCE Button:', ssceSaveBtn);
            console.log('- SSCE Token:', ssceToken ? ssceToken.value : 'NOT FOUND');

            // Additional DOM debugging
            console.log('=== DOM DEBUGGING ===');
            const allForms = document.querySelectorAll('form');
            console.log('Total forms on page:', allForms.length);
            allForms.forEach((form, index) => {
                console.log(`Form ${index + 1}:`, {
                    id: form.id,
                    action: form.action,
                    method: form.method,
                    element: form
                });
            });

            // Look for any elements with 'ssce' in ID or class
            const ssceElements = document.querySelectorAll('[id*="ssce" i], [class*="ssce" i]');
            console.log('Elements with "ssce" in ID/class:', ssceElements.length);
            ssceElements.forEach((el, index) => {
                console.log(`SSCE Element ${index + 1}:`, {
                    tag: el.tagName,
                    id: el.id,
                    className: el.className,
                    element: el
                });
            });
            console.log('=== END DOM DEBUG ===');

            if (ssceForm && ssceSaveBtn && ssceToken && typeof Swal !== 'undefined') {
                console.log('SSCE AJAX: All elements found, attaching handler...');
                ssceSaveBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    console.log('SSCE Button clicked!');

                    // Visual feedback
                    ssceSaveBtn.disabled = true;
                    ssceSaveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                    // Show loading popup
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait while we save your SSCE information',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Collect only SSCE-related form data from the main form
                    const formData = new FormData();

                    // Add CSRF token
                    formData.append('_token', ssceToken.value);

                    // Add SSCE basic info
                    const ssceType = document.getElementById('ssce_type_1');
                    const ssceYear = document.getElementById('ssce_year_1');
                    const ssceRegNumber = document.getElementById('ssce_reg_number_1');
                    const ssceCenter = document.getElementById('ssce_center_1');

                    if (ssceType) formData.append('ssce_type_1', ssceType.value);
                    if (ssceYear) formData.append('ssce_year_1', ssceYear.value);
                    if (ssceRegNumber) formData.append('ssce_reg_number_1', ssceRegNumber.value);
                    if (ssceCenter) formData.append('ssce_center_1', ssceCenter.value);

                    // Add first sitting subjects and grades (1-9)
                    for (let i = 1; i <= 9; i++) {
                        const subject = document.getElementById(`subject_1_${i}`);
                        const grade = document.getElementById(`grade_1_${i}`);

                        if (subject && subject.value) {
                            formData.append(`subject_1_${i}`, subject.value);
                        }
                        if (grade && grade.value) {
                            formData.append(`grade_1_${i}`, grade.value);
                        }
                    }

                    // Add second sitting data if it exists
                    const ssceType2 = document.getElementById('ssce_type_2');
                    const ssceYear2 = document.getElementById('ssce_year_2');
                    const ssceRegNumber2 = document.getElementById('ssce_reg_number_2');
                    const ssceCenter2 = document.getElementById('ssce_center_2');

                    if (ssceType2) formData.append('ssce_type_2', ssceType2.value);
                    if (ssceYear2) formData.append('ssce_year_2', ssceYear2.value);
                    if (ssceRegNumber2) formData.append('ssce_reg_number_2', ssceRegNumber2.value);
                    if (ssceCenter2) formData.append('ssce_center_2', ssceCenter2.value);

                    // Add second sitting subjects and grades (1-9)
                    for (let i = 1; i <= 9; i++) {
                        const subject2 = document.getElementById(`subject_2_${i}`);
                        const grade2 = document.getElementById(`grade_2_${i}`);

                        if (subject2 && subject2.value) {
                            formData.append(`subject_2_${i}`, subject2.value);
                        }
                        if (grade2 && grade2.value) {
                            formData.append(`grade_2_${i}`, grade2.value);
                        }
                    }

                    console.log('SSCE FormData prepared with only SSCE fields');

                    try {
                        const response = await fetch('/application/ssce-info', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': ssceToken.value,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        let data;
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            data = await response.text();
                        }

                        // Close loading popup and show result
                        Swal.close();

                        if (response.ok && typeof data === 'object' && data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.success,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#28a745'
                            });
                        } else if (typeof data === 'object' && data.errors) {
                            let errorMessages = [];
                            const fieldNames = {
                                'ssce_type_1': 'SSCE Type',
                                'ssce_year_1': 'SSCE Year',
                                'ssce_reg_number_1': 'SSCE Registration Number',
                                'ssce_center_1': 'Examination Center',
                                'subject_1_1': 'Subject 1',
                                'subject_1_2': 'Subject 2',
                                'subject_1_3': 'Subject 3',
                                'subject_1_4': 'Subject 4',
                                'subject_1_5': 'Subject 5',
                                'grade_1_1': 'Grade 1',
                                'grade_1_2': 'Grade 2',
                                'grade_1_3': 'Grade 3',
                                'grade_1_4': 'Grade 4',
                                'grade_1_5': 'Grade 5'
                            };
                            for (let field in data.errors) {
                                const fieldLabel = fieldNames[field] || field.toUpperCase();
                                if (Array.isArray(data.errors[field])) {
                                    data.errors[field].forEach(error => {
                                        errorMessages.push(
                                            `• ${fieldLabel}: ${error.replace('The ' + field + ' field', 'This field')}`
                                        );
                                    });
                                } else {
                                    errorMessages.push(
                                        `• ${fieldLabel}: ${data.errors[field].replace('The ' + field + ' field', 'This field')}`
                                    );
                                }
                            }
                            Swal.fire({
                                title: 'Validation Errors',
                                html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following errors:</strong><br><br>' +
                                    errorMessages.join('<br>') + '</div>',
                                icon: 'error',
                                confirmButtonText: 'Fix Errors',
                                confirmButtonColor: '#dc3545',
                                width: '500px'
                            });
                        } else if (typeof data === 'object' && data.error) {
                            Swal.fire({
                                title: 'Error',
                                text: data.error,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        } else if (typeof data === 'string') {
                            Swal.fire({
                                title: 'Server Error',
                                text: 'Server returned HTML instead of JSON. Please check console for details.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            Swal.fire({
                                title: 'Unknown Error',
                                text: 'Unknown response format received from server.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }

                    } catch (err) {
                        console.error('SSCE AJAX Error:', err);
                        Swal.close();
                        Swal.fire({
                            title: 'Network Error',
                            text: 'Network error: ' + err.message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    } finally {
                        // Reset button
                        ssceSaveBtn.disabled = false;
                        ssceSaveBtn.innerHTML =
                            '<i class="fas fa-save me-1"></i> Save SSCE Information';
                    }
                });
            } else {
                console.log(`SSCE AJAX Attempt ${attempt}: Missing elements`);
                console.log('- ssceForm:', !!ssceForm);
                console.log('- ssceSaveBtn:', !!ssceSaveBtn);
                console.log('- ssceToken:', !!ssceToken);
                console.log('- Swal available:', typeof Swal !== 'undefined');

                // Retry up to 5 times with 500ms delay
                if (attempt < 5) {
                    console.log(`Retrying SSCE init in 500ms...`);
                    setTimeout(() => initSsceAjax(attempt + 1), 500);
                } else {
                    console.error('SSCE AJAX: Failed to initialize after 5 attempts');
                }
            }
        }

        // Start SSCE initialization
        initSsceAjax();

        // Final Application Submission
        const submitBtn = document.getElementById('submitApplicationBtn');

        if (submitBtn) {
            submitBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Check if all required sections are saved
                const unsavedSections = await checkUnsavedSections();

                if (unsavedSections.length > 0) {
                    Swal.fire({
                        title: 'Unsaved Sections Detected!',
                        html: `<div style="text-align: left; font-size: 14px;"><strong>Please save the following sections before submitting:</strong><br><br>` +
                            unsavedSections.map(section => `• ${section}`).join('<br>') +
                            '<br><br><em>Use the "Save" buttons in each section to save your data.</em></div>',
                        icon: 'warning',
                        confirmButtonText: 'Save Required Sections',
                        confirmButtonColor: '#ffc107',
                        width: '500px'
                    });
                    return;
                }

                // Show final confirmation warning with checkbox
                Swal.fire({
                    title: '⚠️ Final Submission Warning',
                    html: `<div style="text-align: left; font-size: 14px;">
                           <strong>Important Notice:</strong><br><br>
                           • Once you submit this application, <strong>NO MODIFICATIONS</strong> will be allowed<br>
                           • You will not be able to edit any information after submission<br>
                           • Please review all your data carefully before proceeding<br><br>
                           <div style="border: 2px solid #dc3545; padding: 15px; background: #fff5f5; border-radius: 5px; margin: 15px 0;">
                               <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                   <input type="checkbox" id="finalConfirmCheckbox" style="margin-right: 10px; transform: scale(1.2);">
                                   <span style="font-weight: bold; color: #dc3545;">I confirm that I have reviewed all information and understand that this submission is FINAL and cannot be modified.</span>
                               </label>
                           </div>
                           </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Submit Final Application',
                    cancelButtonText: 'Cancel - Let me review',
                    width: '700px',
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: 'btn-danger'
                    },
                    preConfirm: () => {
                        const checkbox = document.getElementById(
                            'finalConfirmCheckbox');
                        if (!checkbox.checked) {
                            Swal.showValidationMessage(
                                'You must check the confirmation box to proceed');
                            return false;
                        }
                        return true;
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        await submitFinalApplication();
                    }
                });
            });
        }

        // Function to check for unsaved sections
        async function checkUnsavedSections() {
            const unsavedSections = [];

            try {
                // Check each section by making API calls to verify data exists
                const response = await fetch('/application/check-completion', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                if (response.ok) {
                    const data = await response.json();

                    if (!data.personal_info) unsavedSections.push('Personal Information');
                    if (!data.jamb_info && '{{ strtoupper($applicant->mode) }}' !== 'DE') unsavedSections
                        .push('JAMB Information');
                    if (!data.ssce_info) unsavedSections.push('SSCE Information');
                    if (!data.direct_entry && '{{ strtoupper($applicant->mode) }}' === 'DE')
                        unsavedSections.push('Direct Entry Information');
                    if (!data.next_of_kin) unsavedSections.push('Next of Kin Information');
                    if (!data.sponsor) unsavedSections.push('Sponsor Information');
                    if (!data.documents) unsavedSections.push('Document Upload');
                }
            } catch (err) {
                console.error('Error checking section completion:', err);
                // If we can't check, assume all sections need to be saved
                unsavedSections.push(
                    'Unable to verify all sections - please ensure all sections are saved');
            }

            return unsavedSections;
        }

        // Function to submit final application
        async function submitFinalApplication() {
            // Show loading state
            Swal.fire({
                title: 'Submitting Application...',
                html: `<div style="text-align: center;">
                       <div class="spinner-border text-primary mb-3" role="status">
                         <span class="visually-hidden">Loading...</span>
                       </div><br>
                       Please wait while we process your final submission...<br>
                       <small class="text-muted">This may take a few moments</small>
                       </div>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('/application/final-submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Success - application submitted
                    Swal.fire({
                        title: '🎉 Application Submitted Successfully!',
                        html: `<div style="text-align: center; font-size: 14px;">
                               <div class="text-success mb-3">
                                 <i class="fas fa-check-circle" style="font-size: 48px;"></i>
                               </div>
                               <strong>${data.success}</strong><br><br>
                               <div class="alert alert-info" style="font-size: 13px;">
                                 📋 Your application is now under review by our admissions team.<br>
                                 📜 Once admitted, you can download your admission letter using your login credentials.<br>
                                 📱 You can check your application status anytime through your dashboard.
                               </div>
                               <em>Thank you for applying to UNIMAID!</em>
                               </div>`,
                        icon: 'success',
                        confirmButtonText: 'Continue to Dashboard',
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false,
                        width: '600px'
                    }).then(() => {
                        // Redirect to dashboard or success page
                        window.location.href = '/applicant-dashboard';
                    });
                } else if (data.errors) {
                    // Handle validation errors
                    const errorMessages = [];

                    if (typeof data.errors === 'object') {
                        for (let field in data.errors) {
                            if (Array.isArray(data.errors[field])) {
                                data.errors[field].forEach(error => {
                                    errorMessages.push(`• ${error}`);
                                });
                            } else {
                                errorMessages.push(`• ${data.errors[field]}`);
                            }
                        }
                    }

                    Swal.fire({
                        title: 'Submission Failed',
                        html: '<div style="text-align: left; font-size: 14px;"><strong>Please fix the following issues:</strong><br><br>' +
                            errorMessages.join('<br>') + '</div>',
                        icon: 'error',
                        confirmButtonText: 'Fix Issues',
                        confirmButtonColor: '#dc3545',
                        width: '500px'
                    });
                } else {
                    Swal.fire({
                        title: 'Submission Error',
                        text: data.error || 'Unknown error occurred during submission',
                        icon: 'error',
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (err) {
                console.error('Final submission error:', err);
                Swal.fire({
                    title: 'Network Error',
                    text: 'Network error during submission: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    });
</script>

</html>
