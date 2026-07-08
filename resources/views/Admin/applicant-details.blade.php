@php
    $page = 'applicant-details';
    use Illuminate\Support\Facades\Storage;
@endphp
<style>
    /* Modern Color Palette based on #3DA1E3 */
    :root {
        --primary-color: #3DA1E3;
        --primary-dark: #2d7bb8;
        --primary-light: #5db4e8;
        --primary-lighter: #e8f4fd;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
        --card-shadow: 0 4px 6px rgba(61, 161, 227, 0.1);
        --card-shadow-hover: 0 8px 25px rgba(61, 161, 227, 0.15);
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 30px 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(61, 161, 227, 0.3);
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        opacity: 1;
    }

    .profile-header h1 {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        font-weight: 700;
    }

    .back-button {
        margin-bottom: 1.5rem;
    }

    .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(61, 161, 227, 0.3);
    }

    .info-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
        overflow: hidden;
        background: white;
    }

    .info-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-shadow-hover);
    }

    .info-card .card-header {
        border-radius: 20px 20px 0 0 !important;
        font-weight: 700;
        padding: 1.25rem 2rem;
        border: none;
        color: white;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        position: relative;
    }

    .info-card .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
    }

    .info-card .card-body {
        padding: 2rem;
        background: linear-gradient(180deg, #fafbfc 0%, white 100%);
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(61, 161, 227, 0.1);
        transition: all 0.2s ease;
    }

    .info-row:hover {
        background-color: var(--primary-lighter);
        margin: 0 -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
        border-radius: 8px;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--primary-dark);
        flex: 1;
        display: flex;
        align-items: center;
    }

    .info-value {
        color: #2c3e50;
        flex: 2;
        text-align: right;
        font-weight: 500;
    }

    .action-buttons {
        position: sticky;
        top: 20px;
        z-index: 100;
    }

    .btn-action {
        padding: 0.875rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        margin: 0.5rem 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: none;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        border: none;
    }

    .document-item {
        border: 2px dashed rgba(61, 161, 227, 0.3);
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    }

    .document-item:hover {
        border-color: var(--primary-color);
        background: linear-gradient(145deg, var(--primary-lighter) 0%, #ffffff 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(61, 161, 227, 0.15);
    }

    .grade-badge {
        padding: 0.4rem 0.9rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
        font-size: 1.1rem;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .status-pending {
        background: linear-gradient(135deg, #ffc107 0%, #ffb347 100%);
        color: #fff;
    }

    .status-submitted {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: #fff;
    }

    .status-admitted {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
    }

    .status-rejected {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        color: #fff;
    }

    .profile-photo {
        position: relative;
        display: inline-block;
        filter: drop-shadow(0 8px 25px rgba(61, 161, 227, 0.3));
    }

    .profile-photo img {
        border: 4px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .profile-photo img:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 40px rgba(61, 161, 227, 0.3);
    }

    .profile-photo .position-absolute {
        animation: statusPulse 2s infinite;
        border: 3px solid rgba(255, 255, 255, 0.9);
    }

    @keyframes statusPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(61, 161, 227, 0.7);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(61, 161, 227, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(61, 161, 227, 0);
        }
    }

    .profile-photo::after {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border-radius: 50%;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
        z-index: -1;
    }

    .profile-photo img,
    .default-avatar {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-photo img:hover,
    .default-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3) !important;
    }

    .applicant-name {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        font-weight: 700;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-light) 100%);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .timeline-item:hover {
        transform: translateX(5px);
        box-shadow: var(--card-shadow-hover);
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2.25rem;
        top: 1.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--primary-lighter);
    }

    .timeline-date {
        font-size: 0.875rem;
        color: var(--primary-dark);
        font-weight: 600;
    }

    /* Print Styles */
    @media print {

        .back-button,
        .action-buttons,
        .btn {
            display: none !important;
        }

        .profile-header {
            background: var(--primary-color) !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .info-card {
            break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #ddd;
        }

        .info-card .card-header {
            background: var(--primary-color) !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }

    /* Additional Responsive Enhancements */
    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem 0;
            border-radius: 0 0 20px 20px;
        }

        .info-card {
            border-radius: 15px;
            margin-bottom: 1.5rem;
        }

        .info-card .card-body {
            padding: 1.5rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container-fluid">
    <!-- Back Button -->
    <div class="back-button">
        <a href="{{ url('applicants') }}" class="btn btn-outline-primary btn-action">
            <i class="fas fa-arrow-left me-2"></i>Back to Applicants List
        </a>
    </div>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container-fluid position-relative">
            <div class="row align-items-center">
                <div class="col-12 col-md-3 col-lg-2 text-center mb-3 mb-md-0">
                    @php
                        // Get passport photo from documents
                        $passportPhoto = $documents->where('doc_type', 'passport_photo')->first();
                    @endphp
                    <div class="profile-photo mb-3">
                        @if ($passportPhoto)
                            @php
                                // Check file path and generate correct URL
                                $photoPath = $passportPhoto->file_path;
                                // Check if file exists in storage directory
                                $photoExists = file_exists(public_path('storage/' . $photoPath));
                                // Generate URL using project's convention
$photoUrl = asset('storage/' . $photoPath);
                            @endphp
                            @if ($photoExists || $passportPhoto->file_path)
                                <a href="{{ $photoUrl }}">
                                    <img src="{{ $photoUrl }}" alt="{{ $applicant->fullname }}"
                                        class="rounded-circle border border-white border-3 shadow"
                                        style="width: 120px; height: 120px; object-fit: cover;"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                </a>
                            @else
                                <div class="default-avatar rounded-circle border border-white border-3 shadow bg-light d-flex align-items-center justify-content-center"
                                    style="width: 120px; height: 120px;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            @endif
                        @else
                            <div class="default-avatar rounded-circle border border-white border-3 shadow bg-light d-flex align-items-center justify-content-center"
                                style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        @endif

                        <!-- Status Indicator -->
                        @php
                            $statusColors = [
                                'Pending' => 'warning',
                                'Submitted' => 'info',
                                'Admitted' => 'success',
                                'Rejected' => 'danger',
                            ];
                            $statusColor = $statusColors[$applicant->status] ?? 'secondary';
                        @endphp
                        <span
                            class="position-absolute bottom-0 end-0 badge bg-{{ $statusColor }} rounded-pill px-2 py-1"
                            style="font-size: 0.7rem; transform: translate(25%, 25%);">
                            @if ($applicant->status == 'Admitted')
                                <i class="fas fa-check"></i>
                            @elseif($applicant->status == 'Rejected')
                                <i class="fas fa-times"></i>
                            @elseif($applicant->status == 'Submitted')
                                <i class="fas fa-clock"></i>
                            @else
                                <i class="fas fa-pencil"></i>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-6 mb-3 mb-md-0">
                    <h1 class="h2 mb-2 applicant-name">
                        <i class="fas fa-user-graduate me-3"></i>
                        {{ $applicant->fullname }}
                    </h1>
                    <p class="mb-2">
                        <i class="fas fa-id-card me-2"></i>
                        Application ID: <strong>{{ $applicant->username }}</strong>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-university me-2"></i>
                        {{ $program ? $program->title : 'Program not found' }} ({{ ucfirst($applicant->mode) }})
                    </p>
                </div>
                <div class="col-12 col-md-3 col-lg-4 text-center text-md-end mb-3 mb-md-0">
                    @php
                        $statusClasses = [
                            'Pending' => 'status-pending',
                            'Submitted' => 'status-submitted',
                            'Admitted' => 'status-admitted',
                            'Rejected' => 'status-rejected',
                        ];
                        $statusClass = $statusClasses[$applicant->status] ?? 'status-pending';
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $applicant->status }}</span>
                    @if ($applicant->submitted_at)
                        <p class="mt-2 mb-0 small">
                            <i class="fas fa-calendar-check me-1"></i>
                            Submitted: {{ $applicant->submitted_at->format('M d, Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="info-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Full Name:</div>
                                <div class="info-value">{{ $applicant->fullname }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">{{ $applicant->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">{{ $applicant->phone }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Gender:</div>
                                <div class="info-value">{{ $applicant->gender }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Date of Birth:</div>
                                <div class="info-value">
                                    {{ $applicant->dob ? $applicant->dob->format('M d, Y') : 'Not specified' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Nationality:</div>
                                <div class="info-value">{{ $applicant->nationality }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">State:</div>
                                <div class="info-value">{{ $applicant->state }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">LGA:</div>
                                <div class="info-value">{{ $applicant->lga }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">City:</div>
                                <div class="info-value">{{ $applicant->city }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Address:</div>
                                <div class="info-value">{{ $applicant->address }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="info-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Academic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Faculty:</div>
                                <div class="info-value">{{ $faculty ? $faculty->title : 'Not specified' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Department:</div>
                                <div class="info-value">{{ $department ? $department->title : 'Not specified' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Program:</div>
                                <div class="info-value">{{ $program ? $program->title : 'Not specified' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Mode:</div>
                                <div class="info-value">{{ ucfirst($applicant->mode) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JAMB/DE Information -->
            @if ($applicant->mode == 'UTME')
                <div class="info-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>JAMB Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Get all JAMB subjects for this user
                            $jambSubjects = \App\Models\Jamb::where('user_id', $applicant->user_id)->get();
                            $totalScore = $jambSubjects->sum('score');
                        @endphp

                        @if ($jambSubjects->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="info-row">
                                        <div class="info-label">Exam Type:</div>
                                        <div class="info-value">UTME</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-row">
                                        <div class="info-label">Registration Number:</div>
                                        <div class="info-value">{{ $applicant->username }} </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-row">
                                        <div class="info-label">Total Score:</div>
                                        <div class="info-value">
                                            <strong>{{ number_format($totalScore, 0) }}/400</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6>Subject Scores:</h6>
                            <div class="row">
                                @foreach ($jambSubjects as $subject)
                                    <div class="col-md-3 mb-2">
                                        <strong>{{ $subject->subject }}:</strong>
                                        {{ number_format($subject->score, 0) }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                JAMB information has not been provided for this applicant.
                            </div>
                        @endif

                    </div>
                </div>
            @elseif($applicant->mode == 'DE' && $deQualification)
                <div class="info-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-certificate me-2"></i>Direct Entry Qualification
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Qualification Type:</div>
                                    <div class="info-value">{{ $deQualification->qualification_type }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Institution:</div>
                                    <div class="info-value">{{ $deQualification->institution }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Graduation Year:</div>
                                    <div class="info-value">{{ $deQualification->grad_year }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Grade:</div>
                                    <div class="info-value">{{ $deQualification->grade }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            <!-- SSCE Results -->
            @if ($ssce->count() > 0)
                <div class="info-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-scroll me-2"></i>SSCE Results
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach ($ssce as $sitting)
                            <h6>Sitting {{ $sitting->sitting }}</h6>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Exam Type:</strong>
                                    {{ $sitting->type ?? 'Not provided' }}</div>
                                <div class="col-md-4"><strong>Exam Year:</strong>
                                    {{ $sitting->year ?? 'Not provided' }}</div>
                                <div class="col-md-4"><strong>Reg Number:</strong>
                                    {{ $sitting->number ?? 'Not provided' }}
                                </div>
                            </div>
                            @php
                                $results = $ssceResults->where('sitting', $sitting->sitting);
                            @endphp
                            @if ($results->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($results as $result)
                                                <tr>
                                                    <td>{{ $result->subject }}</td>
                                                    <td>
                                                        <span
                                                            class="grade-badge badge bg-{{ in_array($result->grade, ['A1', 'B2', 'B3', 'C4', 'C5', 'C6']) ? 'success' : 'secondary' }}">
                                                            {{ $result->grade }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @if (!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Next of Kin & Sponsor Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Next of Kin
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value">{{ $applicant->n_name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Relationship:</div>
                                <div class="info-value">{{ $applicant->n_relationship }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">{{ $applicant->n_phone }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">{{ $applicant->n_email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Address:</div>
                                <div class="info-value">{{ $applicant->n_address }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-hand-holding-usd me-2"></i>Sponsor Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value">{{ $applicant->s_name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">{{ $applicant->s_phone }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Address:</div>
                                <div class="info-value">{{ $applicant->s_address }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uploaded Documents -->
            @if ($documents->count() > 0)
                <div class="info-card">
                    <div class="card-header bg-purple text-white" style="background-color: #6f42c1;">
                        <h5 class="mb-0">
                            <i class="fas fa-file-upload me-2"></i>Uploaded Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($documents as $doc)
                                <div class="col-md-4">
                                    <div class="document-item">
                                        <div class="mb-2">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </div>
                                        <h6 class="mb-1">{{ $doc->doc_type }}</h6>
                                        <small class="text-muted">{{ $doc->original_name }}</small><br>
                                        <a href="{{ Storage::url($doc->file_path) }}"
                                            class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-eye me-1"></i>View Document
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Action Sidebar -->
        <div class="col-lg-4">
            <div class="action-buttons">
                <!-- Action Card -->
                <div class="info-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Admin Actions
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        @if (in_array($applicant->status, ['Rejected', 'Submitted']))
                            <!-- Admit Button -->
                            <button type="button" class="btn btn-success btn-action w-100 mb-3"
                                data-bs-toggle="modal" data-bs-target="#admitModal">
                                <i class="fas fa-check me-2"></i>Admit Applicant
                            </button>

                            <!-- Reject Button -->
                            <button type="button" class="btn btn-danger btn-action w-100 mb-3"
                                data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times me-2"></i>Reject Applicant
                            </button>
                        @endif
                        @if ($applicant->status === 'Admitted' || $applicant->clearance === 'Cleared')
                            <!-- Admit Button -->
                            <button type="button" class="btn btn-success btn-action w-100 mb-3"
                                data-bs-toggle="modal" data-bs-target="#clearedModal">
                                <i class="fas fa-check me-2"></i>Cleared
                            </button>

                            <!-- Reject Button -->
                            <button type="button" class="btn btn-danger btn-action w-100 mb-3"
                                data-bs-toggle="modal" data-bs-target="#notClearedModal">
                                <i class="fas fa-times me-2"></i>Not Cleared
                            </button>
                        @endif

                        <!-- Download Admission Letter (Available for Admitted Students) -->
                        @if ($applicant->status === 'Admitted')
                            <a href="{{ route('admin.download-admission-letter', $applicant->id) }}"
                                class="btn btn-primary btn-action w-100 mb-3" <i
                                class="fas fa-file-pdf me-2"></i>Download Admission Letter (PDF)
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Application Timeline -->
                @if ($applicant->submitted_at || $applicant->admission_date || $applicant->rejection_date)
                    <div class="info-card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Application Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @if ($applicant->submitted_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Application Submitted</h6>
                                            <small
                                                class="text-muted">{{ $applicant->submitted_at->format('M d, Y H:i') }}</small>
                                        </div>
                                    </div>
                                @endif

                                @if ($applicant->admission_date)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Admitted</h6>
                                            <small
                                                class="text-muted">{{ $applicant->admission_date->format('M d, Y H:i') }}</small>
                                            <small class="d-block text-muted">By:
                                                {{ $applicant->admitted_by }}</small>
                                            @if ($applicant->admission_remarks)
                                                <small
                                                    class="d-block text-muted">{{ $applicant->admission_remarks }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($applicant->rejection_date)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6>Rejected</h6>
                                            <small
                                                class="text-muted">{{ $applicant->rejection_date->format('M d, Y H:i') }}</small>
                                            <small class="d-block text-muted">By:
                                                {{ $applicant->rejected_by }}</small>
                                            <small class="d-block text-muted">Reason:
                                                {{ $applicant->rejection_reason }}</small>
                                            @if ($applicant->rejection_remarks)
                                                <small
                                                    class="d-block text-muted">{{ $applicant->rejection_remarks }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Admit Modal -->
<div class="modal fade" id="admitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check me-2"></i>Admit Applicant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.admit-applicant') }}" method="POST">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to admit <strong>{{ $applicant->fullname }}</strong> to
                        <strong>{{ $program ? $program->title : 'the selected program' }}</strong>.
                    </div>

                    <div class="form-group">
                        <label for="admission_remarks">Admission Remarks (Optional)</label>
                        <textarea class="form-control" id="admission_remarks" name="remarks" rows="3"
                            placeholder="Enter any remarks or special notes for this admission..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Confirm Admission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times me-2"></i>Reject Applicant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reject-applicant') }}" method="POST">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You are about to reject <strong>{{ $applicant->fullname }}</strong>'s application for
                        <strong>{{ $program ? $program->title : 'the selected program' }}</strong>.
                    </div>

                    <div class="form-group mb-3">
                        <label for="rejection_reason">Reason for Rejection <span class="text-danger">*</span></label>
                        <select class="form-control" id="rejection_reason" name="rejection_reason" required>
                            <option value="">Select rejection reason...</option>
                            <option value="Insufficient JAMB Score">Insufficient JAMB Score</option>
                            <option value="Poor SSCE Results">Poor SSCE Results</option>
                            <option value="Missing Required Documents">Missing Required Documents</option>
                            <option value="Program Capacity Full">Program Capacity Full</option>
                            <option value="Failed to Meet Entry Requirements">Failed to Meet Entry Requirements
                            </option>
                            <option value="Incomplete Application">Incomplete Application</option>
                            <option value="Other">Other (Specify in remarks)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rejection_remarks">Additional Remarks</label>
                        <textarea class="form-control" id="rejection_remarks" name="remarks" rows="3"
                            placeholder="Provide detailed explanation for the rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Admit Modal -->
<div class="modal fade" id="clearedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check me-2"></i>Clear the Applicant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.cleared-applicant') }}" method="POST">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to clear <strong>{{ $applicant->fullname }}.
                    </div>

                    <div class="form-group mb-3">
                        <label for="program">Course cleared to <span class="text-danger">*</span></label>
                        <select class="form-control" id="program" name="program" required>
                            @foreach (DB::table('program')->where('department', $applicant->department)->get() as $row)
                                <option value="{{ $row->code }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- level cleared to --}}
                    <div class="form-group mb-3">
                        <label for="level">Level cleared to <span class="text-danger">*</span></label>
                        <select class="form-control" id="level" name="level" required>
                            <option value="">Select Level</option>
                            <option value="100">100 Level</option>
                            <option value="200">200 Level</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Confirm Clearance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="notClearedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times me-2"></i>Reject Applicant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reject-clearing-applicant') }}" method="POST">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You are about to reject <strong>{{ $applicant->fullname }}</strong>'s application for
                        <strong>{{ $program ? $program->title : 'the selected program' }}</strong>.
                    </div>

                    <div class="form-group mb-3">
                        <label for="rejection_reason">Reason for Rejection <span class="text-danger">*</span></label>
                        <select class="form-control" id="rejection_reason" name="rejection_reason" required>
                            <option value="">Select rejection reason...</option>
                            <option value="Insufficient JAMB Score">Insufficient JAMB Score</option>
                            <option value="Poor SSCE Results">Poor SSCE Results</option>
                            <option value="Missing Required Documents">Missing Required Documents</option>
                            <option value="Program Capacity Full">Program Capacity Full</option>
                            <option value="Failed to Meet Entry Requirements">Failed to Meet Entry Requirements
                            </option>
                            <option value="Incomplete Application">Incomplete Application</option>
                            <option value="Other">Other (Specify in remarks)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rejection_remarks">Additional Remarks</label>
                        <textarea class="form-control" id="rejection_remarks" name="remarks" rows="3"
                            placeholder="Provide detailed explanation for the rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }

    @media print {

        .action-buttons,
        .back-button {
            display: none !important;
        }

        .profile-header {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
