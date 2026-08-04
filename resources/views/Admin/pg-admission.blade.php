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
                            <div class="row mb-3 gx-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="awaitingSearch" placeholder="Search by name, email or application number..." onkeyup="if(event.key==='Enter')loadAwaiting()">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary btn-block" onclick="loadAwaiting()"><i class="fas fa-search mr-1"></i>Search</button>
                                </div>
                                <div class="col-md-3 text-right ml-auto">
                                    <button class="btn btn-outline-secondary" onclick="loadAwaiting()"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
                                </div>
                            </div>

                            {{-- Bulk action toolbar --}}
                            <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap py-2 mb-3">
                                <div>
                                    <span class="font-weight-bold"><span id="selectedCount">0</span> selected</span>
                                    <span class="text-muted ml-2 small">Only <strong>Cleared</strong> applications can be VC-approved.</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input type="text" class="form-control form-control-sm mr-2" id="bulkRemarks" placeholder="Remarks (optional)" style="width:280px;">
                                    <button class="btn btn-success btn-sm" id="bulkApproveBtn" onclick="confirmBulkApprove()" disabled>
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
                                            <th width="90">Action</th>
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
                            <div class="row mb-3 gx-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="historySearch" placeholder="Search VC-approved applications..." onkeyup="if(event.key==='Enter')loadHistory()">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary btn-block" onclick="loadHistory()"><i class="fas fa-search mr-1"></i>Search</button>
                                </div>
                                <div class="col-md-3 text-right ml-auto">
                                    <button class="btn btn-outline-secondary" onclick="loadHistory()"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped" id="historyTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>App No</th>
                                            <th>Name</th>
                                            <th>Program</th>
                                            <th>Session</th>
                                            <th>Approved By</th>
                                            <th>Approved At</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="historyLoading"><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading history...</td></tr>
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
    //                AWAITING LIST
    // ══════════════════════════════════════════
    async function loadAwaiting(page) {
        if (page) awaitingPage = page;
        $('#awaitingTable tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading cleared applications...</td></tr>');
        const search = $('#awaitingSearch').val();
        const data = await pgFetch(PG_BASE + '/applications?status=cleared&page=' + awaitingPage + '&search=' + encodeURIComponent(search));
        if (!data.success) { showApiError('awaitingTable', data, 8); return; }

        awaitingTotal = data.pagination.total;
        const items = data.data || [];
        if (items.length === 0) {
            $('#awaitingTable tbody').html('<tr><td colspan="8" class="text-center text-muted py-4">No cleared applications awaiting VC approval.</td></tr>');
        } else {
            let html = '';
            items.forEach(function (a) {
                const name = (a.full_name || a.first_name + ' ' + a.last_name || 'N/A');
                const prog = a.program ? (a.program.name + (a.program.award ? ' (' + a.program.award + ')' : '')) : 'N/A';
                html += '<tr data-id="' + a.id + '">' +
                    '<td><input type="checkbox" class="row-check" value="' + a.id + '" onchange="updateSelection()"></td>' +
                    '<td><small>' + (a.application_number || '-') + '</small></td>' +
                    '<td><strong>' + name + '</strong><br><small class="text-muted">' + (a.email || '') + '</small></td>' +
                    '<td><small>' + prog + '</small><br><small class="text-muted">' + (a.department || '') + '</small></td>' +
                    '<td><small>' + (a.level || '-') + '</small></td>' +
                    '<td><small>' + (a.academic_session || '-') + '</small></td>' +
                    '<td><small>' + (a.cleared_at ? a.cleared_at.replace(' ', ' ') : '-') + '</small></td>' +
                    '<td><button class="btn btn-success btn-sm btn-block" onclick="approveOne(\'' + a.id + '\')"><i class="fas fa-check mr-1"></i>Approve</button></td>' +
                    '</tr>';
            });
            $('#awaitingTable tbody').html(html);
        }
        renderPager('awaitingPager', data.pagination, function (p) { loadAwaiting(p); });
        updateSelection();
    }

    // ══════════════════════════════════════════
    //                SELECTION
    // ══════════════════════════════════════════
    function toggleSelectAll(el) {
        $('.row-check').prop('checked', el.checked);
        updateSelection();
    }

    function updateSelection() {
        const n = $('.row-check:checked').length;
        $('#selectedCount').text(n);
        $('#bulkApproveBtn').prop('disabled', n === 0);
    }

    // ══════════════════════════════════════════
    //                BULK APPROVE (progress)
    // ══════════════════════════════════════════
    async function confirmBulkApprove() {
        const ids = $('.row-check:checked').map(function () { return this.value; }).get();
        if (ids.length === 0) return;
        const remarks = $('#bulkRemarks').val();

        const r = await Swal.fire({
            title: 'VC-Approval Confirmation',
            html: 'Approve <strong>' + ids.length + '</strong> cleared PG application(s)?<br><small class="text-muted">Status will change from Cleared to VC Approved.</small>',
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

        const chunkSize = 25;
        let processed = 0;
        let approvedTotal = 0;
        let skippedTotal = 0;
        let failed = [];

        for (let i = 0; i < ids.length; i += chunkSize) {
            const chunk = ids.slice(i, i + chunkSize);
            $('#progressLabel').text('Approving ' + (i + chunk.length) + ' of ' + ids.length + '...');
            const res = await pgFetch(PG_BASE + '/bulk-approve', {
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
            const pct = Math.round((processed / ids.length) * 100);
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
        const remarks = $('#bulkRemarks').val() || '';
        const r = await Swal.fire({
            title: 'VC-Approval Confirmation',
            text: 'Approve this application? Status will change from Cleared to VC Approved.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Approve'
        });
        if (!r.isConfirmed) return;

        const res = await pgFetch(PG_BASE + '/bulk-approve', {
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
    //                HISTORY
    // ══════════════════════════════════════════
    async function loadHistory(page) {
        if (page) historyPage = page;
        $('#historyTable tbody').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading history...</td></tr>');
        const search = $('#historySearch').val();
        const data = await pgFetch(PG_BASE + '/history?page=' + historyPage + '&search=' + encodeURIComponent(search));
        if (!data.success) { showApiError('historyTable', data, 7); return; }

        const items = data.data || [];
        if (items.length === 0) {
            $('#historyTable tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">No VC-approved applications yet.</td></tr>');
        } else {
            let html = '';
            items.forEach(function (a) {
                const prog = a.program ? a.program.name : 'N/A';
                html += '<tr>' +
                    '<td><small>' + (a.application_number || '-') + '</small></td>' +
                    '<td><strong>' + (a.full_name || 'N/A') + '</strong></td>' +
                    '<td><small>' + prog + '</small><br><small class="text-muted">' + (a.department || '') + '</small></td>' +
                    '<td><small>' + (a.academic_session || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_approved_by || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_approved_at || '-') + '</small></td>' +
                    '<td><small>' + (a.vc_remarks || '-') + '</small></td>' +
                    '</tr>';
            });
            $('#historyTable tbody').html(html);
        }
        renderPager('historyPager', data.pagination, function (p) { loadHistory(p); });
    }

    // ══════════════════════════════════════════
    //                OVERVIEW
    // ══════════════════════════════════════════
    async function loadOverview() {
        const data = await pgFetch(PG_BASE + '/overview');
        if (!data.success || !data.data) return;
        const o = data.data;
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
        const el = $('#' + pagerId);
        if (!pagination || pagination.last_page <= 1) { el.html(''); return; }
        const fn = 'pgPager_' + pagerId.replace(/\W/g, '');
        window[fn] = function (p) { cb(p); };
        let html = '<ul class="pagination pagination-sm justify-content-end mb-0">';
        for (let p = 1; p <= pagination.last_page; p++) {
            html += '<li class="page-item ' + (p === pagination.current_page ? 'active' : '') + '"><a class="page-link" href="javascript:void(0)" onclick="' + fn + '(' + p + ')">' + p + '</a></li>';
        }
        html += '</ul>';
        el.html(html);
    }

    $(document).ready(function () {
        loadAwaiting();
    });
</script>
