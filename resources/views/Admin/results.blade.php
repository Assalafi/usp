<style>
    .results-page-card{border:0;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(34,61,92,.08)}
    .results-page-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#0b78b7,#3ea1e4);color:#fff;padding:24px 26px;border:0}
    .results-page-heading h5{margin:4px 0 4px;font-size:1.35rem;font-weight:700;color:#fff}.results-page-heading p{margin:0;color:rgba(255,255,255,.82);font-size:.83rem}.results-page-kicker{font-size:.68rem;letter-spacing:.13em;font-weight:700;color:rgba(255,255,255,.78)}
    .results-page-count{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:9px 13px;font-size:.74rem;white-space:nowrap}
    .results-page-actions{display:flex;flex-wrap:wrap;gap:9px;padding:18px 26px;border-bottom:1px solid #edf1f5}.results-page-actions .btn{border-radius:9px;font-size:.77rem;font-weight:600}.results-delete-action{margin-left:auto}
    .results-filter-block{padding:19px 26px 23px;background:#fbfdff}.results-filter-intro{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.results-filter-intro strong{display:block;color:#253b53;font-size:.91rem}.results-filter-intro small{display:block;color:#8291a3;margin-top:3px}.results-clear-filter{font-size:.75rem;color:#267eaf;text-decoration:none;font-weight:600}.results-filter-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.results-filter-field label{display:block;color:#53677d;font-size:.72rem;font-weight:700;margin:0 0 6px}.results-filter-field .form-control{height:40px;border-radius:9px;border:1px solid #dce6ef;font-size:.78rem;background:#fff}.results-filter-submit{display:flex;align-items:end}.results-filter-submit .btn{height:40px;width:100%;border-radius:9px;font-size:.77rem;font-weight:700}.results-empty-state{text-align:center!important;padding:58px 15px!important;color:#8a99a9}.results-empty-state i{display:block;font-size:2rem;color:#9ac6df;margin-bottom:12px}.results-empty-state strong,.results-empty-state span{display:block}.results-empty-state strong{color:#42566d;font-size:.9rem}.results-empty-state span{font-size:.78rem;margin-top:5px}.results-status{display:inline-flex;align-items:center;white-space:nowrap;border-radius:999px;padding:5px 9px;font-size:.68rem;font-weight:700;background:#eef2f6;color:#657589}.results-status.submitted{background:#fff3d9;color:#946500}.results-status.approved{background:#e4f6ec;color:#197447}.results-status.final{background:#dff1ff;color:#14618a}.results-pagination-wrap{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:18px;padding-top:15px;border-top:1px solid #edf1f5}.results-pagination-summary{font-size:.76rem;color:#7c8c9e}.results-pagination-wrap .pagination{margin:0}.results-pagination-wrap .page-link{border-radius:7px!important;margin-left:4px;border:1px solid #dbe6ef;color:#347da5;font-size:.75rem}.results-pagination-wrap .page-item.active .page-link{background:#3ea1e4;border-color:#3ea1e4;color:#fff}
    @media (max-width:992px){.results-filter-form{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media (max-width:680px){.results-page-heading{align-items:flex-start;flex-direction:column;padding:20px}.results-page-count{font-size:.7rem}.results-page-actions,.results-filter-block{padding-left:17px;padding-right:17px}.results-page-actions .btn{flex:1 1 calc(50% - 9px);min-height:39px}.results-delete-action{margin-left:0}.results-filter-form{grid-template-columns:1fr;gap:11px}.results-filter-submit{margin-top:3px}.results-pagination-wrap{align-items:flex-start;flex-direction:column}.results-pagination-wrap>div:last-child{max-width:100%;overflow:auto}.results-pagination-wrap .pagination{flex-wrap:nowrap}}
</style>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card results-page-card">
                    <div class="card-header results-page-heading">
                        <div>
                            <span class="results-page-kicker">ACADEMIC RECORDS</span>
                            <h5>Course results</h5>
                            <p>Filter the records you need, then review or manage them safely.</p>
                        </div>
                        <span class="results-page-count"><i class="fas fa-database"></i> {{ $hasResultFilters ? number_format($data->total()) . ' matching' : 'Choose filters to begin' }}</span>
                    </div>
                    <div class="card-block results-page-actions">
                        <a href="{{ url('uploads/result template.xlsx') }}" class="btn btn-primary" download="result template.xlsx"><i class="fas fa-download"></i> Download template</a>
                        <a href="{{ route('results.upload.page') }}" class="btn btn-dark"><i class="fas fa-upload"></i> Upload results</a>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportStudent"><i class="fas fa-user-graduate"></i> Registered students</button>
                        @if (session('accType') == 'Admin')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportResults"><i class="fas fa-file-excel"></i> Export approved</button>
                        @endif
                        <button type="button" class="btn btn-outline-danger results-delete-action" data-bs-toggle="modal" data-bs-target="#deleteResults"><i class="fas fa-trash"></i> Delete results</button>
                    </div>
                    <div class="card-block results-filter-block">
                        <div class="results-filter-intro"><div><strong><i class="fas fa-filter"></i> Find results</strong><small>Use one or more filters. Nothing is loaded until you search.</small></div><a href="{{ url('/results') }}" class="results-clear-filter">Clear all</a></div>
                        <form class="results-filter-form" method="GET" action="{{ url('/results') }}">
                            @if (session('accType') !== 'Staff')
                                <div class="results-filter-field"><label for="facultyf">Faculty</label><select class="form-control faculty" lang="f" name="faculty" id="facultyf"><option value="">All faculties</option>@foreach ($faculty as $roww)<option value="{{ $roww->code }}" {{ ($filters['faculty'] ?? '') === $roww->code ? 'selected' : '' }}>{{ $roww->title }}</option>@endforeach</select></div>
                                <div class="results-filter-field"><label for="departmentf">Department</label><select class="form-control department" lang="f" id="departmentf" name="department"><option value="">All departments</option>@foreach (($filterDepartments ?? collect()) as $departmentOption)<option value="{{ $departmentOption->code }}" {{ ($filters['department'] ?? '') === $departmentOption->code ? 'selected' : '' }}>{{ $departmentOption->title }}</option>@endforeach</select></div>
                                <div class="results-filter-field"><label for="programf">Programme</label><select class="form-control program" id="programf" lang="f" name="program"><option value="">All programmes</option>@foreach (($filterPrograms ?? collect()) as $programOption)<option value="{{ $programOption->code }}" {{ ($filters['program'] ?? '') === $programOption->code ? 'selected' : '' }}>{{ $programOption->title }}</option>@endforeach</select></div>
                            @endif
                            <div class="results-filter-field"><label for="coursef">Course</label><select class="form-control" id="coursef" lang="f" name="code"><option value="">All courses</option>@if (!empty($selectedCourse))<option value="{{ $selectedCourse->code }}" selected>{{ $selectedCourse->code }} — {{ $selectedCourse->title }}</option>@elseif(session('accType') === 'Staff')@foreach ($lecturerCourses as $row)<option value="{{ $row->course }}" {{ ($filters['code'] ?? '') === $row->course ? 'selected' : '' }}>{{ $row->course }}</option>@endforeach@else<option value="" disabled>Select a programme to choose a course</option>@endif</select></div>
                            <div class="results-filter-field"><label for="resultsFilterSession">Academic session</label><select class="form-control" id="resultsFilterSession" name="session"><option value="">All sessions</option>@foreach ($session as $ses)<option value="{{ $ses->title }}" {{ ($filters['session'] ?? '') === $ses->title ? 'selected' : '' }}>{{ $ses->title }}{{ (string) $ses->title === (string) ($sessions ?? '') ? ' — current' : '' }}</option>@endforeach</select></div>
                            <div class="results-filter-field"><label for="resultsFilterSemester">Semester</label><select class="form-control" id="resultsFilterSemester" name="semester"><option value="">All semesters</option><option value="FIRST" {{ ($filters['semester'] ?? '') === 'FIRST' ? 'selected' : '' }}>First semester</option><option value="SECOND" {{ ($filters['semester'] ?? '') === 'SECOND' ? 'selected' : '' }}>Second semester</option><option value="THIRD" {{ ($filters['semester'] ?? '') === 'THIRD' ? 'selected' : '' }}>Third semester</option><option value="RESIT" {{ ($filters['semester'] ?? '') === 'RESIT' ? 'selected' : '' }}>Resit</option></select></div>
                            <div class="results-filter-field"><label for="resultsFilterLevel">Level</label><select class="form-control" id="resultsFilterLevel" name="level"><option value="">All levels</option>@foreach (($resultLevels ?? collect()) as $resultLevel)<option value="{{ $resultLevel }}" {{ ($filters['level'] ?? '') === (string) $resultLevel ? 'selected' : '' }}>{{ $resultLevel }} level</option>@endforeach</select></div>
                            @if (session('accType') === 'Admin')<div class="results-filter-field"><label for="resultsFilterApproval">Approval stage</label><select class="form-control" id="resultsFilterApproval" name="approve"><option value="">All stages</option><option value="system" {{ ($filters['approve'] ?? '') === 'system' ? 'selected' : '' }}>Lecturer submitted</option><option value="hod" {{ ($filters['approve'] ?? '') === 'hod' ? 'selected' : '' }}>Department approved</option><option value="dean" {{ ($filters['approve'] ?? '') === 'dean' ? 'selected' : '' }}>Faculty approved</option><option value="cs" {{ ($filters['approve'] ?? '') === 'cs' ? 'selected' : '' }}>Course System approved</option><option value="senate" {{ ($filters['approve'] ?? '') === 'senate' ? 'selected' : '' }}>Senate approved</option><option value="vc" {{ ($filters['approve'] ?? '') === 'vc' ? 'selected' : '' }}>Vice-Chancellor approved</option></select></div>@endif
                            <div class="results-filter-field"><label for="resultsFilterStudent">Student ID</label><input class="form-control" id="resultsFilterStudent" name="username" value="{{ $filters['username'] ?? '' }}" placeholder="Search student ID"></div>
                            <div class="results-filter-field"><label for="resultsFilterPageSize">Rows per page</label><select class="form-control" id="resultsFilterPageSize" name="per_page"><option value="25" {{ (int) ($perPage ?? 50) === 25 ? 'selected' : '' }}>25 rows</option><option value="50" {{ (int) ($perPage ?? 50) === 50 ? 'selected' : '' }}>50 rows</option><option value="100" {{ (int) ($perPage ?? 50) === 100 ? 'selected' : '' }}>100 rows</option></select></div>
                            <div class="results-filter-submit"><button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Search results</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="results-table" class="table table-striped table-hover align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'ID' }}</th>
                                        <th>{{ 'CODE' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'EXAM' }}</th>
                                        <th>{{ 'TOTAL' }}</th>
                                        <th>{{ 'GRADE' }}</th>
                                        <th>{{ 'SESSION' }}</th>
                                        <th>{{ 'SEMESTER' }}</th>
                                        <th>{{ 'STATUS' }}</th>
                                        <th>{{ 'ACTION' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = $data->firstItem() ?? 1;
                                    @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ $row->ca }}</td>
                                            <td>{{ $row->exam }}</td>
                                            <td>{{ $row->total }}</td>
                                            <td>{{ $row->grade }}</td>
                                            <td>{{ $row->session }}</td>
                                            <td>{{ $row->semester }}</td>
                                            @php
                                                $approvalLabels = [
                                                    'system' => 'Lecturer submitted',
                                                    'hod' => 'Department approved',
                                                    'dean' => 'Faculty approved',
                                                    'cs' => 'Course System approved',
                                                    'senate' => 'Senate approved',
                                                    'vc' => 'Vice-Chancellor approved',
                                                ];
                                                $approval = strtolower((string) ($row->approve ?? ''));
                                                $approvalLabel = $approvalLabels[$approval] ?? 'Not started';
                                                $approvalClass = $approval === 'vc' ? 'final' : (in_array($approval, ['hod', 'dean', 'cs', 'senate'], true) ? 'approved' : 'submitted');
                                            @endphp
                                            <td><span class="results-status {{ $approvalClass }}">{{ $approvalLabel }}</span></td>
                                            <td>
                                                {{-- @if ((session('appointment') == 'HOD' || session('accType') == 'Admin') && $row->corrigenda == 0)
                                                    <a href="#" class="btn btn-icon btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#corrigenda{{ $row->id }}">
                                                        <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                                                    </a>
                                                @endif --}}
                                                @if ((session('faculty') == 'MS' && $row->lecturer == session('username')) || session('accType') == 'Admin')
                                                    <a href="#" class="btn btn-icon btn-warning btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#med{{ $row->id }}">
                                                        <i class="fa fa-file-excel" aria-hidden="true"></i>
                                                    </a>
                                                @endif

                                                @if ($row->approve == 'system')
                                                    <a href="#" class="btn btn-icon btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $row->id }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>

                                                    <button type="button" class="btn btn-icon btn-danger btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete{{ $row->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="update {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <input type="hidden" name="code"
                                                                    value="{{ $row->code }}">
                                                                <input type="hidden" name="username"
                                                                    value="{{ $row->username }}">
                                                                <div class="form-group">
                                                                    <label for="ca">CA</label>
                                                                    <input type="text" name="ca"
                                                                        value="{{ $row->ca }}" id="ca"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="exam">EXAM</label>
                                                                    <input type="text" name="exam"
                                                                        value="{{ $row->exam }}" id="exam"
                                                                        class="form-control" required>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure</h4>
                                                        </div>
                                                        <form class="form-group" action="delete {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="corrigenda{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure, you want to initiate corrigendum on
                                                                {{ $row->code }} for {{ $row->username }}</h4>
                                                        </div>
                                                        <form class="form-group" action="initiate corrigenda"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <div class="form-group">
                                                                    <input type="checkbox" required />
                                                                    <label for="code">Yes, initiate</label>
                                                                </div>
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="med{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure, you want to apply failed clinical on
                                                                {{ $row->code }} for {{ $row->username }}</h4>
                                                        </div>
                                                        <form class="form-group" action="/update-med" method="POST"
                                                            enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <div class="form-group">
                                                                    <input type="checkbox" required />
                                                                    <label for="code">Yes, apply</label>
                                                                </div>
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="results-empty-state">
                                                <i class="fas {{ $hasResultFilters ? 'fa-magnifying-glass' : 'fa-filter' }}"></i>
                                                <strong>{{ $hasResultFilters ? 'No results found' : 'Choose filters to view results' }}</strong>
                                                <span>{{ $hasResultFilters ? 'Try a different course, session, approval stage, or student ID.' : 'Select at least one filter above, then click Search results.' }}</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="results-pagination-wrap">
                            <div class="results-pagination-summary">@if ($hasResultFilters) Showing {{ $data->firstItem() ?? 0 }}-{{ $data->lastItem() ?? 0 }} of {{ number_format($data->total()) }} results @else No results loaded @endif</div>
                            @if ($hasResultFilters && $data->hasPages())
                                <div>{{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
                            @endif
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
@if (session('accType') == 'Staff')
    {{-- Result entry now uses the dedicated /results/upload workspace. --}}
    @if (false)
    <div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card">
                    <div class="card-body">
                        {{-- <a href="{{ url('uploads/result template.xlsx') }}" download="result template.xlsx"><i class="fas fa-download"></i> Download Template</a> --}}
                    </div>

                    <form class="form-group" action="upload {{ $page }}" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <div class="form-group">
                                <label for="course1">Courses</label>
                                <select class="form-control" id="course1" name="course" lang="1" required>
                                    <option value="">Select Course</option>
                                    @foreach ($lecturerCourses as $row)
                                        <option value="{{ $row->course }}">{{ $row->course }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="{{ $sessions }}">Select Option</option>
                                    @foreach ($session as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="semester">Semester<span>*</span></label>
                                <select name="semester" class="form-control" id="semester" required>
                                    <option value="">Select Semester</option>
                                    @include('partials.semester_options')
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="per">Scoring Type</label>
                                <select class="form-control" id="per" name="per" required>
                                    <option value="">Select Option</option>
                                    <option value="70">30/70</option>
                                    <option value="30">70/30</option>
                                    <option value="60">40/60</option>
                                    <option value="40">60/40</option>
                                    <option value="100">0/100</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="file"></label>
                                <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                    class="form-control">
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
    @endif

    <div id="exportStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card">
                    <div class="card-body">
                        {{-- <a href="{{ url('uploads/result template.xlsx') }}" download="result template.xlsx"><i class="fas fa-download"></i> Download Template</a> --}}
                    </div>

                    <form class="form-group" action="/export-courses" method="GET" enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <div class="form-group">
                                <label for="course1">Courses</label>
                                <select class="form-control" id="course1" name="code" lang="1" required>
                                    <option value="">Select Program First</option>
                                    @foreach ($lecturerCourses as $row)
                                        <option value="{{ $row->course }}">{{ $row->course }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- session --}}
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="">Select Option</option>
                                    <option value="2017/2018">2017/2018</option>
                                    <option value="2018/2019">2018/2019</option>
                                    <option value="2019/2020">2019/2020</option>
                                    <option value="2020/2021">2020/2021</option>
                                    <option value="2021/2022">2021/2022</option>
                                    <option value="2022/2023">2022/2023</option>
                                    <option value="2023/2024">2023/2024</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                    <option value="2027/2028">2027/2028</option>
                                    <option value="2028/2029">2028/2029</option>
                                    <option value="2029/2030">2029/2030</option>
                                    <option value="2030/2031">2030/2031</option>
                                </select>
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Export</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@else
    @if (false)
    <div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card" id="uploadBeforeProgress" style="display: block;">
                    <div class="card-body">
                        <a href="{{ url('uploads/result template.xlsx') }}" download="result template.xlsx"><i
                                class="fas fa-download"></i> Download Template</a>
                    </div>

                    <form class="form-group" action="upload {{ $page }}" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <div class="form-group">
                                <label for="faculty1">Faculty</label>
                                <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                    required>
                                    <option value="">Select Option</option>
                                    @foreach ($faculty as $row)
                                        <option value="{{ $row->code }}">{{ $row->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department1">Department</label>
                                <select class="form-control department" id="department1" name="department"
                                    lang="1" required>
                                    <option value="">Select Faculty First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program1">Program</label>
                                <select class="form-control program" id="program1" name="program" lang="1"
                                    required>
                                    <option value="">Select Department First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="course1">Courses</label>
                                <select class="form-control" id="course1" name="course" lang="1" required>
                                    <option value="">Select Program First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="{{ $sessions }}">Select Option</option>
                                    @foreach ($session as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="semester">Semester<span>*</span></label>
                                <select name="semester" class="form-control" id="semester" required>
                                    <option value="">Select Semester</option>
                                    @include('partials.semester_options')
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="per">Scoring Type</label>
                                <select class="form-control" id="per" name="per" required>
                                    <option value="">Select Option</option>
                                    <option value="70">30/70</option>
                                    <option value="30">70/30</option>
                                    <option value="60">40/60</option>
                                    <option value="40">60/40</option>
                                    <option value="100">0/100</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="file"></label>
                                <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                    class="form-control">
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" onclick="updateProgress()" class="btn btn-success">Upload</button>
                        </div>
                    </form>
                </div>

                <div id="progress-container"
                    style="width: 100%; background: #f3f3f3; border: 1px solid #ccc; padding: 5px; display: none;">
                    <div id="progress-bar"
                        style="width: 0%; background: #4caf50; height: 20px; text-align: center; color: white;">
                        0%
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
    <div id="exportStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card">
                    <form class="form-group" action="/export-courses" method="GET" enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <div class="form-group">
                                <label for="facultyex">Faculty</label>
                                <select class="form-control faculty" id="facultyex" name="faculty" lang="ex"
                                    required>
                                    <option value="">Select Option</option>
                                    @foreach ($faculty as $row)
                                        <option value="{{ $row->code }}">{{ $row->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="departmentex">Department</label>
                                <select class="form-control department" id="departmentex" name="department"
                                    lang="ex" required>
                                    <option value="">Select Faculty First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="programex">Program</label>
                                <select class="form-control program" id="programex" name="program" lang="ex"
                                    required>
                                    <option value="">Select Department First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="courseex">Courses</label>
                                <select class="form-control" id="courseex" name="code" lang="ex" required>
                                    <option value="">Select Program First</option>
                                </select>
                            </div>
                            {{-- session --}}
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="">Select Option</option>
                                    <option value="2017/2018">2017/2018</option>
                                    <option value="2018/2019">2018/2019</option>
                                    <option value="2019/2020">2019/2020</option>
                                    <option value="2020/2021">2020/2021</option>
                                    <option value="2021/2022">2021/2022</option>
                                    <option value="2022/2023">2022/2023</option>
                                    <option value="2023/2024">2023/2024</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                    <option value="2027/2028">2027/2028</option>
                                    <option value="2028/2029">2028/2029</option>
                                    <option value="2029/2030">2029/2030</option>
                                    <option value="2030/2031">2030/2031</option>
                                </select>
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Export</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endif

@if (session('accType') == 'Admin')
    <div id="exportResults" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exportResultsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="exportResultsLabel">
                            <i class="fas fa-file-excel text-success me-2"></i>Export approved results
                        </h5>
                        <small class="text-muted">Only final VC-approved results are included.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" action="{{ route('results.export') }}" id="approvedResultsExportForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <i class="fas fa-layer-group text-primary me-1"></i>
                            Results are arranged in the workbook by <strong>faculty, department, level and semester</strong>.
                            Leave any filter as <strong>All</strong> to include every matching record.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="resultsExportFaculty">Faculty</label>
                                <select class="form-select" id="resultsExportFaculty" name="faculty">
                                    <option value="all">All faculties</option>
                                    @foreach (($faculty ?? collect()) as $facultyRow)
                                        <option value="{{ $facultyRow->code }}">{{ $facultyRow->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="resultsExportDepartment">Department</label>
                                <select class="form-select" id="resultsExportDepartment" name="department" disabled>
                                    <option value="all">All departments</option>
                                </select>
                                <div class="form-text">Choose a faculty first to narrow departments.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="resultsExportLevel">Level</label>
                                <select class="form-select" id="resultsExportLevel" name="level">
                                    <option value="all">All levels</option>
                                    @foreach (($resultLevels ?? collect()) as $resultLevel)
                                        <option value="{{ $resultLevel }}">{{ $resultLevel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="resultsExportSession">Academic session</label>
                                <select class="form-select" id="resultsExportSession" name="session">
                                    <option value="all">All sessions</option>
                                    @foreach (($session ?? collect()) as $sessionRow)
                                        <option value="{{ $sessionRow->title }}">{{ $sessionRow->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="resultsExportSemester">Semester</label>
                                <select class="form-select" id="resultsExportSemester" name="semester">
                                    <option value="all">All semesters</option>
                                    <option value="FIRST">First Semester</option>
                                    <option value="SECOND">Second Semester</option>
                                    <option value="THIRD">Third Semester</option>
                                    <option value="RESIT">Resit Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="approvedResultsExportSubmit">
                            <i class="fas fa-download me-1"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const faculty = document.getElementById('resultsExportFaculty');
            const department = document.getElementById('resultsExportDepartment');
            const form = document.getElementById('approvedResultsExportForm');
            const submit = document.getElementById('approvedResultsExportSubmit');
            if (!faculty || !department || !form) return;

            faculty.addEventListener('change', async function () {
                const selectedFaculty = faculty.value;
                department.innerHTML = '<option value="all">All departments</option>';
                department.disabled = selectedFaculty === 'all';
                if (selectedFaculty === 'all') return;

                department.disabled = true;
                department.innerHTML = '<option value="all">Loading departments...</option>';
                try {
                    const token = form.querySelector('input[name="_token"]')?.value || '';
                    const response = await fetch(@json(url('department ajax')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'text/html',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-CSRF-TOKEN': token
                        },
                        body: new URLSearchParams({ _token: token, faculty: selectedFaculty })
                    });
                    if (!response.ok) throw new Error('Department request failed');
                    department.innerHTML = await response.text();
                    if (department.options.length) {
                        department.options[0].value = 'all';
                        department.options[0].textContent = 'All departments';
                    }
                } catch (error) {
                    department.innerHTML = '<option value="all">All departments</option>';
                    window.alert('Departments could not be loaded. You can still export all departments.');
                } finally {
                    department.disabled = false;
                }
            });

            form.addEventListener('submit', function () {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Preparing Excel...';
            });
        });
    </script>
@endif
<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST"
                    enctype="multipart/form-data">
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
                                @for ($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
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
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department</label>
                            <select class="form-control department" id="department2" name="department"
                                lang="2" required>
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

<div id="deleteResults" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Delete Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="delete-results" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="course1">Courses*</label>
                            <select class="form-control" id="code" name="code" lang="1" required>
                                <option value="">Select Program First</option>
                                @foreach ($myUploads as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                        </div>
                            <div class="form-group col-md-12">
                                <label for="semester">Semester<span>*</span></label>
                                <select name="semester" class="form-control" id="semester" required>
                                    <option value="">Select Semester</option>
                                    @include('partials.semester_options')
                                </select>
                            </div>
                        <div class="form-group col-md-12">
                            <label for="session">Session*</label>
                            <select class="form-control" id="session" name="session" required>
                                <option value="">Select Option</option>
                                @foreach ($session as $ses)
                                    <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Delete</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
