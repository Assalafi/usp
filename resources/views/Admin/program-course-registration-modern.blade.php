@php
    $filter = $selectedFilters ?? request()->only(['faculty', 'department', 'program', 'level', 'semester', 'structure_id', 'type', 'grading', 'q']);
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
    $hodReview = $hodReview ?? null;
    $hodReviewTracking = collect($hodReviewTracking ?? []);
    $reviewStructure = $reviewStructure ?? null;
    $reviewStructureId = (int) ($reviewStructureId ?? 0);
@endphp

<link rel="stylesheet" href="{{ asset('css/program-course-registration.css') }}?v=20261013-2">
<style>
    .pcr-page{background:#f4f7fb;padding-bottom:28px}.pcr-page .pcr-hero{background:#fff;color:#0f172a;border:1px solid #dbe5f0;border-left:5px solid #2563eb;box-shadow:0 10px 26px rgba(15,23,42,.06)}.pcr-page .pcr-hero .card-body{padding:26px 30px}.pcr-kicker{color:#2563eb;opacity:1}.pcr-hero h1{color:#0f172a}.pcr-hero p{color:#64748b}.pcr-hero-actions .btn-light{color:#1d4ed8;border:1px solid #bfdbfe}.pcr-stat-row{margin-top:20px}.pcr-stat{border-color:#dbe5f0;background:#f8fbff;color:#0f172a}.pcr-stat strong{color:#0f3b82}.pcr-stat span{color:#64748b}.pcr-panel{box-shadow:0 8px 22px rgba(15,23,42,.045)}.pcr-modal .modal-dialog{max-height:calc(100vh - 2rem);margin:1rem auto}.pcr-modal .modal-content{max-height:calc(100vh - 2rem);display:flex}.pcr-modal form{min-height:0;display:flex;flex-direction:column}.pcr-modal .modal-body{min-height:0;max-height:calc(100vh - 150px);overflow-y:auto;overscroll-behavior:contain}.pcr-modal .modal-footer{flex:0 0 auto}.pcr-pdf-btn{white-space:nowrap}.pcr-filter-actions .pcr-pdf-btn{border-color:#bfdbfe;color:#1d4ed8;background:#eff6ff}.pcr-filter-actions .pcr-pdf-btn:hover{color:#fff;background:#2563eb;border-color:#2563eb}.pcr-view-only{display:inline-flex;align-items:center;padding:.55rem .9rem;border-radius:.5rem;background:#eef2ff;color:#4338ca;font-weight:700}.pcr-readonly .pcr-actions{display:none!important}
    .pcr-review-card{border:1px solid #dbe5f0;border-radius:18px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.045);padding:22px 24px;display:flex;gap:18px}.pcr-review-hod{border-left:5px solid #0f766e}.pcr-review-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:#dff7f2;color:#0f766e;font-size:20px;flex:0 0 auto}.pcr-review-content{min-width:0;flex:1}.pcr-review-heading,.pcr-review-admin-top{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}.pcr-review-kicker{display:block;text-transform:uppercase;letter-spacing:.08em;font-size:11px;font-weight:800;color:#64748b;margin-bottom:4px}.pcr-review-card h2{font-size:19px;line-height:1.2;margin:0;color:#0f172a;font-weight:800}.pcr-review-card p{color:#64748b;max-width:850px}.pcr-review-status{display:inline-flex;align-items:center;white-space:nowrap;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800}.pcr-review-status.is-submitted{background:#dcfce7;color:#166534}.pcr-review-status.is-pending{background:#fff7df;color:#9a6700}.pcr-review-meta{display:flex;flex-wrap:wrap;gap:14px;color:#475569;font-size:13px}.pcr-review-meta span{display:inline-flex;align-items:center;gap:7px}.pcr-review-meta i{color:#2563eb}.pcr-review-meta small{color:#64748b}.pcr-review-empty{display:flex;gap:9px;align-items:center;color:#64748b;background:#f8fafc;border-radius:10px;padding:12px 14px}.pcr-review-empty i{color:#2563eb}.pcr-review-empty i{color:#2563eb}.pcr-review-admin{display:block;border-left:5px solid #2563eb}.pcr-review-admin-top{margin-bottom:16px}.pcr-review-admin-top p{margin:5px 0 0;max-width:720px}.pcr-review-structure-form{min-width:250px}.pcr-review-structure-form label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:5px}.pcr-review-admin-summary{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px}.pcr-review-admin-summary span{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:9px 12px;color:#475569;font-size:13px}.pcr-review-admin-summary strong{color:#0f172a;font-size:16px;margin-right:4px}.pcr-review-table{font-size:13px}.pcr-review-table th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:#f8fafc;white-space:nowrap}.pcr-review-table td{color:#334155}.pcr-review-table small{font-size:11px}.pcr-tabs{display:flex;gap:10px;align-items:stretch;margin:0 0 18px;padding:6px;border:1px solid #dbe5f0;border-radius:16px;background:#eaf1f9;box-shadow:0 6px 18px rgba(15,23,42,.04)}.pcr-tab{border:0;background:transparent;color:#475569;border-radius:11px;display:flex;align-items:center;gap:9px;padding:11px 15px;cursor:pointer;text-align:left;transition:background .18s ease,color .18s ease,box-shadow .18s ease;min-width:210px}.pcr-tab i{font-size:16px;color:#64748b}.pcr-tab span{display:block;font-size:14px;font-weight:800;line-height:1.1}.pcr-tab small{display:block;font-size:11px;color:#64748b;margin-top:3px}.pcr-tab:hover{background:#fff;color:#1d4ed8}.pcr-tab:hover i,.pcr-tab.is-active i{color:#2563eb}.pcr-tab.is-active{background:#fff;color:#0f172a;box-shadow:0 4px 12px rgba(37,99,235,.13)}.pcr-tab-panel[hidden]{display:none!important}.pcr-tab-panel{animation:pcrTabIn .18s ease}.pcr-tab-panel.is-active{display:block}@keyframes pcrTabIn{from{opacity:.2;transform:translateY(3px)}to{opacity:1;transform:none}}@media(max-width:767px){.pcr-page .pcr-hero .card-body{padding:22px 18px}.pcr-modal .modal-dialog{max-height:calc(100vh - 1rem);margin:.5rem auto}.pcr-modal .modal-content{max-height:calc(100vh - 1rem)}.pcr-modal .modal-body{max-height:calc(100vh - 132px);padding:16px}.pcr-filter-actions .pcr-pdf-btn{flex-basis:100%}.pcr-review-card{padding:18px 16px;gap:12px}.pcr-review-heading,.pcr-review-admin-top{display:block}.pcr-review-status{margin-top:10px}.pcr-review-structure-form{min-width:0;margin-top:14px}.pcr-review-table{min-width:720px}.pcr-tabs{overflow-x:auto;gap:7px;margin-left:-2px;margin-right:-2px}.pcr-tab{min-width:180px;padding:10px 12px}.pcr-tab small{display:none}}
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

    <div class="pcr-tabs" role="tablist" aria-label="Curriculum workspace sections">
        <button type="button" class="pcr-tab is-active" id="pcr-tab-register-button" role="tab" aria-selected="true" aria-controls="pcr-tab-register" data-pcr-tab="pcr-tab-register"><i class="fas fa-book-open"></i><span>Course register</span><small>Review and manage courses</small></button>
        @if($isHod)
            <button type="button" class="pcr-tab" id="pcr-tab-review-button" role="tab" aria-selected="false" aria-controls="pcr-tab-review" data-pcr-tab="pcr-tab-review"><i class="fas fa-clipboard-check"></i><span>My review</span><small>Confirm your structure</small></button>
        @else
            <button type="button" class="pcr-tab" id="pcr-tab-tracking-button" role="tab" aria-selected="false" aria-controls="pcr-tab-tracking" data-pcr-tab="pcr-tab-tracking"><i class="fas fa-chart-column"></i><span>HOD submissions</span><small>Monitor confirmations</small></button>
        @endif
    </div>

    <div class="pcr-tab-panels">
    @if($isHod)
        <section class="pcr-tab-panel" id="pcr-tab-review" role="tabpanel" aria-labelledby="pcr-tab-review-button" hidden>
        <div class="pcr-review-card pcr-review-hod mb-4">
            <div class="pcr-review-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="pcr-review-content">
                <div class="pcr-review-heading"><div><span class="pcr-review-kicker">Required academic confirmation</span><h2>Confirm your department curriculum</h2></div><span class="pcr-review-status {{ ($hodReview?->status ?? 'pending') === 'submitted' ? 'is-submitted' : 'is-pending' }}"><i class="fas {{ ($hodReview?->status ?? 'pending') === 'submitted' ? 'fa-check' : 'fa-clock' }} me-1"></i>{{ ($hodReview?->status ?? 'pending') === 'submitted' ? 'Submitted' : 'Pending review' }}</span></div>
                <p class="mb-3">Review the course structure shown below for your department. Submit your confirmation when the programme courses, levels, semesters and course types are correct. Any later change will reopen this review.</p>
                @if($reviewStructure)
                    <div class="pcr-review-meta"><span><i class="fas fa-layer-group"></i><strong>{{ $reviewStructure->name }}</strong> <small>{{ $reviewStructure->from_session }}–{{ $reviewStructure->to_session }}</small></span>@if($hodReview?->submitted_at)<span><i class="fas fa-calendar-check"></i>Last submitted {{ \Illuminate\Support\Carbon::parse($hodReview->submitted_at)->format('j M Y, g:i a') }}</span>@elseif($hodReview?->last_changed_at)<span><i class="fas fa-pen-to-square"></i>Changed {{ \Illuminate\Support\Carbon::parse($hodReview->last_changed_at)->format('j M Y, g:i a') }}</span>@endif</div>
                    <form method="POST" action="{{ route('program-course-registration.submit-review') }}" class="mt-3" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<span class=\"spinner-border spinner-border-sm me-1\"></span>Submitting…';">@csrf<input type="hidden" name="structure_id" value="{{ $reviewStructureId }}"><button class="btn btn-primary pcr-btn" type="submit"><i class="fas fa-paper-plane me-1"></i>{{ ($hodReview?->status ?? 'pending') === 'submitted' ? 'Confirm again' : 'Submit curriculum confirmation' }}</button></form>
                @else
                    <div class="pcr-review-empty"><i class="fas fa-arrow-up-right-from-square"></i><span>Choose a curriculum structure above to begin your review.</span></div>
                @endif
            </div>
        </div>
        </section>
    @else
        <section class="pcr-tab-panel" id="pcr-tab-tracking" role="tabpanel" aria-labelledby="pcr-tab-tracking-button" hidden>
        <div class="pcr-review-card pcr-review-admin mb-4">
            <div class="pcr-review-admin-top"><div><span class="pcr-review-kicker">Administration</span><h2>Curriculum review tracking</h2><p>Monitor which Heads of Department have confirmed the selected curriculum structure. Existing records start as pending until the HOD submits a confirmation.</p></div><div class="pcr-review-admin-actions"><form method="GET" action="{{ url($page) }}" class="pcr-review-structure-form"><label for="reviewStructureId">Curriculum structure</label><select id="reviewStructureId" name="review_structure_id" class="form-select form-select-sm" onchange="try{sessionStorage.setItem('pcr-active-tab','pcr-tab-tracking')}catch(e){};this.form.submit()">@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}" {{ $reviewStructureId === (int) $structure->id ? 'selected' : '' }}>{{ $structure->name }} ({{ $structure->from_session }}–{{ $structure->to_session }})</option>@endforeach</select></form>@if($reviewStructure)<a class="btn btn-outline-primary pcr-btn pcr-review-pdf" href="{{ route('program-course-registration.review-summary', ['structure_id' => $reviewStructureId]) }}"><i class="fas fa-file-pdf me-1"></i> Download PDF summary</a>@endif</div></div>
            @if($reviewStructure)
                <div class="pcr-review-admin-summary"><span><strong>{{ $hodReviewTracking->where('review_status', 'submitted')->count() }}</strong> submitted</span><span><strong>{{ $hodReviewTracking->where('review_status', '!=', 'submitted')->count() }}</strong> pending review</span><span><strong>{{ $hodReviewTracking->count() }}</strong> active HODs</span><span class="pcr-review-checked"><i class="fas fa-clock me-1"></i>Updated {{ now()->format('j M Y, g:i a') }}</span></div>
                <div class="pcr-review-tools"><div class="input-group input-group-sm"><span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span><input type="search" class="form-control border-start-0" id="hodReviewSearch" placeholder="Search HOD or department" aria-label="Search HOD submissions"></div><select class="form-select form-select-sm" id="hodReviewStatus" aria-label="Filter HOD submission status"><option value="all">All statuses</option><option value="submitted">Submitted</option><option value="pending">Pending review</option></select><span class="pcr-review-visible" id="hodReviewVisible">{{ $hodReviewTracking->count() }} shown</span></div>
                <div class="table-responsive"><table class="table pcr-review-table align-middle mb-0"><thead><tr><th>Head of Department</th><th>Department</th><th>Status</th><th>Submitted</th><th>Latest curriculum change</th></tr></thead><tbody>@forelse($hodReviewTracking as $review)<tr data-review-status="{{ $review->review_status === 'submitted' ? 'submitted' : 'pending' }}" data-review-search="{{ strtolower(($review->name ?: '') . ' ' . ($review->username ?: '') . ' ' . ($review->department_title ?: $review->department ?: '')) }}"><td><strong>{{ $review->name ?: 'Unnamed HOD' }}</strong><small class="d-block text-muted">{{ $review->username }}</small></td><td>{{ $review->department_title ?: $review->department }}</td><td><span class="pcr-review-status {{ $review->review_status === 'submitted' ? 'is-submitted' : 'is-pending' }}"><i class="fas {{ $review->review_status === 'submitted' ? 'fa-check' : 'fa-clock' }} me-1"></i>{{ $review->review_status === 'submitted' ? 'Submitted' : 'Pending review' }}</span></td><td>{{ $review->submitted_at ? \Illuminate\Support\Carbon::parse($review->submitted_at)->format('j M Y, g:i a') : '—' }}</td><td>{{ $review->last_changed_at ? \Illuminate\Support\Carbon::parse($review->last_changed_at)->format('j M Y, g:i a') : 'No change recorded' }}@if($review->last_change_action)<small class="d-block text-muted">{{ $review->last_change_action }}</small>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No active Heads of Department were found.</td></tr>@endforelse</tbody></table><div id="hodReviewNoMatch" class="pcr-review-empty" hidden><i class="fas fa-search"></i><span>No HOD matches the selected search and status filter.</span></div></div>
            @else
                <div class="pcr-review-empty"><i class="fas fa-layer-group"></i><span>Create a curriculum structure to start HOD review tracking.</span></div>
            @endif
        </div>
        </section>
    @endif

    <section class="pcr-tab-panel is-active" id="pcr-tab-register" role="tabpanel" aria-labelledby="pcr-tab-register-button">
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
    </section>
    </div>
</div></div>

<div class="modal fade pcr-modal" id="createCourseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5><i class="fas fa-circle-plus me-2"></i>Add courses to a curriculum</h5><p>Choose the academic context first. Course details are copied from the master catalogue.</p></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('create ' . $page) }}" id="createPcrForm"><div class="modal-body">@csrf<div class="pcr-step"><span>1</span> Academic context</div><div class="pcr-form-section"><h6>Where should these courses belong?</h6><div class="row g-3"><div class="col-md-6"><div class="pcr-field"><label for="createStructure">Curriculum structure *</label><select class="form-select" id="createStructure" name="structure_id" required><option value="">Select structure</option>@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}">{{ $structure->name }} · {{ $structure->from_session }}–{{ $structure->to_session }}{{ $structure->status ? ' · Active' : '' }}</option>@endforeach</select></div></div><div class="col-md-6"><div class="pcr-field"><label for="createForm">Student form *</label><select class="form-select" id="createForm" name="form" required><option value="">Select form</option><option value="NEW">New student</option><option value="FORMER">Former / returning</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createFaculty">Faculty / college *</label><select class="form-select" id="createFaculty" name="faculty" required><option value="">Select faculty</option>@foreach($faculty ?? [] as $item)<option value="{{ $item->code }}">{{ $item->title }} ({{ $item->code }})</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createDepartment">Department *</label><select class="form-select" id="createDepartment" name="department" required disabled><option value="">Select faculty first</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="createProgram">Programme *</label><select class="form-select" id="createProgram" name="program" required disabled><option value="">Select department first</option></select></div></div></div></div><div class="pcr-step"><span>2</span> Course selection</div><div class="pcr-form-section"><h6>Choose from the master course catalogue</h6><div class="row g-2 align-items-end"><div class="col-md-9"><div class="pcr-field"><label for="coursePickerSearch">Search course code or title</label><input class="form-control" id="coursePickerSearch" autocomplete="off" placeholder="Type ACC101 or accounting"></div></div><div class="col-md-3"><button type="button" class="btn btn-outline-primary pcr-btn w-100" id="addPickedCourse"><i class="fas fa-plus me-1"></i> Add course</button></div></div><div id="coursePickerResults" class="list-group mt-2" style="max-height:180px;overflow:auto;display:none"></div><div class="pcr-field mt-3"><label for="createCodes">Selected course codes *</label><textarea class="form-control" id="createCodes" name="code" rows="3" required placeholder="Selected codes will appear here"></textarea><small>Use the search above or enter codes separated by commas, spaces or new lines.</small></div><div class="pcr-code-preview mt-3" id="codePreview"><span class="text-muted small">Course previews will appear here.</span></div></div><div class="pcr-danger-note"><i class="fas fa-circle-info mt-1"></i><span>Existing mappings with the same programme, structure and course code will be refreshed from the master course catalogue. Other curriculum settings are preserved.</span></div></div><div class="modal-footer"><button type="button" class="btn btn-light pcr-btn" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary pcr-btn" id="createPcrSubmit"><i class="fas fa-save me-1"></i> Save curriculum courses</button></div></form></div></div></div>

