<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'UNIMAID') }} - Application</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="University Application Dashboard" />
    <meta name="keywords" content="dashboard, university, application">
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .dashboard-body {
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

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .status-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }

        .status-card:hover {
            transform: translateY(-3px);
        }

        .status-pending {
            border-left: 5px solid #ffc107;
        }

        .status-submitted {
            border-left: 5px solid #17a2b8;
        }

        .status-admitted {
            border-left: 5px solid #28a745;
        }

        .status-rejected {
            border-left: 5px solid #dc3545;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .row .col-6 {
            padding: 0.25rem 0.75rem;
        }

        .admission-letter-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .admission-letter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .document-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin: 2px;
            display: inline-block;
        }

        .progress-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 0.5rem;
            }

            .dashboard-body {
                padding: 1rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .row .col-6 {
                font-size: 0.9rem;
            }
        }

        /* University Header Card */
        .university-header-card {
            margin-bottom: 2rem;
        }

        /* Action Cards */
        .quick-actions-card {
            margin-bottom: 2rem;
        }

        .action-card {
            display: block;
            color: inherit;
        }

        .action-hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e7e9fb 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .action-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .action-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .action-icon i {
            width: 60px;
            height: 60px;
            line-height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Enhanced Info Cards */
        .info-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        /* Better spacing for data rows */
        .info-card .row .col-6 {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-card .row .col-6:nth-child(odd) {
            background: #f8fafc;
            font-weight: 500;
        }

        /* Enhanced status badges */
        .badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        /* Document badges improvements */
        .document-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #90caf9;
            transition: all 0.2s ease;
        }

        .document-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- University Header with Logo -->
        <div class="university-header-card">
            <div class="card shadow-lg rounded-4 border-0 overflow-hidden bg-white position-relative"
                style="background: linear-gradient(135deg, #f8fafc 60%, #e7e9fb 100%);">
                <div class="d-flex flex-column flex-md-row align-items-center p-2 p-md-4 gap-1 gap-md-3">
                    <div class="flex-shrink-0 text-center mb-1 mb-md-0">
                        @php
                            $photoPath = DB::table('document_uploads')
                                ->where(['user_id' => $applicant->user_id, 'doc_type' => 'passport_photo'])
                                ->value('file_path');
                            $photoExists = file_exists(public_path('storage/' . $photoPath));
                        @endphp
                        @if ($photoExists)
                            <img src="{{ asset('storage/' . $photoPath) }}" alt="profile picture"
                                class="rounded-circle shadow bg-white"
                                style="width: 80px; height: 80px; border: 3px solid #fff;">
                        @else
                            <img src="{{ asset('uploads/profile.jpg') }}" alt="profile picture"
                                class="rounded-circle shadow bg-white"
                                style="width: 80px; height: 80px; border: 3px solid #fff;">
                        @endif
                    </div>
                    <div class="flex-grow-1 text-center text-md-start">
                        <h3 class="display-6 fw-bold mb-1 text-center" style="color:#4e54c8;letter-spacing:0.01em;">
                            UNIMAID POST UTME DASHBOARD</h3>
                        <p class="text-muted mb-0 text-center">Welcome back, {{ $applicant->first_name }}
                            {{ $applicant->surname }}!
                        </p>
                    </div>
                    <div
                        class="flex-shrink-0 align-self-md-start align-self-center mt-1 mt-md-0 d-flex flex-row flex-md-column gap-2">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#updatePasswordModal"
                            class="btn btn-outline-primary btn-sm px-3 py-1 rounded-pill fw-semibold d-inline-flex align-items-center">
                            <i class="fas fa-key me-1"></i> Password
                        </button>
                        <a href="/logout"
                            class="btn btn-outline-danger btn-sm px-3 py-1 rounded-pill fw-semibold d-inline-flex align-items-center">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
                <!-- SVG Decorative Wave -->
                <div style="height:25px;overflow:hidden;line-height:0;">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none"
                        style="height:100%;width:100%;fill:#667eea;opacity:0.8;">
                        <path
                            d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <div class="action-card">
                                <div class="card border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3" style="color: #28a745;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h5 class="card-title">Application Progress</h5>
                                        <div class="progress mt-3" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $progress['percentage'] }}%"
                                                aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $progress['percentage'] }}% Complete
                                            ({{ $progress['completed'] }}/{{ $progress['total'] }} sections)</small>

                                        <div class="mt-3">
                                            <a href="{{ route('admin.applicant.pdf', $applicant->id) }}"
                                                class="btn btn-outline-primary btn-sm" title="Download Application PDF">
                                                <i class="fas fa-file-pdf me-1"></i> Download Application
                                            </a>
                                            {{-- edit application button --}}
                                            <a href="/change-application-status"
                                                class="btn btn-outline-secondary btn-sm" title="Edit Application">
                                                <i class="fas fa-edit me-1"></i> Edit Application
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Application Status Section -->
                <div class="info-card">
                    <div class="card-body p-4">
                        <h2 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>Application Status
                        </h2>
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <span
                                        class="badge bg-{{ $applicant->status == 'Admitted' ? 'success' : ($applicant->status == 'Rejected' ? 'danger' : ($applicant->status == 'Submitted' ? 'info' : 'warning')) }} me-2">
                                        {{ $applicant->status }}
                                    </span>
                                    <span class="text-muted">Reference: {{ $applicant->username }}</span>
                                </div>

                                @if ($applicant->status == 'Pending')
                                    <p class="text-warning mb-0">
                                        <i class="fas fa-clock me-1"></i>Your application is incomplete. Please complete
                                        all sections.
                                    </p>
                                @elseif($applicant->status == 'Submitted')
                                    <p class="text-info mb-0">
                                        <i class="fas fa-paper-plane me-1"></i>Your application is under review by our
                                        admissions team.
                                    </p>
                                    @if ($applicant->submitted_at)
                                        <small class="text-muted">Submitted on:
                                            {{ date('F j, Y g:i A', strtotime($applicant->submitted_at)) }}</small>
                                    @endif
                                @elseif($applicant->status == 'Admitted')
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check-circle me-1"></i>Congratulations! You have been admitted.
                                    </p>
                                    <button class="btn admission-letter-btn" onclick="downloadAdmissionLetter()">
                                        <i class="fas fa-download me-2"></i>Download Admission Letter
                                    </button>
                                @elseif($applicant->status == 'Rejected')
                                    <p class="text-danger mb-0">
                                        <i class="fas fa-times-circle me-1"></i>We regret to inform you that your
                                        application was not successful.
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="progress-card p-3">
                                    <h6 class="mb-2">Application Progress</h6>
                                    <div class="position-relative">
                                        <svg width="80" height="80" viewBox="0 0 80 80">
                                            <circle cx="40" cy="40" r="35"
                                                stroke="rgba(255,255,255,0.3)" stroke-width="8" fill="none" />
                                            <circle cx="40" cy="40" r="35" stroke="white"
                                                stroke-width="8" fill="none"
                                                stroke-dasharray="{{ 2 * 3.14159 * 35 }}"
                                                stroke-dashoffset="{{ 2 * 3.14159 * 35 * (1 - $progress['percentage'] / 100) }}"
                                                stroke-linecap="round" transform="rotate(-90 40 40)" />
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <strong>{{ $progress['percentage'] }}%</strong>
                                        </div>
                                    </div>
                                    <small class="mt-2 d-block">{{ $progress['completed'] }}/{{ $progress['total'] }}
                                        sections completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Quick Stats -->
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="card info-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                    <h6>Academic Session</h6>
                                    <strong>{{ $applicant->session }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card info-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-graduation-cap fa-2x text-success mb-2"></i>
                                    <h6>Program</h6>
                                    <strong>{{ $applicant->programs->title }} ({{ $applicant->mode }})</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Detailed Information Cards -->
        <div class="row">
            <!-- Personal Information -->
            <div class="col-lg-6 mb-4">
                <div class="info-card">
                    <div class="card-body p-4">
                        <h3 class="section-title">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h3>
                        <div class="row">
                            <div class="col-6 mb-2"><strong>Full Name:</strong></div>
                            <div class="col-6">{{ $applicant->fullname }}</div>

                            <div class="col-6 mb-2"><strong>Email:</strong></div>
                            <div class="col-6">{{ $applicant->email ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Phone:</strong></div>
                            <div class="col-6">{{ $applicant->phone ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Date of Birth:</strong></div>
                            <div class="col-6">
                                {{ $applicant->dob ? date('F j, Y', strtotime($applicant->dob)) : 'Not provided' }}
                            </div>

                            <div class="col-6 mb-2"><strong>Faculty:</strong></div>
                            <div class="col-6">{{ $applicant->facultys->title }}</div>

                            <div class="col-6 mb-2"><strong>Department:</strong></div>
                            <div class="col-6">{{ $applicant->departments->title }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="col-lg-6 mb-4">
                <div class="card info-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book me-2"></i>Academic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (strtoupper($applicant->mode) === 'UTME' && count($jambData) > 0)
                            <h6 class="text-primary mb-3">JAMB Information</h6>
                            <div class="row">
                                @foreach ($jambData as $jamb)
                                    <div class="col-6 mb-2">
                                        <strong>{{ $jamb->subject }}:</strong>
                                        <span class="badge bg-primary">{{ $jamb->score }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strtoupper($applicant->mode) === 'DE' && $deData)
                            <h6 class="text-primary mb-3">Direct Entry Qualification</h6>
                            <div class="row">
                                <div class="col-12 mb-2"><strong>Qualification:</strong>
                                    {{ $deData->qualification_type }}</div>
                                <div class="col-12 mb-2"><strong>Institution:</strong> {{ $deData->institution }}
                                </div>
                                <div class="col-6 mb-2"><strong>Year:</strong> {{ $deData->grad_year }}</div>
                                <div class="col-6 mb-2"><strong>Grade:</strong> {{ $deData->grade }}</div>
                            </div>
                        @endif

                        @if (count($ssceData) > 0)
                            <h6 class="text-success mt-4 mb-3">SSCE Results</h6>
                            @foreach ($ssceData as $index => $ssce)
                                <div class="mb-3">
                                    <h6 class="text-muted">{{ $ssce->type }} ({{ $ssce->year }})</h6>
                                    @if (isset($ssceResults[$ssce->id]))
                                        <div class="row">
                                            @foreach ($ssceResults[$ssce->id] as $result)
                                                <div class="col-6 col-md-4 mb-1">
                                                    <small>{{ $result->subject }}:
                                                        <strong>{{ $result->grade }}</strong></small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Next of Kin and Sponsor Information -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card info-card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Next of Kin</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2"><strong>Name:</strong></div>
                            <div class="col-6">{{ $applicant->n_name ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Relationship:</strong></div>
                            <div class="col-6">{{ $applicant->n_relationship ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Phone:</strong></div>
                            <div class="col-6">{{ $applicant->n_phone ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Email:</strong></div>
                            <div class="col-6">{{ $applicant->n_email ?: 'Not provided' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card info-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-hand-holding-heart me-2"></i>Sponsor Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2"><strong>Name:</strong></div>
                            <div class="col-6">{{ $applicant->s_name ?: 'Not provided' }}</div>

                            <div class="col-6 mb-2"><strong>Phone:</strong></div>
                            <div class="col-6">{{ $applicant->s_phone ?: 'Not provided' }}</div>

                            <div class="col-12 mb-2"><strong>Address:</strong></div>
                            <div class="col-12">{{ $applicant->s_address ?: 'Not provided' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploaded Documents -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card info-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Uploaded Documents</h5>
                    </div>
                    <div class="card-body">
                        @if (count($documents) > 0)
                            <div class="row">
                                @foreach ($documents as $document)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="border rounded p-3 text-center">
                                            <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                            <h6>{{ ucwords(str_replace('_', ' ', $document->doc_type)) }}</h6>
                                            <small class="text-muted">{{ $document->original_name }}</small><br>
                                            <small class="text-muted">{{ round($document->size / 1024, 1) }}
                                                KB</small>
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $document->file_path) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>No documents uploaded yet</p>
                                <a href="/application#documents" class="btn btn-primary">Upload Documents</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function downloadAdmissionLetter() {
            // Show confirmation
            Swal.fire({
                title: '📜 Download Admission Letter',
                text: 'Your admission letter will open in a new tab. You can then print or save it.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Download',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Open admission letter in new tab
                    window.open('/download-admission-letter', '_blank');
                }
            });
        }

        // Show success/error messages if any
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#28a745'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        @endif
    </script>

    <!-- Update Password Modal -->
    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="updatePasswordModalLabel">
                        <i class="fas fa-key me-2"></i>Update Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="update pass" id="updatePasswordForm">
                        @csrf
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" type="password" class="form-control" name="p1" required
                                placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input id="password_confirm" type="password" class="form-control" name="p2"
                                required placeholder="Confirm new password">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
