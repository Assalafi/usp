@php
    $filter = $selectedFilters ?? request()->only(['faculty', 'department', 'program', 'level', 'semester', 'form', 'structure_id', 'type', 'grading', 'q']);
    $filter = is_array($filter) ? $filter : [];
    $recordCount = is_countable($data ?? []) ? count($data) : 0;
    $levelCount = collect($data ?? [])->pluck('level')->filter()->unique()->count();
    $electiveCount = collect($data ?? [])->where('type', 'ELECTIVE')->count();
    $structureJson = collect($courseStructure ?? [])->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'from' => $item->from_session,
            'to' => $item->to_session,
        ];
    })->values();
    $prerequisiteMap = collect($data ?? [])->mapWithKeys(function ($row) {
        return [(string) $row->id => (string) ($row->prerequisites ?? '')];
    });
    $isHod = (bool) ($isHod ?? false);
    $canManage = (bool) ($canManage ?? true);
    $hodDepartment = $hodDepartment ?? null;
    $hodPrograms = collect($hodPrograms ?? []);
@endphp

<link rel="stylesheet" href="{{ asset('css/program-course-registration.css') }}?v=20261013-2">
<style>
    .pcr-page{background:#f4f7fb;padding-bottom:28px}.pcr-page .pcr-hero{background:#fff;color:#0f172a;border:1px solid #dbe5f0;border-left:5px solid #2563eb;box-shadow:0 10px 26px rgba(15,23,42,.06)}.pcr-page .pcr-hero .card-body{padding:26px 30px}.pcr-kicker{color:#2563eb;opacity:1}.pcr-hero h1{color:#0f172a}.pcr-hero p{color:#64748b}.pcr-hero-actions .btn-light{color:#1d4ed8;border:1px solid #bfdbfe}.pcr-stat-row{margin-top:20px}.pcr-stat{border-color:#dbe5f0;background:#f8fbff;color:#0f172a}.pcr-stat strong{color:#0f3b82}.pcr-stat span{color:#64748b}.pcr-panel{box-shadow:0 8px 22px rgba(15,23,42,.045)}.pcr-modal .modal-dialog{max-height:calc(100vh - 2rem);margin:1rem auto}.pcr-modal .modal-content{max-height:calc(100vh - 2rem);display:flex}.pcr-modal form{min-height:0;display:flex;flex-direction:column}.pcr-modal .modal-body{min-height:0;max-height:calc(100vh - 150px);overflow-y:auto;overscroll-behavior:contain}.pcr-modal .modal-footer{flex:0 0 auto}.pcr-pdf-btn{white-space:nowrap}.pcr-filter-actions .pcr-pdf-btn{border-color:#bfdbfe;color:#1d4ed8;background:#eff6ff}.pcr-filter-actions .pcr-pdf-btn:hover{color:#fff;background:#2563eb;border-color:#2563eb}.pcr-view-only{display:inline-flex;align-items:center;padding:.55rem .9rem;border-radius:.5rem;background:#eef2ff;color:#4338ca;font-weight:700}.pcr-readonly .pcr-actions{display:none!important}@media(max-width:767px){.pcr-page .pcr-hero .card-body{padding:22px 18px}.pcr-modal .modal-dialog{max-height:calc(100vh - 1rem);margin:.5rem auto}.pcr-modal .modal-content{max-height:calc(100vh - 1rem)}.pcr-modal .modal-body{max-height:calc(100vh - 132px);padding:16px}.pcr-filter-actions .pcr-pdf-btn{flex-basis:100%}}
</style>

<div class="main-body pcr-page {{ $isHod && !$canManage ? 'pcr-readonly' : '' }}"><div class="page-wrapper">
    <div class="pcr-hero card mb-4"><div class="card-body">
        <div class="pcr-kicker"><i class="fas fa-graduation-cap"></i> {{ $isHod ? 'Department academic records' : 'Academic curriculum workspace' }}</div>
        <h1>{{ $isHod ? 'Programme Courses' : 'Programme course registration' }}</h1>
        <p>{{ $isHod ? 'Review your department’s approved courses by programme, level and semester. When editing is enabled, changes are saved directly to the academic register.' : 'Build and maintain each programme’s course structure in one place. Select the curriculum version first, then review the level, semester, units and course type before saving.' }}</p>
        <div class="pcr-hero-actions">@if($canManage)<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal"><i class="fas fa-plus me-1"></i> Add courses</button>@else<span class="pcr-view-only"><i class="fas fa-eye me-1"></i> View only</span>@endif<a class="btn btn-outline-primary" href="{{ url($page) }}"><i class="fas fa-rotate-left me-1"></i> Clear filters</a></div>
        <div class="pcr-stat-row"><div class="pcr-stat"><strong>{{ number_format($recordCount) }}</strong><span>{{ $isHod ? 'Courses shown' : 'Mappings shown' }}</span></div><div class="pcr-stat"><strong>{{ number_format($levelCount) }}</strong><span>Levels covered</span></div><div class="pcr-stat"><strong>{{ number_format($electiveCount) }}</strong><span>{{ $isHod ? 'Elective courses' : 'Elective mappings' }}</span></div><div class="pcr-stat"><strong>{{ count($courseStructure ?? []) }}</strong><span>{{ $isHod ? 'Curriculum versions' : 'Structures available' }}</span></div></div>
    </div></div>

    @if(session('success'))<div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>@endif
    @if(session('error'))<div class="alert alert-danger d-flex align-items-center gap-2 border-0 shadow-sm"><i class="fas fa-triangle-exclamation"></i><span>{{ session('error') }}</span></div>@endif
    @if($errors->any())<div class="alert alert-danger border-0 shadow-sm"><strong>Please review the form:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="pcr-panel"><div class="pcr-panel-header"><div><h2><i class="fas fa-sliders me-2 text-primary"></i>{{ $isHod ? 'Find programme courses' : 'Find a curriculum' }}</h2><p>{{ $isHod ? 'Choose a programme, level or semester to review its approved courses.' : 'Start with the structure, then narrow the mapping by academic entity.' }}</p></div><span class="pcr-badge structure"><i class="fas fa-eye me-1"></i> {{ $isHod ? 'Department view' : 'Academic register' }}</span></div>
        <form class="pcr-filter" id="pcrFilterForm" method="GET" action="{{ url($page) }}"><div class="pcr-filter-grid">
            <div class="pcr-field"><label for="filterStructure"><i class="fas fa-layer-group"></i> Curriculum structure</label><select class="form-select" id="filterStructure" name="structure_id"><option value="">All structures</option>@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}" {{ (string)($filter['structure_id'] ?? '') === (string)$structure->id ? 'selected' : '' }}>{{ $structure->name }} · {{ $structure->from_session }}–{{ $structure->to_session }}</option>@endforeach</select><small id="structureHelp">Choose a structure to keep a programme curriculum consistent.</small></div>
            @if($isHod)
                <div class="pcr-field"><label>Department</label><div class="form-control bg-light"><strong>{{ $hodDepartment->title ?? session('department') }}</strong><small class="d-block text-muted">All programmes in your department</small></div><input type="hidden" name="department" value="{{ session('department') }}"></div>
                <div class="pcr-field"><label for="filterProgram">Programme</label><select class="form-select" id="filterProgram" name="program"><option value="">All programmes in the department</option>@foreach($hodPrograms as $item)<option value="{{ $item->code }}" {{ ($filter['program'] ?? '') === $item->code ? 'selected' : '' }}>{{ $item->title }} ({{ $item->code }})</option>@endforeach</select></div>
            @else
                <div class="pcr-field"><label for="filterFaculty">Faculty / college</label><select class="form-select" id="filterFaculty" name="faculty" data-selected="{{ $filter['faculty'] ?? '' }}"><option value="">All faculties</option>@foreach($faculty ?? [] as $item)<option value="{{ $item->code }}" {{ ($filter['faculty'] ?? '') === $item->code ? 'selected' : '' }}>{{ $item->title }} ({{ $item->code }})</option>@endforeach</select></div>
                <div class="pcr-field"><label for="filterDepartment">Department</label><select class="form-select" id="filterDepartment" name="department" data-selected="{{ $filter['department'] ?? '' }}" disabled><option value="">Select faculty first</option></select></div>
                <div class="pcr-field"><label for="filterProgram">Programme</label><select class="form-select" id="filterProgram" name="program" data-selected="{{ $filter['program'] ?? '' }}" disabled><option value="">Select department first</option></select></div>
            @endif
            <div class="pcr-field"><label for="filterLevel">Level</label><select class="form-select" id="filterLevel" name="level"><option value="">All levels</option>@foreach([100,200,300,400,500,600,700] as $level)<option value="{{ $level }}" {{ (string)($filter['level'] ?? '') === (string)$level ? 'selected' : '' }}>{{ $level }} level</option>@endforeach</select></div>
            <div class="pcr-field"><label for="filterSemester">Semester</label><select class="form-select" id="filterSemester" name="semester"><option value="">All semesters</option>@foreach($semester ?? [] as $item)<option value="{{ $item->semester }}" {{ ($filter['semester'] ?? '') === $item->semester ? 'selected' : '' }}>{{ $item->semester }}</option>@endforeach</select></div>
            <div class="pcr-field"><label for="filterType">Course type</label><select class="form-select" id="filterType" name="type"><option value="">Core and elective</option><option value="CORE" {{ ($filter['type'] ?? '') === 'CORE' ? 'selected' : '' }}>Core</option><option value="ELECTIVE" {{ ($filter['type'] ?? '') === 'ELECTIVE' ? 'selected' : '' }}>Elective</option></select></div>
            <div class="pcr-field"><label for="filterForm">Student form</label><select class="form-select" id="filterForm" name="form"><option value="">All forms</option><option value="NEW" {{ ($filter['form'] ?? '') === 'NEW' ? 'selected' : '' }}>New student</option><option value="FORMER" {{ ($filter['form'] ?? '') === 'FORMER' ? 'selected' : '' }}>Former / returning</option></select></div>
        </div><div class="pcr-filter-footer"><div class="pcr-structure-note"><i class="fas fa-circle-info"></i><span id="structureDescription">Use the filters to open one curriculum mapping. Course units and titles remain linked to the master course catalogue.</span></div><div class="pcr-filter-actions"><a href="{{ url($page) }}" class="btn btn-light pcr-btn">Reset</a><a id="pcrPdfPreview" href="{{ route('program-course-registration.preview', $filter) }}" target="_blank" rel="noopener" class="btn pcr-btn pcr-pdf-btn"><i class="fas fa-file-pdf me-1"></i> Preview filtered PDF</a><button type="submit" class="btn btn-primary pcr-btn"><i class="fas fa-search me-1"></i> Apply filters</button></div></div><div class="pcr-field mt-3"><label for="filterSearch">Search within the curriculum</label><input class="form-control" id="filterSearch" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Course code, course title or programme name"></div></form>
    </div>

    <div class="pcr-panel pcr-table-panel"><div class="pcr-table-toolbar"><div><h2 class="m-0" style="font-size:17px;font-weight:800;color:#0f172a;">{{ $isHod ? 'Approved programme courses' : 'Curriculum mappings' }}</h2><div class="pcr-results-label mt-1">{{ number_format($recordCount) }} {{ $isHod ? 'course record(s) displayed' : 'record(s) currently displayed' }}</div></div><div class="input-group input-group-sm"><span class="input-group-text bg-white border-end-0"><i class="fas fa-magnifying-glass text-muted"></i></span><input type="search" id="quickSearch" class="form-control border-start-0" placeholder="Search displayed courses"></div></div>
        @if($recordCount)
            <div class="pcr-table-wrap"><table class="table pcr-table align-middle" id="pcrTable"><thead><tr><th>#</th><th>Programme</th><th>Course</th><th>Structure</th><th>Placement</th><th>Classification</th><th>Options</th><th class="text-end">Actions</th></tr></thead><tbody>
                @foreach($data as $index => $row)<tr data-search="{{ strtolower(($row->program_title ?? '') . ' ' . $row->program . ' ' . $row->code . ' ' . ($row->course_title ?? '') . ' ' . $row->level . ' ' . $row->semester) }}"><td class="text-muted">{{ $index + 1 }}</td><td><span class="pcr-program-name">{{ $row->program_title ?: 'Unknown programme' }}</span><span class="pcr-program-code">{{ $row->program }}</span></td><td><span class="pcr-course-code">{{ $row->code }}</span><span class="pcr-course-title">{{ $row->course_title ?: 'Course title not found' }}</span></td><td><span class="pcr-badge structure">{{ $row->structure_name ?: 'Structure #' . $row->structure_id }}</span><small class="d-block text-muted mt-1">{{ $row->structure_from_session }}–{{ $row->structure_to_session }}</small></td><td><strong>{{ $row->level }}</strong><span class="d-block text-muted small">{{ $row->semester }}</span><span class="d-block text-muted small">{{ $row->unit }} unit(s)</span></td><td><span class="pcr-badge {{ strtoupper($row->type) === 'ELECTIVE' ? 'elective' : 'core' }}">{{ $row->type ?: 'CORE' }}{{ strtoupper($row->type) === 'ELECTIVE' && $row->elective ? ' · Group ' . $row->elective : '' }}</span></td><td><span class="pcr-badge semester">{{ strtoupper($row->form ?: 'ALL') }}</span>@if($row->change_semester)<span class="d-block text-success small mt-1"><i class="fas fa-arrows-left-right me-1"></i>Semester change allowed</span>@endif</td><td class="text-end"><div class="pcr-actions"><button type="button" class="btn btn-outline-primary edit-pcr" title="Edit mapping" data-bs-toggle="modal" data-bs-target="#editCourseModal" data-id="{{ $row->id }}" data-program="{{ $row->program }}" data-program-title="{{ e($row->program_title ?: $row->program) }}" data-code="{{ $row->code }}" data-course-title="{{ e($row->course_title ?: '') }}" data-level="{{ $row->level }}" data-semester="{{ $row->semester }}" data-type="{{ $row->type }}" data-elective="{{ $row->elective }}" data-change-semester="{{ $row->change_semester }}" data-form="{{ $row->form }}" data-structure="{{ $row->structure_id }}" data-de="{{ $row->de }}" data-grading="{{ $row->grading }}"><i class="fas fa-pen"></i></button><form method="POST" action="{{ url('delete ' . $page) }}" onsubmit="return confirm('Remove this course from the curriculum mapping? Existing student records will not be deleted.');">@csrf<input type="hidden" name="id" value="{{ $row->id }}"><button type="submit" class="btn btn-outline-danger" title="Remove mapping"><i class="fas fa-trash"></i></button></form></div></td></tr>@endforeach
            </tbody></table></div>
            <div class="pcr-mobile-list" id="pcrMobileList">@foreach($data as $row)<article class="pcr-mobile-card" data-search="{{ strtolower(($row->program_title ?? '') . ' ' . $row->program . ' ' . $row->code . ' ' . ($row->course_title ?? '') . ' ' . $row->level . ' ' . $row->semester) }}"><div class="pcr-mobile-top"><div><span class="pcr-course-code">{{ $row->code }}</span><span class="pcr-course-title">{{ $row->course_title ?: 'Course title not found' }}</span></div><span class="pcr-badge {{ strtoupper($row->type) === 'ELECTIVE' ? 'elective' : 'core' }}">{{ $row->type ?: 'CORE' }}</span></div><div class="pcr-mobile-meta"><div><small>Programme</small><strong>{{ $row->program_title ?: $row->program }}</strong></div><div><small>Structure</small><strong>{{ $row->structure_name ?: '#' . $row->structure_id }}</strong></div><div><small>Level / semester</small><strong>{{ $row->level }} · {{ $row->semester }}</strong></div><div><small>Units / form</small><strong>{{ $row->unit }} · {{ strtoupper($row->form ?: 'ALL') }}</strong></div></div><div class="pcr-mobile-footer"><small class="text-muted">{{ $row->change_semester ? 'Semester change allowed' : 'Fixed semester' }}</small><div class="pcr-actions"><button type="button" class="btn btn-outline-primary edit-pcr" data-bs-toggle="modal" data-bs-target="#editCourseModal" data-id="{{ $row->id }}" data-program="{{ $row->program }}" data-program-title="{{ e($row->program_title ?: $row->program) }}" data-code="{{ $row->code }}" data-course-title="{{ e($row->course_title ?: '') }}" data-level="{{ $row->level }}" data-semester="{{ $row->semester }}" data-type="{{ $row->type }}" data-elective="{{ $row->elective }}" data-change-semester="{{ $row->change_semester }}" data-form="{{ $row->form }}" data-structure="{{ $row->structure_id }}" data-de="{{ $row->de }}" data-grading="{{ $row->grading }}"><i class="fas fa-pen"></i></button><form method="POST" action="{{ url('delete ' . $page) }}" onsubmit="return confirm('Remove this course from the curriculum mapping? Existing student records will not be deleted.');">@csrf<input type="hidden" name="id" value="{{ $row->id }}"><button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button></form></div></div></article>@endforeach</div>
        @else
            <div class="pcr-empty"><i class="fas fa-book-open"></i><h3>No programme courses found</h3><p>{{ $isHod ? 'There are no approved course records for the selected academic filters.' : 'Select a structure and academic entity, or add courses to create the first mapping for this programme.' }}</p>@if($canManage)<button type="button" class="btn btn-primary pcr-btn mt-3" data-bs-toggle="modal" data-bs-target="#createCourseModal"><i class="fas fa-plus me-1"></i> Add courses</button>@endif</div>
        @endif
    </div>