<div class="modal fade pcr-modal" id="editCourseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5><i class="fas fa-pen-to-square me-2"></i>Edit curriculum mapping</h5><p id="editCourseHeading">Update the academic placement and registration rules.</p></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('update ' . $page) }}"><div class="modal-body">@csrf<input type="hidden" name="id" id="editId"><div class="pcr-form-section"><div class="row g-3"><div class="col-md-6"><div class="pcr-field"><label>Course</label><div class="form-control bg-light" id="editCourseDisplay">—</div></div></div><div class="col-md-6"><div class="pcr-field"><label>Programme</label><div class="form-control bg-light" id="editProgramDisplay">—</div></div></div></div></div><div class="row g-3"><div class="col-md-4"><div class="pcr-field"><label for="editLevel">Level *</label><select class="form-select" name="level" id="editLevel" required>@foreach([100,200,300,400,500,600,700] as $level)<option value="{{ $level }}">{{ $level }}</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editSemester">Semester *</label><select class="form-select" name="semester" id="editSemester" required>@foreach($semester ?? [] as $item)<option value="{{ $item->semester }}">{{ $item->semester }}</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editStructure">Curriculum structure *</label><select class="form-select" name="structure_id" id="editStructure" required>@foreach($courseStructure ?? [] as $structure)<option value="{{ $structure->id }}">{{ $structure->name }} ({{ $structure->from_session }}–{{ $structure->to_session }})</option>@endforeach</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editType">Course type *</label><select class="form-select" name="type" id="editType" required><option value="CORE">Core</option><option value="ELECTIVE">Elective</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editElective">Elective group</label><select class="form-select" name="elective" id="editElective"><option value="0">Not an elective</option>@for($i=1;$i<=10;$i++)<option value="{{ $i }}">Group {{ $i }}</option>@endfor</select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editForm">Student form *</label><select class="form-select" name="form" id="editForm" required><option value="NEW">New student</option><option value="FORMER">Former / returning</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editChangeSemester">Semester change</label><select class="form-select" name="change_semester" id="editChangeSemester" required><option value="0">Not allowed</option><option value="1">Allowed</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editDe">Direct entry</label><select class="form-select" name="de" id="editDe" required><option value="0">No</option><option value="1">Yes</option></select></div></div><div class="col-md-4"><div class="pcr-field"><label for="editGrading">Grading system *</label><select class="form-select" name="grading" id="editGrading" required>@foreach($grading ?? [] as $item)<option value="{{ $item->name }}">{{ $item->name }}</option>@endforeach</select></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light pcr-btn" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary pcr-btn"><i class="fas fa-save me-1"></i> Save changes</button></div></form></div></div></div>

