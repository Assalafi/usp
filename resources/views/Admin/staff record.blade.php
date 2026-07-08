@php
    use Illuminate\Support\Facades\DB;
@endphp
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Staff Details</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Staff Management</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/staff') }}">Staff</a></li>
                        <li class="breadcrumb-item active">Staff Details</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-body">
        <div class="page-wrapper">
            <div class="row">
                <div class="col-sm-12">
                    @foreach ($data as $row)
                    @php
                        $unitName = isset($row->unit_id) && $row->unit_id ? DB::table('units')->where('id', $row->unit_id)->value('name') : (isset($row->unit) ? $row->unit : '');
                        $designationName = isset($row->designation_id) && $row->designation_id ? DB::table('designations')->where('id', $row->designation_id)->value('name') : (isset($row->current_rank) ? $row->current_rank : '');
                        $gradeName = isset($row->grade_id) && $row->grade_id ? DB::table('grades')->where('id', $row->grade_id)->value('name') : (isset($row->grade) ? $row->grade : '');
                        $stepName = isset($row->step_id) && $row->step_id ? DB::table('steps')->where('id', $row->step_id)->value('name') : (isset($row->step) ? $row->step : '');
                        $institutions = [];
                        if (!empty($row->institutions)) {
                            $institutions = is_array($row->institutions) ? $row->institutions : json_decode($row->institutions, true);
                            if (!is_array($institutions)) $institutions = [];
                        }
                        $experiences = [];
                        if (!empty($row->experiences)) {
                            $experiences = is_array($row->experiences) ? $row->experiences : json_decode($row->experiences, true);
                            if (!is_array($experiences)) $experiences = [];
                        }
                        $publications = [];
                        if (!empty($row->publications)) {
                            $publications = is_array($row->publications) ? $row->publications : json_decode($row->publications, true);
                            if (!is_array($publications)) $publications = [];
                        }
                        $honours = [];
                        if (!empty($row->honours)) {
                            $honours = is_array($row->honours) ? $row->honours : json_decode($row->honours, true);
                            if (!is_array($honours)) $honours = [];
                        }
                        $memberships = [];
                        if (!empty($row->memberships)) {
                            $memberships = is_array($row->memberships) ? $row->memberships : json_decode($row->memberships, true);
                            if (!is_array($memberships)) $memberships = [];
                        }
                        $docOthers = json_decode($row->doc_others ?? '[]', true) ?: [];
                        $documents = [
                            'doc_photo' => 'Photo',
                            'doc_birth_certificate' => 'Birth Certificate/Declaration of Age',
                            'doc_primary_cert' => 'Primary School Certificate',
                            'doc_ssce' => 'SSCE/GCE',
                            'doc_diploma' => 'Diploma',
                            'doc_degree' => 'Degree',
                            'doc_masters' => 'Masters',
                            'doc_phd' => 'PhD',
                            'doc_indigine' => 'Indigene',
                            'doc_workshop' => 'Workshop Cert',
                            'doc_nysc' => 'NYSC/Exception',
                            'doc_appointment_letter' => 'Appointment Letter',
                            'doc_confirmation' => 'Letter of Confirmation',
                            'doc_professional_body' => 'Certificate of Professional Body Membership',
                        ];
                    @endphp

                    {{-- ── Header Card ── --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                @if($row->picture && file_exists(public_path('storage/picture/' . $row->picture)))
                                <div class="col-auto">
                                    <a href="{{ asset('storage/picture/' . $row->picture) }}" target="_blank">
                                        <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="Photo" class="rounded" style="width:110px;height:130px;object-fit:cover;border:2px solid #dee2e6;">
                                    </a>
                                </div>
                                @else
                                <div class="col-auto">
                                    <div class="rounded d-flex align-items-center justify-content-center" style="width:110px;height:130px;background:#f8f9fa;border:2px solid #dee2e6;">
                                        <i class="fas fa-user fa-4x text-muted"></i>
                                    </div>
                                </div>
                                @endif
                                <div class="col">
                                    <h4 class="mb-1">{{ $row->name }}</h4>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-hashtag"></i> {{ $row->username }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-briefcase"></i> {{ $designationName }}
                                        <span class="mx-1">-</span>
                                        <span class="text-muted">{{ $unitName }}</span>
                                    </p>
                                    <p class="mb-0">
                                        <span class="badge badge-success p-2">ACTIVE STAFF</span>
                                        @if(!empty($row->staff_category))
                                            <span class="badge badge-light p-2 ml-1">{{ $row->staff_category }}</span>
                                        @endif
                                        @if(!empty($row->gender))
                                            <span class="badge badge-light p-2 ml-1">{{ $row->gender }}</span>
                                        @endif
                                        @if(!empty($row->employee_status))
                                            <span class="badge badge-light p-2 ml-1">{{ $row->employee_status }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ url('/staff') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <a href="{{ url('/staff-record-update/' . $row->id) }}" class="btn btn-primary btn-sm ml-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="{{ url('/staff-record/download-cv/' . $row->id) }}" class="btn btn-success btn-sm ml-1" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Download CV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tabbed Content ── --}}
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-personal"><i class="fas fa-user"></i> Personal</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-service"><i class="fas fa-briefcase"></i> Service Record</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-academic"><i class="fas fa-graduation-cap"></i> Academic</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-kin"><i class="fas fa-users"></i> Next of Kin</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-financial"><i class="fas fa-university"></i> Financial</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-documents"><i class="fas fa-folder-open"></i> Documents</a></li>
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
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Full Name</td><td>{{ $row->name ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">SP/JP</td><td>{{ $row->username ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Gender</td><td>{{ $row->gender ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Marital Status</td><td>{{ $row->marital_status ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Date of Birth</td><td>{{ $row->date_of_birth == '1970-01-01' ? 'N/A' : date('F j, Y', strtotime($row->date_of_birth)) }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Phone Number</td><td>{{ $row->phone ?? 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Email</td><td>{{ $row->email ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">State of Origin</td><td>{{ $row->state ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">LGA</td><td>{{ $row->lga ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Nationality</td><td>{{ $row->nationality ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">NIN</td><td>{{ $row->nin ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Home Address</td><td>{{ $row->address ?? 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Service Record Tab ── --}}
                        <div class="tab-pane fade" id="tab-service">
                            <div class="card">
                                <div class="card-header py-2"><h6 class="mb-0">Service Record</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Department/Unit</td><td>{{ $unitName ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Current Designation</td><td>{{ $designationName ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Staff Category</td><td>{{ $row->staff_category ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Employee Status</td><td>{{ $row->employee_status ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Grade</td><td>{{ $gradeName ?: $row->grade ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Step</td><td>{{ $stepName ?: $row->step ?: 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Rank on First Appointment</td><td>{{ $row->rank_of_first_appointment ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Date of First Appointment</td><td>{{ $row->date_of_first_appointment == '1970-01-01' ? 'N/A' : date('F j, Y', strtotime($row->date_of_first_appointment)) }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Date of Assumption</td><td>{{ $row->date_of_asumption == '1970-01-01' ? 'N/A' : date('F j, Y', strtotime($row->date_of_asumption)) }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Date of Confirmation</td><td>{{ $row->date_of_comfirmation == '1970-01-01' ? 'N/A' : date('F j, Y', strtotime($row->date_of_comfirmation)) }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Date of Last Promotion</td><td>{{ $row->date_of_last_promotion == '1970-01-01' ? 'N/A' : date('F j, Y', strtotime($row->date_of_last_promotion)) }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Year of Experience</td><td>{{ $row->year_of_experiance ?? 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Academic Tab ── --}}
                        <div class="tab-pane fade" id="tab-academic">
                            <div class="card">
                                <div class="card-header py-2"><h6 class="mb-0">Academic Information</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Faculty</td><td>{{ $row->faculty ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Department</td><td>{{ $row->department ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Program</td><td>{{ $row->program ?? 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Degree Status</td><td>{{ $row->degree ? 'Available' : 'Not Available' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>

                                    @if(!empty($institutions))
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-graduation-cap"></i> Educational Qualifications</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="thead-light">
                                                <tr><th>#</th><th>Institution</th><th>Degree</th><th>Field of Study</th><th>Year</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($institutions as $i => $inst)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ is_array($inst) ? ($inst['name'] ?? 'N/A') : $inst }}</strong></td>
                                                    <td>{{ is_array($inst) ? ($inst['degree'] ?? 'N/A') : '' }}</td>
                                                    <td>{{ is_array($inst) ? ($inst['field'] ?? 'N/A') : '' }}</td>
                                                    <td><span class="badge badge-info">{{ is_array($inst) ? ($inst['year'] ?? 'N/A') : '' }}</span></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">No education records provided.</p>
                                    @endif

                                    @if(!empty($experiences))
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-briefcase"></i> Work Experience</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="thead-light">
                                                <tr><th>#</th><th>Position</th><th>Organization</th><th>Period</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($experiences as $i => $exp)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ is_array($exp) ? ($exp['position'] ?? 'N/A') : $exp }}</strong></td>
                                                    <td>{{ is_array($exp) ? ($exp['place'] ?? 'N/A') : '' }}</td>
                                                    <td><small class="text-muted">{{ is_array($exp) ? ($exp['date'] ?? 'N/A') : '' }}</small></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">No work experience records provided.</p>
                                    @endif

                                    @if(!empty($publications) && $publications != [''])
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-book"></i> Publications</h6>
                                    <ul class="mb-0 pl-3">
                                        @foreach($publications as $pub)
                                            @if(!empty($pub))
                                                <li>{{ is_array($pub) ? (isset($pub['title']) ? $pub['title'] : implode(' ', array_filter(array_values($pub)))) : $pub }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted mb-0">No publications provided.</p>
                                    @endif

                                    @if(!empty($honours) && $honours != [''])
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-award"></i> Honours / Distinctions</h6>
                                    <ul class="mb-0 pl-3">
                                        @foreach($honours as $hon)
                                            @if(!empty($hon))
                                                <li>{{ is_array($hon) ? (isset($hon['title']) ? $hon['title'] : implode(' ', array_filter(array_values($hon)))) : $hon }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted mb-0">No honours provided.</p>
                                    @endif

                                    @if(!empty($memberships) && $memberships != [''])
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-certificate"></i> Professional Memberships</h6>
                                    <ul class="mb-0 pl-3">
                                        @foreach($memberships as $mem)
                                            @if(!empty($mem))
                                                <li>{{ is_array($mem) ? (isset($mem['title']) ? $mem['title'] : implode(' ', array_filter(array_values($mem)))) : $mem }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted mb-0">No memberships provided.</p>
                                    @endif

                                    @if(!empty($row->extra_curricular))
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-running"></i> Extra-curricular Activities</h6>
                                    <p class="mb-0">{{ $row->extra_curricular }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ── Next of Kin Tab ── --}}
                        <div class="tab-pane fade" id="tab-kin">
                            <div class="card">
                                <div class="card-header py-2"><h6 class="mb-0">Next of Kin Information</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Full Name</td><td>{{ $row->kin_name ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Phone Number</td><td>{{ $row->kin_phone ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Address</td><td>{{ $row->kin_address ?: 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Relationship</td><td>{{ $row->kin_relationship ?: 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Financial Tab ── --}}
                        <div class="tab-pane fade" id="tab-financial">
                            <div class="card">
                                <div class="card-header py-2"><h6 class="mb-0">Financial Details</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Bank Name</td><td>{{ $row->bank_name ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">Account Number</td><td>{{ $row->account_number ?: 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr><td class="font-weight-bold text-muted" style="width:40%">Pension Administrator</td><td>{{ $row->pension_administrator ?: 'N/A' }}</td></tr>
                                                <tr><td class="font-weight-bold text-muted">BVN</td><td>{{ $row->bvn ?: 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Documents Tab ── --}}
                        <div class="tab-pane fade" id="tab-documents">
                            <div class="card">
                                <div class="card-header py-2"><h6 class="mb-0">Uploaded Documents</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($documents as $field => $label)
                                        @php
                                            $val = $row->$field ?? null;
                                            $path = 'storage/staff_documents/';
                                            if ($field === 'doc_photo') {
                                                if (empty($val) && !empty($row->picture)) {
                                                    $val = $row->picture;
                                                    $path = 'storage/picture/';
                                                } else {
                                                    $path = 'storage/staff_documents/';
                                                }
                                            }
                                            $icon = ($field === 'doc_photo') ? 'fa-image text-success' : (strpos(strtolower($label),'cert')!==false || strpos(strtolower($label),'degree')!==false || strpos(strtolower($label),'diploma')!==false || strpos(strtolower($label),'masters')!==false || strpos(strtolower($label),'phd')!==false ? 'fa-certificate text-warning' : 'fa-file-alt text-primary');
                                        @endphp
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <div class="card border h-100">
                                                <div class="card-body p-3 text-center">
                                                    <i class="fas {{ $icon }} fa-3x mb-2"></i>
                                                    <h6 class="mb-1">{{ $label }}</h6>
                                                    <small class="text-muted d-block mb-2" style="word-break:break-all;">{{ $val ?: 'Not uploaded' }}</small>
                                                    @if($val && file_exists(public_path($path . $val)))
                                                        <a href="{{ asset($path . $val) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt"></i> View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    @if(!empty($docOthers))
                                    <hr class="my-3">
                                    <h6 class="mb-2"><i class="fas fa-paperclip"></i> Other Documents</h6>
                                    <div class="row">
                                        @foreach($docOthers as $other)
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <div class="card border h-100">
                                                <div class="card-body p-3 text-center">
                                                    <i class="fas fa-file fa-3x mb-2 text-secondary"></i>
                                                    <h6 class="mb-1">{{ $other['name'] ?? 'Unnamed' }}</h6>
                                                    <small class="text-muted d-block mb-2" style="word-break:break-all;">{{ $other['file'] ?? '' }}</small>
                                                    @if(!empty($other['file']) && file_exists(public_path('storage/staff_documents/' . $other['file'])))
                                                        <a href="{{ asset('storage/staff_documents/' . $other['file']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt"></i> View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Manual tab switching for reliability
    $('.nav-tabs a').click(function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('.tab-pane').removeClass('show active');
        $('.nav-tabs a').removeClass('active');
        $(this).addClass('active');
        $(target).addClass('show active');
    });
});
</script>