</div></div>

<div class="modal fade pcr-modal" id="createCourseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5><i class="fas fa-circle-plus me-2"></i>Add courses to a curriculum</h5><p>Choose the academic context first. Course details are copied from the master catalogue.</p></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('create ' . $page) }}" id="createPcrForm"><div class="modal-body">@csrf<div class="pcr-step"><span>1</span> Academic context</div><div class="pcr-form-section"><h6>Where should these courses belong?</h6><div class="row g-3"><div class="col-md-6"><div class="pcr-field"><label for="createStructure">Curriculum structure *</label><select class="form-select" id="createStructure" name="structure_id" required><option value="">Select structure</option>@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}">{{ $structure->name }} · {{ $structure->from_session }}–{{ $structure->to_session }}{{ $structure->status ? ' · Active' : '' }}</option>@endforeach</select></div></div><div class="col-md-6"><div class="pcr-field"><label for="createForm">Student form *</label><select class="form-select" id="createForm" name="form" required><option value="">Select form</option><option value="NEW">New student</option><option value="FORMER">Former / returning</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createFaculty">Faculty / college *</label><select class="form-select" id="createFaculty" name="faculty" required><option value="">Select faculty</option>@foreach($faculty ?? [] as $item)<option value="{{ $item->code }}">{{ $item->title }} ({{ $item->code }})</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createDepartment">Department *</label><select class="form-select" id="createDepartment" name="department" required disabled><option value="">Select faculty first</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createProgram">Programme *</label><select class="form-select" id="createProgram" name="program" required disabled><option value="">Select department first</option></select></div></div></div></div><div class="pcr-step"><span>2</span> Course selection</div><div class="pcr-form-section"><h6>Choose from the master course catalogue</h6><div class="row g-2 align-items-end"><div class="col-md-9"><div class="pcr-field"><label for="coursePickerSearch">Search course code or title</label><input class="form-control" id="coursePickerSearch" autocomplete="off" placeholder="Type ACC101 or accounting"></div></div><div class="col-md-3"><button type="button" class="btn btn-outline-primary pcr-btn w-100" id="addPickedCourse"><i class="fas fa-plus me-1"></i> Add course</button></div></div><div id="coursePickerResults" class="list-group mt-2" style="max-height:180px;overflow:auto;display:none"></div><div class="pcr-field mt-3"><label for="createCodes">Selected course codes *</label><textarea class="form-control" id="createCodes" name="code" rows="3" required placeholder="Selected codes will appear here"></textarea><small>Use the search above or enter codes separated by commas, spaces or new lines.</small></div><div class="pcr-code-preview mt-3" id="codePreview"><span class="text-muted small">Course previews will appear here.</span></div></div><div class="pcr-danger-note"><i class="fas fa-circle-info mt-1"></i><span>Existing mappings with the same programme, structure and course code will be refreshed from the master course catalogue. Other curriculum settings are preserved.</span></div></div><div class="modal-footer"><button type="button" class="btn btn-light pcr-btn" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary pcr-btn" id="createPcrSubmit"><i class="fas fa-save me-1"></i> Save curriculum courses</button></div></form></div></div></div>

