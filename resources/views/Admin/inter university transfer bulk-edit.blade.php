<!-- Start Content-->
<div class="main-body">
<div class="page-wrapper">
<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
<div class="page-header-title"><h5 class="m-b-10">Bulk Edit Inter-University Transfer Application</h5></div>
<ul class="breadcrumb">
<li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
<li class="breadcrumb-item"><a href="{{ route('inter-transfer.admin') }}">Inter-University Transfer</a></li>
<li class="breadcrumb-item">Bulk Edit</li>
</ul>
</div></div></div></div>

<div class="row"><div class="col-sm-12"><div class="card">
<div class="card-header bg-warning text-dark"><h5><i class="fas fa-edit me-2"></i>Admin Bulk Edit - {{ $application->application_no }}</h5></div>
<div class="card-body">
<form id="bulkEditForm">@csrf

<!-- Basic Information -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-primary"><i class="fas fa-user me-2"></i>Basic Information</h6><hr></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Surname</label>
<input type="text" name="surname" class="form-control" value="{{ $application->surname }}" required></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">First Name</label>
<input type="text" name="first_name" class="form-control" value="{{ $application->first_name }}" required></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Middle Name</label>
<input type="text" name="middle_name" class="form-control" value="{{ $application->middle_name }}"></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Application Status</label>
<select name="status" class="form-control" required>
@foreach(['Awaiting UNIMAID HOD','Awaiting UNIMAID Dean','Awaiting Provost','Awaiting Registrar','Awaiting VC','Approved','Rejected'] as $s)
<option value="{{ $s }}" {{ $application->status == $s ? 'selected' : '' }}>{{ $s }}</option>
@endforeach
</select></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Admission Type</label>
<input type="text" name="admission_type" class="form-control" value="{{ $application->admission_type ?? 'UTME' }}"></div></div>
</div>

<!-- Transfer Information -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-info"><i class="fas fa-exchange-alt me-2"></i>Transfer Information</h6><hr></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Present Institution</label>
<input type="text" name="present_institution" class="form-control" value="{{ $application->present_institution }}" required></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Registration Number</label>
<input type="text" name="registration_number" class="form-control" value="{{ $application->registration_number }}" required></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Year of Study</label>
<input type="text" name="year_of_study" class="form-control" value="{{ $application->year_of_study }}" required></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Transfer Type</label>
<select name="transfer_type" class="form-control" required>
<option value="within_nigeria" {{ $application->transfer_type == 'within_nigeria' ? 'selected' : '' }}>Within Nigeria</option>
<option value="from_abroad" {{ $application->transfer_type == 'from_abroad' ? 'selected' : '' }}>From Abroad</option>
</select></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">JAMB Score</label>
<input type="number" name="jamb_score" class="form-control" value="{{ $application->jamb_score }}" min="0" max="400"></div></div>
<div class="col-12"><div class="form-group"><label class="form-label">Reason for Transfer</label>
<textarea name="reason_for_transfer" class="form-control" rows="3" required>{{ $application->reason_for_transfer }}</textarea></div></div>
</div>

<!-- New Program at UNIMAID (Cascading) -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-success"><i class="fas fa-university me-2"></i>New Program at UNIMAID</h6><hr></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Faculty</label>
<select name="new_faculty" id="newFaculty" class="form-control" required>
<option value="">Select Faculty</option>
@foreach($faculties as $faculty)
<option value="{{ $faculty->code }}" {{ $application->new_faculty == $faculty->code ? 'selected' : '' }}>{{ $faculty->title }}</option>
@endforeach
</select></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Department</label>
<select name="new_department" id="newDepartment" class="form-control" required>
<option value="{{ $application->new_department }}">{{ $newDepartmentTitle }}</option>
</select></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Program</label>
<select name="new_program" id="newProgram" class="form-control" required>
<option value="{{ $application->new_program }}">{{ $newProgramTitle }}</option>
</select></div></div>
</div>

