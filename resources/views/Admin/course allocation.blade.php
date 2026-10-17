<style>
    .allocation-page-card{border:0;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(34,61,92,.08)}
    .allocation-page-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#0b78b7,#3ea1e4);color:#fff;padding:24px 26px}.allocation-page-heading h5{margin:4px 0;color:#fff;font-size:1.35rem;font-weight:700}.allocation-page-heading p{margin:0;color:rgba(255,255,255,.82);font-size:.82rem}.allocation-page-kicker{color:rgba(255,255,255,.78);font-size:.68rem;letter-spacing:.13em;font-weight:700}.allocation-page-count{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid rgba(255,255,255,.22);border-radius:999px;background:rgba(255,255,255,.15);font-size:.74rem;white-space:nowrap}
    .allocation-page-actions{display:flex;flex-wrap:wrap;gap:9px;padding:18px 26px;border-bottom:1px solid #edf1f5}.allocation-page-actions .btn{border-radius:9px;font-size:.77rem;font-weight:600}.allocation-filter{padding:20px 26px 24px;background:#fbfdff}.allocation-filter-intro{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.allocation-filter-intro strong{display:block;color:#253b53;font-size:.92rem}.allocation-filter-intro small{display:block;margin-top:3px;color:#8291a3}.allocation-clear-filter{color:#267eaf;text-decoration:none;font-size:.75rem;font-weight:600}.allocation-filter-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.allocation-filter-field label{display:block;margin:0 0 6px;color:#53677d;font-size:.72rem;font-weight:700}.allocation-filter-field .form-control{height:40px;border:1px solid #dce6ef;border-radius:9px;background:#fff;font-size:.78rem}.allocation-filter-submit{display:flex;align-items:end}.allocation-filter-submit .btn{width:100%;height:40px;border-radius:9px;font-size:.77rem;font-weight:700}.allocation-empty{text-align:center!important;padding:58px 15px!important;color:#8a99a9}.allocation-empty i{display:block;margin-bottom:12px;color:#9ac6df;font-size:2rem}.allocation-empty strong,.allocation-empty span{display:block}.allocation-empty strong{color:#42566d;font-size:.9rem}.allocation-empty span{margin-top:5px;font-size:.78rem}.allocation-role{display:inline-flex;padding:5px 9px;border-radius:999px;background:#e4f6ec;color:#197447;font-size:.68rem;font-weight:700}.allocation-role.support{background:#e9f1ff;color:#2d6195}.allocation-pagination{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:18px;padding-top:15px;border-top:1px solid #edf1f5}.allocation-pagination-summary{font-size:.76rem;color:#7c8c9e}.allocation-pagination .pagination{margin:0}.allocation-pagination .page-link{margin-left:4px;border:1px solid #dbe6ef;border-radius:7px!important;color:#347da5;font-size:.75rem}.allocation-pagination .page-item.active .page-link{border-color:#3ea1e4;background:#3ea1e4;color:#fff}
    .allocation-modal .modal-content{border:0;border-radius:16px;overflow:hidden}.allocation-modal .modal-header{border:0;background:#f4f9fc;padding:18px 20px}.allocation-modal .modal-title{color:#25445c;font-size:1rem;font-weight:700}.allocation-modal .card{border:0;box-shadow:none}.allocation-modal .card-body{max-height:calc(100vh - 170px);overflow-y:auto;padding:20px}.allocation-modal .form-group{margin-bottom:15px}.allocation-modal label{display:block;margin-bottom:6px;color:#53677d;font-size:.76rem;font-weight:700}.allocation-modal .form-control{border-radius:9px;border-color:#dce6ef;min-height:40px}.allocation-modal .form-hint{display:block;margin-top:5px;color:#8494a4;font-size:.72rem;line-height:1.45}.allocation-modal .allocation-form-actions{position:sticky;bottom:-20px;display:flex;justify-content:flex-end;gap:8px;margin:20px -20px -20px;padding:14px 20px;background:rgba(255,255,255,.96);border-top:1px solid #edf1f5}.allocation-modal .allocation-program-list{padding:10px 12px;border:1px solid #dce6ef;border-radius:9px;background:#fbfdff;max-height:190px;overflow-y:auto}.allocation-modal .allocation-program-list:empty:before{content:'Select a course to load its linked programmes.';display:block;color:#8494a4;font-size:.75rem}.allocation-modal .allocation-program-list input{margin-right:6px}.allocation-modal .allocation-program-list label{display:inline;font-weight:500;color:#42566d}.allocation-modal .course-results{position:relative;z-index:3;max-height:190px;overflow-y:auto;border:1px solid #dce6ef;border-radius:9px;background:#fff}.allocation-modal .course-results .list-group-item{border:0;border-bottom:1px solid #edf1f5;font-size:.78rem}.allocation-modal .course-results .list-group-item:last-child{border-bottom:0}
    .allocation-table-scroll{position:relative;overflow-x:auto;-webkit-overflow-scrolling:touch}.allocation-table-scroll #course-allocation-table{min-width:1340px;table-layout:fixed;margin-bottom:0}.allocation-table-scroll #course-allocation-table th,.allocation-table-scroll #course-allocation-table td{vertical-align:middle}.allocation-table-scroll #course-allocation-table th:nth-child(1),.allocation-table-scroll #course-allocation-table td:nth-child(1){width:48px}.allocation-table-scroll #course-allocation-table th:nth-child(2),.allocation-table-scroll #course-allocation-table td:nth-child(2){width:150px}.allocation-table-scroll #course-allocation-table th:nth-child(3),.allocation-table-scroll #course-allocation-table td:nth-child(3){width:180px}.allocation-table-scroll #course-allocation-table th:nth-child(4),.allocation-table-scroll #course-allocation-table td:nth-child(4),.allocation-table-scroll #course-allocation-table th:nth-child(5),.allocation-table-scroll #course-allocation-table td:nth-child(5),.allocation-table-scroll #course-allocation-table th:nth-child(6),.allocation-table-scroll #course-allocation-table td:nth-child(6){width:210px;white-space:normal;overflow-wrap:anywhere;word-break:break-word;line-height:1.3}.allocation-table-scroll #course-allocation-table th:nth-child(7),.allocation-table-scroll #course-allocation-table td:nth-child(7){width:130px}.allocation-table-scroll #course-allocation-table th:nth-child(8),.allocation-table-scroll #course-allocation-table td:nth-child(8){width:95px}.allocation-table-scroll #course-allocation-table th:last-child,.allocation-table-scroll #course-allocation-table td:last-child{position:sticky;right:0;width:110px;min-width:110px;white-space:nowrap;background:#fff;box-shadow:-7px 0 14px rgba(37,68,92,.11);z-index:2}.allocation-table-scroll #course-allocation-table th:last-child{z-index:4;background:#f4f8fb}.allocation-table-scroll #course-allocation-table tbody tr:hover td:last-child{background:#f7fbff}.allocation-actions{display:flex;align-items:center;justify-content:flex-start;gap:6px;white-space:nowrap}.allocation-actions .btn{margin:0}
    .allocation-table-scroll #course-allocation-table th:nth-child(2),.allocation-table-scroll #course-allocation-table td:nth-child(2),.allocation-table-scroll #course-allocation-table th:nth-child(3),.allocation-table-scroll #course-allocation-table td:nth-child(3){white-space:normal;overflow-wrap:anywhere;line-height:1.3}
    @media(max-width:992px){.allocation-filter-form{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:680px){.allocation-page-heading{align-items:flex-start;flex-direction:column;padding:20px}.allocation-page-actions,.allocation-filter{padding-right:17px;padding-left:17px}.allocation-page-actions .btn{flex:1 1 calc(50% - 9px);min-height:39px}.allocation-filter-form{grid-template-columns:1fr;gap:11px}.allocation-filter-submit{margin-top:3px}.allocation-pagination{align-items:flex-start;flex-direction:column}.allocation-pagination>div:last-child{max-width:100%;overflow:auto}.allocation-pagination .pagination{flex-wrap:nowrap}}
</style>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card allocation-page-card">
                    <div class="card-header allocation-page-heading"><div><span class="allocation-page-kicker">ACADEMIC WORKLOAD</span><h5>Course allocation</h5><p>Assign course coordinators and lecturers for each academic session.</p></div><span class="allocation-page-count"><i class="fas fa-users-gear"></i> {{ number_format($data->total()) }} matching</span></div>
                    <div class="card-block allocation-page-actions"><a href="#" class="btn btn-primary createAction" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> Add allocation</a><button type="button" class="btn btn-dark uploadAction" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> Import allocations</button></div>
                    <div class="card-block allocation-filter">
                        <div class="allocation-filter-intro"><div><strong><i class="fas fa-filter"></i> Find allocations</strong><small>Filter by academic entity, staff member, course, role, or session.</small></div><a href="{{ url('/course allocation') }}" class="allocation-clear-filter">Clear all</a></div>
                        <form method="GET" action="{{ url('/course allocation') }}" class="allocation-filter-form">
                            <div class="allocation-filter-field"><label for="allocationSearch">Search</label><input class="form-control" id="allocationSearch" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Staff ID, name, course code or title"></div>
                            @if ($isAdminAllocation)
                                <div class="allocation-filter-field"><label for="facultyf">Faculty / college</label><select class="form-control faculty" lang="f" id="facultyf" name="faculty"><option value="">All faculties</option>@foreach ($faculty as $facultyOption)<option value="{{ $facultyOption->code }}" {{ ($filters['faculty'] ?? '') === $facultyOption->code ? 'selected' : '' }}>{{ $facultyOption->title }}</option>@endforeach</select></div>
                                <div class="allocation-filter-field"><label for="departmentf">Department</label><select class="form-control department" lang="f" id="departmentf" name="department"><option value="">{{ ($filters['faculty'] ?? '') !== '' ? 'All departments' : 'Select faculty first' }}</option>@foreach (($filterDepartments ?? collect()) as $departmentOption)<option value="{{ $departmentOption->code }}" {{ ($filters['department'] ?? '') === $departmentOption->code ? 'selected' : '' }}>{{ $departmentOption->title }}</option>@endforeach</select></div>
                                <div class="allocation-filter-field"><label for="programf">Programme</label><select class="form-control program" lang="f" id="programf" name="program"><option value="">{{ ($filters['department'] ?? '') !== '' ? 'All programmes' : 'Select department first' }}</option>@foreach (($filterPrograms ?? collect()) as $programOption)<option value="{{ $programOption->code }}" {{ ($filters['program'] ?? '') === $programOption->code ? 'selected' : '' }}>{{ $programOption->title }}</option>@endforeach</select></div>
                                <div class="allocation-filter-field"><label for="allocationStaff">Staff ID</label><input class="form-control" id="allocationStaff" name="username" value="{{ $filters['username'] ?? '' }}" placeholder="e.g. SP12345"></div>
                            @endif
                            <div class="allocation-filter-field"><label for="allocationCourse">Course code</label><input class="form-control" id="allocationCourse" name="course" value="{{ $filters['course'] ?? '' }}" placeholder="e.g. ACC101"></div>
                            <div class="allocation-filter-field"><label for="allocationType">Assignment role</label><select class="form-control" id="allocationType" name="type"><option value="">All roles</option><option value="MAIN" {{ ($filters['type'] ?? '') === 'MAIN' ? 'selected' : '' }}>Course coordinator</option><option value="SUPPORT" {{ ($filters['type'] ?? '') === 'SUPPORT' ? 'selected' : '' }}>Course lecturer</option></select></div>
                            <div class="allocation-filter-field"><label for="allocationSession">Academic session</label><select class="form-control" id="allocationSession" name="session"><option value="">All sessions</option>@foreach (($sessions ?? collect()) as $sessionOption)<option value="{{ $sessionOption->title }}" {{ ($filters['session'] ?? '') === $sessionOption->title ? 'selected' : '' }}>{{ $sessionOption->title }}</option>@endforeach</select></div>
                            <div class="allocation-filter-field"><label for="allocationPageSize">Rows per page</label><select class="form-control" id="allocationPageSize" name="per_page"><option value="25" {{ (int) ($perPage ?? 50) === 25 ? 'selected' : '' }}>25 rows</option><option value="50" {{ (int) ($perPage ?? 50) === 50 ? 'selected' : '' }}>50 rows</option><option value="100" {{ (int) ($perPage ?? 50) === 100 ? 'selected' : '' }}>100 rows</option></select></div>
                            <div class="allocation-filter-submit"><button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Find allocations</button></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card allocation-page-card"><div class="card-block">
                    <div class="allocation-table-scroll"><table id="course-allocation-table" class="table table-striped table-hover align-middle" style="width:100%"><thead><tr><th>#</th><th>Staff member</th><th>Course</th><th>Faculty / college</th><th>Department</th><th>Programme</th><th>Role</th><th>Session</th><th>Action</th></tr></thead><tbody>
                                    @php $sn = $data->firstItem() ?? 1; @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td><strong>{{ $row->username }}</strong><small class="d-block text-muted">{{ $row->name ?: 'Staff name unavailable' }}</small></td>
                                            <td><strong>{{ $row->course }}</strong><small class="d-block text-muted">{{ $row->course_title ?: 'Course title unavailable' }}</small></td>
                                            <td>{{ $row->faculty_title ?: $row->faculty ?: '—' }}</td>
                                            <td>{{ $row->department_title ?: $row->department ?: '—' }}</td>
                                            <td>{{ $row->program_title ?: $row->program ?: '—' }}</td>
                                            <td><span class="allocation-role {{ strtoupper((string) $row->type) === 'SUPPORT' ? 'support' : '' }}">{{ strtoupper((string) $row->type) === 'MAIN' ? 'Course coordinator' : 'Course lecturer' }}</span></td>
                                            <td>{{ $row->session }}</td>
                                            <td><div class="allocation-actions">
                                                <a href="#" class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div></td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade allocation-modal" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm modal-dialog-scrollable" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Edit course allocation</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="{{ url('update ' . $page) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <input type="hidden" name="course"
                                                                    value="{{ $row->course }}">
                                                                <div class="form-group">
                                                                    <label for="username">Staff ID</label>
                                                                    <input type="text" name="username"
                                                                        value="{{ $row->username }}" id="username"
                                                                        class="form-control"
                                                                        placeholder="For example, SP12345"
                                                                        required>
                                                                    <small class="form-hint">The staff member is checked before the update is saved.</small>
                                                                    {{-- <select class="form-control" name="username"
                                                                        id="username" required>
                                                                        <option value="{{ $row->username }}">
                                                                            Selected: {{ $row->username }}</option>
                                                                        @foreach ($staff as $staffs)
                                                                            <option value="{{ $staffs->username }}">
                                                                                {{ $staffs->username }}:
                                                                                {{ $staffs->name }}</option>
                                                                        @endforeach
                                                                    </select> --}}
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="allocationCourse{{ $row->id }}">Course</label>
                                                                    <input type="text" id="allocationCourse{{ $row->id }}" class="form-control" value="{{ $row->course }}: {{ $row->course_title ?: 'Course title unavailable' }}" readonly>
                                                                    <small class="form-hint">The course and academic session are kept unchanged when editing an allocation.</small>
                                                                </div>
                                                                {{-- <div class="form-group">
                                                                    <label for="course">Courses</label>
                                                                    <select class="form-control" name="course"
                                                                        id="course" required>
                                                                        <option value="{{ $row->course }}">Selected:
                                                                            {{ $row->course }}</option>
                                                                        @foreach ($courses as $course)
                                                                            <option value="{{ $course->code }}">
                                                                                {{ $course->code }}:
                                                                                {{ $course->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div> --}}
                                                                <div class="form-group">
                                                                    <label for="type">Assignment role</label>
                                                                    <select name="type" id="type"
                                                                        class="form-control" required>
                                                                        <option value="MAIN" {{ strtoupper((string) $row->type) === 'MAIN' ? 'selected' : '' }}>Course Coordinator</option>
                                                                        <option value="SUPPORT" {{ strtoupper((string) $row->type) === 'SUPPORT' ? 'selected' : '' }}>Course Lecturer</option>
                                                                    </select>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <div class="allocation-form-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save changes</button></div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="delete{{ $row->id }}" class="modal fade allocation-modal" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm modal-dialog-scrollable" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Remove course allocation</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Remove this allocation?</h4>
                                                            <p class="text-muted mb-0">{{ $row->course }} assigned to {{ $row->username }} for {{ $row->session }} will be removed.</p>
                                                        </div>
                                                        <form class="form-group" action="{{ url('delete ' . $page) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep allocation</button>
                                                                <button type="submit" class="btn btn-danger">Remove</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="9" class="allocation-empty"><i class="fas fa-users-slash"></i><strong>No allocations found</strong><span>Try another staff member, course, department, role, or session.</span></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="allocation-pagination"><div class="allocation-pagination-summary">Showing {{ $data->firstItem() ?? 0 }}-{{ $data->lastItem() ?? 0 }} of {{ number_format($data->total()) }} allocations</div>@if ($data->hasPages())<div>{{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}</div>@endif</div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const createForm = document.querySelector('#create form');
    const courseSelect = document.getElementById('course');
    const courseSearch = document.getElementById('courseSearch');
    const programmeList = document.getElementById('displayPrograms');
    if (createForm && courseSelect && courseSearch) {
        courseSearch.addEventListener('input', function () {
            this.setCustomValidity(courseSelect.value ? '' : 'Choose a course from the suggestions.');
        });
        createForm.addEventListener('submit', function (event) {
            if (!courseSelect.value) {
                event.preventDefault();
                courseSearch.setCustomValidity('Choose a course from the suggestions.');
                courseSearch.reportValidity();
                return;
            }
            if (!programmeList || !programmeList.querySelector('input[name="programs[]"]:checked')) {
                event.preventDefault();
                window.alert('Select at least one programme linked to this course.');
                return;
            }
            const submit = createForm.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
            }
        });
    }
    document.querySelectorAll('.allocation-modal form').forEach(function (form) {
        if (form === createForm || form.closest('#import')) return;
        form.addEventListener('submit', function () {
            const submit = form.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
            }
        });
    });
});
</script>
<!-- End Content-->
<!-- Show modal content -->
<div id="import" class="modal fade allocation-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Import course allocations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="{{ url('upload ' . $page) }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file">Allocation spreadsheet</label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control" required>
                            <small class="form-hint">Use the official allocation template. Existing assignments are checked before saving.</small>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Import allocations</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<!-- Show modal content -->
<div id="create" class="modal fade allocation-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Add course allocation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="{{ url('create ' . $page) }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="username">Staff ID</label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="For example, SP12345" autocomplete="off" required>
                            <small class="form-hint">Enter the official staff ID. The staff name is verified automatically before saving.</small>
                            {{-- <select class="form-control searchable-select" name="username" id="username" required>
                                <option value="">Select Option</option>
                                @foreach ($staff->where('department', $dept) as $staff)
                                    <option value="{{ $staff->username }}">{{ $staff->username }}:
                                        {{ $staff->name }}</option>
                                @endforeach
                            </select> --}}
                        </div>
                        <div class="form-group">
                            <label for="courseSearch">Course</label>
                            <input type="text" class="form-control" id="courseSearch" placeholder="Search by course code or title" autocomplete="off" required>
                            <small class="form-hint">Choose a course from the suggestions so its linked programmes can be loaded.</small>
                        </div>
                        <div class="form-group" style="position: relative;">
                            <select class="form-control getAllocationPrograms" name="course" id="course" required style="position:absolute;left:-10000px;width:1px;height:1px;opacity:0;">
                                <option value="">Select Option</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->code }}">{{ $course->code }}: {{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="list-group course-results" id="courseResults" style="display: none;"></div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var courseSearch = document.getElementById('courseSearch');
                            var courseSelect = document.getElementById('course');
                            var courseResults = document.getElementById('courseResults');
                            var options = courseSelect.getElementsByTagName('option');
                            
                            // Store original options for reference
                            var originalOptions = [];
                            for (var i = 0; i < options.length; i++) {
                                originalOptions.push({
                                    value: options[i].value,
                                    text: options[i].text
                                });
                            }
                            
                            // Function to update results
                            function updateResults(filter) {
                                // Clear previous results
                                courseResults.innerHTML = '';
                                
                                // Convert filter to lowercase for case-insensitive matching
                                filter = filter.toLowerCase().trim();
                                
                                // If empty filter, hide results
                                if (filter === '') {
                                    courseResults.style.display = 'none';
                                    return;
                                }
                                
                                // Count matches
                                var matchCount = 0;
                                
                                // Add filtered options to results
                                for (var i = 0; i < originalOptions.length; i++) {
                                    // Skip the empty option
                                    if (originalOptions[i].value === "") continue;
                                    
                                    // Check if the text contains the search term
                                    if (originalOptions[i].text.toLowerCase().indexOf(filter) > -1) {
                                        var item = document.createElement('a');
                                        item.href = '#';
                                        item.className = 'list-group-item list-group-item-action';
                                        item.textContent = originalOptions[i].text;
                                        item.dataset.value = originalOptions[i].value;
                                        
                                        item.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            
                                            // Set the visible text
                                            courseSearch.value = this.textContent;
                                            
                                            // Update the hidden select value
                                            courseSelect.value = this.dataset.value;
                                            
                                            // Hide results
                                            courseResults.style.display = 'none';
                                            
                                            // Trigger change event for jQuery event handlers
                                            if (typeof jQuery !== 'undefined') {
                                                jQuery(courseSelect).trigger('change');
                                            } else {
                                                // Fallback to standard event
                                                var event = new Event('change');
                                                courseSelect.dispatchEvent(event);
                                            }
                                        });
                                        
                                        courseResults.appendChild(item);
                                        matchCount++;
                                    }
                                }
                                
                                // Show results if we have matches
                                if (matchCount > 0) {
                                    courseResults.style.display = 'block';
                                } else {
                                    courseResults.style.display = 'none';
                                }
                            }
                            
                            // Handle input events
                            courseSearch.addEventListener('input', function() {
                                updateResults(this.value);
                            });
                            
                            // Hide results when clicking outside
                            document.addEventListener('click', function(e) {
                                if (e.target !== courseSearch && e.target !== courseResults) {
                                    courseResults.style.display = 'none';
                                }
                            });
                            
                            // Show results when focusing on search
                            courseSearch.addEventListener('focus', function() {
                                if (this.value.trim() !== '') {
                                    updateResults(this.value);
                                }
                            });
                        });
                        </script>
                        <div class="form-group">
                            <label for="type">Assignment role</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="MAIN">Course Coordinator</option>
                                <option value="SUPPORT">Course Lecturer</option>
                            </select>
                        </div>
                        {{-- session --}}
                        <div class="form-group">
                            <label for="session">Session</label>
                            <select name="session" id="session" class="form-control" required>
                                <option value="">Select Option</option>
                                @foreach (($sessions ?? collect()) as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ (($filters['session'] ?? '') ?: ($currentSession ?? '')) === $sessionOption->title ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="displayPrograms">Programmes covered by this allocation</label>
                            <small class="form-hint">Select all programmes whose students will take this course.</small>
                            <div id="displayPrograms" class="allocation-program-list"></div>
                        </div>
                        <!-- Details View End -->
                        <div class="allocation-form-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save allocation</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
