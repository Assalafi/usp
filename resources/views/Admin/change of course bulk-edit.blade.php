<!-- Start Content-->
<div class="main-body">
<div class="page-wrapper">
<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
<div class="page-header-title"><h5 class="m-b-10">Bulk Edit Change of Course Application</h5></div>
<ul class="breadcrumb">
<li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
<li class="breadcrumb-item"><a href="{{ route('change-of-course.admin') }}">Change of Course</a></li>
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
<div class="col-md-6"><div class="form-group"><label class="form-label">Student Name</label>
<input type="text" name="student_name" class="form-control" value="{{ $application->student_name }}" required></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Username</label>
<input type="text" class="form-control" value="{{ $application->username }}" readonly></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Application Status</label>
<select name="status" class="form-control" required>
@foreach(['Payment Pending','Awaiting New HOD','Awaiting New Dean','Awaiting Provost','Awaiting Current HOD','Awaiting Current Dean','Awaiting Registrar','Awaiting VC','Approved','Rejected'] as $s)
<option value="{{ $s }}" {{ $application->status == $s ? 'selected' : '' }}>{{ $s }}</option>
@endforeach
</select></div></div>
<div class="col-md-6"><div class="form-group"><label class="form-label">Payment Status</label>
<select name="payment_status" class="form-control" required>
<option value="Paid" {{ $application->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
<option value="Pending" {{ $application->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
</select></div></div>
</div>

<!-- Current Program (Read-Only) -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-info"><i class="fas fa-university me-2"></i>Current Program <span class="badge bg-secondary">Read Only</span></h6><hr></div>
<input type="hidden" name="current_faculty" value="{{ $application->current_faculty }}">
<input type="hidden" name="current_department" value="{{ $application->current_department }}">
<input type="hidden" name="current_program" value="{{ $application->current_program }}">
<div class="col-md-4"><div class="form-group"><label class="form-label">Current Faculty</label>
<input type="text" class="form-control" value="{{ $currentFacultyTitle }}" readonly></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Current Department</label>
<input type="text" class="form-control" value="{{ $currentDepartmentTitle }}" readonly></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Current Program</label>
<input type="text" class="form-control" value="{{ $currentProgramTitle }}" readonly></div></div>
</div>

<!-- New Program (Cascading) -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-success"><i class="fas fa-exchange-alt me-2"></i>New Program</h6><hr></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">New Faculty</label>
<select name="new_faculty" id="newFaculty" class="form-control" required>
<option value="">Select Faculty</option>
@foreach($faculties as $faculty)
<option value="{{ $faculty->code }}" {{ $application->new_faculty == $faculty->code ? 'selected' : '' }}>{{ $faculty->title }}</option>
@endforeach
</select></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">New Department</label>
<select name="new_department" id="newDepartment" class="form-control" required>
<option value="{{ $application->new_department }}">{{ $newDepartmentTitle }}</option>
</select></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">New Program</label>
<select name="new_program" id="newProgram" class="form-control" required>
<option value="{{ $application->new_program }}">{{ $newProgramTitle }}</option>
</select></div></div>
</div>

<!-- Additional Info -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-secondary"><i class="fas fa-info-circle me-2"></i>Additional Information</h6><hr></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Admission Type</label>
<input type="text" name="admission_type" class="form-control" value="{{ $application->admission_type ?? 'UTME' }}"></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">JAMB Score</label>
<input type="number" name="jamb_score" class="form-control" value="{{ $application->jamb_score }}" min="0" max="400"></div></div>
<div class="col-md-4"><div class="form-group"><label class="form-label">Session</label>
<input type="text" class="form-control" value="{{ $application->session }}" readonly></div></div>
<div class="col-12"><div class="form-group"><label class="form-label">Reason for Change</label>
<textarea name="reason_for_change" class="form-control" rows="3" required>{{ $application->reason_for_change }}</textarea></div></div>
</div>

<!-- Officer Actions -->
<div class="row mb-4">
<div class="col-12"><h6 class="text-danger"><i class="fas fa-user-shield me-2"></i>Officer Actions (Override)</h6><hr></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">New HOD</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Willing to Accept</label><select name="new_hod_willing" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->new_hod_willing ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->new_hod_willing ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Recommended Level</label><input type="text" name="new_hod_recommended_level" class="form-control form-control-sm" value="{{ $application->new_hod_recommended_level ?? '' }}"></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="new_hod_remarks" class="form-control form-control-sm" rows="2">{{ $application->new_hod_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="new_hod_name" class="form-control form-control-sm" value="{{ $application->new_hod_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="new_hod_date" class="form-control form-control-sm" value="{{ $application->new_hod_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">New Dean</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="new_dean_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->new_dean_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->new_dean_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="new_dean_remarks" class="form-control form-control-sm" rows="2">{{ $application->new_dean_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="new_dean_name" class="form-control form-control-sm" value="{{ $application->new_dean_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="new_dean_date" class="form-control form-control-sm" value="{{ $application->new_dean_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Current HOD</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Willing to Release</label><select name="current_hod_willing" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->current_hod_willing ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->current_hod_willing ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Reason</label><textarea name="current_hod_reason" class="form-control form-control-sm" rows="2">{{ $application->current_hod_reason ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="current_hod_name" class="form-control form-control-sm" value="{{ $application->current_hod_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="current_hod_date" class="form-control form-control-sm" value="{{ $application->current_hod_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Current Dean</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="current_dean_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->current_dean_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->current_dean_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="current_dean_remarks" class="form-control form-control-sm" rows="2">{{ $application->current_dean_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="current_dean_name" class="form-control form-control-sm" value="{{ $application->current_dean_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="current_dean_date" class="form-control form-control-sm" value="{{ $application->current_dean_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Provost</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Recommendation</label><select name="provost_recommendation" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Yes" {{ ($application->provost_recommendation ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ ($application->provost_recommendation ?? '') == 'No' ? 'selected' : '' }}>No</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="provost_remarks" class="form-control form-control-sm" rows="2">{{ $application->provost_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="provost_name" class="form-control form-control-sm" value="{{ $application->provost_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="provost_date" class="form-control form-control-sm" value="{{ $application->provost_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">Registrar</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Decision</label><select name="registrar_decision" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Approved" {{ ($application->registrar_decision ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option><option value="Rejected" {{ ($application->registrar_decision ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="registrar_remarks" class="form-control form-control-sm" rows="2">{{ $application->registrar_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="registrar_name" class="form-control form-control-sm" value="{{ $application->registrar_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="registrar_date" class="form-control form-control-sm" value="{{ $application->registrar_date ?? '' }}"></div>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-header"><h6 class="mb-0">VC</h6></div><div class="card-body">
<div class="form-group mb-2"><label>Decision</label><select name="vc_decision" class="form-control form-control-sm"><option value="">Keep Current</option><option value="Approved" {{ ($application->vc_decision ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option><option value="Rejected" {{ ($application->vc_decision ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option></select></div>
<div class="form-group mb-2"><label>Remarks</label><textarea name="vc_remarks" class="form-control form-control-sm" rows="2">{{ $application->vc_remarks ?? '' }}</textarea></div>
<div class="form-group mb-2"><label>Name</label><input type="text" name="vc_name" class="form-control form-control-sm" value="{{ $application->vc_name ?? '' }}"></div>
<div class="form-group"><label>Date</label><input type="date" name="vc_date" class="form-control form-control-sm" value="{{ $application->vc_date ?? '' }}"></div>
</div></div></div>
</div>

<!-- Submit -->
<div class="row"><div class="col-12">
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i><strong>Warning:</strong> Modifying critical application data. This action will be logged.</div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Application</button>
<button type="button" class="btn btn-warning" onclick="resubmitApplication()">
    <i class="fas fa-redo me-2"></i>Resubmit Application (Reset All Approvals)
</button>
<a href="{{ route('change-of-course.show', $application->id) }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
</div>
</div></div>
</form>
</div></div></div></div>
</div></div>

<script>
function resubmitApplication() {
    console.log('RESUBMIT JS: Function called');
    
    if (confirm('Are you sure you want to resubmit this application?\n\nThis will:\n• Reset ALL approval levels (HOD, Dean, Provost, Registrar, VC)\n• Set status back to "Awaiting New HOD"\n• Allow the student to modify their application\n• Log this action for audit purposes\n\nThis action cannot be undone!')) {
        
        console.log('RESUBMIT JS: User confirmed');
        
        // Show loading state
        const btn = event.target;
        const originalText = btn.innerHTML;
        console.log('RESUBMIT JS: Setting loading state');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        btn.disabled = true;
        
        const url = '{{ route("change-of-course.resubmit", $application->id) }}';
        console.log('RESUBMIT JS: URL =', url);
        
        // Send resubmit request
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            console.log('RESUBMIT JS: Response received', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('RESUBMIT JS: Data received', data);
            if (data.success) {
                alert('Application has been successfully reset!\n\nThe student can now submit a fresh application.');
                window.location.href = '{{ route("change-of-course.admin") }}';
            } else {
                console.error('RESUBMIT JS: Server returned error', data);
                alert('Error: ' + (data.message || data.error || 'Failed to resubmit application'));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('RESUBMIT JS: Network error', error);
            alert('Network error: ' + error.message + '\n\nPlease check the console for more details and try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    } else {
        console.log('RESUBMIT JS: User cancelled');
    }
}

$(document).ready(function(){
$('#newFaculty').change(function(){
var fc=$(this).val(),ds=$('#newDepartment'),ps=$('#newProgram');
if(fc){
$.ajax({url:'{{ route("change-of-course.get-departments") }}',type:'POST',data:{_token:'{{ csrf_token() }}',faculty:fc},success:function(r){
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
$.ajax({url:'{{ route("change-of-course.get-programs") }}',type:'POST',data:{_token:'{{ csrf_token() }}',department:dc},success:function(r){
ps.html('<option value="">Select Program</option>');
r.forEach(function(p){ps.append('<option value="'+p.code+'">'+p.title+'</option>');});
ps.prop('disabled',false);
}});
}else{ps.html('<option value="">Select Department First</option>').prop('disabled',true);}
});
$('#bulkEditForm').on('submit',function(e){
e.preventDefault();
if(!confirm('Are you sure you want to update this application?'))return false;
$.ajax({url:'{{ route("change-of-course.bulk-update", $application->id) }}',method:'POST',data:$(this).serialize(),
success:function(r){if(r.success){alert('Application updated successfully!');window.location.href='{{ route("change-of-course.show", $application->id) }}';}else{alert('Error: '+(r.error||'Unknown error'));}},
error:function(x){var m='An error occurred.';if(x.responseJSON&&x.responseJSON.error)m=x.responseJSON.error;alert(m);}
});
});
});
</script>