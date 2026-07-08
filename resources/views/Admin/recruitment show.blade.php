@php
    $statusColors = [
        'DRAFT' => 'badge-warning', 'NEW' => 'badge-primary', 'SCREENING' => 'badge-info',
        'SHORTLISTED' => 'badge-success', 'INTERVIEW_SCHEDULED' => 'badge-purple',
        'INTERVIEWING' => 'badge-purple', 'INTERVIEW_COMPLETED' => 'badge-indigo',
        'OFFER_PENDING' => 'badge-warning', 'OFFER_SENT' => 'badge-success',
        'OFFER_ACCEPTED' => 'badge-success', 'OFFER_DECLINED' => 'badge-secondary',
        'HIRED' => 'badge-success', 'REJECTED' => 'badge-danger', 'WITHDRAWN' => 'badge-secondary',
    ];
@endphp
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Applicant Details</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Human Resource</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/recruitment') }}">Recruitment</a></li>
                        <li class="breadcrumb-item active">Applicant Details</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-body">
        <div class="page-wrapper">
            <div class="row">
                <div class="col-sm-12">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    @if(isset($applicant))
                        @php $application = $applicant['applications'][0] ?? null; @endphp
                        @if($application)

                        {{-- ── Header Card ── --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    @if($application['passport_photo'])
                                    <div class="col-auto">
                                        <a href="{{ $application['passport_photo'] }}" target="_blank">
                                            <img src="{{ $application['passport_photo'] }}" alt="Photo" class="rounded" style="width:110px;height:130px;object-fit:cover;border:2px solid #dee2e6;">
                                        </a>
                                    </div>
                                    @endif
                                    <div class="col">
                                        <h4 class="mb-1">{{ $application['personal']['first_name'] }} {{ $application['personal']['middle_name'] ?? '' }} {{ $application['personal']['last_name'] }}</h4>
                                        <p class="text-muted mb-1">
                                            @if($application['application_number'])
                                                <i class="fas fa-hashtag"></i> {{ $application['application_number'] }}
                                                <span class="mx-2">|</span>
                                            @endif
                                            @if(isset($application['job']['title']))
                                                <i class="fas fa-briefcase"></i> {{ $application['job']['title'] }}
                                                <span class="mx-1">-</span>
                                                <span class="text-muted">{{ $application['job']['department_name'] ?? '' }}</span>
                                            @endif
                                        </p>
                                        <p class="mb-0">
                                            <span class="badge {{ $statusColors[$application['status']] ?? 'badge-secondary' }} p-2">
                                            @if($application['status'] === 'NEW')
                                                SUBMITTED
                                            @elseif($application['status'] === 'DRAFT')
                                                DRAFT ({{ ($application['current_step'] ?? 0) + 1 }})
                                            @else
                                                {{ $application['status'] }}
                                            @endif
                                        </span>
                                            @if(!empty($application['job']['staff_type']))
                                                <span class="badge badge-light p-2 ml-1">{{ $application['job']['staff_type'] }}</span>
                                            @endif
                                            @if(!empty($application['personal']['gender']))
                                                <span class="badge badge-light p-2 ml-1">{{ $application['personal']['gender'] }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{ url('/recruitment') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </a>
                                        @if($application['status'] !== 'DRAFT')
                                            <a href="{{ url('/recruitment/download-cv/' . $applicant['id']) }}" class="btn btn-success btn-sm ml-1" target="_blank">
                                                <i class="fas fa-file-pdf"></i> Download CV
                                            </a>
                                        @else
                                            <button class="btn btn-secondary btn-sm ml-1" disabled title="CV not available for draft applications">
                                                <i class="fas fa-file-pdf"></i> CV Not Available
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Tabbed Content ── --}}
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-personal"><i class="fas fa-user"></i> Personal</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-contact"><i class="fas fa-phone"></i> Contact</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-job"><i class="fas fa-briefcase"></i> Job & Professional</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-education"><i class="fas fa-graduation-cap"></i> Education</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-experience"><i class="fas fa-building"></i> Experience</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-referees"><i class="fas fa-users"></i> Referees</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-documents"><i class="fas fa-folder-open"></i> Documents <span class="badge badge-primary">{{ count($application['documents'] ?? []) }}</span></a></li>
                        </ul>

                        <div class="tab-content">
                            {{-- ── Personal Tab ── --}}
                            <div class="tab-pane fade show active" id="tab-personal">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Personal Information</h6></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td class="font-weight-bold text-muted" style="width:40%">First Name</td><td>{{ $application['personal']['first_name'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Middle Name</td><td>{{ $application['personal']['middle_name'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Last Name</td><td>{{ $application['personal']['last_name'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Date of Birth</td><td>{{ $application['personal']['date_of_birth'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Place of Birth</td><td>{{ $application['personal']['place_of_birth'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Gender</td><td>{{ $application['personal']['gender'] ?? 'N/A' }}</td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td class="font-weight-bold text-muted" style="width:40%">Marital Status</td><td>{{ $application['personal']['marital_status'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Nationality</td><td>{{ $application['personal']['nationality'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">State of Origin</td><td>{{ $application['personal']['state_of_origin'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">LGA</td><td>{{ $application['personal']['local_govt_of_origin'] ?? 'N/A' }}</td></tr>
                                                    @if(!empty($application['personal']['nin']))
                                                    <tr><td class="font-weight-bold text-muted">NIN</td><td>{{ $application['personal']['nin'] }}</td></tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Contact Tab ── --}}
                            <div class="tab-pane fade" id="tab-contact">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Contact Information</h6></div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr><td class="font-weight-bold text-muted" style="width:20%">Email</td><td>{{ $application['contact']['contact_email'] ?? $applicant['email'] ?? 'N/A' }}</td></tr>
                                            <tr><td class="font-weight-bold text-muted">Phone</td><td>{{ $application['contact']['contact_phone'] ?? $applicant['phone'] ?? 'N/A' }}</td></tr>
                                            <tr><td class="font-weight-bold text-muted">Permanent Address</td><td>{{ $application['contact']['permanent_home_address'] ?? 'N/A' }}</td></tr>
                                            <tr><td class="font-weight-bold text-muted">Current Address</td><td>{{ $application['contact']['current_postal_address'] ?? 'N/A' }}</td></tr>
                                            <tr><td class="font-weight-bold text-muted">City</td><td>{{ $application['contact']['city'] ?? 'N/A' }}</td></tr>
                                            <tr><td class="font-weight-bold text-muted">Country</td><td>{{ $application['contact']['country'] ?? 'N/A' }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Job & Professional Tab ── --}}
                            <div class="tab-pane fade" id="tab-job">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header py-2"><h6 class="mb-0">Position Applied For</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td class="font-weight-bold text-muted" style="width:40%">Position</td><td><strong>{{ $application['job']['title'] ?? 'N/A' }}</strong></td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Department</td><td>{{ $application['job']['department_name'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Staff Type</td><td>{{ $application['job']['staff_type'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Job Type</td><td>{{ $application['job']['job_type'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Experience Level</td><td>{{ $application['job']['experience_level'] ?? 'N/A' }}</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header py-2"><h6 class="mb-0">Professional Summary</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td class="font-weight-bold text-muted" style="width:40%">Employment Status</td><td>{{ $application['professional']['employment_status'] ?? 'N/A' }}</td></tr>
                                                    <tr><td class="font-weight-bold text-muted">Years of Experience</td><td>{{ $application['professional']['experience_years'] ?? '0' }} years</td></tr>
                                                </table>
                                                @if(!empty($application['professional']['extra_curricular_activities']))
                                                    <hr class="my-2">
                                                    <small class="font-weight-bold text-muted">Extra Curricular Activities</small>
                                                    <p class="mb-0 mt-1">{{ $application['professional']['extra_curricular_activities'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Education Tab ── --}}
                            <div class="tab-pane fade" id="tab-education">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Educational Background</h6></div>
                                    <div class="card-body">
                                        @if(!empty($application['education']))
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead class="thead-light">
                                                        <tr><th>#</th><th>Institution</th><th>Qualification</th><th>Field of Study</th><th>Year</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($application['education'] as $i => $edu)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td><strong>{{ $edu['institution'] ?? 'N/A' }}</strong></td>
                                                            <td>{{ $edu['degree'] ?? 'N/A' }}</td>
                                                            <td>{{ $edu['field_of_study'] ?? 'N/A' }}</td>
                                                            <td><span class="badge badge-info">{{ $edu['graduation_year'] ?? 'N/A' }}</span></td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No education records provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- ── Experience Tab ── --}}
                            <div class="tab-pane fade" id="tab-experience">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Work Experience</h6></div>
                                    <div class="card-body">
                                        @if(!empty($application['work_experience']))
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead class="thead-light">
                                                        <tr><th>#</th><th>Position</th><th>Organization</th><th>Period</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($application['work_experience'] as $i => $work)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td><strong>{{ $work['position'] ?? 'N/A' }}</strong></td>
                                                            <td>{{ $work['place'] ?? 'N/A' }}</td>
                                                            <td><small class="text-muted">{{ $work['date'] ?? 'N/A' }}</small></td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No work experience records provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- ── Referees Tab ── --}}
                            <div class="tab-pane fade" id="tab-referees">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Referees</h6></div>
                                    <div class="card-body">
                                        @if(!empty($application['referees']))
                                            <div class="row">
                                                @foreach($application['referees'] as $i => $referee)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border">
                                                        <div class="card-body p-3">
                                                            <h6 class="mb-2"><i class="fas fa-user-tie text-primary mr-1"></i> Referee {{ $i + 1 }}</h6>
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <tr><td class="font-weight-bold text-muted" style="width:30%">Name</td><td>{{ $referee['name'] ?? 'N/A' }}</td></tr>
                                                                <tr><td class="font-weight-bold text-muted">Address</td><td>{{ $referee['address'] ?? 'N/A' }}</td></tr>
                                                                @if(!empty($referee['phone']))
                                                                <tr><td class="font-weight-bold text-muted">Phone</td><td>{{ $referee['phone'] }}</td></tr>
                                                                @endif
                                                                @if(!empty($referee['email']))
                                                                <tr><td class="font-weight-bold text-muted">Email</td><td>{{ $referee['email'] }}</td></tr>
                                                                @endif
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No referees provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- ── Documents Tab ── --}}
                            <div class="tab-pane fade" id="tab-documents">
                                <div class="card">
                                    <div class="card-header py-2"><h6 class="mb-0">Uploaded Documents</h6></div>
                                    <div class="card-body">
                                        @if(!empty($application['documents']))
                                            <div class="row">
                                                @foreach($application['documents'] as $doc)
                                                @php
                                                    $ext = strtolower(pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION));
                                                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','bmp']);
                                                    $isPdf = $ext === 'pdf';
                                                    $iconClass = $isPdf ? 'fa-file-pdf text-danger' : ($isImage ? 'fa-file-image text-success' : 'fa-file text-primary');
                                                @endphp
                                                <div class="col-md-4 col-lg-3 mb-3">
                                                    <div class="card border h-100">
                                                        <div class="card-body p-3 text-center">
                                                            <i class="fas {{ $iconClass }} fa-3x mb-2"></i>
                                                            <h6 class="mb-1">{{ $doc['label'] ?? 'Document' }}</h6>
                                                            <small class="text-muted d-block mb-2" style="word-break:break-all;">{{ $doc['file_name'] ?? '' }}</small>
                                                            @if(!empty($doc['file_url']))
                                                                <a href="{{ $doc['file_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-external-link-alt"></i> View
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No documents uploaded.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Bootstrap tabs
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
});
</script>
