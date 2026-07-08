<style>
.approval-timeline{position:relative;padding-left:30px;margin:0}
.approval-timeline::before{content:'';position:absolute;left:14px;top:0;bottom:0;width:3px;background:#e9ecef;border-radius:2px}
.approval-step{position:relative;margin-bottom:24px}
.approval-step:last-child{margin-bottom:0}
.approval-step .step-icon{position:absolute;left:-30px;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;z-index:1;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.15)}
.step-icon.pending{background:#f8f9fa;color:#6c757d;border-color:#dee2e6}
.step-icon.active{background:#0d6efd;color:#fff;animation:pulse 2s infinite}
.step-icon.done{background:#198754;color:#fff}
.step-icon.rejected{background:#dc3545;color:#fff}
.step-icon.skipped{background:#adb5bd;color:#fff}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(13,110,253,.4)}70%{box-shadow:0 0 0 10px rgba(13,110,253,0)}}
.approval-card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);transition:all .3s ease;overflow:hidden}
.approval-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.1);transform:translateY(-1px)}
.approval-card .card-header{border:none;padding:14px 20px;font-weight:600}
.approval-card .card-body{padding:20px}
.approval-card.active-step{border-left:4px solid #0d6efd}
.approval-card.done-step{border-left:4px solid #198754}
.approval-card.skipped-step{border-left:4px solid #adb5bd;opacity:.7}
.info-card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.info-card .card-header{border:none;border-radius:12px 12px 0 0;padding:16px 20px}
.info-card .card-header.bg-dark{background:#212529!important;color:#fff!important}
.info-card .card-header.bg-primary{background:#0d6efd!important;color:#fff!important}
.info-card .card-header.bg-info{background:#0dcaf0!important;color:#fff!important}
.info-card .card-body{padding:20px}
.status-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-weight:600;font-size:13px}
.officer-info th{font-weight:500;color:#6c757d;width:30%;font-size:13px;padding:8px 12px!important}
.officer-info td{font-size:13px;padding:8px 12px!important}
.btn-approve{background:linear-gradient(135deg,#198754,#20c997);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-weight:600;transition:all .2s}
.btn-approve:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(25,135,84,.3);color:#fff}
.btn-provost{background:linear-gradient(135deg,#6f42c1,#9461fb);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-weight:600}
.btn-provost:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(111,66,193,.3);color:#fff}
.bg-purple{background:#6f42c1!important}
.doc-card{border:2px dashed #dee2e6;border-radius:12px;padding:20px;text-align:center;transition:all .2s}
.doc-card:hover{border-color:#0d6efd;background:#f8f9ff}
.doc-card.uploaded{border-color:#198754;border-style:solid;background:#f0fff4}
</style>
<div class="main-body">
<div class="page-wrapper">
<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
<div class="page-header-title"><h5 class="m-b-10">Inter-University Transfer Application</h5></div>
<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/dash"><i class="feather icon-home"></i></a></li>
<li class="breadcrumb-item"><a href="/inter-university-transfer/admin">Transfer Applications</a></li>
<li class="breadcrumb-item">{{ $application->application_no }}</li></ul>
</div></div></div></div>

@php
$sc = ['Awaiting UNIMAID HOD'=>'warning','Awaiting UNIMAID Dean'=>'warning','Awaiting Provost'=>'purple','Awaiting Registrar'=>'info','Awaiting VC'=>'info','Approved'=>'success','Rejected'=>'danger'];
$isMedical = in_array($application->new_program, ['MBBS', 'DBS']);
$hodDone = $application->unimaid_hod_recommendation != 'Pending';
$deanDone = $application->unimaid_dean_recommendation != 'Pending';
$provostDone = $application->provost_recommendation != 'Pending';
$regDone = $application->registrar_decision != 'Pending';
$vcDone = $application->vc_decision != 'Pending';
$newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
$showProvost = $isMedical || ($newFaculty && $newFaculty->college == 1);
@endphp

<div class="row">
<div class="col-lg-8">

<!-- Applicant Info -->
<div class="card info-card mb-4">
<div class="card-header bg-primary text-white d-flex align-items-center">
<i class="fas fa-user me-2"></i><h5 class="mb-0">Applicant Information</h5>
<span class="badge bg-light text-primary ms-auto">{{ $application->application_no }}</span>
</div>
<div class="card-body">
<div class="row mb-3">
<div class="col-md-8">
<h5 class="mb-1">{{ $application->surname }} {{ $application->first_name }} {{ $application->middle_name }}</h5>
<p class="text-muted mb-0"><i class="fas fa-envelope me-1"></i>{{ $application->email }} &nbsp; <i class="fas fa-phone me-1"></i>{{ $application->phone ?? '-' }}</p>
</div>
<div class="col-md-4 text-end">
<span class="badge bg-{{ $application->transfer_type=='within_nigeria'?'primary':'secondary' }} p-2">{{ $application->transfer_type=='within_nigeria'?'Within Nigeria':'From Abroad' }}</span>
</div>
</div>
<hr>
<div class="row">
<div class="col-md-6">
<table class="table table-sm officer-info mb-0">
<tr><th>Date of Birth</th><td>{{ $application->date_of_birth ? date('d M Y', strtotime($application->date_of_birth)) : '-' }}</td></tr>
<tr><th>Nationality</th><td>{{ $application->nationality }}</td></tr>
<tr><th>Postal Address</th><td>{{ $application->postal_address }}</td></tr>
</table>
</div>
<div class="col-md-6">
<table class="table table-sm officer-info mb-0">
<tr><th>Present Institution</th><td><strong>{{ $application->present_institution }}</strong></td></tr>
<tr><th>Reg. Number</th><td>{{ $application->registration_number }}</td></tr>
<tr><th>Year of Study</th><td>{{ $application->year_of_study }}</td></tr>
</table>
</div>
</div>
<hr>
<div class="row align-items-center">
<div class="col-md-8">
<p class="mb-1 text-muted small">Applying For</p>
<h6 class="text-primary mb-0"><i class="fas fa-graduation-cap me-1"></i><strong>{{ $newProgramTitle }}</strong></h6>
<small class="text-muted">{{ $newDepartmentTitle }}, {{ $newFacultyTitle }}</small>
</div>
<div class="col-md-4 text-end">
<span class="badge bg-success p-2">Paid - &#8358;{{ number_format($application->amount, 2) }}</span>
@if($application->rrr)<br><small class="text-muted">RRR: {{ $application->rrr }}</small>@endif
</div>
</div>
@if($application->reason_for_transfer)
<hr>
<p class="mb-1 text-muted small">Reason for Transfer</p>
<p class="mb-0">{{ $application->reason_for_transfer }}</p>
@endif

<hr>
<h6 class="text-primary"><i class="fas fa-file-alt me-1"></i>JAMB Information</h6>
<div class="row">
<div class="col-md-4"><small class="text-muted">Admission Type</small><br><span class="badge bg-info">{{ $application->admission_type ?? 'UTME' }}</span></div>
@if($application->admission_type == 'UTME')
<div class="col-md-4"><small class="text-muted">JAMB Score</small><br><strong>{{ $application->jamb_score ?? '-' }}</strong> / 400</div>
@endif
<div class="col-md-4"><small class="text-muted">JAMB Result</small><br>
@if($application->jamb_result_file)<a href="{{ asset('uploads/transfer_documents/' . $application->jamb_result_file) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a>@else<span class="badge bg-warning">Not Uploaded</span>@endif
</div>
</div>

<hr>
<h6 class="text-primary"><i class="fas fa-certificate me-1"></i>Educational Qualifications</h6>
<table class="table table-bordered table-sm mb-0">
<thead class="table-light"><tr><th>WASC/SSCE</th><th>TC II</th><th>GCE/HSC</th><th>IJMB</th><th>NCE</th><th>Others</th></tr></thead>
<tbody><tr>
<td>{{ $application->qualifications_wasc ?? '-' }}</td><td>{{ $application->qualifications_tc2 ?? '-' }}</td>
<td>{{ $application->qualifications_gce ?? '-' }}</td><td>{{ $application->qualifications_ijmb ?? '-' }}</td>
<td>{{ $application->qualifications_nce ?? '-' }}</td><td>{{ $application->qualifications_others ?? '-' }}</td>
</tr></tbody>
</table>
</div></div>

<!-- Uploaded Documents -->
<div class="card info-card mb-4">
<div class="card-header bg-info text-white d-flex align-items-center"><i class="fas fa-folder-open me-2"></i><h5 class="mb-0">Documents</h5></div>
<div class="card-body">
<div class="row">
<div class="col-md-4 mb-3">
<div class="doc-card {{ $application->certificates_upload ? 'uploaded' : '' }}">
<i class="fas fa-{{ $application->certificates_upload ? 'file-check text-success' : 'file-upload text-muted' }}" style="font-size:28px"></i>
<p class="fw-semibold mb-1 mt-2">Certificates</p>
@if($application->certificates_upload)<a href="{{ asset($application->certificates_upload) }}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i> View</a>@else<span class="badge bg-warning">Not Uploaded</span>@endif
</div>
</div>
<div class="col-md-4 mb-3">
<div class="doc-card {{ $application->present_institution_approval ? 'uploaded' : '' }}">
<i class="fas fa-{{ $application->present_institution_approval ? 'file-check text-success' : 'file-upload text-muted' }}" style="font-size:28px"></i>
<p class="fw-semibold mb-1 mt-2">HOD & Dean Approval</p>
@if($application->present_institution_approval)<a href="{{ asset($application->present_institution_approval) }}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i> View</a>@else<span class="badge bg-warning">Not Uploaded</span>@endif
</div>
</div>
<div class="col-md-4 mb-3">
<div class="doc-card {{ $application->transcript_upload ? 'uploaded' : '' }}">
<i class="fas fa-{{ $application->transcript_upload ? 'file-check text-success' : 'file-upload text-muted' }}" style="font-size:28px"></i>
<p class="fw-semibold mb-1 mt-2">Transcript</p>
@if($application->transcript_upload)<a href="{{ asset($application->transcript_upload) }}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i> View</a>@else<span class="badge bg-secondary">Optional</span>@endif
</div>
</div>
</div>
</div></div>

<!-- Approval Workflow -->
<div class="card info-card mb-4">
<div class="card-header bg-dark text-white d-flex align-items-center">
<i class="fas fa-tasks me-2"></i><h5 style="color: #fff" class="mb-0">Approval Workflow</h5>
@if($isMedical)<span class="badge bg-warning text-dark ms-auto"><i class="fas fa-stethoscope me-1"></i>MBBS/DBS Special Workflow</span>@endif
</div>
<div class="card-body">
<div class="approval-timeline">

<!-- STEP 1: UNIMAID HOD -->
<div class="approval-step">
@if($isMedical)
<div class="step-icon skipped"><i class="fas fa-forward"></i></div>
<div class="approval-card card skipped-step">
<div class="card-header bg-light"><i class="fas fa-user-tie me-2 text-muted"></i>UNIMAID HOD <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
<div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - handled by the Provost.</small></div>
</div>
@else
<div class="step-icon {{ $application->status == 'Awaiting UNIMAID HOD' ? 'active' : ($hodDone ? ($application->unimaid_hod_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
<i class="fas {{ $hodDone ? ($application->unimaid_hod_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting UNIMAID HOD' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
</div>
<div class="approval-card card {{ $application->status == 'Awaiting UNIMAID HOD' ? 'active-step' : ($hodDone ? 'done-step' : '') }}">
<div class="card-header {{ $application->status == 'Awaiting UNIMAID HOD' ? 'bg-primary text-white' : 'bg-light' }}">
<i class="fas fa-user-tie me-2"></i>Head of Department (UNIMAID)
@if($hodDone)<span class="status-pill {{ $application->unimaid_hod_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->unimaid_hod_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->unimaid_hod_recommendation=='Yes' ? 'Recommended' : 'Not Recommended' }}</span>@endif
</div>
<div class="card-body">
@if($application->unimaid_hod_recommendation == 'Pending' && $application->status == 'Awaiting UNIMAID HOD' && ($canApproveHOD ?? false))
<form id="hodForm">
<div class="row mb-3"><div class="col-md-6">
<label class="form-label fw-semibold">Decision *</label>
<select class="form-control" name="decision" required><option value="">Select</option><option value="Yes">Recommend</option><option value="No">Do Not Recommend</option></select>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea></div>
<button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit</button>
</form>
@elseif($hodDone)
<table class="table table-sm officer-info mb-0">
<tr><th>Officer</th><td>{{ $application->unimaid_hod_name ?? '-' }}</td></tr>
<tr><th>Date</th><td>{{ $application->unimaid_hod_date ? date('d M Y', strtotime($application->unimaid_hod_date)) : '-' }}</td></tr>
@if($application->unimaid_hod_remarks)<tr><th>Remarks</th><td>{{ $application->unimaid_hod_remarks }}</td></tr>@endif
</table>
@else
<p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
@endif
</div></div>
@endif
</div>

<!-- STEP 2: UNIMAID Dean -->
<div class="approval-step">
@if($isMedical)
<div class="step-icon skipped"><i class="fas fa-forward"></i></div>
<div class="approval-card card skipped-step">
<div class="card-header bg-light"><i class="fas fa-user-graduate me-2 text-muted"></i>UNIMAID Dean <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
<div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - handled by the Provost.</small></div>
</div>
@else
<div class="step-icon {{ $application->status == 'Awaiting UNIMAID Dean' ? 'active' : ($deanDone ? ($application->unimaid_dean_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
<i class="fas {{ $deanDone ? ($application->unimaid_dean_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting UNIMAID Dean' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
</div>
<div class="approval-card card {{ $application->status == 'Awaiting UNIMAID Dean' ? 'active-step' : ($deanDone ? 'done-step' : '') }}">
<div class="card-header {{ $application->status == 'Awaiting UNIMAID Dean' ? 'bg-primary text-white' : 'bg-light' }}">
<i class="fas fa-user-graduate me-2"></i>Dean of Faculty (UNIMAID)
@if($deanDone)<span class="status-pill {{ $application->unimaid_dean_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->unimaid_dean_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->unimaid_dean_recommendation=='Yes' ? 'Recommended' : 'Not Recommended' }}</span>@endif
</div>
<div class="card-body">
@if($application->unimaid_dean_recommendation == 'Pending' && $application->status == 'Awaiting UNIMAID Dean' && ($canApproveDean ?? false))
<form id="deanForm">
<div class="row mb-3"><div class="col-md-6">
<label class="form-label fw-semibold">Decision *</label>
<select class="form-control" name="decision" required><option value="">Select</option><option value="Yes">Recommend</option><option value="No">Do Not Recommend</option></select>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea></div>
<button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit</button>
</form>
@elseif($deanDone)
<table class="table table-sm officer-info mb-0">
<tr><th>Officer</th><td>{{ $application->unimaid_dean_name ?? '-' }}</td></tr>
<tr><th>Date</th><td>{{ $application->unimaid_dean_date ? date('d M Y', strtotime($application->unimaid_dean_date)) : '-' }}</td></tr>
@if($application->unimaid_dean_remarks)<tr><th>Remarks</th><td>{{ $application->unimaid_dean_remarks }}</td></tr>@endif
</table>
@else
<p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
@endif
</div></div>
@endif
</div>

<!-- STEP 3: Provost -->
@if($showProvost)
<div class="approval-step">
<div class="step-icon {{ $application->status == 'Awaiting Provost' ? 'active' : ($provostDone ? ($application->provost_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
<i class="fas {{ $provostDone ? ($application->provost_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Provost' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
</div>
<div class="approval-card card {{ $application->status == 'Awaiting Provost' ? 'active-step' : ($provostDone ? 'done-step' : '') }}" style="border-left-color:#6f42c1!important">
<div class="card-header {{ $application->status == 'Awaiting Provost' ? 'bg-purple text-white' : 'bg-light' }}">
<i class="fas fa-university me-2"></i>Provost (UNIMAID)
@if($isMedical)<span class="badge bg-warning text-dark ms-2" style="font-size:10px"><i class="fas fa-stethoscope me-1"></i>Combined Decision</span>@endif
@if($provostDone)<span class="status-pill {{ $application->provost_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->provost_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->provost_recommendation=='Yes' ? 'Recommended' : 'Not Recommended' }}</span>@endif
</div>
<div class="card-body">
@if($application->provost_recommendation == 'Pending' && $application->status == 'Awaiting Provost' && ($canApproveProvost ?? false))
<form id="provostForm">
<div class="row mb-3"><div class="col-md-6">
<label class="form-label fw-semibold">Decision *</label>
<select class="form-control" name="decision" required><option value="">Select</option><option value="Yes">Recommend</option><option value="No">Do Not Recommend</option></select>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea></div>
<button type="submit" class="btn btn-provost"><i class="fas fa-paper-plane me-2"></i>Submit</button>
</form>
@elseif($provostDone)
<table class="table table-sm officer-info mb-0">
<tr><th>Officer</th><td>{{ $application->provost_name ?? '-' }}</td></tr>
<tr><th>Date</th><td>{{ $application->provost_date ? date('d M Y', strtotime($application->provost_date)) : '-' }}</td></tr>
@if($application->provost_remarks)<tr><th>Remarks</th><td>{{ $application->provost_remarks }}</td></tr>@endif
</table>
@else
<p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
@endif
</div></div>
</div>
@endif

<!-- STEP 4: Registrar -->
<div class="approval-step">
<div class="step-icon {{ $application->status == 'Awaiting Registrar' ? 'active' : ($regDone ? ($application->registrar_decision=='Approved' ? 'done' : 'rejected') : 'pending') }}">
<i class="fas {{ $regDone ? ($application->registrar_decision=='Approved' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Registrar' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
</div>
<div class="approval-card card {{ $application->status == 'Awaiting Registrar' ? 'active-step' : ($regDone ? 'done-step' : '') }}">
<div class="card-header {{ $application->status == 'Awaiting Registrar' ? 'bg-danger text-white' : 'bg-light' }}">
<i class="fas fa-gavel me-2"></i>Registrar
@if($regDone)<span class="status-pill {{ $application->registrar_decision=='Approved' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->registrar_decision=='Approved' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->registrar_decision }}</span>@endif
</div>
<div class="card-body">
@if($application->registrar_decision == 'Pending' && $application->status == 'Awaiting Registrar' && ($canApproveRegistrar ?? false))
<form id="registrarForm">
<div class="row mb-3"><div class="col-md-6">
<label class="form-label fw-semibold">Decision *</label>
<select class="form-control" name="decision" required><option value="">Select</option><option value="Approved">Approve</option><option value="Rejected">Reject</option></select>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea></div>
<button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit</button>
</form>
@elseif($regDone)
<table class="table table-sm officer-info mb-0">
<tr><th>Officer</th><td>{{ $application->registrar_name ?? '-' }}</td></tr>
<tr><th>Date</th><td>{{ $application->registrar_date ? date('d M Y', strtotime($application->registrar_date)) : '-' }}</td></tr>
@if($application->registrar_remarks)<tr><th>Remarks</th><td>{{ $application->registrar_remarks }}</td></tr>@endif
</table>
@else
<p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
@endif
</div></div>
</div>

<!-- STEP 5: VC -->
<div class="approval-step">
<div class="step-icon {{ $application->status == 'Awaiting VC' ? 'active' : ($vcDone ? ($application->vc_decision=='Approved' ? 'done' : 'rejected') : 'pending') }}">
<i class="fas {{ $vcDone ? ($application->vc_decision=='Approved' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting VC' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
</div>
<div class="approval-card card {{ $application->status == 'Awaiting VC' ? 'active-step' : ($vcDone ? 'done-step' : '') }}">
<div class="card-header {{ $application->status == 'Awaiting VC' ? 'bg-dark text-white' : 'bg-light' }}">
<i class="fas fa-stamp me-2"></i>Vice-Chancellor
@if($vcDone)<span class="status-pill {{ $application->vc_decision=='Approved' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->vc_decision=='Approved' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->vc_decision }}</span>@endif
</div>
<div class="card-body">
@if($application->vc_decision=='Pending' && $application->status=='Awaiting VC' && ($canApproveVC ?? false))
<form id="vcForm">
<div class="row mb-3"><div class="col-md-6">
<label class="form-label fw-semibold">Decision *</label>
<select class="form-control" name="decision" required><option value="">Select</option><option value="Approved">Approve</option><option value="Rejected">Reject</option></select>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea></div>
<button type="submit" class="btn btn-approve"><i class="fas fa-stamp me-2"></i>Submit Final Decision</button>
</form>
@elseif($vcDone)
<table class="table table-sm officer-info mb-0">
<tr><th>Officer</th><td>{{ $application->vc_name ?? '-' }}</td></tr>
<tr><th>Date</th><td>{{ $application->vc_date ? date('d M Y', strtotime($application->vc_date)) : '-' }}</td></tr>
@if($application->vc_remarks)<tr><th>Remarks</th><td>{{ $application->vc_remarks }}</td></tr>@endif
</table>
@else
<p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
@endif
</div></div>
</div>

</div><!-- end approval-timeline -->
</div></div>

</div>

<!-- Sidebar -->
<div class="col-lg-4">
<div class="card info-card mb-3">
<div class="card-header bg-{{ $sc[$application->status] ?? 'secondary' }} text-white"><h6 class="mb-0"><i class="fas fa-info-circle me-1"></i>Status</h6></div>
<div class="card-body text-center">
<span class="badge bg-{{ $sc[$application->status] ?? 'secondary' }} p-3" style="font-size:15px;border-radius:10px">{{ $application->status }}</span>
<p class="mt-2 text-muted small mb-0">Session: {{ $application->session }}</p>
</div></div>

@if($application->status == 'Approved')
<div class="card info-card border-success mb-3">
<div class="card-header bg-success text-white"><h6 class="mb-0"><i class="fas fa-user-check me-1"></i>Transfer Approved</h6></div>
<div class="card-body">
<p class="mb-1 text-muted small">New Program</p>
<p class="text-success fw-bold mb-2">{{ $newProgramTitle }}</p>
<p class="mb-1 text-muted small">Department</p>
<p class="text-success fw-bold mb-0">{{ $newDepartmentTitle }}</p>
</div></div>
@endif

<div class="card info-card mb-3"><div class="card-body">
<a href="/inter-university-transfer/admin" class="btn btn-outline-secondary w-100 mb-2"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
<button onclick="window.print()" class="btn btn-outline-info w-100 mb-2"><i class="fas fa-print me-1"></i> Print Application</button>
@if($application->status == 'Approved')
<a href="{{ route('inter-transfer.admission-letter', $application->id) }}" target="_blank" class="btn btn-success w-100"><i class="fas fa-file-pdf me-1"></i> Download Admission Letter</a>
@endif
</div></div>
</div>
</div>
</div></div>

<script>
$('#hodForm').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);fd.append('_token','{{ csrf_token() }}');
    $.ajax({url:'/inter-university-transfer/hod-action/{{ $application->id }}',type:'POST',data:fd,processData:false,contentType:false,
        success:function(r){swal('Success',r.message,'success').then(function(){location.reload();});},
        error:function(x){swal('Error',x.responseJSON?x.responseJSON.error:'Failed','error');}
    });
});
$('#deanForm').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);fd.append('_token','{{ csrf_token() }}');
    $.ajax({url:'/inter-university-transfer/dean-action/{{ $application->id }}',type:'POST',data:fd,processData:false,contentType:false,
        success:function(r){swal('Success',r.message,'success').then(function(){location.reload();});},
        error:function(x){swal('Error',x.responseJSON?x.responseJSON.error:'Failed','error');}
    });
});
$('#provostForm').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);fd.append('_token','{{ csrf_token() }}');
    $.ajax({url:'/inter-university-transfer/provost-action/{{ $application->id }}',type:'POST',data:fd,processData:false,contentType:false,
        success:function(r){swal('Success',r.message,'success').then(function(){location.reload();});},
        error:function(x){swal('Error',x.responseJSON?x.responseJSON.error:'Failed','error');}
    });
});
$('#registrarForm').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);fd.append('_token','{{ csrf_token() }}');
    $.ajax({url:'/inter-university-transfer/registrar-action/{{ $application->id }}',type:'POST',data:fd,processData:false,contentType:false,
        success:function(r){swal('Success',r.message,'success').then(function(){location.reload();});},
        error:function(x){swal('Error',x.responseJSON?x.responseJSON.error:'Failed','error');}
    });
});
$('#vcForm').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);fd.append('_token','{{ csrf_token() }}');
    $.ajax({url:'/inter-university-transfer/vc-action/{{ $application->id }}',type:'POST',data:fd,processData:false,contentType:false,
        success:function(r){swal('Success',r.message,'success').then(function(){location.reload();});},
        error:function(x){swal('Error',x.responseJSON?x.responseJSON.error:'Failed','error');}
    });
});
</script>
