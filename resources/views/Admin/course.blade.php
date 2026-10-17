@php

    use Illuminate\Support\Facades\DB;
@endphp
<style>
    .course-page-card{border:0;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(34,61,92,.08)}
    .course-page-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#0b78b7,#3ea1e4);color:#fff;padding:24px 26px}
    .course-page-heading h5{margin:4px 0;font-size:1.35rem;color:#fff;font-weight:700}.course-page-heading p{margin:0;color:rgba(255,255,255,.82);font-size:.82rem}.course-page-kicker{font-size:.68rem;letter-spacing:.13em;font-weight:700;color:rgba(255,255,255,.78)}
    .course-page-count{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid rgba(255,255,255,.22);border-radius:999px;background:rgba(255,255,255,.15);font-size:.74rem;white-space:nowrap}
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
                        <div class="table-responsive">
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
                                        <td class="text-nowrap">
                                            <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group" action="update {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="code">Code</label>
                                                                <input type="text" name="code" value="{{ $row->code }}" id="code" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="title">Title</label>
                                                                <input type="text" name="title" value="{{ $row->title }}" id="title" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="type">Course Type</label>
                                                                <select class="form-control" id="type" name="type" required>
                                                                    <option value="{{ $row -> type }}">Select Option</option>
                                                                    <option value="Core">Core</option>
                                                                    <option value="Elective">Elective</option>
                                                                    <option value="Prerequsite">Prerequsite</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="class">Class Type</label>
                                                                <select class="form-control" id="class" name="class" required>
                                                                    <option value="{{ $row -> class }}">Select Option</option>
                                                                    <option value="Theory">Theory</option>
                                                                    <option value="Practical">Practical</option>
                                                                    <option value="Both">Both</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="unit">Unit</label>
                                                                <select class="form-control" id="unit" name="unit" required>
                                                                    <option value="{{ $row -> unit }}">Select Option</option>
                                                                    @for ($i = 1; $i <= 50; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="level">Level</label>
                                                                <select class="form-control" id="level" name="level" required>
                                                                    <option value="{{ $row -> level }}">Select Option</option>
                                                                    @for ($i = 1; $i <= 7; $i++)
                                                                        <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="semester">Semester</label>
                                                                <select class="form-control" id="semester" name="semester" required>
                                                                    <option value="{{ $row -> semester }}">Select Option</option>
                                                                    @include('partials.semester_options', ['selectedSemester' => $row->semester])
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="faculty{{ ($row -> id) + 3 }}">Faculty</label>
                                                                <select class="form-control faculty" id="faculty{{ ($row -> id) + 3 }}" name="faculty" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> faculty }}">Select Option</option>
                                                                    @foreach ($faculty as $roww)
                                                                        <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="department{{ ($row -> id) + 3 }}">Department</label>
                                                                <select class="form-control department" id="department{{ ($row -> id) + 3 }}" lang="{{ ($row -> id) + 3 }}" name="department" required>
                                                                    <option value="{{ $row -> department }}">Select Faculty First</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="program">Program</label>
                                                                <select class="form-control" id="program{{ ($row -> id) + 3 }}" name="program" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> program }}">Select Department First</option>
                                                                </select>
                                                            </div>
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h4>Are You Sure</h4>
                                                    </div>
                                                    <form class="form-group" action="delete {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                                                        <button type="submit" class="btn btn-danger">Yes</button>
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
<!-- Show modal content -->
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="upload {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control">
                        </div>
                        <!-- Details View End -->
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" name="code" id="code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="type">Course Type</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select Option</option>
                                <option value="Core">Core</option>
                                <option value="Elective">Elective</option>
                                <option value="Prerequsite">Prerequsite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="class">Class Type</label>
                            <select class="form-control" id="class" name="class" required>
                                <option value="">Select Option</option>
                                <option value="Theory">Theory</option>
                                <option value="Practical">Practical</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit">Unit</label>
                            <select class="form-control" id="unit" name="unit" required>
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 50; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Select Option</option>
                                @include('partials.semester_options')
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="faculty2">Faculty</label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2" required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row -> code }}">{{ $row -> code }}: {{ $row -> title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department</label>
                            <select class="form-control department" id="department2" name="department" lang="2" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control" id="program2" name="program" lang="2" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <!-- Details View End -->
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="exportStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Export enrolled students</h5>
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