<style>
.pcr-course-placement{display:flex;flex-direction:column;gap:6px;align-items:flex-end;min-width:190px}.pcr-direct-entry-choice{font-size:.72rem;color:#2f5d83;display:flex;align-items:center;gap:6px;white-space:nowrap}.pcr-direct-entry-choice input{accent-color:#1769ce}.direct-entry-status{background:#fff4df;color:#9a6200!important;border:1px solid #f2d79f}.pcr-mobile-top>.direct-entry-status{margin-top:6px;display:inline-flex}#pcrTable .pcr-badge.semester,.pcr-mobile-meta>div:last-child{display:none}
@media(max-width:767px){.pcr-course-placement{align-items:flex-start;min-width:0}.pcr-direct-entry-choice{white-space:normal}}
</style>

<style>
.pcr-review-admin-actions{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;justify-content:flex-end}.pcr-review-admin-actions .pcr-review-structure-form{min-width:250px}.pcr-review-pdf{white-space:nowrap}.pcr-review-checked{font-size:12px!important;color:#64748b!important;background:#fff!important;border-color:transparent!important}.pcr-review-tools{display:flex;align-items:center;gap:10px;margin:0 0 12px}.pcr-review-tools .input-group{max-width:360px}.pcr-review-tools select{max-width:170px}.pcr-review-visible{margin-left:auto;color:#64748b;font-size:12px;white-space:nowrap}.pcr-review-table tr.is-filtered-out{display:none}.pcr-review-tools+.table-responsive #hodReviewNoMatch{margin-top:10px}@media(max-width:767px){.pcr-review-admin-actions{display:block}.pcr-review-admin-actions .pcr-review-structure-form{min-width:0;margin-top:12px}.pcr-review-pdf{margin-top:10px;width:100%}.pcr-review-tools{display:grid;grid-template-columns:1fr auto}.pcr-review-tools .input-group{max-width:none;grid-column:1 / -1}.pcr-review-visible{margin-left:0}}
</style>

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
    const tabButtons = Array.from(document.querySelectorAll('[data-pcr-tab]'));
    const tabPanels = Array.from(document.querySelectorAll('.pcr-tab-panel'));
    if (!tabButtons.length || !tabPanels.length) return;

    const storageKey = 'pcr-active-tab';
    const validPanel = function (id) {
        return tabPanels.some(function (panel) { return panel.id === id; });
    };
    const activate = function (id, remember) {
        if (!validPanel(id)) id = 'pcr-tab-register';
        tabButtons.forEach(function (button) {
            const active = button.dataset.pcrTab === id;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
            button.tabIndex = active ? 0 : -1;
        });
        tabPanels.forEach(function (panel) {
            const active = panel.id === id;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
        });
        if (remember) {
            try { window.sessionStorage.setItem(storageKey, id); } catch (error) { /* private browsing */ }
        }
    };

    tabButtons.forEach(function (button, index) {
        button.addEventListener('click', function () { activate(button.dataset.pcrTab, true); });
        button.addEventListener('keydown', function (event) {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            let next = index;
            if (event.key === 'ArrowLeft') next = (index - 1 + tabButtons.length) % tabButtons.length;
            if (event.key === 'ArrowRight') next = (index + 1) % tabButtons.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabButtons.length - 1;
            tabButtons[next].focus();
            activate(tabButtons[next].dataset.pcrTab, true);
        });
    });

    let saved = null;
    try { saved = window.sessionStorage.getItem(storageKey); } catch (error) { saved = null; }
    activate(validPanel(saved) ? saved : 'pcr-tab-register', false);

    const reviewSearch = document.getElementById('hodReviewSearch');
    const reviewStatus = document.getElementById('hodReviewStatus');
    const reviewVisible = document.getElementById('hodReviewVisible');
    const reviewNoMatch = document.getElementById('hodReviewNoMatch');
    const reviewRows = Array.from(document.querySelectorAll('.pcr-review-table tbody tr[data-review-status]'));
    const applyReviewFilter = function () {
        if (!reviewRows.length) return;
        const search = String(reviewSearch?.value || '').trim().toLowerCase();
        const status = String(reviewStatus?.value || 'all');
        let visible = 0;
        reviewRows.forEach(function (row) {
            const matchesSearch = !search || String(row.dataset.reviewSearch || '').includes(search);
            const matchesStatus = status === 'all' || row.dataset.reviewStatus === status;
            const show = matchesSearch && matchesStatus;
            row.classList.toggle('is-filtered-out', !show);
            if (show) visible += 1;
        });
        if (reviewVisible) reviewVisible.textContent = visible + (visible === 1 ? ' shown' : ' shown');
        if (reviewNoMatch) reviewNoMatch.hidden = visible !== 0;
    };
    reviewSearch?.addEventListener('input', applyReviewFilter);
    reviewStatus?.addEventListener('change', applyReviewFilter);
    applyReviewFilter();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Curriculum structures now determine every course mapping. Hide the old
    // new/returning-student control from legacy records without deleting the
    // column, so old data remains safe while it is no longer used.
    ['createForm', 'editForm'].forEach(function (id) {
        const field = document.getElementById(id);
        if (field) {
            field.disabled = true;
            const wrapper = field.closest('.pcr-field')?.parentElement;
            if (wrapper) wrapper.remove();
        }
    });

    const directLabel = document.querySelector('label[for="editDe"]');
    if (directLabel) directLabel.textContent = 'Direct-entry foundation course';
    const directField = document.getElementById('editDe');
    if (directField) {
        const note = document.createElement('small');
        note.className = 'd-block text-muted mt-1';
        note.textContent = 'Choose Yes when a lower-level course is required for students admitted directly into 200 level.';
        directField.parentElement.appendChild(note);
    }

    document.querySelectorAll('.edit-pcr').forEach(function (button) {
        const row = button.closest('tr');
        const badge = row?.querySelector('.pcr-badge.semester');
        if (badge) {
            badge.className = 'pcr-badge direct-entry-status';
            badge.textContent = String(button.dataset.de || '0') === '1' ? 'Direct-entry foundation' : 'Standard course';
        }
        const mobile = button.closest('.pcr-mobile-card');
        const unitsMeta = mobile ? Array.from(mobile.querySelectorAll('.pcr-mobile-meta>div')).find(function (item) {
            return item.querySelector('small')?.textContent.trim() === 'Units / form';
        }) : null;
        const oldFormText = unitsMeta?.querySelector('strong');
        if (unitsMeta) unitsMeta.querySelector('small').textContent = 'Units';
        if (oldFormText) oldFormText.textContent = oldFormText.textContent.replace(/\s*·\s*(NEW|FORMER|ALL)$/i, '').trim();
        if (mobile && String(button.dataset.de || '0') === '1') {
            const tag = document.createElement('span');
            tag.className = 'pcr-badge direct-entry-status';
            tag.textContent = 'Direct-entry foundation';
            mobile.querySelector('.pcr-mobile-top')?.appendChild(tag);
        }
    });
});
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
    const directEntry = {};
    const mapInput = document.createElement('input');
    mapInput.type = 'hidden';
    mapInput.name = 'elective_map';
    mapInput.id = 'electiveMap';
    form.appendChild(mapInput);
    const directEntryInput = document.createElement('input');
    directEntryInput.type = 'hidden';
    directEntryInput.name = 'de_map';
    directEntryInput.id = 'directEntryMap';
    form.appendChild(directEntryInput);

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
        const directClean = {};
        Object.keys(directEntry).forEach(function (code) {
            directClean[code] = Number(directEntry[code] || 0);
        });
        directEntryInput.value = JSON.stringify(directClean);
    };
    const render = function () {
        const codes = selectedCodes();
        if (!codes.length) {
            preview.innerHTML = '<span class="text-muted small">Course previews will appear here.</span>';
            mapInput.value = '{}';
            directEntryInput.value = '{}';
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
        preview.querySelectorAll('.pcr-code-row').forEach(function (row) {
            const codeNode = row.querySelector('.pcr-code-info strong');
            const code = codeNode ? codeNode.textContent.trim() : '';
            if (!catalogMap[code]) return;
            if (String(catalogMap[code].level || '') !== '100') return;
            const placement = document.createElement('div');
            placement.className = 'pcr-course-placement';
            placement.innerHTML = '<label class="pcr-direct-entry-choice"><input type="checkbox" data-direct-entry-code="' + code + '" ' + (String(directEntry[code] || 0) === '1' ? 'checked' : '') + '> Direct-entry foundation course</label>';
            row.appendChild(placement);
        });
        preview.querySelectorAll('[data-elective-code]').forEach(function (select) {
            select.addEventListener('change', syncMap);
        });
        preview.querySelectorAll('[data-direct-entry-code]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                directEntry[checkbox.dataset.directEntryCode] = checkbox.checked ? 1 : 0;
                syncMap();
            });
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
