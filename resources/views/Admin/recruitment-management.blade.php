@php
    $statistics = $statistics ?? [];
@endphp
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Recruitment Portal Management</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="/recruitment">Recruitment</a></li>
                        <li class="breadcrumb-item active">Portal Management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-body">
        <div class="page-wrapper">
            <div class="row">
                <div class="col-sm-12">

                    {{-- ── Stats Cards ── --}}
                    <div class="row mb-3">
                        <div class="col-6 col-md-3">
                            <div class="card mb-0" style="border-left:4px solid #4680ff;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-sitemap fa-2x" style="color:#4680ff;"></i></div>
                                    <div><h4 class="mb-0" id="statSections">{{ $statistics['sections_count'] ?? 0 }}</h4><small class="text-muted">Sections</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card mb-0" style="border-left:4px solid #28a745;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-building fa-2x" style="color:#28a745;"></i></div>
                                    <div><h4 class="mb-0" id="statDepts">{{ $statistics['departments_count'] ?? 0 }}</h4><small class="text-muted">Departments</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card mb-0" style="border-left:4px solid #17a2b8;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-briefcase fa-2x" style="color:#17a2b8;"></i></div>
                                    <div><h4 class="mb-0" id="statJobs">{{ $statistics['jobs_count'] ?? 0 }}</h4><small class="text-muted">Jobs ({{ $statistics['open_jobs_count'] ?? 0 }} Open)</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card mb-0" style="border-left:4px solid #ffa21d;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-file-alt fa-2x" style="color:#ffa21d;"></i></div>
                                    <div><h4 class="mb-0" id="statApps">{{ $statistics['submitted_applications_count'] ?? 0 }}</h4><small class="text-muted">Submitted ({{ $statistics['applicants_count'] ?? 0 }} Applicants)</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tabs Navigation ── --}}
                    <div class="card">
                        <div class="card-header py-2">
                            <ul class="nav nav-tabs card-header-tabs" id="mgmtTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-sections-link" href="javascript:void(0)" onclick="switchTab('tabSections')">
                                        <i class="fas fa-sitemap mr-1"></i>Sections
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-departments-link" href="javascript:void(0)" onclick="switchTab('tabDepartments')">
                                        <i class="fas fa-building mr-1"></i>Departments
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-jobs-link" href="javascript:void(0)" onclick="switchTab('tabJobs')">
                                        <i class="fas fa-briefcase mr-1"></i>Jobs / Positions
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-block p-3">
                            {{-- ── Sections Tab ── --}}
                            <div class="mgmt-tab" id="tabSections">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0 d-inline">Faculties / Schools / Sections</h6>
                                        <small class="text-muted ml-2" id="sectionsCount"></small>
                                    </div>
                                    <div>
                                        <button class="btn btn-outline-secondary btn-sm mr-1" onclick="loadSections()"><i class="fas fa-sync-alt"></i></button>
                                        <button class="btn btn-primary btn-sm" onclick="showAddSection()"><i class="fas fa-plus mr-1"></i>Add Section</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped" id="sectionsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Staff Type</th>
                                                <th>Departments</th>
                                                <th width="130">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading sections...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ── Departments Tab ── --}}
                            <div class="mgmt-tab" id="tabDepartments" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0 d-inline">Departments</h6>
                                        <small class="text-muted ml-2" id="deptsCount"></small>
                                    </div>
                                    <div>
                                        <select class="form-control form-control-sm d-inline-block mr-2" id="deptFilterSection" style="width:200px;" onchange="loadDepartments()">
                                            <option value="">All Sections</option>
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm mr-1" onclick="loadDepartments()"><i class="fas fa-sync-alt"></i></button>
                                        <button class="btn btn-primary btn-sm" onclick="showAddDepartment()"><i class="fas fa-plus mr-1"></i>Add Department</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped" id="departmentsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Department Name</th>
                                                <th>Section / Faculty</th>
                                                <th>Location</th>
                                                <th width="130">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading departments...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ── Jobs Tab ── --}}
                            <div class="mgmt-tab" id="tabJobs" style="display:none;">
                                {{-- ── Batch Rank Order Update ── --}}
                                <div class="alert alert-info mb-3" style="border-left:4px solid #17a2b8;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><i class="fas fa-layer-group mr-1"></i>Batch Rank Order Update</strong>
                                            <small class="text-muted ml-2">Update rank for all jobs with the same title</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <select class="form-control form-control-sm mr-2" id="batchJobTitle" style="width:250px;">
                                                <option value="">Select Job Title...</option>
                                            </select>
                                            <input type="number" class="form-control form-control-sm mr-2" id="batchRankOrder" min="1" max="99" placeholder="Rank (1=highest)" style="width:120px;">
                                            <button class="btn btn-primary btn-sm" onclick="batchUpdateRank()"><i class="fas fa-check mr-1"></i>Apply</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0 d-inline">Jobs / Positions</h6>
                                        <small class="text-muted ml-2" id="jobsCount"></small>
                                    </div>
                                    <div>
                                        <select class="form-control form-control-sm d-inline-block mr-2" id="jobFilterStatus" style="width:130px;" onchange="loadJobs()">
                                            <option value="">All Status</option>
                                            <option value="OPEN">Open</option>
                                            <option value="CLOSED">Closed</option>
                                            <option value="DRAFT">Draft</option>
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm mr-1" onclick="loadJobs()"><i class="fas fa-sync-alt"></i></button>
                                        <button class="btn btn-primary btn-sm" onclick="showAddJob()"><i class="fas fa-plus mr-1"></i>Add Job</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped" id="jobsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Department</th>
                                                <th>Staff Type</th>
                                                <th>Status</th>
                                                <th>Rank</th>
                                                <th width="130">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading jobs...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_KEY = '{{ config("app.recruitment_api_key", env("RECRUITMENT_API_KEY")) }}';
