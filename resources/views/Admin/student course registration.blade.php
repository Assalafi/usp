@php
    $hasFilters = (bool) ($hasFilters ?? false);
    $summary = $summary ?? ['records' => 0, 'units' => 0];
    $activeFilters = collect([$username ?? '', $course_code ?? '', $session ?? '', $semester ?? '', $level ?? '', $type ?? ''])
        ->filter(fn ($value) => trim((string) $value) !== '')
        ->count();
    $currentSession = session('system_session');
@endphp

<script>
    $(document).ready(function() {
        $('#checkAll').click(function(e) {
            if ($(this).is(':checked')) {
                $('.itemCheckbox').prop('checked', true);
            } else {
                $('.itemCheckbox').prop('checked', false);
            }
        });

        // Update the selected IDs when the bulk delete modal is shown
        $('#bulkDeleteModal').on('show.bs.modal', function() {
            let selected = [];
            $('.itemCheckbox:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                alert('Please select at least one record to delete!');
                return false;
            }

            $('#selected-ids').val(selected.join(','));
        });
    });
</script>

<div class="p-4 admin-scr-page">
    <div class="admin-scr-shell">
        <section class="admin-scr-hero">
            <div><span class="admin-scr-kicker"><i class="fas fa-graduation-cap"></i> Academic operations</span><h1>Student course registration</h1><p>Find a student’s registration, review saved courses, or register courses on the student’s behalf.</p></div>
            <span class="admin-scr-hero-icon"><i class="fas fa-book-open"></i></span>
        </section>
    <div class="row mb-4 admin-scr-toolbar">
        <div class="col-md-6">
            <span class="admin-scr-kicker">Registration records</span>
            <h4>Review and manage registrations</h4>
            <p class="admin-scr-subtitle">Filter first to keep the page fast and focused.</p>
        </div>
        {{-- Add register Courses Button if username is not empty --}}
        @if (isset($getProgramCourses) && $getProgramCourses->count())
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                    data-bs-target="#registerModal">
                    <i class="fas fa-plus"></i> Register courses
                </button>
            </div>
        @else
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-search"></i> Search a student first
                </button>
            </div>
        @endif
        <div class="col-md-3 text-end">
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#bulkDeleteModal">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="card mb-4 admin-scr-filter-card">
        <div class="card-body">
            <form action="/student course registration" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="username" class="form-label">Student ID / username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="e.g. 22/08/08/0010" value="{{ $username ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="course_code" class="form-label">Course code</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="course_code" name="course_code"
                            placeholder="e.g. CSC408" value="{{ $course_code ?? '' }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="session" class="form-label">Academic session</label>
                    <select class="form-select" id="session" name="session">
                        <option value="">All sessions</option>
                        @if (isset($sessions))
                            @foreach ($sessions as $sessionItem)
                                <option value="{{ $sessionItem->title }}"
                                    {{ isset($session) && $session == $sessionItem->title ? 'selected' : '' }}>
                                    {{ $sessionItem->title }}{{ (string) $sessionItem->title === (string) $currentSession ? ' · current' : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select" id="semester" name="semester">
                        <option value="">All semesters</option>
                        <option value="FIRST" {{ isset($semester) && $semester == 'FIRST' ? 'selected' : '' }}>FIRST
                        </option>
                        <option value="SECOND" {{ isset($semester) && $semester == 'SECOND' ? 'selected' : '' }}>SECOND
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="level" class="form-label">Level</label>
                    <select class="form-select" id="level" name="level">
                        <option value="">All levels</option>
                        @foreach ([100, 200, 300, 400, 500, 600, 700, 800] as $levelOption)
                            <option value="{{ $levelOption }}" {{ (string) ($level ?? '') === (string) $levelOption ? 'selected' : '' }}>{{ $levelOption }} level</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Course type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All course types</option>
                        <option value="CORE" {{ strtoupper((string) ($type ?? '')) === 'CORE' ? 'selected' : '' }}>Core</option>
                        <option value="ELECTIVE" {{ strtoupper((string) ($type ?? '')) === 'ELECTIVE' ? 'selected' : '' }}>Elective</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end admin-scr-filter-actions">
                    <div>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                        <a href="/student course registration" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-scr-summary" aria-label="Registration summary">
        <div class="admin-scr-summary-card blue"><span><i class="fas fa-list-check"></i></span><div><strong>{{ number_format((int) ($summary['records'] ?? 0)) }}</strong><small>Matching registrations</small></div></div>
        <div class="admin-scr-summary-card green"><span><i class="fas fa-cubes"></i></span><div><strong>{{ number_format((float) ($summary['units'] ?? 0), 0) }}</strong><small>Total course units</small></div></div>
        <div class="admin-scr-summary-card amber"><span><i class="fas fa-calendar-alt"></i></span><div><strong>{{ $session ?: 'All' }}</strong><small>Academic session</small></div></div>
        <div class="admin-scr-summary-card purple"><span><i class="fas fa-sliders"></i></span><div><strong>{{ $activeFilters }}</strong><small>Filters in use</small></div></div>
    </div>

    @if (!$hasFilters)
        <div class="admin-scr-empty"><span><i class="fas fa-magnifying-glass"></i></span><h2>Start with a filter</h2><p>Search by student ID, course, session or level to review registrations without loading the whole database.</p></div>
    @elseif (isset($data) && count($data) === 0)
        <div class="admin-scr-empty"><span class="warning"><i class="fas fa-file-circle-question"></i></span><h2>No registration found</h2><p>Try a different student ID, course code or academic session.</p></div>
    @endif

    <div class="card admin-scr-results {{ !$hasFilters || (isset($data) && count($data) === 0) ? 'd-none' : '' }}">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover admin-scr-table">
                    <thead>
                        <tr>
                            <th class="admin-scr-check"><input type="checkbox" id="checkAll" aria-label="Select all visible registrations"></th>
                            <th>SN</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Academic details</th>
                            <th>Assessment</th>
                            <th class="admin-scr-actions-head">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($data) && count($data) > 0)
                            @foreach ($data as $item)
                                <tr>
                                    <td class="admin-scr-check"><input type="checkbox" class="itemCheckbox" value="{{ $item->id }}" aria-label="Select {{ $item->username }} {{ $item->code }}"></td>
                                    <td class="admin-scr-number">{{ $data->firstItem() + $loop->index }}</td>
                                    <td><strong class="admin-scr-student-id">{{ $item->username }}</strong><small class="admin-scr-muted">Student ID</small></td>
                                    <td><strong class="admin-scr-course-code">{{ $item->code }}</strong><small class="admin-scr-course-title">{{ $item->course_title ?: 'Course title unavailable' }}</small></td>
                                    <td><div class="admin-scr-detail-pills"><span>{{ $item->level ?: '—' }} level</span><span>{{ ucfirst(strtolower((string) ($item->type ?: 'Course'))) }}</span><span>{{ ucfirst(strtolower((string) ($item->semester ?: '—'))) }}</span><span>{{ $item->session }}</span></div></td>
                                    <td><div class="admin-scr-assessment"><span>{{ $item->unit ?: 0 }} unit{{ (int) $item->unit === 1 ? '' : 's' }}</span>@if ($item->total !== null)<span>Total {{ $item->total }}</span>@endif @if ($item->grade)<b class="admin-scr-grade">{{ $item->grade }}</b>@endif</div></td>
                                    <td class="admin-scr-actions">
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">
                                                    Delete
                                                    Course Registration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete the course registration for:
                                                <ul>
                                                    <li><strong>Username:</strong> {{ $item->username }}</li>
                                                    <li><strong>Course Code:</strong> {{ $item->code }}</li>
                                                    <li><strong>Session:</strong> {{ $item->session }}</li>
                                                    <li><strong>Semester:</strong> {{ $item->semester }}</li>
                                                </ul>
                                                <p class="text-danger"><strong>This action cannot be undone!</strong>
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="/delete student course registration" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id"
                                                        value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">No records found. Please apply filters to view
                                    data.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                @if ($data->hasPages())
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card-footer">
                                <div class="row align-items-center">
                                    <div class="col-sm-6">
                                        <p class="text-muted mb-0">
                                            Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of
                                            {{ $data->total() }} results
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex justify-content-end">
                                            {{ $data->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
        <div class="modal fade admin-scr-modal" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Delete Selected Course Registrations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the selected course registration records?
                    <p class="text-danger mt-3"><strong>Warning:</strong> This action cannot be undone and will
                        permanently remove all selected records!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="/delete student course registration" method="POST">
                        @csrf
                        <input type="hidden" name="ids" id="selected-ids">
                        <button type="submit" class="btn btn-danger">Delete Selected</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Register Course Modal if username is not empty, loaded from $getProgramCourses load them in table with check box to select multiple courses submit code,semester,level,type --}}
    @if (isset($getProgramCourses) && $getProgramCourses->count())
        <div class="modal fade admin-scr-modal" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Register Course for {{ $username }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="/registerCoursesManually" method="POST">
                            @csrf
                            <input type="hidden" name="username" value="{{ $username }}">
                            {{-- Add selection of session from current year/previous to 2008/2009 using for loop --}}
                    <div class="admin-scr-modal-toolbar">
                        <label class="admin-scr-modal-session">Academic session
                            <select class="form-control" name="session" id="registrationSession" required>
                                <option value="">Select session</option>
                                @foreach (($sessions ?? collect()) as $sessionItem)
                                    <option value="{{ $sessionItem->title }}" {{ (string) $sessionItem->title === (string) $currentSession ? 'selected' : '' }}>{{ $sessionItem->title }}{{ (string) $sessionItem->title === (string) $currentSession ? ' · current' : '' }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="admin-scr-modal-search"><i class="fas fa-search"></i><input type="search" id="modalCourseSearch" placeholder="Find a course" autocomplete="off"></label>
                        <label class="admin-scr-modal-select-all"><input type="checkbox" id="modalCheckAll"> Select all visible</label>
                    </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover admin-scr-picker-table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>SN</th>
                                            <th>Course</th>
                                            <th>Unit</th>
                                            <th>Level</th>
                                            <th>Type</th>
                                            <th>Semester</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getProgramCourses as $item)
                                            {{-- arrange them as array of objects in the checkbox name="courses[]" like [{"id": 1, "code": "CS101", "unit": 3, "level": 100, "type": "Core", "semester": 1, "session": "2022/2023"}, {"id": 2, "code": "CS102", "unit": 3, "level": 100, "type": "Core", "semester": 2, "session": "2022/2023"}] --}}

                                            <tr data-course-picker data-search="{{ strtolower(($item->code ?? '') . ' ' . ($item->title ?? '')) }}">
                                                <td><input type="checkbox" value="{{ json_encode($item) }}"
                                                        name="courses[]"></td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><strong>{{ $item->code }}</strong><small class="admin-scr-course-title">{{ $item->title ?? 'Course title unavailable' }}</small></td>
                                                <td>{{ $item->unit }}</td>
                                                <td>{{ $item->level }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>{{ $item->semester }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>

<style>
    .admin-scr-page{background:#f4f8fc;min-height:calc(100vh - 70px);margin:-20px;padding:clamp(12px,2vw,28px)}.admin-scr-page>.admin-scr-shell{max-width:1400px;margin:0 auto}.admin-scr-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;background:linear-gradient(125deg,#1768b8,#3ea1e4);border-radius:18px;padding:clamp(20px,3vw,30px);color:#fff;box-shadow:0 10px 25px rgba(23,104,184,.16);margin-bottom:14px}.admin-scr-kicker{display:block;font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#5081a0}.admin-scr-hero .admin-scr-kicker{color:#fff;opacity:.84}.admin-scr-hero h1{margin:7px 0 6px;font-size:clamp(1.4rem,3vw,2rem);font-weight:800}.admin-scr-hero p{margin:0;max-width:700px;opacity:.9;font-size:.86rem}.admin-scr-hero-icon{width:58px;height:58px;display:grid;place-items:center;border-radius:16px;background:rgba(255,255,255,.18);font-size:1.5rem;flex:0 0 auto}.admin-scr-toolbar h4{margin:4px 0 0;color:#214767;font-weight:800}.admin-scr-subtitle{margin:3px 0 0;color:#7b8fa4;font-size:.75rem}.admin-scr-filter-actions{min-width:190px}.admin-scr-filter-actions .btn{font-size:.72rem}.admin-scr-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:0 0 14px}.admin-scr-summary-card{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e0e9f2;border-radius:12px;padding:12px;box-shadow:0 4px 14px rgba(28,70,108,.04)}.admin-scr-summary-card>span{width:35px;height:35px;display:grid;place-items:center;border-radius:10px;font-size:.85rem}.admin-scr-summary-card strong,.admin-scr-summary-card small{display:block}.admin-scr-summary-card strong{color:#1f4566;font-size:1rem;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}.admin-scr-summary-card small{color:#8093a6;font-size:.67rem;margin-top:3px}.admin-scr-summary-card.blue>span{background:#eaf4ff;color:#1768b8}.admin-scr-summary-card.green>span{background:#e8f8ef;color:#15834d}.admin-scr-summary-card.amber>span{background:#fff4dc;color:#a36a08}.admin-scr-summary-card.purple>span{background:#f0eaff;color:#7046bc}.admin-scr-empty{text-align:center;background:#fff;border:1px dashed #cbdce9;border-radius:15px;padding:52px 18px;color:#7a8ea1;margin-bottom:14px}.admin-scr-empty>span{display:grid;place-items:center;width:55px;height:55px;margin:0 auto 13px;border-radius:50%;background:#eaf4ff;color:#3e8abe;font-size:1.35rem}.admin-scr-empty>span.warning{background:#fff4dd;color:#a56a08}.admin-scr-empty h2{color:#2a4f6d;font-size:1.1rem;margin:0 0 6px}.admin-scr-empty p{max-width:530px;margin:0 auto;font-size:.8rem}.admin-scr-results{border:0;box-shadow:0 5px 18px rgba(28,70,108,.045)}.admin-scr-results .card-body{padding:0}.admin-scr-results .table-responsive{overflow-x:auto}.admin-scr-table{min-width:980px}.admin-scr-table thead th{background:#f5f9fc;color:#6a8398;font-size:.64rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e3ebf2;white-space:nowrap}.admin-scr-table td{vertical-align:middle;color:#38556e;font-size:.76rem}.admin-scr-table tbody tr:hover{background:#fbfdff}.admin-scr-check{width:38px;text-align:center!important}.admin-scr-check input{width:16px;height:16px;accent-color:#1768b8}.admin-scr-number{color:#91a0ae!important;font-size:.7rem!important}.admin-scr-student-id,.admin-scr-course-code{display:block;color:#1768b8;font-size:.78rem}.admin-scr-muted,.admin-scr-course-title{display:block;color:#8496a7;font-size:.64rem;margin-top:3px;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.admin-scr-detail-pills,.admin-scr-assessment{display:flex;align-items:center;gap:5px;flex-wrap:wrap;max-width:255px}.admin-scr-detail-pills span,.admin-scr-assessment span{padding:4px 6px;border-radius:6px;background:#f0f5f9;color:#678198;font-size:.64rem;white-space:nowrap}.admin-scr-grade{display:inline-grid;place-items:center;min-width:24px;padding:4px 6px;border-radius:6px;background:#e8f5ee;color:#15784b;font-size:.68rem}.admin-scr-actions-head,.admin-scr-actions{width:62px;position:sticky;right:0;background:#fff;box-shadow:-5px 0 10px rgba(36,72,102,.04)}.admin-scr-table thead .admin-scr-actions-head{background:#f5f9fc}.admin-scr-actions .btn{border-color:#f1c8cc;color:#b3414d;background:#fff}.admin-scr-actions .btn:hover{background:#fff1f2}.admin-scr-results .card-footer{border-top:1px solid #e8eef4;background:#fff}.admin-scr-results .pagination{margin:0;flex-wrap:wrap;justify-content:flex-end}.admin-scr-results .page-link{border-radius:6px!important;margin:0 2px;border-color:#dce7ef;color:#4f718c;font-size:.72rem}.admin-scr-results .page-item.active .page-link{background:#1768b8;border-color:#1768b8}.admin-scr-modal .modal-content{border:0;border-radius:15px;overflow:hidden}.admin-scr-modal .modal-header{border-bottom:1px solid #e8eef4}.admin-scr-modal .modal-body{max-height:70vh;overflow:auto}.admin-scr-modal .modal-body .table-responsive{max-height:50vh;overflow:auto}.admin-scr-modal .modal-body table{min-width:620px}.admin-scr-modal .modal-body thead th{position:sticky;top:0;background:#f5f9fc;z-index:1}.admin-scr-modal .modal-footer{border-top:1px solid #e8eef4}.admin-scr-modal .modal-footer .btn{font-size:.75rem}
    @media (max-width:900px){.admin-scr-page{margin:-12px;padding:12px}.admin-scr-summary{grid-template-columns:repeat(2,1fr)}.admin-scr-filter-actions{min-width:0}}
    @media (max-width:600px){.admin-scr-page{margin:-10px;padding:9px}.admin-scr-hero{padding:18px 15px;border-radius:14px}.admin-scr-hero-icon{width:45px;height:45px;font-size:1.15rem}.admin-scr-hero p{font-size:.76rem}.admin-scr-toolbar{margin-bottom:14px}.admin-scr-toolbar>div{width:100%;text-align:left!important}.admin-scr-toolbar>div:not(:first-child){margin-top:10px;text-align:left!important}.admin-scr-toolbar .btn{font-size:.68rem}.admin-scr-summary{gap:7px}.admin-scr-summary-card{padding:9px;gap:7px}.admin-scr-summary-card>span{width:29px;height:29px;font-size:.73rem}.admin-scr-summary-card strong{font-size:.84rem;max-width:105px}.admin-scr-summary-card small{font-size:.58rem}.admin-scr-filter-card{padding:0;border:0;box-shadow:none;background:transparent}.admin-scr-filter-card>.card-body{padding:0}.admin-scr-filter-card form{background:#fff;border:1px solid #e0e9f2;border-radius:13px;padding:12px}.admin-scr-results .card-body{padding:0}.admin-scr-results .card-footer{padding:10px}.admin-scr-results .card-footer .d-flex{justify-content:center!important;margin-top:9px}.admin-scr-results .card-footer p{font-size:.68rem}.admin-scr-modal .modal-body{max-height:68vh}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalSearch = document.getElementById('modalCourseSearch');
    const pickerRows = Array.from(document.querySelectorAll('[data-course-picker]'));
    const modalChoices = Array.from(document.querySelectorAll('[data-course-picker] input[name="courses[]"]'));
    const modalSelectAll = document.getElementById('modalCheckAll');
    const registrationForm = document.querySelector('#registerModal form');
    function updateModalSelection() {
        const visible = pickerRows.filter(row => !row.hidden).map(row => row.querySelector('input[name="courses[]"]')).filter(Boolean);
        const selected = modalChoices.filter(input => input.checked);
        if (modalSelectAll) {
            const visibleSelected = visible.filter(input => input.checked);
            modalSelectAll.checked = visible.length > 0 && visibleSelected.length === visible.length;
            modalSelectAll.indeterminate = visibleSelected.length > 0 && visibleSelected.length < visible.length;
        }
        const submit = registrationForm?.querySelector('button[type="submit"]');
        if (submit) submit.innerHTML = selected.length ? `<i class="fas fa-plus"></i> Register ${selected.length} course${selected.length === 1 ? '' : 's'}` : '<i class="fas fa-plus"></i> Register selected';
    }
    function filterModalCourses() {
        const search = (modalSearch?.value || '').trim().toLowerCase();
        pickerRows.forEach(row => row.hidden = !!search && !(row.dataset.search || '').includes(search));
        updateModalSelection();
    }
    modalSearch?.addEventListener('input', filterModalCourses);
    modalChoices.forEach(input => input.addEventListener('change', updateModalSelection));
    modalSelectAll?.addEventListener('change', function () {
        pickerRows.filter(row => !row.hidden).forEach(row => { const input = row.querySelector('input[name="courses[]"]'); if (input) input.checked = this.checked; });
        updateModalSelection();
    });
    registrationForm?.addEventListener('submit', function (event) {
        if (!modalChoices.some(input => input.checked)) {
            event.preventDefault();
            if (typeof window.swal === 'function') window.swal('Choose courses', 'Select at least one course before registering.', 'info'); else window.alert('Select at least one course before registering.');
            return;
        }
        const submit = this.querySelector('button[type="submit"]');
        if (submit) { submit.disabled = true; submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering…'; }
    });
    updateModalSelection();
});
</script>
