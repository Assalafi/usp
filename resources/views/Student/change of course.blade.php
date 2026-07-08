@php
    $currentSession = session('system_session');
@endphp

<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <!-- Page Header -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-4"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <img src="{{ asset('uploads/logo.png') }}" alt="UNIMAID Logo" style="height: 60px;" class="mb-3">
                        <h3 class="mb-1">UNIVERSITY OF MAIDUGURI</h3>
                        <h5 class="mb-1">(Office of the Registrar)</h5>
                        <h4 class="mb-0">INTER DEPARTMENTAL TRANSFER FORM</h4>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (isset($hasPaid) && $hasPaid)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Payment Verified:</strong> You have successfully paid the processing fee. You can now
                        submit your application.
                    </div>
                @endif

                @if ($application)
                    @if (isset($hasPaid) && $hasPaid)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>You have an unused payment.</strong> Please submit your new application using the form below.
                        </div>
                    @else
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Apply for Another Transfer</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">You can make a new payment to submit another application for a different department/program.</p>
                                <a href="{{ route('change-of-course.new-application') }}" class="btn btn-success">
                                    <i class="fas fa-credit-card me-2"></i>Make New Payment & Apply
                                </a>
                            </div>
                        </div>
                    @endif

                    @foreach (($applications ?? collect([$application])) as $app)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>{{ $app->application_no }}</h5>
                            <span>
                                @if ($app->status == 'Payment Pending')
                                    <span class="badge bg-warning text-dark">{{ $app->status }}</span>
                                @elseif($app->status == 'Approved')
                                    <span class="badge bg-success">{{ $app->status }}</span>
                                @elseif($app->status == 'Rejected')
                                    <span class="badge bg-danger">{{ $app->status }}</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ $app->status }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Current Department:</strong></p>
                                    <p>{{ DB::table('department')->where('code', $app->current_department)->value('title') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Requested Department:</strong></p>
                                    <p class="text-primary">{{ DB::table('department')->where('code', $app->new_department)->value('title') }}</p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Transfer Type:</strong> {{ ucfirst($app->transfer_type ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Amount Paid:</strong> ₦{{ number_format($app->amount ?? 0, 2) }}</p>
                                </div>
                            </div>

                            {{-- Approved: Show new account details --}}
                            @if ($app->status == 'Approved')
                                <div class="card border-success mb-3 mt-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Transfer Approved</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Important:</strong> A new student account has been created. Use the credentials below to login.
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card bg-light mb-2">
                                                    <div class="card-body py-2">
                                                        <small class="text-muted">New Login Username</small>
                                                        <h5 class="text-primary mb-0"><i class="fas fa-user me-1"></i>{{ $app->application_no }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light mb-2">
                                                    <div class="card-body py-2">
                                                        <small class="text-muted">Default Password</small>
                                                        <h5 class="text-danger mb-0"><i class="fas fa-key me-1"></i>{{ \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026') }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-4"><small><strong>Faculty:</strong> {{ DB::table('faculty')->where('code', $app->new_faculty)->value('title') }}</small></div>
                                            <div class="col-md-4"><small><strong>Department:</strong> {{ DB::table('department')->where('code', $app->new_department)->value('title') }}</small></div>
                                            <div class="col-md-4"><small><strong>Program:</strong> {{ DB::table('program')->where('code', $app->new_program)->value('title') }}</small></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Payment Pending --}}
                            @if ($app->payment_status == 'Pending')
                                <div class="alert alert-warning mt-2 mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Payment Required</strong>
                                </div>
                                <a href="{{ route('change-of-course.payment', $app->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-credit-card me-1"></i>Proceed to Payment
                                </a>
                            @elseif(!in_array($app->status, ['Approved', 'Rejected']))
                                {{-- In Progress: Show timeline --}}
                                <div class="alert alert-success mt-2 mb-2">
                                    <i class="fas fa-check-circle me-2"></i><strong>Payment Completed</strong> — Application is being processed.
                                </div>
                                <div class="timeline mt-3">
                                    <small class="text-muted"><strong>Progress:</strong></small>
                                    <div class="timeline-item {{ $app->new_hod_willing != 'Pending' ? 'completed' : '' }}">
                                        <i class="fas fa-user-check"></i>
                                        <span>New HOD:
                                            @if ($app->new_hod_willing == 'Yes') <span class="badge bg-success">Approved</span>
                                            @elseif($app->new_hod_willing == 'No') <span class="badge bg-danger">Rejected</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </span>
                                    </div>
                                    <div class="timeline-item {{ $app->new_dean_recommendation != 'Pending' ? 'completed' : '' }}">
                                        <i class="fas fa-user-tie"></i>
                                        <span>New Dean:
                                            @if ($app->new_dean_recommendation == 'Yes') <span class="badge bg-success">Approved</span>
                                            @elseif($app->new_dean_recommendation == 'No') <span class="badge bg-danger">Rejected</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </span>
                                    </div>
                                    <div class="timeline-item {{ $app->current_hod_willing != 'Pending' ? 'completed' : '' }}">
                                        <i class="fas fa-user-minus"></i>
                                        <span>Current HOD:
                                            @if ($app->current_hod_willing == 'Yes') <span class="badge bg-success">Approved</span>
                                            @elseif($app->current_hod_willing == 'No') <span class="badge bg-danger">Rejected</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </span>
                                    </div>
                                    <div class="timeline-item {{ $app->current_dean_recommendation != 'Pending' ? 'completed' : '' }}">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Current Dean:
                                            @if ($app->current_dean_recommendation == 'Yes') <span class="badge bg-success">Approved</span>
                                            @elseif($app->current_dean_recommendation == 'No') <span class="badge bg-danger">Rejected</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </span>
                                    </div>
                                    <div class="timeline-item {{ $app->registrar_decision != 'Pending' ? 'completed' : '' }}">
                                        <i class="fas fa-gavel"></i>
                                        <span>Registrar:
                                            @if ($app->registrar_decision == 'Approved') <span class="badge bg-success">Approved</span>
                                            @elseif($app->registrar_decision == 'Rejected') <span class="badge bg-danger">Rejected</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                @else
                    <!-- New Application Form -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>PART A: STUDENT'S PERSONAL DATA
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('change-of-course.store') }}"
                                id="changeOfCourseForm" enctype="multipart/form-data">
                                @csrf

                                <!-- Auto-populated Student Information (Read-only) -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> The following information is auto-populated from your student
                                    record.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>1. Name of Student</strong></label>
                                        <input type="text" class="form-control" value="{{ $student->fullname }}"
                                            readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>2. Identity Number</strong></label>
                                        <input type="text" class="form-control" value="{{ $student->username }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>3. Present Department</strong></label>
                                        <input type="text" class="form-control"
                                            value="{{ DB::table('department')->where('code', $student->department)->value('title') }}"
                                            readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>4. Faculty of</strong></label>
                                        <input type="text" class="form-control"
                                            value="{{ DB::table('faculty')->where('code', $student->faculty)->value('title') }}"
                                            readonly>
                                    </div>
                                </div>

                                <!-- JAMB Information -->
                                <hr class="my-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>JAMB Information
                                </h6>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><strong>Admission Type</strong> <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="admission_type" id="admissionType" required>
                                            <option value="" selected>-- Select Admission Type --</option>
                                            <option value="UTME">UTME</option>
                                            <option value="DE">Direct Entry (DE)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="jambScoreField">
                                        <label class="form-label"><strong>JAMB Score</strong> <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jamb_score" min="0" max="400"
                                            placeholder="Enter UTME score (0-400)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><strong>JAMB Result/DE Slip</strong> <span
                                                class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="jamb_result_file" accept="image/*,.pdf">
                                        <small class="text-muted">Upload JAMB result (UTME) or DE slip (PDF/Image)</small>
                                    </div>
                                </div>

                                <!-- New Department Selection -->
                                <hr class="my-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-arrow-right me-2"></i>Transfer Details
                                </h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>5. Faculty to transfer to</strong> <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="new_faculty" id="newFaculty" required>
                                            <option value="">Select Faculty</option>
                                            @foreach ($faculties as $faculty)
                                                <option value="{{ $faculty->code }}">{{ $faculty->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>6. Department to transfer to</strong> <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="new_department" id="newDepartment" required
                                            disabled>
                                            <option value="">Select Faculty First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>Program to transfer to</strong> <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="new_program" id="newProgram" required
                                            disabled>
                                            <option value="">Select Department First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>7. Reason(s) for changing your
                                                course</strong> <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason_for_change" rows="5"
                                            placeholder="Please provide detailed reasons for your request (minimum 20 characters)" required minlength="20"></textarea>
                                        <small class="text-muted">Minimum 20 characters required</small>
                                    </div>
                                </div>

                                <!-- Important Notice -->
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Information:</h6>
                                    <ul class="mb-0">
                                        <li>A non-refundable fee is required to process
                                            your application</li>
                                        <li>You will be redirected to payment page after submission</li>
                                        <li>Your application will only be processed after successful payment</li>
                                        <li>Processing time may take 2-4 weeks depending on approvals</li>
                                    </ul>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Show new application form when there's an unused paid invoice and existing applications --}}
                @if ($application && isset($hasPaid) && $hasPaid)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Submit New Application</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('change-of-course.store') }}"
                                id="changeOfCourseForm" enctype="multipart/form-data">
                                @csrf

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> The following information is auto-populated from your student record.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>1. Name of Student</strong></label>
                                        <input type="text" class="form-control" value="{{ $student->fullname }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>2. Identity Number</strong></label>
                                        <input type="text" class="form-control" value="{{ $student->username }}" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>3. Present Department</strong></label>
                                        <input type="text" class="form-control"
                                            value="{{ DB::table('department')->where('code', $student->department)->value('title') }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>4. Faculty of</strong></label>
                                        <input type="text" class="form-control"
                                            value="{{ DB::table('faculty')->where('code', $student->faculty)->value('title') }}" readonly>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>JAMB Information</h6>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><strong>Admission Type</strong> <span class="text-danger">*</span></label>
                                        <select class="form-select" name="admission_type" id="admissionType" required>
                                            <option value="" selected>-- Select Admission Type --</option>
                                            <option value="UTME">UTME</option>
                                            <option value="DE">Direct Entry (DE)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="jambScoreField">
                                        <label class="form-label"><strong>JAMB Score</strong> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jamb_score" min="0" max="400" placeholder="Enter UTME score (0-400)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><strong>JAMB Result/DE Slip</strong> <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="jamb_result_file" accept="image/*,.pdf">
                                        <small class="text-muted">Upload JAMB result (UTME) or DE slip (PDF/Image)</small>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-arrow-right me-2"></i>Transfer Details</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>5. Faculty to transfer to</strong> <span class="text-danger">*</span></label>
                                        <select class="form-select" name="new_faculty" id="newFaculty" required>
                                            <option value="">Select Faculty</option>
                                            @foreach ($faculties as $faculty)
                                                <option value="{{ $faculty->code }}">{{ $faculty->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>6. Department to transfer to</strong> <span class="text-danger">*</span></label>
                                        <select class="form-select" name="new_department" id="newDepartment" required disabled>
                                            <option value="">Select Faculty First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>Program to transfer to</strong> <span class="text-danger">*</span></label>
                                        <select class="form-select" name="new_program" id="newProgram" required disabled>
                                            <option value="">Select Department First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>7. Reason(s) for changing your course</strong> <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason_for_change" rows="5"
                                            placeholder="Please provide detailed reasons for your request (minimum 20 characters)" required minlength="20"></textarea>
                                        <small class="text-muted">Minimum 20 characters required</small>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Forced JAMB Upload Modal --}}
@if ($application)
    @php
        $allApps = $applications ?? collect([$application]);
        $missingJamb = $allApps->first(function($a) {
            if ($a->payment_status == 'Pending') return false;
            if (empty($a->jamb_result_file)) return true;
            if ($a->admission_type == 'UTME' && empty($a->jamb_score)) return true;
            return false;
        });
    @endphp
    @if ($missingJamb)
        <div class="modal fade" id="forceJambUploadModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="forceJambUploadLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="forceJambUploadLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>JAMB Information Required
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-file-upload fa-3x text-danger mb-2"></i>
                            <h6>You must provide your JAMB information to proceed.</h6>
                            <p class="text-muted small">Your application ({{ $missingJamb->application_no }}) cannot be processed without this information.</p>
                        </div>
                        <form method="POST" action="{{ route('change-of-course.upload-jamb-result') }}" enctype="multipart/form-data" id="forceJambForm">
                            @csrf
                            <input type="hidden" name="application_id" value="{{ $missingJamb->id }}">
                            @if ($missingJamb->admission_type == 'UTME' && empty($missingJamb->jamb_score))
                                <div class="mb-3">
                                    <label class="form-label fw-bold">JAMB Score (UTME) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="jamb_score" min="0" max="400" placeholder="Enter your UTME score (0-400)" required id="forceJambScore">
                                </div>
                            @endif
                            @if (empty($missingJamb->jamb_result_file))
                                <div class="mb-3">
                                    <label class="form-label fw-bold">JAMB Result / DE Slip <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="jamb_result_file" accept="image/*,.pdf" required id="forceJambFile">
                                    <small class="text-muted">Accepted: PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                </div>
                            @endif
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg" id="forceJambBtn">
                                    <i class="fas fa-upload me-2"></i>Submit JAMB Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding: 15px 0 15px 30px;
        border-left: 2px solid #e0e0e0;
    }

    .timeline-item.completed {
        border-left-color: #28a745;
    }

    .timeline-item i {
        position: absolute;
        left: -12px;
        top: 18px;
        width: 24px;
        height: 24px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #666;
    }

    .timeline-item.completed i {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle admission type change
        $('#admissionType').on('change', function() {
            var admissionType = $(this).val();
            if (admissionType === 'DE') {
                $('#jambScoreField').hide();
                $('input[name="jamb_score"]').prop('required', false).val('');
            } else {
                $('#jambScoreField').show();
                $('input[name="jamb_score"]').prop('required', true);
            }
        });

        // Initialize on page load
        $('#admissionType').trigger('change');

        // Load departments when faculty changes
        $('#newFaculty').change(function() {
            const facultyCode = $(this).val();
            const departmentSelect = $('#newDepartment');
            const programSelect = $('#newProgram');

            if (facultyCode) {
                $.ajax({
                    url: '{{ route('change-of-course.get-departments') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        faculty: facultyCode
                    },
                    success: function(response) {
                        departmentSelect.html(
                            '<option value="">Select Department</option>');
                        response.forEach(function(dept) {
                            departmentSelect.append(
                                `<option value="${dept.code}">${dept.title}</option>`
                            );
                        });
                        departmentSelect.prop('disabled', false);
                        programSelect.html(
                            '<option value="">Select Department First</option>').prop(
                            'disabled', true);
                    },
                    error: function() {
                        alert('Error loading departments. Please try again.');
                    }
                });
            } else {
                departmentSelect.html('<option value="">Select Faculty First</option>').prop('disabled',
                    true);
                programSelect.html('<option value="">Select Department First</option>').prop('disabled',
                    true);
            }
        });

        // Load programs when department changes
        $('#newDepartment').change(function() {
            const departmentCode = $(this).val();
            const programSelect = $('#newProgram');

            if (departmentCode) {
                $.ajax({
                    url: '{{ route('change-of-course.get-programs') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        department: departmentCode
                    },
                    success: function(response) {
                        programSelect.html('<option value="">Select Program</option>');
                        response.forEach(function(prog) {
                            programSelect.append(
                                `<option value="${prog.code}">${prog.title}</option>`
                            );
                        });
                        programSelect.prop('disabled', false);
                    },
                    error: function() {
                        alert('Error loading programs. Please try again.');
                    }
                });
            } else {
                programSelect.html('<option value="">Select Department First</option>').prop('disabled',
                    true);
            }
        });

        // Form validation
        $('#changeOfCourseForm').submit(function(e) {
            const reason = $('textarea[name="reason_for_change"]').val();
            if (reason.length < 20) {
                e.preventDefault();
                alert('Please provide a more detailed reason (at least 20 characters)');
                return false;
            }
        });

        // Force JAMB upload modal
        if ($('#forceJambUploadModal').length) {
            var modal = new bootstrap.Modal(document.getElementById('forceJambUploadModal'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        }
    });
</script>
