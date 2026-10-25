@php
    $ssceBySitting = $ssce->keyBy('sitting');
    $resultsBySitting = $ssceResults->groupBy('sitting');
    $documentTypes = [
        'passport_photo' => 'Passport photograph',
        'jamb_result' => 'JAMB result',
        'ssce_result' => 'SSCE result (1st sitting)',
        'ssce_result_2' => 'SSCE result (2nd sitting)',
        'birth_certificate' => 'Birth certificate',
        'nin_document' => 'NIN document',
        'jamb_admission' => 'JAMB admission letter',
        'indigine' => 'Indigene certificate',
        'primary_cert' => 'Primary school certificate',
        'direct_entry_cert' => 'Direct Entry certificate',
    ];
    $oldValue = fn ($key, $fallback = '') => old($key, $fallback ?? '');
@endphp

<style>
    .applicant-editor { max-width: 1500px; margin: 0 auto; }
    .applicant-editor-hero { border: 0; border-radius: 18px; background: linear-gradient(120deg,#0b5cab,#1577c5); color: #fff; box-shadow: 0 12px 28px rgba(20,83,140,.16); }
    .applicant-editor-hero h3 { font-weight: 800; letter-spacing: -.02em; }
    .applicant-editor-hero p { opacity: .86; }
    .applicant-editor .nav-tabs { gap: .35rem; border-bottom: 1px solid #dee5ec; overflow-x: auto; flex-wrap: nowrap; }
    .applicant-editor .nav-tabs .nav-link { border: 0; border-bottom: 3px solid transparent; color: #64748b; white-space: nowrap; font-weight: 700; padding: .75rem .85rem; }
    .applicant-editor .nav-tabs .nav-link.active { color: #0b5cab; border-bottom-color: #0b5cab; background: #f4f8fc; }
    .applicant-editor .editor-card { border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 6px 18px rgba(15,23,42,.04); }
    .applicant-editor .editor-card > .card-header { border-radius: 14px 14px 0 0; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 800; color: #26384a; }
    .applicant-editor .form-label { color: #475569; font-size: .78rem; font-weight: 700; }
    .applicant-editor .form-control, .applicant-editor .form-select { min-height: 42px; border-radius: 9px; }
    .applicant-editor .record-row { border: 1px solid #e4eaf0; border-radius: 12px; padding: .8rem; background: #fff; }
    .applicant-editor .record-row + .record-row { margin-top: .65rem; }
    .applicant-editor .section-note { color: #64748b; font-size: .8rem; }
    .applicant-editor .sticky-actions { position: sticky; bottom: 0; z-index: 5; padding: .85rem 1rem; border: 1px solid #dce5ee; border-radius: 14px; background: rgba(255,255,255,.96); box-shadow: 0 -8px 24px rgba(15,23,42,.08); }
    .applicant-editor .document-current { font-size: .75rem; color: #64748b; word-break: break-word; }
</style>

<div class="main-body applicant-editor">
    <div class="page-wrapper">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <a href="{{ route('admin.view-applicant', $applicant->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to record</a>
            <a href="{{ url('applicants') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i>Applicants list</a>
        </div>

        <div class="card applicant-editor-hero mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="small text-uppercase opacity-75 fw-bold">Applicant record editor</div>
                        <h3 class="mb-1">{{ $applicant->fullname ?: trim($applicant->surname . ' ' . $applicant->first_name) }}</h3>
                        <p class="mb-0">Update personal information, admission details, and the complete academic record in one secure form.</p>
                    </div>
                    <div class="text-md-end">
                        <div class="small opacity-75">Application number</div>
                        <strong class="fs-5">{{ $applicant->username }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Please correct the highlighted fields.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.update-applicant', $applicant->id) }}" method="POST" enctype="multipart/form-data" id="applicantEditorForm">
            @csrf
            <ul class="nav nav-tabs mb-4" id="applicantEditorTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#identity-tab" type="button"><i class="fas fa-user me-1"></i>Identity & contact</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#academic-tab" type="button"><i class="fas fa-graduation-cap me-1"></i>Academic & admission</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#exam-tab" type="button"><i class="fas fa-clipboard-list me-1"></i>JAMB / Direct Entry</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ssce-tab" type="button"><i class="fas fa-scroll me-1"></i>SSCE results</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#relations-tab" type="button"><i class="fas fa-users me-1"></i>Kin & sponsor</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents-tab" type="button"><i class="fas fa-file-upload me-1"></i>Documents</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="identity-tab">
                    <div class="card editor-card mb-3"><div class="card-header"><i class="fas fa-id-card me-2 text-primary"></i>Personal information</div><div class="card-body"><div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Application / ID number *</label><input name="username" class="form-control" required value="{{ $oldValue('username', $applicant->username) }}"></div>
                        <div class="col-md-4"><label class="form-label">Surname *</label><input name="surname" class="form-control" required value="{{ $oldValue('surname', $applicant->surname) }}"></div>
                        <div class="col-md-4"><label class="form-label">First name *</label><input name="first_name" class="form-control" required value="{{ $oldValue('first_name', $applicant->first_name) }}"></div>
                        <div class="col-md-4"><label class="form-label">Other name</label><input name="other_name" class="form-control" value="{{ $oldValue('other_name', $applicant->other_name) }}"></div>
                        <div class="col-md-4"><label class="form-label">Gender *</label><select name="gender" class="form-select" required><option value="">Select gender</option>@foreach(['Male','Female'] as $value)<option value="{{ $value }}" {{ strtolower((string) $oldValue('gender', $applicant->gender)) === strtolower($value) ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Date of birth</label><input type="date" name="dob" class="form-control" value="{{ $oldValue('dob', optional($applicant->dob)->format('Y-m-d')) }}"></div>
                        <div class="col-md-4"><label class="form-label">Nationality</label><input name="nationality" class="form-control" value="{{ $oldValue('nationality', $applicant->nationality) }}"></div>
                        <div class="col-md-4"><label class="form-label">State</label><input name="state" class="form-control" value="{{ $oldValue('state', $applicant->state) }}"></div>
                        <div class="col-md-4"><label class="form-label">LGA</label><input name="lga" class="form-control" value="{{ $oldValue('lga', $applicant->lga) }}"></div>
                        <div class="col-md-4"><label class="form-label">City</label><input name="city" class="form-control" value="{{ $oldValue('city', $applicant->city) }}"></div>
                        <div class="col-md-4"><label class="form-label">Place of birth</label><input name="pob" class="form-control" value="{{ $oldValue('pob', $applicant->pob) }}"></div>
                        <div class="col-md-4"><label class="form-label">Marital status</label><input name="marital_status" class="form-control" value="{{ $oldValue('marital_status', $applicant->marital_status) }}"></div>
                        <div class="col-md-4"><label class="form-label">Religion</label><input name="religion" class="form-control" value="{{ $oldValue('religion', $applicant->religion) }}"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ $oldValue('phone', $applicant->phone) }}"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $oldValue('email', $applicant->email) }}"></div>
                        <div class="col-12"><label class="form-label">Residential address</label><textarea name="address" rows="2" class="form-control">{{ $oldValue('address', $applicant->address) }}</textarea></div>
                    </div></div></div>
                </div>

                <div class="tab-pane fade" id="academic-tab">
                    <div class="card editor-card mb-3"><div class="card-header"><i class="fas fa-university me-2 text-success"></i>Programme and admission information</div><div class="card-body"><div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Faculty / college</label><select name="faculty" id="editorFaculty" class="form-select"><option value="">Select faculty</option>@foreach($faculty as $item)<option value="{{ $item->code }}" {{ $oldValue('faculty', $applicant->faculty) == $item->code ? 'selected' : '' }}>{{ $item->title }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Department</label><select name="department" id="editorDepartment" class="form-select"><option value="">Select department</option>@foreach($departments as $item)<option value="{{ $item->code }}" data-faculty="{{ $item->faculty }}" {{ $oldValue('department', $applicant->department) == $item->code ? 'selected' : '' }}>{{ $item->title }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Programme</label><select name="program" id="editorProgram" class="form-select"><option value="">Select programme</option>@foreach($programs as $item)<option value="{{ $item->code }}" data-faculty="{{ $item->faculty }}" data-department="{{ $item->department }}" {{ $oldValue('program', $applicant->program) == $item->code ? 'selected' : '' }}>{{ $item->title }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Mode of entry *</label><select name="mode" id="editorMode" class="form-select" required><option value="UTME" {{ $oldValue('mode', $applicant->mode) === 'UTME' ? 'selected' : '' }}>UTME</option><option value="DE" {{ $oldValue('mode', $applicant->mode) === 'DE' ? 'selected' : '' }}>Direct Entry (DE)</option></select></div>
                        <div class="col-md-4"><label class="form-label">Application session</label><select name="session" class="form-select"><option value="">Select session</option>@foreach($sessions as $item)<option value="{{ $item->title }}" {{ $oldValue('session', $applicant->session) == $item->title ? 'selected' : '' }}>{{ $item->title }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Aggregate / screening score</label><input type="number" step="0.01" name="score" class="form-control" value="{{ $oldValue('score', $applicant->score) }}"></div>
                    </div></div></div>
                    <div class="card editor-card"><div class="card-header"><i class="fas fa-tasks me-2 text-warning"></i>Application workflow</div><div class="card-body"><div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Application status</label><select name="status" class="form-select">@foreach(['Pending','Submitted','Admitted','Rejected'] as $value)<option value="{{ $value }}" {{ $oldValue('status', $applicant->status) == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Clearance</label><select name="clearance" class="form-select"><option value="">Not set</option>@foreach(['Pending','Cleared','Rejected'] as $value)<option value="{{ $value }}" {{ $oldValue('clearance', $applicant->clearance) == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Action / decision</label><input name="action" class="form-control" value="{{ $oldValue('action', $applicant->action) }}"></div>
                        <div class="col-md-3"><label class="form-label">Admission date</label><input type="date" name="admission_date" class="form-control" value="{{ $oldValue('admission_date', optional($applicant->admission_date)->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label class="form-label">Rejection date</label><input type="date" name="rejection_date" class="form-control" value="{{ $oldValue('rejection_date', optional($applicant->rejection_date)->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label class="form-label d-block">Admission flag</label><label class="btn btn-outline-success"><input type="checkbox" name="admitted" value="1" class="me-1" {{ old('admitted', $applicant->admitted) ? 'checked' : '' }}> Mark as admitted</label></div>
                        <div class="col-md-6"><label class="form-label">Admission remarks</label><input name="admission_remarks" class="form-control" value="{{ $oldValue('admission_remarks', $applicant->admission_remarks) }}"></div>
                        <div class="col-md-6"><label class="form-label">Rejection reason</label><input name="rejection_reason" class="form-control" value="{{ $oldValue('rejection_reason', $applicant->rejection_reason) }}"></div>
                        <div class="col-md-6"><label class="form-label">Rejection remarks</label><textarea name="rejection_remarks" rows="2" class="form-control">{{ $oldValue('rejection_remarks', $applicant->rejection_remarks) }}</textarea></div>
                    </div></div></div>
                </div>

                <div class="tab-pane fade" id="exam-tab">
                    <div class="card editor-card mb-3"><div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-list-ol me-2 text-info"></i>JAMB subject scores</span><button type="button" class="btn btn-sm btn-outline-primary" id="addJamb"><i class="fas fa-plus me-1"></i>Add subject</button></div><div class="card-body"><p class="section-note">Update each subject and score. Remove only records that are genuinely incorrect.</p><div id="jambRows">
                        @foreach($jamb as $row)<div class="record-row jamb-row"><input type="hidden" name="jamb[{{ $row->id }}][id]" value="{{ $row->id }}"><div class="row g-2 align-items-end"><div class="col-md-7"><label class="form-label">Subject</label><input name="jamb[{{ $row->id }}][subject]" class="form-control" value="{{ $row->subject }}"></div><div class="col-md-3"><label class="form-label">Score</label><input type="number" min="0" max="400" name="jamb[{{ $row->id }}][score]" class="form-control" value="{{ $row->score }}"></div><div class="col-md-2"><label class="form-label d-block">Remove</label><label class="btn btn-outline-danger btn-sm"><input type="checkbox" name="jamb[{{ $row->id }}][delete]" value="1" class="me-1"> Delete</label></div></div></div>@endforeach
                    </div><div id="jambEmpty" class="text-muted small {{ $jamb->isNotEmpty() ? 'd-none' : '' }}">No JAMB subjects have been recorded yet.</div></div></div>
                    <div class="card editor-card"><div class="card-header"><i class="fas fa-certificate me-2 text-info"></i>Direct Entry qualification</div><div class="card-body"><p class="section-note">Complete this section for DE applicants. It remains available for corrections even when the mode is changed.</p><div class="row g-3"><div class="col-md-4"><label class="form-label">Qualification</label><input name="de_qualification" class="form-control" value="{{ $oldValue('de_qualification', $deQualification?->qualification_type) }}"></div><div class="col-md-4"><label class="form-label">Institution</label><input name="de_institution" class="form-control" value="{{ $oldValue('de_institution', $deQualification?->institution) }}"></div><div class="col-md-4"><label class="form-label">Graduation year</label><input name="de_grad_year" class="form-control" value="{{ $oldValue('de_grad_year', $deQualification?->grad_year) }}"></div><div class="col-md-4"><label class="form-label">Grade / class</label><input name="de_grade" class="form-control" value="{{ $oldValue('de_grade', $deQualification?->grade) }}"></div><div class="col-md-8"><label class="form-label">Registration number</label><input name="de_reg_number" class="form-control" value="{{ $oldValue('de_reg_number', $deQualification?->reg_number) }}"></div></div></div></div>
                </div>

                <div class="tab-pane fade" id="ssce-tab">
                    <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>Maintain each sitting separately. Clear a sitting only when the record should be removed from the applicant’s academic history.</div>
                    @foreach([1,2] as $sitting)
                        @php $exam = $ssceBySitting->get($sitting); $resultRows = ($resultsBySitting->get($sitting) ?? collect())->values(); $rowCount = max(5, $resultRows->count()); @endphp
                        <div class="card editor-card mb-3"><div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-scroll me-2 text-warning"></i>SSCE sitting {{ $sitting }}</span><label class="btn btn-sm btn-outline-danger mb-0"><input type="checkbox" name="ssce[{{ $sitting }}][remove]" value="1" class="me-1"> Remove sitting</label></div><div class="card-body"><input type="hidden" name="ssce[{{ $sitting }}][id]" value="{{ $exam?->id }}"><div class="row g-3 mb-3"><div class="col-md-3"><label class="form-label">Exam type</label><input name="ssce[{{ $sitting }}][type]" class="form-control" value="{{ $oldValue('ssce.' . $sitting . '.type', $exam?->type) }}"></div><div class="col-md-3"><label class="form-label">Exam year</label><input name="ssce[{{ $sitting }}][year]" class="form-control" value="{{ $oldValue('ssce.' . $sitting . '.year', $exam?->year) }}"></div><div class="col-md-3"><label class="form-label">Registration number</label><input name="ssce[{{ $sitting }}][number]" class="form-control" value="{{ $oldValue('ssce.' . $sitting . '.number', $exam?->number) }}"></div><div class="col-md-3"><label class="form-label">Centre</label><input name="ssce[{{ $sitting }}][center_name]" class="form-control" value="{{ $oldValue('ssce.' . $sitting . '.center_name', $exam?->center_name) }}"></div></div><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th style="width:55%">Subject</th><th style="width:25%">Grade</th><th style="width:20%">Remark</th></tr></thead><tbody id="ssceResults{{ $sitting }}">@for($index=0;$index<$rowCount;$index++)@php $result=$resultRows->get($index); @endphp<tr><td><input name="ssce_results[{{ $sitting }}][{{ $index }}][subject]" class="form-control form-control-sm" value="{{ old('ssce_results.' . $sitting . '.' . $index . '.subject', $result?->subject) }}"></td><td><input name="ssce_results[{{ $sitting }}][{{ $index }}][grade]" class="form-control form-control-sm" value="{{ old('ssce_results.' . $sitting . '.' . $index . '.grade', $result?->grade) }}"></td><td><input name="ssce_results[{{ $sitting }}][{{ $index }}][remark]" class="form-control form-control-sm" value="{{ old('ssce_results.' . $sitting . '.' . $index . '.remark', $result?->remark) }}"></td></tr>@endfor</tbody></table></div><button type="button" class="btn btn-sm btn-outline-secondary mt-2 addSsceRow" data-sitting="{{ $sitting }}"><i class="fas fa-plus me-1"></i>Add result row</button></div></div>
                    @endforeach
                </div>

                <div class="tab-pane fade" id="relations-tab">
                    <div class="row g-3"><div class="col-lg-6"><div class="card editor-card"><div class="card-header"><i class="fas fa-user-friends me-2 text-secondary"></i>Next of kin</div><div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label">Name</label><input name="n_name" class="form-control" value="{{ $oldValue('n_name', $applicant->n_name) }}"></div><div class="col-md-6"><label class="form-label">Relationship</label><input name="n_relationship" class="form-control" value="{{ $oldValue('n_relationship', $applicant->n_relationship) }}"></div><div class="col-md-6"><label class="form-label">Phone</label><input name="n_phone" class="form-control" value="{{ $oldValue('n_phone', $applicant->n_phone) }}"></div><div class="col-md-6"><label class="form-label">Email</label><input type="email" name="n_email" class="form-control" value="{{ $oldValue('n_email', $applicant->n_email) }}"></div><div class="col-12"><label class="form-label">Address</label><textarea name="n_address" rows="3" class="form-control">{{ $oldValue('n_address', $applicant->n_address) }}</textarea></div></div></div></div></div><div class="col-lg-6"><div class="card editor-card"><div class="card-header"><i class="fas fa-hand-holding-usd me-2 text-dark"></i>Sponsor</div><div class="card-body"><div class="row g-3"><div class="col-12"><label class="form-label">Name</label><input name="s_name" class="form-control" value="{{ $oldValue('s_name', $applicant->s_name) }}"></div><div class="col-12"><label class="form-label">Phone</label><input name="s_phone" class="form-control" value="{{ $oldValue('s_phone', $applicant->s_phone) }}"></div><div class="col-12"><label class="form-label">Address</label><textarea name="s_address" rows="5" class="form-control">{{ $oldValue('s_address', $applicant->s_address) }}</textarea></div></div></div></div></div></div>
                </div>

                <div class="tab-pane fade" id="documents-tab"><div class="card editor-card"><div class="card-header"><i class="fas fa-file-upload me-2 text-purple"></i>Applicant documents</div><div class="card-body"><p class="section-note">Upload a replacement only when the existing document needs correction. Existing files remain unchanged when no replacement is selected.</p><div class="row g-3">@foreach($documentTypes as $type => $label)@php $existingDoc=$documents->firstWhere('doc_type',$type); @endphp<div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="file" name="document_files[{{ $type }}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">@if($existingDoc)<div class="document-current mt-1"><i class="fas fa-paperclip me-1"></i>{{ $existingDoc->original_name ?: 'Uploaded' }}</div>@else<span class="text-muted small">No document uploaded</span>@endif</div>@endforeach</div></div></div></div>
            </div>

            <div class="sticky-actions mt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-muted"><i class="fas fa-shield-alt me-1"></i>All related academic records are saved together in one transaction.</span>
                <div class="d-flex gap-2"><a href="{{ route('admin.view-applicant', $applicant->id) }}" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save complete record</button></div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const faculty = document.getElementById('editorFaculty');
    const department = document.getElementById('editorDepartment');
    const program = document.getElementById('editorProgram');
    const filterAcademic = () => {
        const facultyValue = faculty?.value || '';
        const departmentValue = department?.value || '';
        Array.from(department?.options || []).forEach((option, index) => {
            option.hidden = index > 0 && facultyValue && option.dataset.faculty !== facultyValue;
        });
        Array.from(program?.options || []).forEach((option, index) => {
            option.hidden = index > 0 && ((facultyValue && option.dataset.faculty !== facultyValue) || (departmentValue && option.dataset.department !== departmentValue));
        });
        if (department?.selectedOptions[0]?.hidden) department.value = '';
        if (program?.selectedOptions[0]?.hidden) program.value = '';
    };
    faculty?.addEventListener('change', () => { department.value = ''; program.value = ''; filterAcademic(); });
    department?.addEventListener('change', () => { program.value = ''; filterAcademic(); });
    filterAcademic();

    let jambIndex = 0;
    document.getElementById('addJamb')?.addEventListener('click', function () {
        const key = 'new_' + (jambIndex++);
        document.getElementById('jambEmpty')?.classList.add('d-none');
        document.getElementById('jambRows').insertAdjacentHTML('beforeend', `<div class="record-row jamb-row"><div class="row g-2 align-items-end"><div class="col-md-7"><label class="form-label">Subject</label><input name="jamb[${key}][subject]" class="form-control"></div><div class="col-md-3"><label class="form-label">Score</label><input type="number" min="0" max="400" name="jamb[${key}][score]" class="form-control"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-record"><i class="fas fa-trash me-1"></i>Remove</button></div></div></div>`);
    });
    document.addEventListener('click', function (event) {
        const remove = event.target.closest('.remove-record');
        if (remove) remove.closest('.record-row')?.remove();
        const addSsce = event.target.closest('.addSsceRow');
        if (addSsce) {
            const sitting = addSsce.dataset.sitting;
            const body = document.getElementById('ssceResults' + sitting);
            const index = body.querySelectorAll('tr').length;
            body.insertAdjacentHTML('beforeend', `<tr><td><input name="ssce_results[${sitting}][${index}][subject]" class="form-control form-control-sm"></td><td><input name="ssce_results[${sitting}][${index}][grade]" class="form-control form-control-sm"></td><td><input name="ssce_results[${sitting}][${index}][remark]" class="form-control form-control-sm"></td></tr>`);
        }
    });
});
</script>
