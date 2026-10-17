@php

    use Illuminate\Support\Facades\DB;
@endphp
<style>
    .course-page-card{border:0;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(34,61,92,.08)}
    .course-page-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#0b78b7,#3ea1e4);color:#fff;padding:24px 26px}
    .course-page-heading h5{display:block!important;position:static!important;margin:4px 0!important;margin-right:0!important;font-size:1.35rem;color:#fff;font-weight:700}.card .card-header.course-page-heading h5:after{display:none!important}.course-page-heading p{margin:0;color:rgba(255,255,255,.82);font-size:.82rem}.course-page-kicker{display:block;font-size:.68rem;letter-spacing:.13em;font-weight:700;color:rgba(255,255,255,.78)}
    .course-page-count{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid rgba(255,255,255,.22);border-radius:999px;background:rgba(255,255,255,.15);font-size:.74rem;white-space:nowrap}
    .course-table-scroll{position:relative;overflow-x:auto;-webkit-overflow-scrolling:touch}.course-table-scroll #course-table{min-width:1570px;table-layout:fixed;margin-bottom:0}.course-table-scroll #course-table th,.course-table-scroll #course-table td{vertical-align:middle}.course-table-scroll #course-table th:nth-child(1),.course-table-scroll #course-table td:nth-child(1){width:48px}.course-table-scroll #course-table th:nth-child(2),.course-table-scroll #course-table td:nth-child(2){width:190px;white-space:normal;overflow-wrap:anywhere;line-height:1.3}.course-table-scroll #course-table th:nth-child(3),.course-table-scroll #course-table td:nth-child(3){width:105px}.course-table-scroll #course-table th:nth-child(4),.course-table-scroll #course-table td:nth-child(4){width:115px}.course-table-scroll #course-table th:nth-child(5),.course-table-scroll #course-table td:nth-child(5){width:75px}.course-table-scroll #course-table th:nth-child(6),.course-table-scroll #course-table td:nth-child(6){width:80px}.course-table-scroll #course-table th:nth-child(7),.course-table-scroll #course-table td:nth-child(7){width:105px}.course-table-scroll #course-table th:nth-child(8),.course-table-scroll #course-table td:nth-child(8),.course-table-scroll #course-table th:nth-child(9),.course-table-scroll #course-table td:nth-child(9),.course-table-scroll #course-table th:nth-child(10),.course-table-scroll #course-table td:nth-child(10){width:210px;white-space:normal;overflow-wrap:anywhere;word-break:break-word;line-height:1.3}.course-table-scroll #course-table th:nth-child(11),.course-table-scroll #course-table td:nth-child(11){width:120px}.course-table-scroll #course-table th:last-child,.course-table-scroll #course-table td:last-child{position:sticky;right:0;width:110px;min-width:110px;white-space:nowrap;background:#fff;box-shadow:-7px 0 14px rgba(37,68,92,.11);z-index:2}.course-table-scroll #course-table th:last-child{z-index:4;background:#f4f8fb}.course-table-scroll #course-table tbody tr:hover td:last-child{background:#f7fbff}.course-actions{display:flex;align-items:center;gap:6px;white-space:nowrap}.course-actions .btn{margin:0}
    .course-modal .modal-content{border:0;border-radius:16px;overflow:hidden}.course-modal .modal-header{border:0;background:#f4f9fc;padding:18px 20px}.course-modal .modal-title{color:#25445c;font-size:1rem;font-weight:700}.course-modal .card{border:0;box-shadow:none}.course-modal .card-body{max-height:calc(100vh - 165px);overflow-y:auto;padding:20px}.course-modal .form-group{margin-bottom:15px}.course-modal label{display:block;margin-bottom:6px;color:#53677d;font-size:.76rem;font-weight:700}.course-modal .form-control{border-radius:9px;border-color:#dce6ef;min-height:40px}.course-modal .form-hint{display:block;margin-top:5px;color:#8494a4;font-size:.72rem;line-height:1.45}.course-modal .course-form-actions{position:sticky;bottom:-20px;display:flex;justify-content:flex-end;gap:8px;margin:20px -20px -20px;padding:14px 20px;background:rgba(255,255,255,.96);border-top:1px solid #edf1f5}.course-modal .modal-body-note{padding:0 20px 14px;color:#718398;font-size:.78rem;line-height:1.5}.course-modal .required-mark{color:#c44242}.course-modal .btn{border-radius:8px;font-size:.76rem;font-weight:600}
    .course-page-actions{display:flex;flex-wrap:wrap;gap:9px;padding:18px 26px;border-bottom:1px solid #edf1f5}.course-page-actions .btn{border-radius:9px;font-size:.77rem;font-weight:600}.course-page-filter{padding:20px 26px 24px;background:#fbfdff}.course-filter-intro{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.course-filter-intro strong{display:block;color:#253b53;font-size:.92rem}.course-filter-intro small{display:block;color:#8291a3;margin-top:3px}.course-clear-filter{color:#267eaf;text-decoration:none;font-size:.75rem;font-weight:600}.course-filter-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.course-filter-field label{display:block;margin:0 0 6px;color:#53677d;font-size:.72rem;font-weight:700}.course-filter-field .form-control{height:40px;border:1px solid #dce6ef;border-radius:9px;background:#fff;font-size:.78rem}.course-filter-submit{display:flex;align-items:end}.course-filter-submit .btn{width:100%;height:40px;border-radius:9px;font-size:.77rem;font-weight:700}.course-empty-state{text-align:center!important;padding:58px 15px!important;color:#8a99a9}.course-empty-state i{display:block;margin-bottom:12px;color:#9ac6df;font-size:2rem}.course-empty-state strong,.course-empty-state span{display:block}.course-empty-state strong{color:#42566d;font-size:.9rem}.course-empty-state span{margin-top:5px;font-size:.78rem}.course-status{display:inline-flex;padding:5px 9px;border-radius:999px;background:#e4f6ec;color:#197447;font-size:.68rem;font-weight:700}.course-status.inactive{background:#fff1e6;color:#a35314}.course-pagination{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:18px;padding-top:15px;border-top:1px solid #edf1f5}.course-pagination-summary{font-size:.76rem;color:#7c8c9e}.course-pagination .pagination{margin:0}.course-pagination .page-link{margin-left:4px;border:1px solid #dbe6ef;border-radius:7px!important;color:#347da5;font-size:.75rem}.course-pagination .page-item.active .page-link{border-color:#3ea1e4;background:#3ea1e4;color:#fff}
    @media (max-width:992px){.course-filter-form{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media (max-width:680px){.course-page-heading{align-items:flex-start;flex-direction:column;padding:20px}.course-page-actions,.course-page-filter{padding-right:17px;padding-left:17px}.course-page-actions .btn{flex:1 1 calc(50% - 9px);min-height:39px}.course-filter-form{grid-template-columns:1fr;gap:11px}.course-filter-submit{margin-top:3px}.course-pagination{align-items:flex-start;flex-direction:column}.course-pagination>div:last-child{max-width:100%;overflow:auto}.course-pagination .pagination{flex-wrap:nowrap}}
</style>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card course-page-card">
                    <div class="card-header course-page-heading">
                        <div><span class="course-page-kicker">ACADEMIC CATALOGUE</span><h5>Course catalogue</h5><p>Find, review, and manage courses by academic entity and structure.</p></div>
                        <span class="course-page-count"><i class="fas fa-book-open"></i> {{ number_format($data->total()) }} matching</span>
                    </div>
                    <div class="card-block course-page-actions">
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> Add course</a>
                        <button type="button" class="btn btn-dark uploadAction" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> Import courses</button>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportStudent"><i class="fas fa-download"></i> Export enrolled students</button>
                    </div>
                    <div class="card-block course-page-filter">
                        <div class="course-filter-intro"><div><strong><i class="fas fa-filter"></i> Filter courses</strong><small>Use any combination of filters. Results are loaded page by page.</small></div><a class="course-clear-filter" href="{{ url('/course') }}">Clear all</a></div>
                        <form class="course-filter-form" method="GET" action="{{ url('/course') }}">
                            <div class="course-filter-field"><label for="courseSearch">Search</label><input class="form-control" id="courseSearch" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code or course title"></div>
                            <div class="course-filter-field"><label for="facultyf">Faculty / college</label><select class="form-control faculty" lang="f" name="faculty" id="facultyf"><option value="">All faculties</option>@foreach ($faculty as $roww)<option value="{{ $roww->code }}" {{ ($filters['faculty'] ?? '') === $roww->code ? 'selected' : '' }}>{{ $roww->title }}</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="departmentf">Department</label><select class="form-control department" lang="f" id="departmentf" name="department"><option value="">{{ ($filters['faculty'] ?? '') !== '' ? 'All departments' : 'Select faculty first' }}</option>@foreach (($filterDepartments ?? collect()) as $departmentOption)<option value="{{ $departmentOption->code }}" {{ ($filters['department'] ?? '') === $departmentOption->code ? 'selected' : '' }}>{{ $departmentOption->title }}</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="programf">Programme</label><select class="form-control program" id="programf" lang="f" name="program"><option value="">{{ ($filters['department'] ?? '') !== '' ? 'All programmes' : 'Select department first' }}</option>@foreach (($filterPrograms ?? collect()) as $programOption)<option value="{{ $programOption->code }}" {{ ($filters['program'] ?? '') === $programOption->code ? 'selected' : '' }}>{{ $programOption->title }}</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="courseType">Course type</label><select class="form-control" id="courseType" name="type"><option value="">All types</option><option value="Core" {{ ($filters['type'] ?? '') === 'Core' ? 'selected' : '' }}>Core</option><option value="Elective" {{ ($filters['type'] ?? '') === 'Elective' ? 'selected' : '' }}>Elective</option><option value="Prerequsite" {{ ($filters['type'] ?? '') === 'Prerequsite' ? 'selected' : '' }}>Prerequisite</option></select></div>
                            <div class="course-filter-field"><label for="courseClass">Class format</label><select class="form-control" id="courseClass" name="class"><option value="">All formats</option><option value="Theory" {{ ($filters['class'] ?? '') === 'Theory' ? 'selected' : '' }}>Theory</option><option value="Practical" {{ ($filters['class'] ?? '') === 'Practical' ? 'selected' : '' }}>Practical</option><option value="Both" {{ ($filters['class'] ?? '') === 'Both' ? 'selected' : '' }}>Theory and practical</option></select></div>
                            <div class="course-filter-field"><label for="courseLevel">Level</label><select class="form-control" id="courseLevel" name="level"><option value="">All levels</option>@foreach (($courseLevels ?? collect()) as $level)<option value="{{ $level }}" {{ ($filters['level'] ?? '') === (string) $level ? 'selected' : '' }}>{{ $level }} level</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="courseSemester">Semester</label><select class="form-control" id="courseSemester" name="semester"><option value="">All semesters</option>@foreach (($courseSemesters ?? collect()) as $semester)<option value="{{ $semester }}" {{ ($filters['semester'] ?? '') === (string) $semester ? 'selected' : '' }}>{{ ucfirst(strtolower($semester)) }} semester</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="courseUnit">Credit units</label><select class="form-control" id="courseUnit" name="unit"><option value="">All units</option>@foreach (($courseUnits ?? collect()) as $unit)<option value="{{ $unit }}" {{ ($filters['unit'] ?? '') === (string) $unit ? 'selected' : '' }}>{{ $unit }} {{ (int) $unit === 1 ? 'unit' : 'units' }}</option>@endforeach</select></div>
                            <div class="course-filter-field"><label for="courseStatus">Status</label><select class="form-control" id="courseStatus" name="status"><option value="">All statuses</option><option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option><option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option><option value="3" {{ ($filters['status'] ?? '') === '3' ? 'selected' : '' }}>Pending review</option></select></div>
                            <div class="course-filter-field"><label for="coursePageSize">Rows per page</label><select class="form-control" id="coursePageSize" name="per_page"><option value="25" {{ (int) ($perPage ?? 50) === 25 ? 'selected' : '' }}>25 rows</option><option value="50" {{ (int) ($perPage ?? 50) === 50 ? 'selected' : '' }}>50 rows</option><option value="100" {{ (int) ($perPage ?? 50) === 100 ? 'selected' : '' }}>100 rows</option></select></div>
                            <div class="course-filter-submit"><button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Find courses</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card course-page-card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="course-table-scroll">
                            <table id="course-table" class="table table-striped table-hover align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Course</th>
                                        <th>Type</th>
                                        <th>Format</th>
                                        <th>Units</th>
                                        <th>Level</th>
                                        <th>Semester</th>
                                        <th>Faculty / college</th>
                                        <th>Department</th>
                                        <th>Programme</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = $data->firstItem() ?? 1;
                                    @endphp
                                    @forelse ($data as $row)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td><strong>{{ $row->code }}</strong><small class="d-block text-muted">{{ $row->title }}</small></td>
                                        <td>{{ $row->type ?: '—' }}</td>
                                        <td>{{ $row->class ?: '—' }}</td>
                                        <td>{{ $row->unit }}</td>
                                        <td>{{ $row->level ?: '—' }}</td>
                                        <td>{{ $row->semester ? ucfirst(strtolower($row->semester)) : '—' }}</td>
                                        <td>{{ $row->faculty_title ?: $row->faculty ?: '—' }}</td>
                                        <td>{{ $row->department_title ?: $row->department ?: '—' }}</td>
                                        <td>{{ $row->program_title ?: $row->program ?: '—' }}</td>
                                        <td><span class="course-status {{ (string) $row->status === '1' ? '' : 'inactive' }}">{{ (string) $row->status === '1' ? 'Active' : ((string) $row->status === '3' ? 'Pending review' : 'Inactive') }}</span></td>
                                        <td><div class="course-actions">
                                            <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div></td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $row->id }}" class="modal fade course-modal" tabindex="-1" role="dialog" aria-labelledby="updateCourseLabel{{ $row->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="updateCourseLabel{{ $row->id }}">Edit course</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group course-form" action="{{ url('update ' . $page) }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <p class="modal-body-note">Update the academic details below. Changing the academic entity will also update where this course appears in the catalogue.</p>
                                                            <div class="form-group">
                                                                <label for="courseCode{{ $row->id }}">Course code <span class="required-mark">*</span></label>
                                                                <input type="text" name="code" value="{{ $row->code }}" id="courseCode{{ $row->id }}" class="form-control" maxlength="50" required>
                                                                <small class="form-hint">Use the official code, for example ACC101. Spaces are removed automatically.</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseTitle{{ $row->id }}">Course title <span class="required-mark">*</span></label>
                                                                <input type="text" name="title" value="{{ $row->title }}" id="courseTitle{{ $row->id }}" class="form-control" maxlength="255" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseType{{ $row->id }}">Course type <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="courseType{{ $row->id }}" name="type" required>
                                                                    <option value="">Choose course type</option>
                                                                    <option value="CORE" {{ strtoupper((string) $row->type) === 'CORE' ? 'selected' : '' }}>Core</option>
                                                                    <option value="ELECTIVE" {{ strtoupper((string) $row->type) === 'ELECTIVE' ? 'selected' : '' }}>Elective</option>
                                                                    <option value="PREREQUSITE" {{ strtoupper((string) $row->type) === 'PREREQUSITE' ? 'selected' : '' }}>Prerequisite</option>
                                                                    @if ($row->type && !in_array(strtoupper((string) $row->type), ['CORE', 'ELECTIVE', 'PREREQUSITE'], true))<option value="{{ $row->type }}" selected>Existing value: {{ $row->type }}</option>@endif
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseClass{{ $row->id }}">Class format <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="courseClass{{ $row->id }}" name="class" required>
                                                                    <option value="">Choose class format</option>
                                                                    <option value="THEORY" {{ strtoupper((string) $row->class) === 'THEORY' ? 'selected' : '' }}>Theory</option>
                                                                    <option value="PRACTICAL" {{ strtoupper((string) $row->class) === 'PRACTICAL' ? 'selected' : '' }}>Practical</option>
                                                                    <option value="BOTH" {{ strtoupper((string) $row->class) === 'BOTH' ? 'selected' : '' }}>Theory and practical</option>
                                                                    @if ($row->class && !in_array(strtoupper((string) $row->class), ['THEORY', 'PRACTICAL', 'BOTH'], true))<option value="{{ $row->class }}" selected>Existing value: {{ $row->class }}</option>@endif
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseUnit{{ $row->id }}">Credit units <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="courseUnit{{ $row->id }}" name="unit" required>
                                                                    <option value="">Choose credit units</option>
                                                                    @for ($i = 0; $i <= 50; $i++)<option value="{{ $i }}" {{ (string) $row->unit === (string) $i ? 'selected' : '' }}>{{ $i }} {{ $i === 1 ? 'unit' : 'units' }}</option>@endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseLevel{{ $row->id }}">Level <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="courseLevel{{ $row->id }}" name="level" required>
                                                                    <option value="">Choose level</option>
                                                                    @for ($i = 1; $i <= 8; $i++)<option value="{{ $i * 100 }}" {{ (string) $row->level === (string) ($i * 100) ? 'selected' : '' }}>{{ $i * 100 }} level</option>@endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="courseSemester{{ $row->id }}">Semester <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="courseSemester{{ $row->id }}" name="semester" required>
                                                                    <option value="">Choose semester</option>
                                                                    @include('partials.semester_options', ['selectedSemester' => $row->semester])
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="faculty{{ ($row->id) + 3 }}">Faculty / college <span class="required-mark">*</span></label>
                                                                <select class="form-control faculty" id="faculty{{ ($row->id) + 3 }}" name="faculty" lang="{{ ($row->id) + 3 }}" required>
                                                                    <option value="">Choose faculty / college</option>
                                                                    @foreach ($faculty as $roww)<option value="{{ $roww->code }}" {{ (string) $row->faculty === (string) $roww->code ? 'selected' : '' }}>{{ $roww->title }}</option>@endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="department{{ ($row->id) + 3 }}">Department <span class="required-mark">*</span></label>
                                                                <select class="form-control department" id="department{{ ($row->id) + 3 }}" lang="{{ ($row->id) + 3 }}" name="department" required>
                                                                    <option value="{{ $row->department }}">Current department: {{ $row->department_title ?: $row->department }}</option>
                                                                </select>
                                                                <small class="form-hint">Choose a faculty first to load departments.</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="program{{ ($row->id) + 3 }}">Programme <span class="required-mark">*</span></label>
                                                                <select class="form-control" id="program{{ ($row->id) + 3 }}" name="program" lang="{{ ($row->id) + 3 }}" required>
                                                                    <option value="{{ $row->program }}">Current programme: {{ $row->program_title ?: $row->program }}</option>
                                                                </select>
                                                                <small class="form-hint">Choose a department to load programmes.</small>
                                                            </div>
                                                            <!-- Details View End -->
                                                        <div class="course-form-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save changes</button></div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="delete{{ $row->id }}" class="modal fade course-modal" tabindex="-1" role="dialog" aria-labelledby="deleteCourseLabel{{ $row->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-sm modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteCourseLabel{{ $row->id }}">Remove course</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h4>Remove this course?</h4>
                                                        <p class="text-muted mb-0">{{ $row->code }} — {{ $row->title }} will be removed from the course catalogue.</p>
                                                    </div>
                                                    <form class="form-group course-form" action="{{ url('delete ' . $page) }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep course</button>
                                                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Remove</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr><td colspan="12" class="course-empty-state"><i class="fas fa-book-open"></i><strong>No courses found</strong><span>Try changing your filters or search for a different course code or title.</span></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="course-pagination">
                            <div class="course-pagination-summary">Showing {{ $data->firstItem() ?? 0 }}-{{ $data->lastItem() ?? 0 }} of {{ number_format($data->total()) }} courses</div>
                            @if ($data->hasPages())<div>{{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}</div>@endif
                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.course-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
                return;
            }
            const submit = form.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
            }
        });
    });
    document.querySelectorAll('.course-form input[name="code"]').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/\s+/g, '');
        });
    });
});
</script>
<!-- Show modal content -->
<div id="import" class="modal fade course-modal" tabindex="-1" role="dialog" aria-labelledby="importCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importCourseLabel">Import courses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group course-form" action="{{ url('upload ' . $page) }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file">Course spreadsheet <span class="required-mark">*</span></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control" required>
                            <small class="form-hint">Use the official course template. Existing course codes are checked before importing.</small>
                        </div>
                        <!-- Details View End -->
                    <div class="course-form-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Import courses</button></div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="create" class="modal fade course-modal" tabindex="-1" role="dialog" aria-labelledby="createCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCourseLabel">Add course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group course-form" action="{{ url('create ' . $page) }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <p class="modal-body-note">Add a course to the academic catalogue. Select the faculty first, then the department and programme so the academic links remain correct.</p>
                        <div class="form-group">
                            <label for="createCourseCode">Course code <span class="required-mark">*</span></label>
                            <input type="text" name="code" id="createCourseCode" class="form-control" maxlength="50" placeholder="For example, ACC101" required>
                            <small class="form-hint">Use the official code. Spaces are removed automatically.</small>
                        </div>
                        <div class="form-group">
                            <label for="createCourseTitle">Course title <span class="required-mark">*</span></label>
                            <input type="text" name="title" id="createCourseTitle" class="form-control" maxlength="255" placeholder="For example, Basic Accounting I" required>
                        </div>
                        <div class="form-group">
                            <label for="createCourseType">Course type <span class="required-mark">*</span></label>
                            <select class="form-control" id="createCourseType" name="type" required>
                                <option value="">Choose course type</option>
                                <option value="CORE">Core</option>
                                <option value="ELECTIVE">Elective</option>
                                <option value="PREREQUSITE">Prerequisite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="createCourseClass">Class format <span class="required-mark">*</span></label>
                            <select class="form-control" id="createCourseClass" name="class" required>
                                <option value="">Choose class format</option>
                                <option value="THEORY">Theory</option>
                                <option value="PRACTICAL">Practical</option>
                                <option value="BOTH">Theory and practical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="createCourseUnit">Credit units <span class="required-mark">*</span></label>
                            <select class="form-control" id="createCourseUnit" name="unit" required>
                                <option value="">Choose credit units</option>
                                @for ($i = 0; $i <= 50; $i++)<option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'unit' : 'units' }}</option>@endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="createCourseLevel">Level <span class="required-mark">*</span></label>
                            <select class="form-control" id="createCourseLevel" name="level" required>
                                <option value="">Choose level</option>
                                @for ($i = 1; $i <= 8; $i++)<option value="{{ $i * 100 }}">{{ $i * 100 }} level</option>@endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="createCourseSemester">Semester <span class="required-mark">*</span></label>
                            <select class="form-control" id="createCourseSemester" name="semester" required>
                                <option value="">Choose semester</option>
                                @include('partials.semester_options')
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="faculty2">Faculty / college <span class="required-mark">*</span></label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2" required>
                                <option value="">Choose faculty / college</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department <span class="required-mark">*</span></label>
                            <select class="form-control department" id="department2" name="department" lang="2" required>
                                <option value="">Choose faculty first</option>
                            </select>
                            <small class="form-hint">Departments are loaded after selecting a faculty.</small>
                        </div>
                        <div class="form-group">
                            <label for="program2">Programme <span class="required-mark">*</span></label>
                            <select class="form-control" id="program2" name="program" lang="2" required>
                                <option value="">Choose department first</option>
                            </select>
                            <small class="form-hint">Programmes are loaded after selecting a department.</small>
                        </div>
                        <!-- Details View End -->
                    <div class="course-form-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save course</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="exportStudent" class="modal fade course-modal" tabindex="-1" role="dialog" aria-labelledby="exportCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportCourseLabel">Export enrolled students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <div class="card-block">
                    <form class="needs-validation" novalidate method="GET" action="/export-courses">
                        @csrf
                        <div class="row gx-2">
                            <div class="form-group col-md-12">
                                <label for="exportCourseCode">Course code <span>*</span></label>
                                <input class="form-control" name="code" id="exportCourseCode" placeholder="For example, ACC101" required>
                                <small class="text-muted">Enter the exact course code shown in the catalogue.</small>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="exportCourseSession">Academic session <span>*</span></label>
                                <select class="form-control" name="session" id="exportCourseSession" required>
                                    <option value="">Select session</option>
                                    @foreach (($academicSessions ?? collect()) as $academicSession)
                                        <option value="{{ $academicSession->title }}">{{ $academicSession->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-download"></i> {{ ('Export') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
