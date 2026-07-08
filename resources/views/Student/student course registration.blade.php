@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;
    $course_flag = DB::table('program')
        ->where(['code' => session('program')])
        ->select('courses')
        ->value('courses');
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        @if ($course_flag == 1 || strpos(session('faculty'), '.PG') !== false)

            <!-- Session Selection - Mobile Optimized -->
            <div class="row mb-4">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row text-center g-3">
                                <div class="col-4">
                                    <h4 class="text-primary mb-1 fs-5 fs-md-4" id="totalCourses">0</h4>
                                    <small class="text-muted d-block">Total Courses</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success mb-1 fs-5 fs-md-4" id="firstSemesterCount">0</h4>
                                    <small class="text-muted d-block">First</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info mb-1 fs-5 fs-md-4" id="secondSemesterCount">0</h4>
                                    <small class="text-muted d-block">Second</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="courseRegistrationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="registered-tab" data-bs-toggle="tab"
                                        data-bs-target="#registered-courses" type="button" role="tab">
                                        <i class="fas fa-list me-2"></i>Registered
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="register-tab" data-bs-toggle="tab"
                                        data-bs-target="#register-courses" type="button" role="tab">
                                        <i class="fas fa-plus-circle me-2"></i>Register
                                    </button>
                                </li>
                                <li class="nav-item" style="width: 33%;">
                                    <form action="/student course registration" method="GET"
                                        style="padding: 5px;margin: 0%;">
                                        <select name="session" class="form-select" onchange="this.form.submit()"
                                            style="width: 100%;">
                                            @php
                                                // Get current system session and extract year
                                                $currentSession = session('system_session');
                                                $currentYear = (int) substr($currentSession, 0, 4);
                                                $endYear = 2005;

                                                // Generate sessions from current year down to 2005/2006
                                                $sessions = [];
                                                for ($year = $currentYear; $year >= $endYear; $year--) {
                                                    $nextYear = $year + 1;
                                                    $sessionValue = $year . '/' . $nextYear;
                                                    $sessions[] = $sessionValue;
                                                }
                                            @endphp

                                            @foreach ($sessions as $index => $sessionValue)
                                                <option value="{{ $sessionValue }}"
                                                    {{ request('session', session('system_session')) == $sessionValue ? 'selected' : '' }}>
                                                    {{ $sessionValue }}{{ $index == 0 ? ' (Current)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="courseRegistrationTabContent">

                                <!-- Registered Courses Tab -->
                                <div class="tab-pane fade show active" id="registered-courses" role="tabpanel">
                                    @php
                                        // Get ALL registered courses for this student (all levels) ordered by level, semester, type
                                        $allRegisteredCourses = DB::table('student_course_registration as scr')
                                            ->join('course', 'scr.code', '=', 'course.code')
                                            ->join('program_course_registration as pcr', function ($join) {
                                                $join
                                                    ->on('scr.code', '=', 'pcr.code')
                                                    ->where('pcr.program', session('program'))
                                                    ->where('pcr.structure_id', session('structure_id'));
                                            })
                                            ->where('scr.username', session('id_number'))
                                            ->where('scr.session', request('session', session('system_session')))
                                            ->select('scr.*', 'course.title', 'pcr.level', 'pcr.type')
                                            ->orderBy('pcr.level')
                                            ->orderBy('scr.semester')
                                            ->orderBy('pcr.type')
                                            ->orderBy('scr.code')
                                            ->get();

                                        $coursesBySemester = $allRegisteredCourses->groupBy('semester');
                                        $semesters = ['FIRST', 'SECOND'];
                                    @endphp

                                    @if (count($allRegisteredCourses) > 0)
                                        <!-- Semester Tabs Navigation -->
                                        <ul class="nav nav-pills nav-fill mb-4" id="semesterTabs" role="tablist">
                                            @foreach ($semesters as $index => $semester)
                                                @if (isset($coursesBySemester[$semester]) && count($coursesBySemester[$semester]) > 0)
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}"
                                                            id="semester-{{ $semester }}-tab" data-bs-toggle="pill"
                                                            data-bs-target="#semester-{{ $semester }}"
                                                            type="button" role="tab">
                                                            {{ $semester }}
                                                            <span
                                                                class="badge bg-light text-dark ms-2">{{ count($coursesBySemester[$semester]) }}</span>
                                                        </button>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>

                                        <!-- Bulk Actions -->
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <button type="button" class="btn btn-outline-danger"
                                                            id="bulkRemoveBtn" style="display: none;"
                                                            onclick="bulkRemoveCourses()">
                                                            <i class="fas fa-trash me-2"></i>Remove Selected (<span
                                                                id="selectedCount">0</span>)
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            id="clearAllBtn" style="display: none;"
                                                            onclick="clearAllSelections()">
                                                            <i class="fas fa-times me-1"></i>Clear All
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Semester Tab Content -->
                                        <div class="tab-content" id="semesterTabContent">
                                            @foreach ($semesters as $index => $semester)
                                                @if (isset($coursesBySemester[$semester]) && count($coursesBySemester[$semester]) > 0)
                                                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
                                                        id="semester-{{ $semester }}" role="tabpanel">
                                                        <!-- Mobile-friendly cards for small screens -->
                                                        <div class="d-md-none">
                                                            @php $sn = 1; @endphp
                                                            @foreach ($coursesBySemester[$semester] as $course)
                                                                @php
                                                                    $change = DB::table('program_course_registration')
                                                                        ->where([
                                                                            'program' => session('program'),
                                                                            'structure_id' => session('structure_id'),
                                                                            'code' => $course->code,
                                                                        ])
                                                                        ->value('change_semester');
                                                                @endphp
                                                                <div
                                                                    class="card mb-3 border-start border-primary border-3">
                                                                    <div class="card-body p-3">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-start mb-2">
                                                                            <div class="form-check">
                                                                                <input
                                                                                    class="form-check-input bulk-select-checkbox"
                                                                                    type="checkbox"
                                                                                    value="{{ $course->id }}"
                                                                                    id="bulk_{{ $course->id }}">
                                                                                <label class="form-check-label"
                                                                                    for="bulk_{{ $course->id }}">
                                                                                    <h6
                                                                                        class="mb-1 text-primary fw-bold">
                                                                                        {{ $course->code }}</h6>
                                                                                    <p class="mb-1 small">
                                                                                        {{ $course->title ?: 'Course Title' }}
                                                                                    </p>
                                                                                </label>
                                                                            </div>
                                                                            <span
                                                                                class="badge bg-dark">{{ $course->level }}L</span>
                                                                        </div>
                                                                        <div class="row g-2 mb-2">
                                                                            <div class="col-3">
                                                                                <small
                                                                                    class="text-muted d-block">Units</small>
                                                                                <span
                                                                                    class="badge bg-info">{{ $course->unit }}</span>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <small
                                                                                    class="text-muted d-block">Semester</small>
                                                                                @if ($change == 1)
                                                                                    <button
                                                                                        class="btn btn-outline-info btn-sm p-1"
                                                                                        type="button"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#update{{ $course->id }}">
                                                                                        {{ $course->semester }} <i
                                                                                            class="fas fa-edit"></i>
                                                                                    </button>
                                                                                @else
                                                                                    <span
                                                                                        class="badge bg-secondary">{{ $course->semester }}</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <small
                                                                                    class="text-muted d-block">Type</small>
                                                                                <span
                                                                                    class="badge {{ $course->type == 'CORE' ? 'bg-success' : 'bg-warning' }}">
                                                                                    {{ $course->type }}
                                                                                </span>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <small
                                                                                    class="text-muted d-block">Action</small>
                                                                                <button
                                                                                    class="btn btn-outline-danger btn-sm"
                                                                                    onclick="removeCourse('{{ $course->id }}')">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                @if ($change == 1)
                                                                    <!-- Change Semester Modal -->
                                                                    <div id="update{{ $course->id }}"
                                                                        class="modal fade" tabindex="-1"
                                                                        role="dialog">
                                                                        <div class="modal-dialog modal-sm"
                                                                            role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Change
                                                                                        Semester</h5>
                                                                                    <button type="button"
                                                                                        class="btn-close"
                                                                                        data-bs-dismiss="modal"></button>
                                                                                </div>
                                                                                <form action="change-semester"
                                                                                    method="POST">
                                                                                    <div class="modal-body">
                                                                                        @csrf
                                                                                        <input type="hidden"
                                                                                            name="id"
                                                                                            value="{{ $course->id }}">
                                                                                        <div class="mb-3">
                                                                                            <label
                                                                                                class="form-label fw-bold">Select
                                                                                                New Semester:</label>
                                                                                            <select class="form-select"
                                                                                                name="semester"
                                                                                                required>
                                                                                                <option
                                                                                                    value="{{ $course->semester }}">
                                                                                                    Current:
                                                                                                    {{ $course->semester }}
                                                                                                </option>
                                                                                                <option value="FIRST">
                                                                                                    First Semester
                                                                                                </option>
                                                                                                <option value="SECOND">
                                                                                                    Second Semester
                                                                                                </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button"
                                                                                            class="btn btn-secondary"
                                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                                        <button type="submit"
                                                                                            class="btn btn-primary">Save
                                                                                            Changes</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>

                                                        <!-- Desktop table for larger screens -->
                                                        <div class="d-none d-md-block">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped">
                                                                    <thead class="table-primary">
                                                                        <tr>
                                                                            <th width="50">
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input"
                                                                                        type="checkbox"
                                                                                        id="selectAllSemester{{ $semester }}">
                                                                                    <label class="form-check-label"
                                                                                        for="selectAllSemester{{ $semester }}">All</label>
                                                                                </div>
                                                                            </th>
                                                                            <th>Course Code</th>
                                                                            <th>Course Title</th>
                                                                            <th>Units</th>
                                                                            <th>Level</th>
                                                                            <th>Semester</th>
                                                                            <th>Type</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @php $sn = 1; @endphp
                                                                        @foreach ($coursesBySemester[$semester] as $course)
                                                                            @php
                                                                                $change = DB::table(
                                                                                    'program_course_registration',
                                                                                )
                                                                                    ->where([
                                                                                        'program' => session('program'),
                                                                                        'structure_id' => session(
                                                                                            'structure_id',
                                                                                        ),
                                                                                        'code' => $course->code,
                                                                                    ])
                                                                                    ->value('change_semester');
                                                                            @endphp
                                                                            <tr>
                                                                                <td>
                                                                                    <div class="form-check">
                                                                                        <input
                                                                                            class="form-check-input bulk-select-checkbox"
                                                                                            type="checkbox"
                                                                                            value="{{ $course->id }}"
                                                                                            id="bulk_desktop_{{ $course->id }}">
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <strong
                                                                                        class="text-primary">{{ $course->code }}</strong>
                                                                                </td>
                                                                                <td>{{ $course->title ?: 'Course Title' }}
                                                                                </td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge bg-info">{{ $course->unit }}</span>
                                                                                </td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge bg-dark">{{ $course->level }}L</span>
                                                                                </td>
                                                                                <td>
                                                                                    @if ($change == 1)
                                                                                        <button
                                                                                            class="btn btn-outline-info btn-sm"
                                                                                            type="button"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#update{{ $course->id }}">
                                                                                            {{ $course->semester }} <i
                                                                                                class="fas fa-edit ms-1"></i>
                                                                                        </button>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge bg-secondary">{{ $course->semester }}</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge {{ $course->type == 'CORE' ? 'bg-success' : 'bg-warning' }}">
                                                                                        {{ $course->type }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <button
                                                                                        class="btn btn-outline-danger btn-sm"
                                                                                        onclick="removeCourse('{{ $course->id }}')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="row mt-4">
                                            @if (session('student_session') == session('system_session') || strpos(session('faculty'), '.PG') !== false)
                                                <div class="col-md-6 mb-3">
                                                    <form action="create {{ $page }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" value="new" name="register">
                                                        <button type="submit" class="btn btn-warning btn-lg w-100">
                                                            <i class="fas fa-redo me-2"></i>Regenerate Core Courses
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            <div class="col-md-6 mb-3">
                                                <button class="btn btn-info btn-sm w-100" data-bs-toggle="modal"
                                                    data-bs-target="#printModal">
                                                    <i class="fas fa-print me-2"></i>Print Course Registration
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No courses registered yet</h5>
                                            <p class="text-muted">Switch to the "Register Courses" tab to select
                                                courses</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Register Courses Tab -->
                                <div class="tab-pane fade" id="register-courses" role="tabpanel">
                                    @php
                                        // Get all available courses and group by level (only up to student's current level)
$availableCourses = DB::table('program_course_registration')
    ->join('course', 'program_course_registration.code', '=', 'course.code')
    ->where('program_course_registration.program', session('program'))
    ->where('program_course_registration.structure_id', session('structure_id'))
    ->where('program_course_registration.level', '<=', session('level'))
    ->select(
        'course.*',
        'program_course_registration.semester',
        'program_course_registration.type',
        'program_course_registration.level',
    )
    ->orderBy('program_course_registration.level')
    ->orderBy('program_course_registration.semester')
    ->orderBy('program_course_registration.type')
    ->orderBy('course.code')
    ->get();

$registeredCourses = collect($data)->pluck('code')->toArray();
$coursesByLevel = $availableCourses->groupBy('level');
                                        $levels = $coursesByLevel->keys()->sortDesc();
                                    @endphp

                                    <form id="courseRegistrationForm" action="/register-my-courses" method="POST">
                                        @csrf
                                        <input type="hidden" name="session"
                                            value="{{ request('session', session('system_session')) }}">

                                        @if (count($levels) > 0)
                                            <!-- Level Tabs Navigation -->
                                            <ul class="nav nav-pills nav-fill mb-4" id="levelTabs" role="tablist">
                                                @foreach ($levels as $index => $level)
                                                    <li class="nav-item" role="presentation">
                                                        <button
                                                            class="nav-link {{ $level == session('level') ? 'active' : '' }}"
                                                            id="level-{{ $level }}-tab" data-bs-toggle="pill"
                                                            data-bs-target="#level-{{ $level }}"
                                                            type="button" role="tab">
                                                            {{ $level }} Level
                                                            <span
                                                                class="badge bg-light text-dark ms-2">{{ count($coursesByLevel[$level]) }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <!-- Level Tab Content -->
                                            <div class="tab-content" id="levelTabContent">
                                                @foreach ($levels as $index => $level)
                                                    <div class="tab-pane fade {{ $level == session('level') ? 'show active' : '' }}"
                                                        id="level-{{ $level }}" role="tabpanel">

                                                        <!-- Mobile-friendly cards for small screens -->
                                                        <div class="d-md-none">
                                                            <div class="mb-3">
                                                                <div class="form-check">
                                                                    <input
                                                                        class="form-check-input select-all-level-mobile"
                                                                        type="checkbox"
                                                                        id="selectAllLevel{{ $level }}Mobile"
                                                                        data-level="{{ $level }}">
                                                                    <label class="form-check-label fw-bold"
                                                                        for="selectAllLevel{{ $level }}Mobile">
                                                                        Select All {{ $level }} Level Courses
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            @foreach ($coursesByLevel[$level] as $course)
                                                                <div
                                                                    class="card mb-3 border-start border-success border-3">
                                                                    <div class="card-body p-3">
                                                                        <div class="form-check mb-2">
                                                                            <input
                                                                                class="form-check-input course-checkbox-mobile course-checkbox-level-{{ $level }}"
                                                                                type="checkbox" name="courses[]"
                                                                                value="{{ json_encode(['code' => $course->code, 'semester' => $course->semester, 'type' => $course->type, 'unit' => $course->unit, 'level' => $level]) }}"
                                                                                id="mobile_course_{{ $course->code }}"
                                                                                data-units="{{ $course->unit }}"
                                                                                data-level="{{ $level }}"
                                                                                {{ in_array($course->code, $registeredCourses) ? 'checked' : '' }}>
                                                                            <label
                                                                                class="form-check-label fw-bold text-primary"
                                                                                for="mobile_course_{{ $course->code }}">
                                                                                {{ $course->code }}
                                                                            </label>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <p class="mb-1 small">{{ $course->title }}
                                                                            </p>
                                                                        </div>
                                                                        <div class="row g-2">
                                                                            <div class="col-4">
                                                                                <small
                                                                                    class="text-muted d-block">Units</small>
                                                                                <span
                                                                                    class="badge bg-info">{{ $course->unit }}</span>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <small
                                                                                    class="text-muted d-block">Semester</small>
                                                                                <span
                                                                                    class="badge bg-secondary">{{ $course->semester }}</span>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <small
                                                                                    class="text-muted d-block">Type</small>
                                                                                <span
                                                                                    class="badge {{ $course->type == 'CORE' ? 'bg-success' : 'bg-warning' }}">
                                                                                    {{ $course->type }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Desktop table for larger screens -->
                                                        <div class="d-none d-md-block">
                                                            <div class="table-responsive">
                                                                <table class="table table-hover">
                                                                    <thead class="table-success">
                                                                        <tr>
                                                                            <th width="50">
                                                                                <div class="form-check">
                                                                                    <input
                                                                                        class="form-check-input select-all-level"
                                                                                        type="checkbox"
                                                                                        id="selectAllLevel{{ $level }}"
                                                                                        data-level="{{ $level }}">
                                                                                    <label class="form-check-label"
                                                                                        for="selectAllLevel{{ $level }}">All</label>
                                                                                </div>
                                                                            </th>
                                                                            <th>Course Code</th>
                                                                            <th>Course Title</th>
                                                                            <th>Units</th>
                                                                            <th>Semester</th>
                                                                            <th>Type</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($coursesByLevel[$level] as $course)
                                                                            <tr class="course-row">
                                                                                <td>
                                                                                    <div class="form-check">
                                                                                        <input
                                                                                            class="form-check-input course-checkbox course-checkbox-level-{{ $level }}"
                                                                                            type="checkbox"
                                                                                            name="desktop_courses[]"
                                                                                            value="{{ json_encode(['code' => $course->code, 'semester' => $course->semester, 'type' => $course->type, 'unit' => $course->unit, 'level' => $level]) }}"
                                                                                            id="course_{{ $course->code }}"
                                                                                            data-units="{{ $course->unit }}"
                                                                                            data-level="{{ $level }}"
                                                                                            {{ in_array($course->code, $registeredCourses) ? 'checked' : '' }}>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <strong
                                                                                        class="text-primary">{{ $course->code }}</strong>
                                                                                </td>
                                                                                <td>{{ $course->title }}</td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge bg-info">{{ $course->unit }}</span>
                                                                                </td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge bg-secondary">{{ $course->semester }}</span>
                                                                                </td>
                                                                                <td>
                                                                                    <span
                                                                                        class="badge {{ $course->type == 'CORE' ? 'bg-success' : 'bg-warning' }}">
                                                                                        {{ $course->type }}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Action Buttons -->
                                            <div
                                                class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-2">
                                                <div>
                                                    <button type="button"
                                                        class="btn btn-outline-secondary btn-sm px-4 w-100 w-md-auto"
                                                        onclick="clearAllCourses()">
                                                        <i class="fas fa-times me-1"></i>Clear All Selected
                                                    </button>
                                                </div>
                                                <div>
                                                    <button type="submit"
                                                        class="btn btn-success btn-sm px-4 w-100 w-md-auto">
                                                        <i class="fas fa-save me-2"></i>Register Selected Courses
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No courses available for registration</h5>
                                                <p class="text-muted">Please contact your academic advisor</p>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Modal -->
            <div id="printModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Print Course Registration</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="/get-registered-courses" method="GET">
                            <div class="modal-body">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Session to Print:</label>
                                    <select class="form-select" name="session" required>
                                        <option value="">Choose session...</option>
                                        @php
                                            // Get current system session and extract year
                                            $currentSession = session('system_session');
                                            $currentYear = (int) substr($currentSession, 0, 4);
                                            $endYear = 2005;

                                            // Generate sessions from current year down to 2005/2006
                                            $sessions = [];
                                            for ($year = $currentYear; $year >= $endYear; $year--) {
                                                $nextYear = $year + 1;
                                                $sessionValue = $year . '/' . $nextYear;
                                                $sessions[] = $sessionValue;
                                            }
                                        @endphp

                                        @foreach ($sessions as $index => $sessionValue)
                                            <option value="{{ $sessionValue }}"
                                                {{ request('session', session('system_session')) == $sessionValue ? 'selected' : '' }}>
                                                {{ $sessionValue }}{{ $index == 0 ? ' (Current)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-print me-2"></i>Generate Print
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h4>Course Registration Not Available</h4>
                <p class="text-muted">Course registration is not enabled for your program at this time.</p>
            </div>
        @endif
    </div>
</div>

<!-- Custom CSS -->
<style>
    .course-row:hover {
        background-color: #f8f9fa;
    }

    .course-checkbox:checked+label {
        font-weight: bold;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75em;
    }

    .nav-tabs .nav-link {
        color: #495057;
        border: 1px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin: 2px 0;
        }

        .card-body {
            padding: 1rem !important;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.7em;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .nav-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .fs-5 {
            font-size: 1.1rem !important;
        }
    }
</style>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const courseCheckboxes = document.querySelectorAll('.course-checkbox');
        const totalCoursesSpan = document.getElementById('totalCourses');
        const firstSemesterCountSpan = document.getElementById('firstSemesterCount');
        const secondSemesterCountSpan = document.getElementById('secondSemesterCount');

        // Update counters
        function updateCounters() {
            // Count registered courses by semester from the page data
            const firstSemesterTab = document.getElementById('semester-FIRST');
            const secondSemesterTab = document.getElementById('semester-SECOND');

            let totalRegistered = 0;
            let firstSemesterTotal = 0;
            let secondSemesterTotal = 0;

            // Count FIRST semester courses (count unique courses, not checkboxes)
            if (firstSemesterTab) {
                const firstSemesterCourses = firstSemesterTab.querySelectorAll(
                    '.d-md-none .bulk-select-checkbox');
                firstSemesterTotal = firstSemesterCourses.length;
                totalRegistered += firstSemesterTotal;
            }

            // Count SECOND semester courses (count unique courses, not checkboxes)
            if (secondSemesterTab) {
                const secondSemesterCourses = secondSemesterTab.querySelectorAll(
                    '.d-md-none .bulk-select-checkbox');
                secondSemesterTotal = secondSemesterCourses.length;
                totalRegistered += secondSemesterTotal;
            }

            // Update display
            totalCoursesSpan.textContent = totalRegistered;
            firstSemesterCountSpan.textContent = firstSemesterTotal;
            secondSemesterCountSpan.textContent = secondSemesterTotal;
        }

        // Select All functionality (legacy - may not exist in level-based tabs)
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                courseCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateCounters();
            });
        }

        // Individual checkbox change (legacy - kept for compatibility)
        courseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCounters();

                // Update select all checkbox state (count only desktop checkboxes to avoid double counting)
                const checkedCount = document.querySelectorAll(
                        '.d-none .course-checkbox:checked')
                    .length;
                const totalDesktopCheckboxes = document.querySelectorAll(
                    '.d-none .course-checkbox').length;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = checkedCount === totalDesktopCheckboxes;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount <
                        totalDesktopCheckboxes;
                }
            });
        });

        // Level-based Select All functionality
        const selectAllLevelCheckboxes = document.querySelectorAll('.select-all-level');
        const selectAllLevelMobileCheckboxes = document.querySelectorAll('.select-all-level-mobile');

        // Desktop level select all
        selectAllLevelCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const level = this.dataset.level;
                const levelCheckboxes = document.querySelectorAll(
                    `.course-checkbox-level-${level}`);

                levelCheckboxes.forEach(courseCheckbox => {
                    courseCheckbox.checked = this.checked;
                });
                updateCounters();
            });
        });

        // Mobile level select all
        selectAllLevelMobileCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const level = this.dataset.level;
                const levelCheckboxes = document.querySelectorAll(
                    `.course-checkbox-level-${level}`);

                levelCheckboxes.forEach(courseCheckbox => {
                    courseCheckbox.checked = this.checked;
                });
                updateCounters();
            });
        });

        // Sync mobile and desktop checkboxes
        function syncCheckboxes(sourceCheckbox) {
            const courseCode = sourceCheckbox.id.replace('mobile_course_', '').replace('course_', '');
            const mobileCheckbox = document.getElementById(`mobile_course_${courseCode}`);
            const desktopCheckbox = document.getElementById(`course_${courseCode}`);

            if (mobileCheckbox && desktopCheckbox) {
                mobileCheckbox.checked = sourceCheckbox.checked;
                desktopCheckbox.checked = sourceCheckbox.checked;
            }
        }

        // Add sync listeners to all course checkboxes
        document.querySelectorAll('.course-checkbox, .course-checkbox-mobile').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                syncCheckboxes(this);
                updateCounters();
            });
        });

        // Individual checkbox changes (both desktop and mobile)
        const allCourseCheckboxes = document.querySelectorAll('.course-checkbox, .course-checkbox-mobile');
        allCourseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCounters();

                // Update the corresponding level select all checkbox (count only mobile to avoid double counting)
                const level = this.dataset.level;
                const levelCheckboxesMobile = document.querySelectorAll(
                    `.d-md-none .course-checkbox-level-${level}`);
                const checkedLevelCount = document.querySelectorAll(
                    `.d-md-none .course-checkbox-level-${level}:checked`).length;

                // Update desktop level select all
                const levelSelectAll = document.getElementById(`selectAllLevel${level}`);
                if (levelSelectAll) {
                    levelSelectAll.checked = checkedLevelCount === levelCheckboxesMobile.length;
                    levelSelectAll.indeterminate = checkedLevelCount > 0 && checkedLevelCount <
                        levelCheckboxesMobile.length;
                }

                // Update mobile level select all
                const levelSelectAllMobile = document.getElementById(
                    `selectAllLevel${level}Mobile`);
                if (levelSelectAllMobile) {
                    levelSelectAllMobile.checked = checkedLevelCount === levelCheckboxesMobile
                        .length;
                    levelSelectAllMobile.indeterminate = checkedLevelCount > 0 &&
                        checkedLevelCount < levelCheckboxesMobile.length;
                }
            });
        });

        // Clear All Courses function
        window.clearAllCourses = function() {
            allCourseCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Reset all level select all checkboxes
            selectAllLevelCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.indeterminate = false;
            });
            selectAllLevelMobileCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.indeterminate = false;
            });

            updateCounters();
        };

        // Bulk selection functionality
        const bulkSelectCheckboxes = document.querySelectorAll('.bulk-select-checkbox');
        const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Update bulk remove button visibility and count
        function updateBulkRemoveButton() {
            // Count all bulk select checkboxes (both mobile and desktop)
            const selectedCheckboxes = document.querySelectorAll('.bulk-select-checkbox:checked');
            const count = selectedCheckboxes.length;
            const clearAllBtn = document.getElementById('clearAllBtn');

            if (count > 0) {
                bulkRemoveBtn.style.display = 'inline-block';
                clearAllBtn.style.display = 'inline-block';
                selectedCountSpan.textContent = count;
            } else {
                bulkRemoveBtn.style.display = 'none';
                clearAllBtn.style.display = 'none';
            }
        }

        // Add event listeners to bulk select checkboxes
        bulkSelectCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkRemoveButton);
        });

        // Semester select all functionality
        const semesterSelectAllCheckboxes = document.querySelectorAll('[id^="selectAllSemester"]');
        semesterSelectAllCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const semester = this.id.replace('selectAllSemester', '');
                // Count only mobile checkboxes to avoid double counting
                const semesterCheckboxes = document.querySelectorAll(
                    `#semester-${semester} .d-md-none .bulk-select-checkbox`);

                // Also update desktop checkboxes to keep them in sync
                const desktopCheckboxes = document.querySelectorAll(
                    `#semester-${semester} .d-none .bulk-select-checkbox`);

                semesterCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                desktopCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBulkRemoveButton();
            });
        });

        // Bulk remove function
        window.bulkRemoveCourses = function() {
            // Get all selected checkboxes (both mobile and desktop)
            const selectedCheckboxes = document.querySelectorAll('.bulk-select-checkbox:checked');
            const courseIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            // Remove duplicates (in case both mobile and desktop are checked)
            const uniqueCourseIds = [...new Set(courseIds)];

            if (uniqueCourseIds.length === 0) return;

            if (confirm(
                    `Are you sure you want to remove ${uniqueCourseIds.length} selected course(s) from your registration?`
                )) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/delete-my-course';

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }

                uniqueCourseIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        };

        // Clear all selections function
        window.clearAllSelections = function() {
            bulkSelectCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            semesterSelectAllCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateBulkRemoveButton();
        };

        // Remove course function
        window.removeCourse = function(courseId) {
            if (confirm('Are you sure you want to remove this course from your registration?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/delete-my-course';

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = courseId;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        };

        // Initialize counters and bulk button
        updateCounters();
        updateBulkRemoveButton();
    });
</script>