<!-- Officer Actions -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-danger"><i class="fas fa-user-shield me-2"></i>Officer Actions (Override)</h6><hr></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">UNIMAID HOD</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="unimaid_hod_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->unimaid_hod_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->unimaid_hod_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="unimaid_hod_remarks" class="form-control form-control-sm" rows="2">{{ $application->unimaid_hod_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>HOD Name</label><input type="text" name="unimaid_hod_name" class="form-control form-control-sm" value="{{ $application->unimaid_hod_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="unimaid_hod_date" class="form-control form-control-sm" value="{{ $application->unimaid_hod_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">UNIMAID Dean</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="unimaid_dean_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->unimaid_dean_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->unimaid_dean_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="unimaid_dean_remarks" class="form-control form-control-sm" rows="2">{{ $application->unimaid_dean_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Dean Name</label><input type="text" name="unimaid_dean_name" class="form-control form-control-sm" value="{{ $application->unimaid_dean_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="unimaid_dean_date" class="form-control form-control-sm" value="{{ $application->unimaid_dean_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Provost</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="provost_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->provost_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->provost_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="provost_remarks" class="form-control form-control-sm" rows="2">{{ $application->provost_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Provost Name</label><input type="text" name="provost_name" class="form-control form-control-sm" value="{{ $application->provost_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="provost_date" class="form-control form-control-sm" value="{{ $application->provost_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Registrar</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Decision</label><select name="registrar_decision" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Approved" {{ ($application->registrar_decision ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option><option value="Rejected" {{ ($application->registrar_decision ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="registrar_remarks" class="form-control form-control-sm" rows="2">{{ $application->registrar_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Registrar Name</label><input type="text" name="registrar_name" class="form-control form-control-sm" value="{{ $application->registrar_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="registrar_date" class="form-control form-control-sm" value="{{ $application->registrar_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">VC</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Decision</label><select name="vc_decision" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Approved" {{ ($application->vc_decision ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option><option value="Rejected" {{ ($application->vc_decision ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="vc_remarks" class="form-control form-control-sm" rows="2">{{ $application->vc_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>VC Name</label><input type="text" name="vc_name" class="form-control form-control-sm" value="{{ $application->vc_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="vc_date" class="form-control form-control-sm" value="{{ $application->vc_date ?? '' }}"></div>
</div></div></div>
</div>

<!-- Submit -->
<div class="row"><div class="col-12">
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i><strong>Warning:</strong> Modifying critical application data. This action will be logged.</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Application</button>
<a href="{{ route('inter-transfer.show', $application->id) }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
</div></div>
</form>
</div></div></div></div>
</div></div>

<script>
$(document).ready(function(){
$('#newFaculty').change(function(){
var fc=$(this).val(),ds=$('#newDepartment'),ps=$('#newProgram');
if(fc){
$.ajax({url:'{{ route("inter-transfer.get-departments") }}',type:'POST',data:{_token:'{{ csrf_token() }}',faculty:fc},success:function(r){
ds.html('<option value="">Select Department</option>');
r.forEach(function(d){ds.append('<option value="'+d.code+'">'+d.title+'</option>');});
ds.prop('disabled',false);
ps.html('<option value="">Select Department First</option>').prop('disabled',true);
}});
}else{ds.html('<option value="">Select Faculty First</option>').prop('disabled',true);ps.html('<option value="">Select Department First</option>').prop('disabled',true);}
});
$('#newDepartment').change(function(){
var dc=$(this).val(),ps=$('#newProgram');
if(dc){
$.ajax({url:'{{ route("inter-transfer.get-programs") }}',type:'POST',data:{_token:'{{ csrf_token() }}',department:dc},success:function(r){
ps.html('<option value="">Select Program</option>');
r.forEach(function(p){ps.append('<option value="'+p.code+'">'+p.title+'</option>');});
ps.prop('disabled',false);
}});
}else{ps.html('<option value="">Select Department First</option>').prop('disabled',true);}
});
$('#bulkEditForm').on('submit',function(e){
e.preventDefault();
if(!confirm('Are you sure you want to update this application?'))return false;
$.ajax({url:'{{ route("inter-transfer.bulk-update", $application->id) }}',method:'POST',data:$(this).serialize(),
success:function(r){if(r.success){alert('Application updated successfully!');window.location.href='{{ route("inter-transfer.show", $application->id) }}';}else{alert('Error: '+(r.error||'Unknown error'));}},
error:function(x){var m='An error occurred.';if(x.responseJSON&&x.responseJSON.error)m=x.responseJSON.error;alert(m);}
});
});
});
</script>