const API_BASE = 'https://employee.umstad.online/api/management';

let sectionsCache = [];
let departmentsCache = [];
let jobsCache = [];

// ── Tab Switching ──
function switchTab(tabId) {
    $('.mgmt-tab').hide();
    $('#' + tabId).show();
    $('#mgmtTabs .nav-link').removeClass('active');
    if (tabId === 'tabSections') $('#tab-sections-link').addClass('active');
    else if (tabId === 'tabDepartments') $('#tab-departments-link').addClass('active');
    else if (tabId === 'tabJobs') $('#tab-jobs-link').addClass('active');
}

// ── API Helpers ──
async function apiGet(endpoint) {
    try {
        const r = await fetch(API_BASE + '/' + endpoint, { headers: { 'X-API-Key': API_KEY, 'Accept': 'application/json' } });
        return await r.json();
    } catch(e) { console.error('API GET error:', e); return { success: false, message: 'Network error' }; }
}
async function apiPost(endpoint, data) {
    try {
        const r = await fetch(API_BASE + '/' + endpoint, { method: 'POST', headers: { 'X-API-Key': API_KEY, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        return await r.json();
    } catch(e) { return { success: false, message: 'Network error' }; }
}
async function apiPut(endpoint, data) {
    try {
        const r = await fetch(API_BASE + '/' + endpoint, { method: 'PUT', headers: { 'X-API-Key': API_KEY, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        return await r.json();
    } catch(e) { return { success: false, message: 'Network error' }; }
}
async function apiDelete(endpoint) {
    try {
        const r = await fetch(API_BASE + '/' + endpoint, { method: 'DELETE', headers: { 'X-API-Key': API_KEY, 'Accept': 'application/json' } });
        return await r.json();
    } catch(e) { return { success: false, message: 'Network error' }; }
}

// ══════════════════════════════════════════════
//                 SECTIONS
// ══════════════════════════════════════════════
async function loadSections() {
    $('#sectionsTable tbody').html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>');
    const res = await apiGet('sections');
    if (!res.success) { $('#sectionsTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>'); return; }
    sectionsCache = res.data;
    $('#sectionsCount').text('(' + res.data.length + ' records)');
    updateSectionFilter();

    if (res.data.length === 0) {
        $('#sectionsTable tbody').html('<tr><td colspan="5" class="text-center text-muted py-4">No sections found. Click "Add Section" to create one.</td></tr>');
        return;
    }

    let html = '';
    res.data.forEach((s, i) => {
        const escapedName = s.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += '<tr>' +
            '<td>' + (i+1) + '</td>' +
            '<td><strong>' + s.name + '</strong></td>' +
            '<td><span class="badge badge-' + (s.staff_type==='ACADEMIC'?'primary':'info') + '">' + s.staff_type.replace('_',' ') + '</span></td>' +
            '<td><span class="badge badge-light">' + (s.departments_count || '-') + '</span></td>' +
            '<td>' +
                '<button class="btn btn-outline-info btn-sm mr-1" onclick="editSection(\'' + s.id + '\',\'' + escapedName + '\',\'' + s.staff_type + '\')" title="Edit"><i class="fas fa-edit"></i></button>' +
                '<button class="btn btn-outline-danger btn-sm" onclick="deleteSection(\'' + s.id + '\')" title="Delete"><i class="fas fa-trash"></i></button>' +
            '</td></tr>';
    });
    $('#sectionsTable tbody').html(html);
}

function updateSectionFilter() {
    let opts = '<option value="">All Sections</option>';
    sectionsCache.forEach(s => { opts += '<option value="' + s.id + '">' + s.name + '</option>'; });
    $('#deptFilterSection').html(opts);
}

function showAddSection() {
    Swal.fire({
        title: 'Add New Section',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Section Name</label>' +
            '<input id="sName" class="form-control mb-3" placeholder="e.g. Faculty of Sciences">' +
            '<label class="small font-weight-bold">Staff Type</label>' +
            '<select id="sType" class="form-control"><option value="ACADEMIC">Academic</option><option value="NON_ACADEMIC">Non-Academic</option></select>' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-plus mr-1"></i>Create', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const name = document.getElementById('sName').value.trim();
            if (!name) { Swal.showValidationMessage('Section name is required'); return false; }
            const res = await apiPost('sections', { name, staff_type: document.getElementById('sType').value });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed to create'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadSections(); Swal.fire({icon:'success',title:'Created!',text:'Section added successfully',timer:1500,showConfirmButton:false}); } });
}

function editSection(id, name, type) {
    Swal.fire({
        title: 'Edit Section',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Section Name</label>' +
            '<input id="sName" class="form-control mb-3" value="' + name + '">' +
            '<label class="small font-weight-bold">Staff Type</label>' +
            '<select id="sType" class="form-control"><option value="ACADEMIC" ' + (type==='ACADEMIC'?'selected':'') + '>Academic</option><option value="NON_ACADEMIC" ' + (type==='NON_ACADEMIC'?'selected':'') + '>Non-Academic</option></select>' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-save mr-1"></i>Update', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const res = await apiPut('sections/' + id, { name: document.getElementById('sName').value.trim(), staff_type: document.getElementById('sType').value });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadSections(); Swal.fire({icon:'success',title:'Updated!',timer:1500,showConfirmButton:false}); } });
}

async function deleteSection(id) {
    const r = await Swal.fire({ title: 'Delete Section?', text: 'All departments under this section must be removed first.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: '<i class="fas fa-trash mr-1"></i>Delete' });
    if (r.isConfirmed) {
        const res = await apiDelete('sections/' + id);
        if (res.success) { loadSections(); Swal.fire({icon:'success',title:'Deleted!',timer:1500,showConfirmButton:false}); }
        else { Swal.fire('Cannot Delete', res.message || 'This section has departments. Remove them first.', 'error'); }
    }
}

// ══════════════════════════════════════════════
//                DEPARTMENTS
// ══════════════════════════════════════════════
async function loadDepartments() {
    $('#departmentsTable tbody').html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>');
    let endpoint = 'departments';
    const filterSection = $('#deptFilterSection').val();
    if (filterSection) endpoint += '?section_id=' + filterSection;

    const res = await apiGet(endpoint);
    if (!res.success) { $('#departmentsTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>'); return; }
    departmentsCache = res.data;
    $('#deptsCount').text('(' + res.data.length + ' records)');

    if (res.data.length === 0) {
        $('#departmentsTable tbody').html('<tr><td colspan="5" class="text-center text-muted py-4">No departments found.</td></tr>');
        return;
    }

    let html = '';
    res.data.forEach((d, i) => {
        const sectionName = d.section ? d.section.name : 'N/A';
        const escapedName = d.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const escapedLoc = d.location.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += '<tr>' +
            '<td>' + (i+1) + '</td>' +
            '<td><strong>' + d.name + '</strong></td>' +
            '<td><span class="text-muted">' + sectionName + '</span></td>' +
            '<td>' + d.location + '</td>' +
            '<td>' +
                '<button class="btn btn-outline-info btn-sm mr-1" onclick="editDepartment(\'' + d.id + '\',\'' + escapedName + '\',\'' + d.section_id + '\',\'' + escapedLoc + '\')" title="Edit"><i class="fas fa-edit"></i></button>' +
                '<button class="btn btn-outline-danger btn-sm" onclick="deleteDepartment(\'' + d.id + '\')" title="Delete"><i class="fas fa-trash"></i></button>' +
            '</td></tr>';
    });
    $('#departmentsTable tbody').html(html);
}

function getSectionOptions(selectedId) {
    return sectionsCache.map(function(s) {
        return '<option value="' + s.id + '" ' + (s.id===selectedId?'selected':'') + '>' + s.name + ' (' + s.staff_type.replace('_',' ') + ')</option>';
    }).join('');
}

function showAddDepartment() {
    Swal.fire({
        title: 'Add New Department',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Department Name</label>' +
            '<input id="dName" class="form-control mb-3" placeholder="e.g. Computer Science">' +
            '<label class="small font-weight-bold">Section / Faculty</label>' +
            '<select id="dSection" class="form-control mb-3">' + getSectionOptions('') + '</select>' +
            '<label class="small font-weight-bold">Location</label>' +
            '<input id="dLocation" class="form-control" value="University of Maiduguri">' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-plus mr-1"></i>Create', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const name = document.getElementById('dName').value.trim();
            if (!name) { Swal.showValidationMessage('Department name is required'); return false; }
            const res = await apiPost('departments', { name, section_id: document.getElementById('dSection').value, location: document.getElementById('dLocation').value.trim() || 'University of Maiduguri' });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed to create'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadDepartments(); Swal.fire({icon:'success',title:'Created!',text:'Department added',timer:1500,showConfirmButton:false}); } });
}

function editDepartment(id, name, sectionId, location) {
    Swal.fire({
        title: 'Edit Department',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Department Name</label>' +
            '<input id="dName" class="form-control mb-3" value="' + name + '">' +
            '<label class="small font-weight-bold">Section / Faculty</label>' +
            '<select id="dSection" class="form-control mb-3">' + getSectionOptions(sectionId) + '</select>' +
            '<label class="small font-weight-bold">Location</label>' +
            '<input id="dLocation" class="form-control" value="' + location + '">' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-save mr-1"></i>Update', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const res = await apiPut('departments/' + id, { name: document.getElementById('dName').value.trim(), section_id: document.getElementById('dSection').value, location: document.getElementById('dLocation').value.trim() });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadDepartments(); Swal.fire({icon:'success',title:'Updated!',timer:1500,showConfirmButton:false}); } });
}

async function deleteDepartment(id) {
    const r = await Swal.fire({ title: 'Delete Department?', text: 'All jobs under this department must be removed first.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: '<i class="fas fa-trash mr-1"></i>Delete' });
    if (r.isConfirmed) {
        const res = await apiDelete('departments/' + id);
        if (res.success) { loadDepartments(); Swal.fire({icon:'success',title:'Deleted!',timer:1500,showConfirmButton:false}); }
        else { Swal.fire('Cannot Delete', res.message || 'This department has jobs. Remove them first.', 'error'); }
    }
}

// ══════════════════════════════════════════════
//                    JOBS
// ══════════════════════════════════════════════
async function loadJobs() {
    $('#jobsTable tbody').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>');
    let endpoint = 'jobs';
    const filterStatus = $('#jobFilterStatus').val();
    if (filterStatus) endpoint += '?status=' + filterStatus;

    const res = await apiGet(endpoint);
    if (!res.success) { $('#jobsTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load</td></tr>'); return; }
    jobsCache = res.data;
    $('#jobsCount').text('(' + res.data.length + ' records)');

    // Populate batch job title dropdown with distinct titles
    populateBatchJobTitles();

    if (res.data.length === 0) {
        $('#jobsTable tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">No jobs found.</td></tr>');
        return;
    }

    const statusColors = { 'OPEN':'success','CLOSED':'danger','DRAFT':'secondary','FILLED':'primary','SHORTLISTING':'info','CANCELLED':'dark' };
    let html = '';
    res.data.forEach((j, i) => {
        const dept = j.department ? j.department.name : 'N/A';
        const section = (j.department && j.department.section) ? j.department.section.name : '';
        html += '<tr>' +
            '<td>' + (i+1) + '</td>' +
            '<td><strong>' + j.title + '</strong></td>' +
            '<td>' + dept + (section ? '<br><small class="text-muted">' + section + '</small>' : '') + '</td>' +
            '<td><span class="badge badge-' + (j.staff_type==='ACADEMIC'?'primary':'info') + '">' + j.staff_type.replace('_',' ') + '</span></td>' +
            '<td><span class="badge badge-' + (statusColors[j.status]||'secondary') + '">' + j.status + '</span></td>' +
            '<td><strong>' + j.rank_order + '</strong></td>' +
            '<td>' +
                '<button class="btn btn-outline-info btn-sm mr-1" onclick="editJob(\'' + j.id + '\')" title="Edit"><i class="fas fa-edit"></i></button>' +
                '<button class="btn btn-outline-danger btn-sm" onclick="deleteJob(\'' + j.id + '\')" title="Delete"><i class="fas fa-trash"></i></button>' +
            '</td></tr>';
    });
    $('#jobsTable tbody').html(html);
}

function getDeptOptions(selectedId) {
    return departmentsCache.map(function(d) {
        const sec = d.section ? ' (' + d.section.name + ')' : '';
        return '<option value="' + d.id + '" ' + (d.id===selectedId?'selected':'') + '>' + d.name + sec + '</option>';
    }).join('');
}

function populateBatchJobTitles() {
    const titles = [...new Set(jobsCache.map(j => j.title))].sort();
    let opts = '<option value="">Select Job Title...</option>';
    titles.forEach(t => {
        const count = jobsCache.filter(j => j.title === t).length;
        opts += '<option value="' + t + '">' + t + ' (' + count + ' jobs)</option>';
    });
    $('#batchJobTitle').html(opts);
}

async function batchUpdateRank() {
    const title = $('#batchJobTitle').val();
    const rank = parseInt($('#batchRankOrder').val());

    if (!title) { Swal.fire('Error','Please select a job title','error'); return; }
    if (!rank || rank < 1 || rank > 99) { Swal.fire('Error','Please enter a valid rank (1-99)','error'); return; }

    const r = await Swal.fire({
        title: 'Batch Update Rank Order?',
        text: 'Update all ' + jobsCache.filter(j => j.title === title).length + ' jobs with title "' + title + '" to rank ' + rank,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        confirmButtonText: '<i class="fas fa-check mr-1"></i>Update All'
    });

    if (r.isConfirmed) {
        const res = await apiPost('jobs/batch-rank', { title, rank_order: rank });
        if (res.success) {
            loadJobs();
            Swal.fire({icon:'success',title:res.message,timer:2000,showConfirmButton:false});
            $('#batchJobTitle').val('');
            $('#batchRankOrder').val('');
        } else {
            Swal.fire('Error', res.message || 'Failed to update', 'error');
        }
    }
}

function showAddJob() {
    Swal.fire({
        title: 'Add New Job / Position',
        width: '550px',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Job Title</label>' +
            '<input id="jTitle" class="form-control mb-2" placeholder="e.g. Professor, Lecturer I">' +
            '<label class="small font-weight-bold">Department</label>' +
            '<select id="jDept" class="form-control mb-2">' + getDeptOptions('') + '</select>' +
            '<div class="row"><div class="col-6">' +
            '<label class="small font-weight-bold">Staff Type</label>' +
            '<select id="jStaff" class="form-control mb-2"><option value="ACADEMIC">Academic</option><option value="NON_ACADEMIC">Non-Academic</option></select>' +
            '</div><div class="col-6">' +
            '<label class="small font-weight-bold">Status</label>' +
            '<select id="jStatus" class="form-control mb-2"><option value="OPEN">Open</option><option value="DRAFT">Draft</option><option value="CLOSED">Closed</option></select>' +
            '</div></div>' +
            '<label class="small font-weight-bold">Rank Order <small class="text-muted">(1=highest priority, used for sorting)</small></label>' +
            '<input id="jRank" class="form-control" type="number" min="1" max="99" value="99">' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-plus mr-1"></i>Create', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const title = document.getElementById('jTitle').value.trim();
            if (!title) { Swal.showValidationMessage('Job title is required'); return false; }
            const res = await apiPost('jobs', {
                title, department_id: document.getElementById('jDept').value,
                staff_type: document.getElementById('jStaff').value,
                status: document.getElementById('jStatus').value,
                rank_order: parseInt(document.getElementById('jRank').value) || 99
            });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed to create'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadJobs(); Swal.fire({icon:'success',title:'Created!',text:'Job added',timer:1500,showConfirmButton:false}); } });
}

async function editJob(id) {
    const job = jobsCache.find(j => j.id === id);
    if (!job) { Swal.fire('Error','Job not found','error'); return; }

    Swal.fire({
        title: 'Edit Job / Position',
        width: '550px',
        html: '<div class="text-left px-3">' +
            '<label class="small font-weight-bold">Job Title</label>' +
            '<input id="jTitle" class="form-control mb-2" value="' + job.title.replace(/"/g,'&quot;') + '">' +
            '<label class="small font-weight-bold">Department</label>' +
            '<select id="jDept" class="form-control mb-2">' + getDeptOptions(job.department_id) + '</select>' +
            '<div class="row"><div class="col-6">' +
            '<label class="small font-weight-bold">Staff Type</label>' +
            '<select id="jStaff" class="form-control mb-2"><option value="ACADEMIC" ' + (job.staff_type==='ACADEMIC'?'selected':'') + '>Academic</option><option value="NON_ACADEMIC" ' + (job.staff_type==='NON_ACADEMIC'?'selected':'') + '>Non-Academic</option></select>' +
            '</div><div class="col-6">' +
            '<label class="small font-weight-bold">Status</label>' +
            '<select id="jStatus" class="form-control mb-2"><option value="OPEN" ' + (job.status==='OPEN'?'selected':'') + '>Open</option><option value="DRAFT" ' + (job.status==='DRAFT'?'selected':'') + '>Draft</option><option value="CLOSED" ' + (job.status==='CLOSED'?'selected':'') + '>Closed</option><option value="FILLED" ' + (job.status==='FILLED'?'selected':'') + '>Filled</option><option value="CANCELLED" ' + (job.status==='CANCELLED'?'selected':'') + '>Cancelled</option></select>' +
            '</div></div>' +
            '<label class="small font-weight-bold">Rank Order</label>' +
            '<input id="jRank" class="form-control" type="number" min="1" max="99" value="' + job.rank_order + '">' +
            '</div>',
        showCancelButton: true, confirmButtonText: '<i class="fas fa-save mr-1"></i>Update', confirmButtonColor: '#4680ff',
        preConfirm: async () => {
            const res = await apiPut('jobs/' + id, {
                title: document.getElementById('jTitle').value.trim(),
                department_id: document.getElementById('jDept').value,
                staff_type: document.getElementById('jStaff').value,
                status: document.getElementById('jStatus').value,
                rank_order: parseInt(document.getElementById('jRank').value) || 99
            });
            if (!res.success) { Swal.showValidationMessage(res.message || 'Failed'); return false; }
            return res;
        }
    }).then(r => { if (r.isConfirmed) { loadJobs(); Swal.fire({icon:'success',title:'Updated!',timer:1500,showConfirmButton:false}); } });
}

async function deleteJob(id) {
    const r = await Swal.fire({ title: 'Delete Job?', text: 'Jobs with existing applications cannot be deleted.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: '<i class="fas fa-trash mr-1"></i>Delete' });
    if (r.isConfirmed) {
        const res = await apiDelete('jobs/' + id);
        if (res.success) { loadJobs(); Swal.fire({icon:'success',title:'Deleted!',timer:1500,showConfirmButton:false}); }
        else { Swal.fire('Cannot Delete', res.message || 'This job has applications and cannot be removed.', 'error'); }
    }
}

// ══════════════════════════════════════════════
//                    INIT
// ══════════════════════════════════════════════
$(document).ready(function() {
    loadSections();
    loadDepartments();
    loadJobs();
});
</script>
