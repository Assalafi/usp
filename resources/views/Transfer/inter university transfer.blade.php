<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Inter-University Transfer Application</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dash"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#!">Inter-University Transfer</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @if (!$hasPaid && !$application)
                    <!-- Step 1: Payment Required -->
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Payment Required</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill-wave fa-3x text-warning mb-3"></i>
                            <h5>You need to make payment before proceeding</h5>
                            <p class="text-muted">Please complete your transfer fee payment to access the application
                                form.</p>
                            <a href="/inter-university-transfer/payment" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card mr-2"></i> Proceed to Payment
                            </a>
                        </div>
                    </div>
                @elseif($hasPaid && !$application)
                    <!-- Step 2: Fill Application -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle mr-2"></i> Payment Completed</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                            <h5>Payment verified! Fill your application form now.</h5>
                            <a href="/inter-university-transfer/form" class="btn btn-success btn-lg">
                                <i class="fas fa-edit mr-2"></i> Fill Application Form
                            </a>
                        </div>
                    </div>
                @elseif($application)
                    <!-- Application Status -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Application Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Application No:</strong></p>
                                    <h6 class="text-primary">{{ $application->application_no }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    @php
                                        $statusColors = [
                                            'Draft' => 'secondary',
                                            'Awaiting Documents' => 'info',
                                            'Awaiting UNIMAID HOD' => 'warning',
                                            'Awaiting UNIMAID Dean' => 'warning',
                                            'Awaiting Registrar' => 'warning',
                                            'Awaiting VC' => 'warning',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }} p-2">
                                        {{ $application->status }}
                                    </span>
                                </div>
                            </div>

                            <table class="table table-bordered">
                                <tr>
                                    <th width="35%">Full Name</th>
                                    <td>{{ $application->surname }} {{ $application->first_name }}
                                        {{ $application->middle_name }}</td>
                                </tr>
                                <tr>
                                    <th>Present Institution</th>
                                    <td>{{ $application->present_institution }}</td>
                                </tr>
                                <tr>
                                    <th>Registration Number</th>
                                    <td>{{ $application->registration_number }}</td>
                                </tr>
                                <tr>
                                    <th>Transfer Type</th>
                                    <td>{{ $application->transfer_type == 'within_nigeria' ? 'Within Nigeria' : 'From Abroad' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Course Applied For</th>
                                    <td>
                                        @php
                                            $progTitle = DB::table('program')
                                                ->where('code', $application->new_program)
                                                ->value('title');
                                            $deptTitle = DB::table('department')
                                                ->where('code', $application->new_department)
                                                ->value('title');
                                            $facTitle = DB::table('faculty')
                                                ->where('code', $application->new_faculty)
                                                ->value('title');
                                        @endphp
                                        {{ $progTitle }} <br>
                                        <small class="text-muted">{{ $deptTitle }}, {{ $facTitle }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment</th>
                                    <td>
                                        <span class="badge bg-success">Paid -
                                            &#8358;{{ number_format($application->amount, 2) }}</span>
                                        @if ($application->rrr)
                                            <br><small class="text-muted">RRR: {{ $application->rrr }}</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date Applied</th>
                                    <td>{{ $application->created_at ? date('d M Y', strtotime($application->created_at)) : 'N/A' }}
                                    </td>
                                </tr>
                            </table>

                            @if (!$application->jamb_result_file)
                                <!-- JAMB Result Upload Alert -->
                                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i> JAMB Result Required</h6>
                                    <p class="mb-2">Please upload your JAMB result (UTME) or DE slip to complete your application.</p>
                                    <form method="POST" action="{{ route('inter-transfer.upload-jamb-result') }}" enctype="multipart/form-data" class="mb-0">
                                        @csrf
                                        <div class="row align-items-end">
                                            <div class="col-md-6">
                                                <input type="file" class="form-control" name="jamb_result_file" accept="image/*,.pdf" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-upload mr-1"></i> Upload JAMB Result
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            @if ($application->status == 'Awaiting Documents')
                                <!-- Document Upload Section -->
                                <div class="card mt-3 border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-upload mr-2"></i> Upload Required Documents
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('inter-transfer.upload-documents') }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label><strong>1. Certificates / Educational Qualifications</strong>
                                                    <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control" name="certificates_upload"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    {{ $application->certificates_upload ? '' : 'required' }}>
                                                @if ($application->certificates_upload)
                                                    <small class="text-success"><i class="fas fa-check"></i> Already
                                                        uploaded -
                                                        <a href="{{ asset($application->certificates_upload) }}"
                                                            target="_blank">View</a>
                                                    </small>
                                                @endif
                                                <small class="text-muted d-block">Photocopies of WASC/SSCE, TC II, GCE,
                                                    IJMB, NCE etc.</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label><strong>2. Present Institution HOD & Dean Approval</strong> <span
                                                        class="text-danger">*</span></label>
                                                <input type="file" class="form-control"
                                                    name="present_institution_approval" accept=".pdf,.jpg,.jpeg,.png"
                                                    {{ $application->present_institution_approval ? '' : 'required' }}>
                                                @if ($application->present_institution_approval)
                                                    <small class="text-success"><i class="fas fa-check"></i> Already
                                                        uploaded -
                                                        <a href="{{ asset($application->present_institution_approval) }}"
                                                            target="_blank">View</a>
                                                    </small>
                                                @endif
                                                <small class="text-muted d-block">Single document containing both HOD
                                                    and Dean recommendation from your present institution.</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label><strong>3. Academic Transcript</strong> (Optional)</label>
                                                <input type="file" class="form-control" name="transcript_upload"
                                                    accept=".pdf,.jpg,.jpeg,.png">
                                                @if ($application->transcript_upload)
                                                    <small class="text-success"><i class="fas fa-check"></i> Already
                                                        uploaded -
                                                        <a href="{{ asset($application->transcript_upload) }}"
                                                            target="_blank">View</a>
                                                    </small>
                                                @endif
                                                <small class="text-muted d-block">Official academic transcript from your
                                                    present institution.</small>
                                            </div>

                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-upload mr-2"></i> Upload Documents
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($application->status != 'Draft' && $application->status != 'Awaiting Documents')
                        <!-- Approval Progress -->
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-tasks mr-2"></i> Approval Progress</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Stage</th>
                                                <th>Status</th>
                                                <th>Officer</th>
                                                <th>Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Present Institution (HOD & Dean)</strong></td>
                                                <td>
                                                    @if ($application->present_institution_approval)
                                                        <span class="badge bg-success">Document Uploaded</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending Upload</span>
                                                    @endif
                                                </td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>
                                                    @if ($application->present_institution_approval)
                                                        <a href="{{ asset($application->present_institution_approval) }}"
                                                            target="_blank">View Document</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>UNIMAID HOD</strong></td>
                                                <td>
                                                    @if ($application->unimaid_hod_recommendation == 'Yes')
                                                        <span class="badge bg-success">Recommended</span>
                                                    @elseif($application->unimaid_hod_recommendation == 'No')
                                                        <span class="badge bg-danger">Not Recommended</span>
                                                    @else
                                                        <span class="badge bg-secondary">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $application->unimaid_hod_name ?? '-' }}</td>
                                                <td>{{ $application->unimaid_hod_date ? date('d M Y', strtotime($application->unimaid_hod_date)) : '-' }}
                                                </td>
                                                <td>{{ $application->unimaid_hod_remarks ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>UNIMAID Dean</strong></td>
                                                <td>
                                                    @if ($application->unimaid_dean_recommendation == 'Yes')
                                                        <span class="badge bg-success">Recommended</span>
                                                    @elseif($application->unimaid_dean_recommendation == 'No')
                                                        <span class="badge bg-danger">Not Recommended</span>
                                                    @else
                                                        <span class="badge bg-secondary">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $application->unimaid_dean_name ?? '-' }}</td>
                                                <td>{{ $application->unimaid_dean_date ? date('d M Y', strtotime($application->unimaid_dean_date)) : '-' }}
                                                </td>
                                                <td>{{ $application->unimaid_dean_remarks ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Registrar</strong></td>
                                                <td>
                                                    @if ($application->registrar_decision == 'Approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($application->registrar_decision == 'Rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @else
                                                        <span class="badge bg-secondary">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $application->registrar_name ?? '-' }}</td>
                                                <td>{{ $application->registrar_date ? date('d M Y', strtotime($application->registrar_date)) : '-' }}
                                                </td>
                                                <td>{{ $application->registrar_remarks ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Application Guide</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Steps to Complete:</strong>
                            <ol class="mt-2 pl-3">
                                <li class="{{ $hasPaid ? 'text-success' : '' }}">
                                    <i
                                        class="fas {{ $hasPaid ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-1"></i>
                                    Make Payment
                                </li>
                                <li
                                    class="{{ $application && $application->status != 'Draft' ? 'text-success' : '' }}">
                                    <i
                                        class="fas {{ $application && $application->status != 'Draft' ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-1"></i>
                                    Fill Application Form
                                </li>
                                <li
                                    class="{{ $application && $application->certificates_upload && $application->present_institution_approval ? 'text-success' : '' }}">
                                    <i
                                        class="fas {{ $application && $application->certificates_upload && $application->present_institution_approval ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-1"></i>
                                    Upload Documents
                                </li>
                                <li>
                                    <i class="fas fa-circle text-muted mr-1"></i>
                                    Await Approval
                                </li>
                            </ol>
                        </div>

                        <hr>
                        <div class="alert alert-info small mb-0">
                            <strong>Note:</strong><br>
                            <ul class="pl-3 mb-0">
                                <li>You should request your present institution to forward an official copy of your
                                    Academic Transcripts to <strong>The Registrar, University of Maiduguri, P.M.B 1069
                                        Maiduguri</strong></li>
                                <li class="mt-1">Only successful applications will be acknowledged.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Forced JAMB Upload Modal --}}
@if ($application && !in_array($application->status, ['Draft', 'Payment Pending']))
    @php
        $iutMissingJamb = empty($application->jamb_result_file) || ($application->admission_type == 'UTME' && empty($application->jamb_score));
    @endphp
    @if ($iutMissingJamb)
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
                            <p class="text-muted small">Your application ({{ $application->application_no }}) cannot be processed without this information.</p>
                        </div>
                        <form method="POST" action="{{ route('inter-transfer.upload-jamb-result') }}" enctype="multipart/form-data" id="forceJambForm">
                            @csrf
                            @if ($application->admission_type == 'UTME' && empty($application->jamb_score))
                                <div class="mb-3">
                                    <label class="form-label fw-bold">JAMB Score (UTME) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="jamb_score" min="0" max="400" placeholder="Enter your UTME score (0-400)" required id="forceJambScore">
                                </div>
                            @endif
                            @if (empty($application->jamb_result_file))
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('forceJambUploadModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            });
        </script>
    @endif
@endif