<div class="modal fade pcr-modal" id="editCourseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5><i class="fas fa-pen-to-square me-2"></i>Edit curriculum mapping</h5><p id="editCourseHeading">Update the academic placement and registration rules.</p></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('update ' . $page) }}"><div class="modal-body">@csrf<input type="hidden" name="id" id="editId"><div class="pcr-form-section"><div class="row g-3"><div class="col-md-6"><div class="pcr-field"><label>Course</label><div class="form-control bg-light" id="editCourseDisplay">—</div></div></div><div class="col-md-6"><div class="pcr-field"><label>Programme</label><div class="form-control bg-light" id="editProgramDisplay">—</div></div></div></div></div><div class="row g-3"><div class="col-md-4"><div class="pcr-field"><label for="editLevel">Level *</label><select class="form-select" name="level" id="editLevel" required>@foreach([100,200,300,400,500,600,700] as $level)<option value="{{ $level }}">{{ $level }}</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editSemester">Semester *</label><select class="form-select" name="semester" id="editSemester" required>@foreach($semester ?? [] as $item)<option value="{{ $item->semester }}">{{ $item->semester }}</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editStructure">Curriculum structure *</label><select class="form-select" name="structure_id" id="editStructure" required>@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}">{{ $structure->name }} ({{ $structure->from_session }}–{{ $structure->to_session }})</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editType">Course type *</label><select class="form-select" name="type" id="editType" required><option value="CORE">Core</option><option value="ELECTIVE">Elective</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editElective">Elective group</label><select class="form-select" name="elective" id="editElective"><option value="0">Not an elective</option>@for($i=1;$i<=10;$i++)<option value="{{ $i }}">Group {{ $i }}</option>@endfor</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editForm">Student form *</label><select class="form-select" name="form" id="editForm" required><option value="NEW">New student</option><option value="FORMER">Former / returning</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editChangeSemester">Semester change</label><select class="form-select" name="change_semester" id="editChangeSemester" required><option value="0">Not allowed</option><option value="1">Allowed</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editDe">Direct entry</label><select class="form-select" name="de" id="editDe" required><option value="0">No</option><option value="1">Yes</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editGrading">Grading system *</label><select class="form-select" name="grading" id="editGrading" required>@foreach($grading ?? [] as $item)<option value="{{ $item->name }}">{{ $item->name }}</option>@endforeach</select></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light pcr-btn" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary pcr-btn"><i class="fas fa-save me-1"></i> Save changes</button></div></form></div></div></div>

