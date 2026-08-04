@php
    $overview = $overview ?? [];
    $pageName = 'pg-admission';
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h5 class="m-b-10"><i class="fas fa-graduation-cap"></i> PG School Admission</h5>
                                </div>
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
                                    <li class="breadcrumb-item"><a href="/applicants">Admission</a></li>
                                    <li class="breadcrumb-item active">PG School Admission</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($overview))
            <div class="row mb-3" id="summaryCards">
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #4680ff;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-user-clock fa-2x" style="color:#4680ff;"></i></div>
                            <div><h4 class="mb-0" id="statCleared">{{ $overview['cleared'] ?? 0 }}</h4><small class="text-muted">Awaiting VC</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #6f42c1;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-check-double fa-2x" style="color:#6f42c1;"></i></div>
                            <div><h4 class="mb-0" id="statVcApproved">{{ $overview['vc_approved'] ?? 0 }}</h4><small class="text-muted">VC Approved</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #17a2b8;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-paper-plane fa-2x" style="color:#17a2b8;"></i></div>
                            <div><h4 class="mb-0" id="statSubmitted">{{ $overview['submitted'] ?? 0 }}</h4><small class="text-muted">Submitted</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #ffa21d;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-thumbs-up fa-2x" style="color:#ffa21d;"></i></div>
                            <div><h4 class="mb-0" id="statRecommended">{{ $overview['recommended'] ?? 0 }}</h4><small class="text-muted">Recommended</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #28a745;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-user-graduate fa-2x" style="color:#28a745;"></i></div>
                            <div><h4 class="mb-0" id="statApproved">{{ $overview['approved'] ?? 0 }}</h4><small class="text-muted">Approved</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="card mb-0" style="border-left:4px solid #dc3545;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="mr-3"><i class="fas fa-ban fa-2x" style="color:#dc3545;"></i></div>
                            <div><h4 class="mb-0" id="statRejected">{{ $overview['rejected'] ?? 0 }}</h4><small class="text-muted">Rejected</small></div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-danger mb-3">
                <strong><i class="fas fa-unlink mr-1"></i>Unable to reach the PG School portal.</strong>
                Check that the PG School admission API key and URL are configured, then refresh.
            </div>
        @endif

        <div class="row">
            <div class="col-sm-12">
                {{-- ══════════════ FILTER BAR (shared) ══════════════ --}}
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><i class="fas fa-filter mr-1"></i>Filters</strong>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()"><i class="fas fa-undo mr-1"></i>Reset</button>
                        </div>
                        <div class="row gx-2 gy-2">
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Faculty</label>
                                <select class="form-control form-control-sm" id="fFaculty" onchange="onFacultyChange()">
                                    <option value="">All Faculties</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Department</label>
                                <select class="form-control form-control-sm" id="fDepartment" onchange="onDepartmentChange()">
                                    <option value="">All Departments</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Program</label>
                                <select class="form-control form-control-sm" id="fProgram">
                                    <option value="">All Programs</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Level</label>
                                <select class="form-control form-control-sm" id="fLevel">
                                    <option value="">All Levels</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Semester</label>
                                <select class="form-control form-control-sm" id="fSemester">
                                    <option value="">All Semesters</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Session</label>
                                <select class="form-control form-control-sm" id="fSession">
                                    <option value="">All Sessions</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Status</label>
                                <select class="form-control form-control-sm" id="fStatus">
                                    <option value="cleared" selected>Awaiting VC (Cleared)</option>
                                    <option value="vc_approved">VC Approved</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="recommended">Recommended</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="small font-weight-bold">Search</label>
                                <input type="text" class="form-control form-control-sm" id="fSearch" placeholder="Name / No / Email" onkeyup="if(event.key==='Enter')applyFilters()">
                            </div>
                            <div class="col-md-3 col-lg-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm btn-block" onclick="applyFilters()"><i class="fas fa-search mr-1"></i>Apply</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-2">
                        <ul class="nav nav-tabs card-header-tabs" id="pgTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-awaiting-link" href="javascript:void(0)" onclick="switchPgTab('tabAwaiting','tab-awaiting-link')">
                                    <i class="fas fa-user-clock mr-1"></i>Awaiting VC Approval
                                    <span class="badge badge-warning ml-1" id="awaitingBadge">{{ $overview['cleared'] ?? 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-history-link" href="javascript:void(0)" onclick="switchPgTab('tabHistory','tab-history-link')">
                                    <i class="fas fa-history mr-1"></i>History
                                    <span class="badge badge-primary ml-1" id="historyBadge">{{ $overview['vc_approved'] ?? 0 }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-block p-3">

                        {{-- ══════════════ AWAITING TAB ══════════════ --}}
                        <div class="pg-tab" id="tabAwaiting">
                            {{-- Bulk action toolbar --}}
                            <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap py-2 mb-3">
                                <div>
                                    <span class="font-weight-bold"><span id="selectedCount">0</span> selected</span>
                                    <span class="text-muted ml-2 small">Only <strong>Cleared</strong> applications can be VC-approved.</span>
                                </div>
                                <div class="d-flex align-items-center flex-wrap">
                                    <button class="btn btn-outline-info btn-sm mr-2 mb-1" id="selectAllMatchingBtn" onclick="selectAllMatching()" title="Select every application matching the current filters (across all pages)">
                                        <i class="fas fa-check-double mr-1"></i>Select All Matching
                                    </button>
                                    <input type="text" class="form-control form-control-sm mr-2 mb-1" id="bulkRemarks" placeholder="Remarks (optional)" style="width:260px;">
                                    <button class="btn btn-success btn-sm mb-1" id="bulkApproveBtn" onclick="confirmBulkApprove()" disabled>
                                        <i class="fas fa-check-double mr-1"></i>Approve Selected
                                    </button>
                                </div>
                            </div>

                            {{-- Progress panel --}}
                            <div id="progressPanel" class="mb-3" style="display:none;">
                                <div class="card mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong id="progressLabel">Processing...</strong>
                                            <span id="progressPercent">0%</span>
                                        </div>
                                        <div class="progress" style="height:22px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressBar" role="progressbar" style="width:0%"></div>
                                        </div>
                                        <div class="text-muted small mt-2" id="progressDetail"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped" id="awaitingTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="36"><input type="checkbox" id="selectAllCheck" onchange="toggleSelectAll(this)"></th>
                                            <th>App No</th>
                                            <th>Name</th>
                                            <th>Program</th>
                                            <th>Level</th>
                                            <th>Session</th>
                                            <th>Cleared At</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="awaitingLoading"><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading cleared applications...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <nav id="awaitingPager" class="mt-2"></nav>
                        </div>

                        {{-- ══════════════ HISTORY TAB ══════════════ --}}
                        <div class="pg-tab" id="tabHistory" style="display:none;">
                            {{-- Bulk revert toolbar --}}
                            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap py-2 mb-3">
                                <div>
                                    <span class="font-weight-bold"><span id="histSelectedCount">0</span> selected</span>
                                    <span class="text-muted ml-2 small">Select to revert <strong>VC Approved</strong> applications back to <strong>Cleared</strong>.</span>
                                </div>
                                <div class="d-flex align-items-center flex-wrap">
                                    <button class="btn btn-outline-info btn-sm mr-2 mb-1" id="selectAllHistBtn" onclick="selectAllHistoryMatching()" title="Select every VC-approved application matching the current filters">
                                        <i class="fas fa-check-double mr-1"></i>Select All Matching
                                    </button>
                                    <input type="text" class="form-control form-control-sm mr-2 mb-1" id="revertRemarks" placeholder="Reason for revert (optional)" style="width:260px;">
                                    <button class="btn btn-warning btn-sm mb-1" id="bulkRevertBtn" onclick="confirmBulkRevert()" disabled>
                                        <i class="fas fa-undo mr-1"></i>Revert Selected
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped" id="historyTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="36"><input type="checkbox" id="histSelectAllCheck" onchange="toggleHistSelectAll(this)"></th>
                                            <th>App No</th>
                                            <th>Name</th>
                                            <th>Program</th>
                                            <th>Session</th>
                                            <th>Approved By</th>
                                            <th>Approved At</th>
                                            <th>Remarks</th>
                                            <th width="80">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="historyLoading"><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading history...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <nav id="historyPager" class="mt-2"></nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $pgUrl = url('/pg-admission');
    $pgCsrf = csrf_token();
@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var PG_BASE = '{{ $pgUrl }}';
    var PG_CSRF = '{{ $pgCsrf }}';
    var awaitingPage = 1;
    var historyPage = 1;
    var awaitingTotal = 0;
    var historyTotal = 0;
    var filterOptions = { faculties: [], departments: [], programs: [], levels: [], sessions: [], semesters: [] };

    // ══════════════════════════════════════════
    //                TABS
    // ══════════════════════════════════════════
    function switchPgTab(tabId, linkId) {
        $('.pg-tab').hide();
        $('#' + tabId).show();
        $('#pgTabs .nav-link').removeClass('active');
        $('#' + linkId).addClass('active');
        if (tabId === 'tabHistory') loadHistory();
        else loadAwaiting();
    }

    async function pgFetch(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['Accept'] = 'application/json';
        options.headers['X-CSRF-TOKEN'] = PG_CSRF;
        const res = await fetch(url, options);
        return res.json();
    }

    // ══════════════════════════════════════════
    //                FILTERS
    // ══════════════════════════════════════════
    async function loadFilters() {
        const data = await pgFetch(PG_BASE + '/filters');
        if (!data.success || !data.data) return;
        filterOptions = data.data;

        let facOpts = '<option value="">All Faculties</option>';
        (filterOptions.faculties || []).forEach(function (f) { facOpts += '<option value="' + f.id + '">' + f.name + '</option>'; });
        $('#fFaculty').html(facOpts);

        let lvlOpts = '<option value="">All Levels</option>';
        (filterOptions.levels || []).forEach(function (l) { lvlOpts += '<option value="' + l.id + '">' + l.name + '</option>'; });
        $('#fLevel').html(lvlOpts);

        let semOpts = '<option value="">All Semesters</option>';
        (filterOptions.semesters || []).forEach(function (s) { semOpts += '<option value="' + s.id + '">' + s.name + '</option>'; });
        $('#fSemester').html(semOpts);

        let sesOpts = '<option value="">All Sessions</option>';
        (filterOptions.sessions || []).forEach(function (s) { sesOpts += '<option value="' + s.id + '">' + s.name + '</option>'; });
        $('#fSession').html(sesOpts);
    }

    function onFacultyChange() {
        const fid = $('#fFaculty').val();
        let deptOpts = '<option value="">All Departments</option>';
        (filterOptions.departments || []).forEach(function (d) {
            if (!fid || d.faculty_id === fid) deptOpts += '<option value="' + d.id + '">' + d.name + '</option>';
        });
        $('#fDepartment').html(deptOpts);
        $('#fProgram').html('<option value="">All Programs</option>');
    }

    function onDepartmentChange() {
        const fid = $('#fFaculty').val();
        const did = $('#fDepartment').val();
        let progOpts = '<option value="">All Programs</option>';
        (filterOptions.programs || []).forEach(function (p) {
            const inDept = !did || p.department_id === did;
            const dept = (filterOptions.departments || []).find(function (d) { return d.id === p.department_id; });
            const inFac = !fid || (dept && dept.faculty_id === fid);
            if (inDept && inFac) progOpts += '<option value="' + p.id + '">' + p.name + (p.award ? ' (' + p.award + ')' : '') + '</option>';
        });
        $('#fProgram').html(progOpts);
    }

    function resetFilters() {
        $('#fFaculty').val('');
        $('#fDepartment').html('<option value="">All Departments</option>');
        $('#fProgram').html('<option value="">All Programs</option>');
        $('#fLevel').val('');
        $('#fSemester').val('');
        $('#fSession').val('');
        $('#fStatus').val('cleared');
        $('#fSearch').val('');
        applyFilters();
    }

    function getFilterParams() {
        var p = {};
        if ($('#fFaculty').val()) p.faculty_id = $('#fFaculty').val();
        if ($('#fDepartment').val()) p.department_id = $('#fDepartment').val();
        if ($('#fProgram').val()) p.program_id = $('#fProgram').val();
        if ($('#fLevel').val()) p.level_id = $('#fLevel').val();
        if ($('#fSemester').val()) p.academic_semester_id = $('#fSemester').val();
        if ($('#fSession').val()) p.academic_session_id = $('#fSession').val();
        if ($('#fSearch').val()) p.search = $('#fSearch').val();
        return p;
    }

    function applyFilters() {
        awaitingPage = 1;
        historyPage = 1;
        loadAwaiting(1);
        loadHistory(1);
    }

    function buildUrl(base, params) {
        var qs = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');
        return base + (qs ? '?' + qs : '');
    }

    // ══════════════════════════════════════════
    //                AWAITING LIST
    // ══════════════════════════════════════════
    async function loadAwaiting(page) {
        if (page) awaitingPage = page;
        $('#awaitingTable tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading cleared applications...</td></tr>');
        var params = getFilterParams();
        params.status = $('#fStatus').val();
        params.page = awaitingPage;
        var data = await pgFetch(buildUrl(PG_BASE + '/applications', params));
        if (!data.success) { showApiError('awaitingTable', data, 8); return; }

        awaitingTotal = data.pagination.total;
        $('#awaitingBadge').text(data.pagination.total);
        var items = data.data || [];
        if (items.length === 0) {
            $('#awaitingTable tbody').html('<tr><td colspan="8" class="text-center text-muted py-4">No applications found matching the selected filters.</td></tr>');
        } else {
            var html = '';
            items.forEach(function (a) {
                var name = (a.full_name || 'N/A');
                var prog = a.program ? (a.program.name + (a.program.award ? ' (' + a.program.award + ')' : '')) : 'N/A';
                var statusBadge = statusBadgeHtml(a.status);
                html += '<tr data-id="' + a.id + '">' +
                    '<td><input type="checkbox" class="row-check" value="' + a.id + '" onchange="updateSelection()"></td>' +
                    '<td><small>' + (a.application_number || '-') + '</small></td>' +
                    '<td><strong>' + name + '</strong><br><small class="text-muted">' + (a.email || '') + '</small></td>' +
                    '<td><small>' + prog + '</small><br><small class="text-muted">' + (a.faculty || '') + ' / ' + (a.department || '') + '</small></td>' +
                    '<td><small>' + (a.level || '-') + '</small></td>' +
                    '<td><small>' + (a.academic_session || '-') + '</small></td>' +
                    '<td><small>' + (a.cleared_at || '-') + '</small></td>' +
                    '<td>' + statusBadge + '<button class="btn btn-success btn-sm btn-block mt-1" onclick="approveOne(\'' + a.id + '\')"><i class="fas fa-check mr-1"></i>Approve</button></td>' +
                    '</tr>';
            });
            $('#awaitingTable tbody').html(html);
        }
        renderPager('awaitingPager', data.pagination, function (p) { loadAwaiting(p); });
        updateSelection();
    }

    function statusBadgeHtml(status) {
        var map = {
            'draft': ['secondary', 'Draft'],
            'submitted': ['info', 'Submitted'],
            'under_review': ['warning', 'Under Review'],
            'recommended': ['warning', 'Recommended'],
            'cleared': ['primary', 'Cleared'],
            'vc_approved': ['success', 'VC Approved'],
            'approved': ['success', 'Approved'],
            'rejected': ['danger', 'Rejected']
        };
        var m = map[status] || ['secondary', status];
        return '<span class="badge badge-' + m[0] + ' mb-1">' + m[1] + '</span>';
    }

    // ══════════════════════════════════════════
    //                SELECTION (awaiting)
    // ══════════════════════════════════════════
    function toggleSelectAll(el) {
        $('.row-check').prop('checked', el.checked);
        updateSelection();
    }

    function updateSelection() {
        var n = $('.row-check:checked').length;
        $('#selectedCount').text(n);
        $('#bulkApproveBtn').prop('disabled', n === 0);
    }

    async function selectAllMatching() {
        var params = getFilterParams();
        params.status = $('#fStatus').val();
        params.all_ids = 1;
        var data = await pgFetch(buildUrl(PG_BASE + '/applications', params));
        if (!data.success || !data.data) { Swal.fire('Error', 'Could not load matching applications.', 'error'); return; }
        var ids = data.data;
        if (ids.length === 0) { Swal.fire('No Results', 'No applications match the current filters.', 'info'); return; }
        // Select on current page + remember full matching set
        $('#selectAllMatchingBtn').attr('data-ids', JSON.stringify(ids));
        $('.row-check').prop('checked', true);
        $('#selectedCount').text(ids.length + ' (all matching)');
        $('#bulkApproveBtn').prop('disabled', ids.length === 0);
        Swal.fire({
            icon: 'info',
            title: ids.length + ' matching',
            text: 'All applications matching the current filters are selected. Click "Approve Selected" to continue.',
            timer: 2500,
            showConfirmButton: false
        });
    }

    // ══════════════════════════════════════════
    //                BULK APPROVE (progress)
    // ══════════════════════════════════════════
    async function confirmBulkApprove() {
        var allIds = $('#selectAllMatchingBtn').attr('data-ids');
        var ids;
        if (allIds) {
            try { ids = JSON.parse(allIds); } catch (e) { ids = null; }
        }
        if (!ids || ids.length === 0) {
            ids = $('.row-check:checked').map(function () { return this.value; }).get();
        }
        if (ids.length === 0) return;
        var remarks = $('#bulkRemarks').val();

        var r = await Swal.fire({
            title: 'VC-Approval Confirmation',
            html: 'Approve <strong>' + ids.length + '</strong> PG application(s)?<br><small class="text-muted">Status will change from Cleared to VC Approved.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-check-double mr-1"></i>Yes, Approve',
            cancelButtonText: 'Cancel'
        });
        if (!r.isConfirmed) return;

        $('#progressPanel').show();
        $('#bulkApproveBtn').prop('disabled', true);
        $('#selectAllCheck').prop('checked', false);
        $('.row-check').prop('disabled', true);
        $('#selectAllMatchingBtn').removeAttr('data-ids');

        var chunkSize = 25;
        var processed = 0;
        var approvedTotal = 0;
        var skippedTotal = 0;
        var failed = [];

        for (var i = 0; i < ids.length; i += chunkSize) {
            var chunk = ids.slice(i, i + chunkSize);
            $('#progressLabel').text('Approving ' + (Math.min(i + chunk.length, ids.length)) + ' of ' + ids.length + '...');
            var res = await pgFetch(PG_BASE + '/bulk-approve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ application_ids: chunk, remarks: remarks })
            });

            if (res.success && res.data) {
                approvedTotal += res.data.approved || 0;
                skippedTotal += res.data.skipped || 0;
                if (res.data.results) {
                    res.data.results.forEach(function (x) { if (!x.success) failed.push(x.reason || 'Unknown'); });
                }
            } else {
                failed.push(res.message || 'Request failed');
            }

            processed += chunk.length;
            var pct = Math.round((processed / ids.length) * 100);
            $('#progressBar').css('width', pct + '%');
            $('#progressPercent').text(pct + '%');
            $('#progressDetail').text('Approved: ' + approvedTotal + ' | Skipped: ' + skippedTotal + ' | Failed: ' + failed.length);
        }

        $('#progressLabel').text('Completed');
        $('#progressBar').css('width', '100%');
        $('#progressPercent').text('100%');

        setTimeout(function () {
            $('#progressPanel').hide();
            $('#progressBar').css('width', '0%');
            $('.row-check').prop('disabled', false);
            $('#bulkRemarks').val('');
            loadAwaiting();
            loadOverview();
            loadHistory();
            Swal.fire({
                icon: approvedTotal > 0 ? 'success' : 'warning',
                title: 'Complete',
                html: 'Approved: <strong>' + approvedTotal + '</strong><br>Skipped: <strong>' + skippedTotal + '</strong><br>Failed: <strong>' + failed.length + '</strong>' +
                    (failed.length ? '<br><small class="text-danger">' + failed.slice(0, 3).join('<br>') + '</small>' : ''),
                timer: 4000,
                showConfirmButton: false
            });
        }, 600);
    }

    async function approveOne(id) {
        var remarks = $('#bulkRemarks').val() || '';
        var r = await Swal.fire({
            title: 'VC-Approval Confirmation',
            text: 'Approve this application? Status will change from Cleared to VC Approved.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Approve'
        });
        if (!r.isConfirmed) return;

        var res = await pgFetch(PG_BASE + '/bulk-approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ application_ids: [id], remarks: remarks })
        });

        if (res.success && res.data && res.data.approved > 0) {
            Swal.fire({ icon: 'success', title: 'Approved!', timer: 1500, showConfirmButton: false });
            loadAwaiting();
            loadOverview();
            loadHistory();
        } else {
            Swal.fire('Error', (res.data && res.data.results && res.data.results[0] && res.data.results[0].reason) || res.message || 'Could not approve application.', 'error');
        }
    }

    // ══════════════════════════════════════════
    //                HISTORY + REVERT
    // ══════════════════════════════════════════
    async function loadHistory(page) {
        if (page) historyPage = page;
        $('#historyTable tbody').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading history...</td></tr>');
        var params = getFilterParams();
        params.page = historyPage;
        var data = await pgFetch(buildUrl(PG_BASE + '/history', params));
        if (!data.success) { showApiError('historyTable', data, 9); return; }

        historyTotal = data.pagination.total;
        $('#historyBadge').text(data.pagination.total);
        var items = data.data || [];
        if (items.length === 0) {
            $('#historyTable tbody').html('<tr><td colspan="9" class="text-center text-muted py-4">No VC-approved applications matching the selected filters.</td></tr>');
        } else {
            var html = '';
            items.forEach(function (a) {
                var prog = a.program ? a.program.name : 'N/A';
                html += '<tr data-id="' + a.id + '">' +
                    '<td><input type="checkbox" class="hist-row-check" value="' + a.id + '" onchange="updateHistSelection()"></td>' +
                    '<td><small>' + (a.application_number || '-') + '</small></td>' +
                    '<td><strong>' + (a.full_name || 'N/A') + '</strong></td>' +
                    '<td><small>' + prog + '</small><br><small class="text-muted">' + (a.faculty || '') + ' / ' + (a.department || '') + '</small></td>' +
                    '<td><small>' + (a.academic_session || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_approved_by || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_approved_at || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_remarks || '-') + '</small></td>' +
                    '<td><button class="btn btn-warning btn-sm btn-block" onclick="revertOne(\'' + a.id + '\')"><i class="fas fa-undo mr-1"></i>Revert</button></td>' +
                    '</tr>';
            });
            $('#historyTable tbody').html(html);
        }
        renderPager('historyPager', data.pagination, function (p) { loadHistory(p); });
        updateHistSelection();
    }

    function toggleHistSelectAll(el) {
        $('.hist-row-check').prop('checked', el.checked);
        updateHistSelection();
    }

    function updateHistSelection() {
        var n = $('.hist-row-check:checked').length;
        $('#histSelectedCount').text(n);
        $('#bulkRevertBtn').prop('disabled', n === 0);
    }

    async function selectAllHistoryMatching() {
        var params = getFilterParams();
        params.all_ids = 1;
        var data = await pgFetch(buildUrl(PG_BASE + '/history', params));
        if (!data.success || !data.data) { Swal.fire('Error', 'Could not load matching history.', 'error'); return; }
        var ids = data.data;
        if (ids.length === 0) { Swal.fire('No Results', 'No VC-approved applications match the current filters.', 'info'); return; }
        $('#selectAllHistBtn').attr('data-ids', JSON.stringify(ids));
        $('.hist-row-check').prop('checked', true);
        $('#histSelectedCount').text(ids.length + ' (all matching)');
        $('#bulkRevertBtn').prop('disabled', ids.length === 0);
        Swal.fire({
            icon: 'info',
            title: ids.length + ' matching',
            text: 'All VC-approved applications matching the current filters are selected.',
            timer: 2500,
            showConfirmButton: false
        });
    }

    async function confirmBulkRevert() {
        var allIds = $('#selectAllHistBtn').attr('data-ids');
        var ids;
        if (allIds) {
            try { ids = JSON.parse(allIds); } catch (e) { ids = null; }
        }
        if (!ids || ids.length === 0) {
            ids = $('.hist-row-check:checked').map(function () { return this.value; }).get();
        }
        if (ids.length === 0) return;
        var remarks = $('#revertRemarks').val();

        var r = await Swal.fire({
            title: 'Revert Confirmation',
            html: 'Revert <strong>' + ids.length + '</strong> application(s) back to <strong>Cleared</strong>?<br><small class="text-muted">The VC approval will be undone and the application will return to the awaiting list.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffa21d',
            confirmButtonText: '<i class="fas fa-undo mr-1"></i>Yes, Revert'
        });
        if (!r.isConfirmed) return;

        $('#progressPanel').show();
        $('#bulkRevertBtn').prop('disabled', true);
        $('#histSelectAllCheck').prop('checked', false);
        $('.hist-row-check').prop('disabled', true);
        $('#selectAllHistBtn').removeAttr('data-ids');

        var chunkSize = 25;
        var processed = 0;
        var revertedTotal = 0;
        var skippedTotal = 0;
        var failed = [];

        for (var i = 0; i < ids.length; i += chunkSize) {
            var chunk = ids.slice(i, i + chunkSize);
            $('#progressLabel').text('Reverting ' + (Math.min(i + chunk.length, ids.length)) + ' of ' + ids.length + '...');
            var res = await pgFetch(PG_BASE + '/bulk-revert', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ application_ids: chunk, remarks: remarks })
            });

            if (res.success && res.data) {
                revertedTotal += res.data.reverted || 0;
                skippedTotal += res.data.skipped || 0;
                if (res.data.results) {
                    res.data.results.forEach(function (x) { if (!x.success) failed.push(x.reason || 'Unknown'); });
                }
            } else {
                failed.push(res.message || 'Request failed');
            }

            processed += chunk.length;
            var pct = Math.round((processed / ids.length) * 100);
            $('#progressBar').css('width', pct + '%');
            $('#progressPercent').text(pct + '%');
            $('#progressDetail').text('Reverted: ' + revertedTotal + ' | Skipped: ' + skippedTotal + ' | Failed: ' + failed.length);
        }

        $('#progressLabel').text('Completed');
        $('#progressBar').css('width', '100%');
        $('#progressPercent').text('100%');

        setTimeout(function () {
            $('#progressPanel').hide();
            $('#progressBar').css('width', '0%');
            $('.hist-row-check').prop('disabled', false);
            $('#revertRemarks').val('');
            loadHistory();
            loadAwaiting();
            loadOverview();
            Swal.fire({
                icon: revertedTotal > 0 ? 'success' : 'warning',
                title: 'Complete',
                html: 'Reverted: <strong>' + revertedTotal + '</strong><br>Skipped: <strong>' + skippedTotal + '</strong><br>Failed: <strong>' + failed.length + '</strong>' +
                    (failed.length ? '<br><small class="text-danger">' + failed.slice(0, 3).join('<br>') + '</small>' : ''),
                timer: 4000,
                showConfirmButton: false
            });
        }, 600);
    }

    async function revertOne(id) {
        var remarks = $('#revertRemarks').val() || '';
        var r = await Swal.fire({
            title: 'Revert Confirmation',
            text: 'Revert this application back to Cleared? The VC approval will be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffa21d',
            confirmButtonText: '<i class="fas fa-undo mr-1"></i>Revert'
        });
        if (!r.isConfirmed) return;

        var res = await pgFetch(PG_BASE + '/bulk-revert', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ application_ids: [id], remarks: remarks })
        });

        if (res.success && res.data && res.data.reverted > 0) {
            Swal.fire({ icon: 'success', title: 'Reverted!', timer: 1500, showConfirmButton: false });
            loadHistory();
            loadAwaiting();
            loadOverview();
        } else {
            Swal.fire('Error', (res.data && res.data.results && res.data.results[0] && res.data.results[0].reason) || res.message || 'Could not revert application.', 'error');
        }
    }

    // ══════════════════════════════════════════
    //                OVERVIEW
    // ══════════════════════════════════════════
    async function loadOverview() {
        var data = await pgFetch(PG_BASE + '/overview');
        if (!data.success || !data.data) return;
        var o = data.data;
        $('#statCleared').text(o.cleared || 0);
        $('#statVcApproved').text(o.vc_approved || 0);
        $('#statSubmitted').text(o.submitted || 0);
        $('#statRecommended').text(o.recommended || 0);
        $('#statApproved').text(o.approved || 0);
        $('#statRejected').text(o.rejected || 0);
        $('#awaitingBadge').text(o.cleared || 0);
        $('#historyBadge').text(o.vc_approved || 0);
    }

    // ══════════════════════════════════════════
    //                HELPERS
    // ══════════════════════════════════════════
    function showApiError(tableId, data, cols) {
        $('#' + tableId + ' tbody').html('<tr><td colspan="' + cols + '" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-1"></i>' + (data.message || 'Failed to load data') + '</td></tr>');
    }

    function renderPager(pagerId, pagination, cb) {
        var el = $('#' + pagerId);
        if (!pagination || pagination.last_page <= 1) { el.html(''); return; }
        var fn = 'pgPager_' + pagerId.replace(/\W/g, '');
        window[fn] = function (p) { cb(p); };
        var html = '<ul class="pagination pagination-sm justify-content-end mb-0">';
        for (var p = 1; p <= pagination.last_page; p++) {
            html += '<li class="page-item ' + (p === pagination.current_page ? 'active' : '') + '"><a class="page-link" href="javascript:void(0)" onclick="' + fn + '(' + p + ')">' + p + '</a></li>';
        }
        html += '</ul>';
        el.html(html);
    }

    $(document).ready(function () {
        loadFilters().then(function () { loadAwaiting(); loadHistory(); loadOverview(); });
    });
</script>