<script>
(()=>{
    const catalog=@json(collect($courseCatalog ?? [])->values()); const catalogMap=Object.fromEntries(catalog.map(item=>[String(item.code||'').toUpperCase(),item])); const endpoint=(base,value)=>`${base}/${encodeURIComponent(value)}`;
    const populate=(select,items,placeholder,selected='')=>{select.innerHTML=`<option value="">${placeholder}</option>`;items.forEach(item=>{const o=document.createElement('option');o.value=item.code;o.textContent=`${item.title} (${item.code})`;if(String(item.code)===String(selected))o.selected=true;select.appendChild(o)});select.disabled=false};
    const loadPrograms=async(department,select,selected='')=>{select.disabled=true;select.innerHTML='<option value="">Loading programmes…</option>';if(!department){select.innerHTML='<option value="">Select department first</option>';return}try{const r=await fetch(endpoint('{{ url('/get-programs') }}',department),{headers:{Accept:'application/json'}});populate(select,await r.json(),'Select programme',selected)}catch(e){select.innerHTML='<option value="">Unable to load programmes</option>'}};
    const loadDepartments=async(faculty,dept,program,selectedDept='',selectedProgram='')=>{dept.disabled=true;program.disabled=true;dept.innerHTML='<option value="">Loading departments…</option>';program.innerHTML='<option value="">Select department first</option>';if(!faculty){dept.innerHTML='<option value="">Select faculty first</option>';return}try{const r=await fetch(endpoint('{{ url('/get-departments') }}',faculty),{headers:{Accept:'application/json'}});populate(dept,await r.json(),'Select department',selectedDept);if(dept.value)await loadPrograms(dept.value,program,selectedProgram)}catch(e){dept.innerHTML='<option value="">Unable to load departments</option>'}};
    const ff=document.getElementById('filterFaculty'),fd=document.getElementById('filterDepartment'),fp=document.getElementById('filterProgram');ff?.addEventListener('change',()=>loadDepartments(ff.value,fd,fp));fd?.addEventListener('change',()=>loadPrograms(fd.value,fp));if(ff?.value)loadDepartments(ff.value,fd,fp,fd.dataset.selected,fp.dataset.selected);
    const cf=document.getElementById('createFaculty'),cd=document.getElementById('createDepartment'),cp=document.getElementById('createProgram');cf?.addEventListener('change',()=>loadDepartments(cf.value,cd,cp));cd?.addEventListener('change',()=>loadPrograms(cd.value,cp));
    if (@json($isHod)) {
        const hodFaculty=@json($faculty[0]->code ?? session('faculty'));
        const hodDepartment=@json($hodDepartment?->code ?? session('department'));
        const hodDepartmentTitle=@json($hodDepartment?->title ?? session('department'));
        const hodPrograms=@json($hodPrograms->values());
        if (cf) { cf.value=hodFaculty || ''; cf.disabled=false; }
        if (cd) { cd.innerHTML=`<option value="${hodDepartment}">${hodDepartmentTitle}</option>`; cd.value=hodDepartment; cd.disabled=false; }
        if (cp) { cp.innerHTML='<option value="">Select programme</option>'+hodPrograms.map(item=>`<option value="${item.code}">${item.title} (${item.code})</option>`).join(''); cp.disabled=false; }
    }
    const ss=document.getElementById('filterStructure'),sh=document.getElementById('structureHelp'),sd=document.getElementById('structureDescription');const structures=@json($structureJson);const updateStructure=()=>{const x=structures.find(row=>String(row.id)===String(ss?.value));if(sh)sh.textContent=x?`${x.name} · ${x.from}–${x.to}`:'Choose a structure to keep a programme curriculum consistent.';if(sd)sd.textContent=x?`${x.name} covers ${x.from} to ${x.to}. Review this version before changing course placement.`:'Use the filters to open one curriculum mapping. Course units and titles remain linked to the master course catalogue.'};ss?.addEventListener('change',updateStructure);updateStructure();
    const quick=document.getElementById('quickSearch');quick?.addEventListener('input',()=>{const v=quick.value.trim().toLowerCase();document.querySelectorAll('#pcrTable tbody tr,#pcrMobileList .pcr-mobile-card').forEach(row=>row.style.display=!v||(row.dataset.search||'').includes(v)?'':'none')});
    const ci=document.getElementById('createCodes'),preview=document.getElementById('codePreview'),picker=document.getElementById('coursePickerSearch'),pickerResults=document.getElementById('coursePickerResults'),addPicked=document.getElementById('addPickedCourse');const selectedCodes=()=>[...new Set((ci?.value||'').toUpperCase().split(/[\s,;]+/).map(x=>x.trim()).filter(Boolean))];const renderCodes=()=>{const codes=selectedCodes();if(!codes.length){preview.innerHTML='<span class="text-muted small">Course previews will appear here.</span>';return}preview.innerHTML=codes.map(code=>{const x=catalogMap[code];return `<span class="pcr-code-chip">${code}${x?` <small>${x.title} · ${x.unit||0} unit(s)</small>`:' <small class="text-danger">not found</small>'}</span>`}).join('')};const addCode=code=>{const codes=selectedCodes();if(code&&!codes.includes(code)){codes.push(code);ci.value=codes.join(', ');renderCodes()}};const renderPicker=()=>{const q=(picker?.value||'').trim().toLowerCase();if(!pickerResults||!q){if(pickerResults)pickerResults.style.display='none';return}const matches=catalog.filter(x=>`${x.code} ${x.title}`.toLowerCase().includes(q)).slice(0,12);pickerResults.innerHTML=matches.length?matches.map(x=>`<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-pick-code="${x.code}"><span><strong>${x.code}</strong><small class="d-block text-muted">${x.title}</small></span><span class="badge bg-light text-primary">${x.unit||0} unit(s)</span></button>`).join(''):'<div class="list-group-item text-muted small">No matching course was found.</div>';pickerResults.style.display='block';pickerResults.querySelectorAll('[data-pick-code]').forEach(b=>b.addEventListener('click',()=>{addCode(b.dataset.pickCode);picker.value='';pickerResults.style.display='none'}))};picker?.addEventListener('input',renderPicker);addPicked?.addEventListener('click',()=>{const exact=String(picker?.value||'').trim().toUpperCase();if(catalogMap[exact]){addCode(exact);picker.value='';pickerResults.style.display='none'}else renderPicker()});ci?.addEventListener('input',renderCodes);document.getElementById('createPcrForm')?.addEventListener('submit',e=>{const missing=selectedCodes().filter(x=>!catalogMap[x]);if(missing.length){e.preventDefault();alert(`These course codes were not found in the master catalogue:\n\n${missing.join(', ')}`)}});
    document.querySelectorAll('.edit-pcr').forEach(btn=>btn.addEventListener('click',()=>{const d=btn.dataset;document.getElementById('editId').value=d.id;document.getElementById('editCourseDisplay').textContent=`${d.code} — ${d.courseTitle||'Course title not found'}`;document.getElementById('editProgramDisplay').textContent=`${d.programTitle} (${d.program})`;const map={editLevel:'level',editSemester:'semester',editType:'type',editElective:'elective',editChangeSemester:'changeSemester',editForm:'form',editStructure:'structure',editDe:'de',editGrading:'grading'};Object.entries(map).forEach(([id,key])=>{const field=document.getElementById(id);if(field)field.value=d[key]??''});document.getElementById('editCourseHeading').textContent=`Update ${d.code} in ${d.programTitle}. Course units remain controlled by the master catalogue.`}));document.getElementById('editType')?.addEventListener('change',e=>{const x=document.getElementById('editElective');x.disabled=e.target.value!=='ELECTIVE';if(e.target.value!=='ELECTIVE')x.value='0'});document.querySelectorAll('.pcr-modal form').forEach(form=>form.addEventListener('submit',()=>{const button=form.querySelector('button[type="submit"]');if(button){button.disabled=true;button.innerHTML='<span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving…'}}));
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createPcrForm');
    const codesField = document.getElementById('createCodes');
    const preview = document.getElementById('codePreview');
    if (!form || !codesField || !preview) return;

    const catalog = @json(collect($courseCatalog ?? [])->values());
    const catalogMap = Object.fromEntries(catalog.map(function (item) {
        return [String(item.code || '').toUpperCase(), item];
    }));
    const groups = {};
    const mapInput = document.createElement('input');
    mapInput.type = 'hidden';
    mapInput.name = 'elective_map';
    mapInput.id = 'electiveMap';
    form.appendChild(mapInput);

    const selectedCodes = function () {
        return [...new Set(String(codesField.value || '').toUpperCase().split(/[\s,;]+/).map(function (code) {
            return code.trim();
        }).filter(Boolean))];
    };
    const syncMap = function () {
        preview.querySelectorAll('[data-elective-code]').forEach(function (select) {
            groups[select.dataset.electiveCode] = select.value;
        });
        const clean = {};
        Object.keys(groups).forEach(function (code) {
            if (groups[code]) clean[code] = Number(groups[code]);
        });
        mapInput.value = JSON.stringify(clean);
    };
    const render = function () {
        const codes = selectedCodes();
        if (!codes.length) {
            preview.innerHTML = '<span class="text-muted small">Course previews will appear here.</span>';
            mapInput.value = '{}';
            return;
        }
        preview.innerHTML = codes.map(function (code) {
            const item = catalogMap[code];
            if (!item) return '<div class="pcr-code-row"><div class="pcr-code-info"><strong>' + code + '</strong><small class="text-danger">Course not found in the master catalogue</small></div></div>';
            const isElective = String(item.type || 'CORE').toUpperCase() === 'ELECTIVE';
            const options = ['<option value="">Select group</option>'].concat(Array.from({length: 20}, function (_, index) {
                const value = index + 1;
                return '<option value="' + value + '" ' + (String(groups[code] || '') === String(value) ? 'selected' : '') + '>Elective group ' + value + '</option>';
            })).join('');
            return '<div class="pcr-code-row"><div class="pcr-code-info"><strong>' + code + '</strong><small>' + (item.title || 'Course title unavailable') + ' · ' + (item.level || '—') + ' level · ' + (item.semester || '—') + '</small></div>' + (isElective ? '<select class="pcr-elective-select" data-elective-code="' + code + '" aria-label="Elective group for ' + code + '">' + options + '</select>' : '<span class="pcr-core-label">Core course</span>') + '</div>';
        }).join('');
        preview.querySelectorAll('[data-elective-code]').forEach(function (select) {
            select.addEventListener('change', syncMap);
        });
        syncMap();
    };
    codesField.addEventListener('input', render);
    form.addEventListener('submit', function (event) {
        render();
        const missing = selectedCodes().filter(function (code) {
            const item = catalogMap[code];
            return item && String(item.type || 'CORE').toUpperCase() === 'ELECTIVE' && !groups[code];
        });
        if (missing.length) {
            event.preventDefault();
            const button = document.getElementById('createPcrSubmit');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-save me-1"></i> Save curriculum courses';
            }
            alert('Select an elective group for: ' + missing.join(', ') + '. Courses in the same group are linked together for student registration.');
        }
    });
    render();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('pcrFilterForm');
    const pdfPreview = document.getElementById('pcrPdfPreview');
    const pdfPreviewBase = @json(route('program-course-registration.preview'));
    const syncPdfPreview = function () {
        if (!filterForm || !pdfPreview) return;
        const params = new URLSearchParams();
        new FormData(filterForm).forEach(function (value, key) {
            const text = String(value || '').trim();
            if (text) params.set(key, text);
        });
        pdfPreview.href = pdfPreviewBase + (params.toString() ? '?' + params.toString() : '');
    };
    filterForm?.querySelectorAll('select, input').forEach(function (field) {
        field.addEventListener('change', syncPdfPreview);
        field.addEventListener('input', syncPdfPreview);
    });
    syncPdfPreview();

    const prerequisites = @json($prerequisiteMap);
    const modalBody = document.querySelector('#editCourseModal .modal-body');
    if (modalBody && !document.getElementById('editPrerequisites') && !@json($isHod)) {
        const section = document.createElement('div');
        section.className = 'pcr-form-section mt-3';
        section.innerHTML = '<div class="pcr-field"><label for="editPrerequisites"><i class="fas fa-link"></i> Prerequisite course(s)</label><textarea class="form-control" name="prerequisites" id="editPrerequisites" rows="2" placeholder="Example: MTH101, PHY101"></textarea><small>Optional. Enter course codes separated by commas, spaces or new lines. Leave empty when there is no prerequisite.</small></div>';
        modalBody.appendChild(section);
    }

    document.querySelectorAll('.edit-pcr').forEach(function (button) {
        button.addEventListener('click', function () {
            const field = document.getElementById('editPrerequisites');
            if (field) field.value = prerequisites[String(button.dataset.id)] || '';
        });
    });

    if (!@json($isHod)) {
        document.querySelectorAll('#pcrTable tbody tr, #pcrMobileList .pcr-mobile-card').forEach(function (row) {
            const edit = row.querySelector('.edit-pcr');
            const value = edit ? prerequisites[String(edit.dataset.id)] : '';
            if (!value) return;
            const note = document.createElement('small');
            note.className = 'd-block text-warning mt-1';
            note.innerHTML = '<i class="fas fa-link me-1"></i>Prerequisite: ' + value;
            const target = row.querySelector('.pcr-course-title') || row.querySelector('.pcr-mobile-meta');
            if (target) target.appendChild(note);
        });
    }
});
</script>
