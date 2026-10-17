@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $hasPrerequisites = Schema::hasColumn('program_course_registration', 'prerequisites');

    $courseFlag = DB::table('program')
        ->where('code', session('program'))
        ->value('courses');

    $studentCurrentLevel = (int) (Student::where('user_id', session('id'))->value('level')
        ?? Student::where('username', session('id_number'))->value('level')
        ?? session('current_level')
        ?? session('level')
        ?? 0);
    $studentEntryMode = strtoupper((string) (Student::where('user_id', session('id'))->value('mode_of_entry')
        ?? Student::where('username', session('id_number'))->value('mode_of_entry')
        ?? ''));

    $selectedSession = request('session', session('system_session'));

    $registeredSelect = [
        'scr.*',
        'course.title',
        'pcr.change_semester',
        'pcr.type as programme_type',
        'pcr.de as programme_de',
    ];
    if ($hasPrerequisites) {
        $registeredSelect[] = 'pcr.prerequisites';
    }

    $registeredCourses = DB::table('student_course_registration as scr')
        ->join('course', 'scr.code', '=', 'course.code')
        ->leftJoin('program_course_registration as pcr', function ($join) {
            $join->on('scr.code', '=', 'pcr.code')
                ->where('pcr.program', session('program'))
                ->where('pcr.structure_id', session('structure_id'))
                ->whereColumn('pcr.level', 'scr.level');
        })
        ->where('scr.username', session('id_number'))
        ->where('scr.session', $selectedSession)
        ->select($registeredSelect)
        ->orderBy('scr.level')
        ->orderBy('scr.semester')
        ->orderBy('scr.code')
        ->get();

    $availableSelect = [
        'course.code',
        'course.title',
        'course.unit',
        'pcr.semester',
        'pcr.type',
        'pcr.level',
        'pcr.elective',
        'pcr.change_semester',
        'pcr.de',
    ];
    if ($hasPrerequisites) {
        $availableSelect[] = 'pcr.prerequisites';
    }

    $availableCourses = DB::table('program_course_registration as pcr')
        ->join('course', 'pcr.code', '=', 'course.code')
        ->where('pcr.program', session('program'))
        ->where('pcr.structure_id', session('structure_id'))
        ->where('pcr.level', '<=', $studentCurrentLevel)
        ->select($availableSelect)
        ->orderBy('pcr.level')
        ->orderBy('pcr.semester')
        ->orderBy('pcr.type')
        ->orderBy('course.code')
        ->get();

    $registeredCodes = $registeredCourses->pluck('code')->map(fn ($code) => strtoupper(trim((string) $code)))->all();

    // A failed course is carried into the current session by the academic system.
    // We keep the codes so they can be highlighted, but every earlier-level course
    // remains available for a student who needs to carry it over.
    // Carryovers are based on the latest approved result, not simply on a
    // course being below the student's current level. This keeps the page
    // honest: earlier-level courses are options, while only an approved F is
    // marked as a genuine carryover.
    $carryoverCodes = collect();
    if (Schema::hasTable('results')) {
        $approvedResults = DB::table('results')
            ->where('username', session('id_number'))
            ->where('approve', 'vc')
            ->whereNotNull('code')
            ->orderByDesc('session')
            ->orderByDesc('id')
            ->get(['code', 'grade']);

        $latestResultByCode = $approvedResults->groupBy(fn ($result) => strtoupper(trim((string) $result->code)))
            ->map(fn ($rows) => $rows->first());
        $carryoverCodes = $latestResultByCode
            ->filter(fn ($result) => strtoupper(trim((string) ($result->grade ?? ''))) === 'F')
            ->keys()
            ->values();
    }

    $allCourseLevels = $availableCourses->groupBy('level');
    $currentLevelKey = $allCourseLevels->keys()->first(function ($level) use ($studentCurrentLevel) {
        return (int) $level === $studentCurrentLevel;
    });
    $courseLevels = collect();
    $levels = collect();

    // Always put the student's current level first.
    if ($currentLevelKey !== null) {
        $courseLevels->put($currentLevelKey, $allCourseLevels->get($currentLevelKey));
        $levels->push($currentLevelKey);
    }

    // Add only lower-level courses that are actually carried into this session.
    foreach ($allCourseLevels->keys()->sortDesc() as $level) {
        if ((int) $level >= $studentCurrentLevel || $level === $currentLevelKey) {
            continue;
        }
        $courseLevels->put($level, $allCourseLevels->get($level)->values());
        $levels->push($level);
    }

    $carryoverCount = $levels->filter(function ($level) use ($studentCurrentLevel) {
        return (int) $level < $studentCurrentLevel;
    })->sum(function ($level) use ($courseLevels) {
        return $courseLevels->get($level, collect())->count();
    });
    $confirmedCarryoverCount = $availableCourses->filter(function ($course) use ($carryoverCodes, $studentCurrentLevel) {
        return (int) $course->level < $studentCurrentLevel
            && $carryoverCodes->contains(strtoupper(trim((string) $course->code)));
    })->count();
    $sessions = DB::table('session')->orderBy('title', 'desc')->get();
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-body ug-course-page">
    <div class="page-wrapper">
        @if ($courseFlag == 1 || strpos((string) session('faculty'), '.PG') !== false)
            <div class="ug-course-shell">
                <div class="ug-course-stats" aria-label="Registration summary">
                    <div class="ug-session-stat"><span class="ug-stat-icon blue"><i class="fas fa-calendar-alt"></i></span><div class="ug-session-stat-content"><small>Registration session</small><form action="{{ url('/student course registration') }}" method="GET"><select id="registrationSession" name="session" onchange="this.form.submit()" aria-label="Choose registration session">@foreach ($sessions as $sessionRow) @php $sessionTitle = $sessionRow->title ?? $sessionRow->session ?? ''; @endphp @if ($sessionTitle !== '')<option value="{{ $sessionTitle }}" {{ $selectedSession == $sessionTitle ? 'selected' : '' }}>{{ $sessionTitle }}{{ $selectedSession == $sessionTitle && $sessionTitle == session('system_session') ? ' (Current)' : '' }}</option>@endif @endforeach</select></form></div></div>
                    <div><span class="ug-stat-icon blue"><i class="fas fa-book"></i></span><span><strong id="registeredCount">{{ $registeredCourses->count() }}</strong><small>Registered</small></span></div>
                    <div><span class="ug-stat-icon green"><i class="fas fa-check-circle"></i></span><span><strong id="selectedCount">0</strong><small>Selected now</small></span></div>
                    <div><span class="ug-stat-icon amber"><i class="fas fa-layer-group"></i></span><span><strong id="selectedUnits">0</strong><small>Selected units</small></span></div>
                    <div><span class="ug-stat-icon purple"><i class="fas fa-calendar-alt"></i></span><span><strong>{{ $studentCurrentLevel ?: '—' }}</strong><small>Current level</small></span></div>
                </div>

                <div class="ug-course-tabs" role="tablist" aria-label="Course registration sections">
                    <button class="ug-tab active" type="button" data-target="registeredPanel" aria-selected="true"><i class="fas fa-list-check"></i> Saved courses</button>
                    <button class="ug-tab" type="button" data-target="selectPanel" aria-selected="false"><i class="fas fa-plus-circle"></i> Choose courses</button>
                </div>

                <section id="registeredPanel" class="ug-panel" role="tabpanel">
                    <div class="ug-panel-heading">
                        <div>
                            <div class="ug-heading-kicker"><i class="fas fa-circle-check"></i> {{ $selectedSession }}</div>
                            <h2>Saved course registration</h2>
                            <p>{{ $registeredCourses->count() ? 'These are the courses currently saved for this session. You can remove a course or change an eligible semester.' : 'Nothing is saved for this session yet. Choose your courses to begin.' }}</p>
                        </div>
                        <button type="button" class="ug-primary-button ug-heading-action" data-target="selectPanel"><i class="fas fa-plus"></i> Add courses</button>
                    </div>

                    @if ($registeredCourses->count())
                        <div class="ug-unregister-toolbar">
                            <label class="ug-select-all"><input type="checkbox" id="selectAllRegistered"><span>Select all</span></label>
                            <div class="ug-unregister-actions">
                                <span id="registeredSelectionCount">0 selected</span>
                                <button type="button" class="ug-danger-button" id="unregisterSelected" disabled><i class="fas fa-trash-alt"></i> Unregister selected</button>
                            </div>
                        </div>
                        <div class="ug-semester-sections">
                            @foreach (['FIRST' => 'First semester', 'SECOND' => 'Second semester'] as $semesterCode => $semesterLabel)
                                @php $semesterCourses = $registeredCourses->where('semester', $semesterCode); @endphp
                                @if ($semesterCourses->count())
                                    <div class="ug-semester-block">
                                        <div class="ug-semester-heading"><span>{{ $semesterLabel }}</span><b>{{ $semesterCourses->count() }} course{{ $semesterCourses->count() === 1 ? '' : 's' }}</b></div>
                                        <div class="ug-registered-list">
                                            @foreach ($semesterCourses as $course)
                                                @php $canChangeSemester = (string) ($course->change_semester ?? '0') === '1'; @endphp
                                                <article class="ug-registered-card">
                                                    <label class="ug-registered-check" title="Select {{ $course->code }}"><input type="checkbox" class="registered-choice" value="{{ $course->id }}"><span></span></label>
                                                    <div class="ug-registered-main"><strong class="ug-course-code">{{ $course->code }}</strong><small class="ug-course-name">{{ $course->title ?: 'Course title unavailable' }}</small><div class="ug-course-badges">@if ($carryoverCodes->contains(strtoupper(trim((string) $course->code))))<span class="ug-carryover-pill"><i class="fas fa-rotate-left"></i> Carryover</span>@endif @if ($studentEntryMode === 'DE' && (int) ($course->programme_de ?? 0) === 1 && (int) $course->level < $studentCurrentLevel)<span class="ug-foundation-pill">Direct-entry foundation</span>@endif</div></div>
                                                    <div class="ug-course-actions">
                                                        @if ($canChangeSemester)
                                                            <form action="{{ url('/change-semester') }}" method="POST" class="ug-semester-form">
                                                                @csrf
                                                                <input type="hidden" name="id" value="{{ $course->id }}">
                                                                <select id="semester-{{ $course->id }}" name="semester" onchange="this.form.submit()" aria-label="Change semester for {{ $course->code }}">
                                                                    <option value="FIRST" {{ $course->semester === 'FIRST' ? 'selected' : '' }}>First</option>
                                                                    <option value="SECOND" {{ $course->semester === 'SECOND' ? 'selected' : '' }}>Second</option>
                                                                </select>
                                                            </form>
                                                        @endif
                                                        <button type="button" class="ug-delete-button" onclick="removeRegisteredCourse('{{ $course->id }}', '{{ $course->code }}')" aria-label="Remove {{ $course->code }}"><i class="fas fa-trash-alt"></i><span>Remove</span></button>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @if (session('student_session') == session('system_session') || strpos((string) session('faculty'), '.PG') !== false)
                            <form action="{{ url('/create ' . $page) }}" method="POST" class="ug-regenerate-form">
                                @csrf
                                <input type="hidden" name="register" value="new">
                                <button type="submit" class="ug-outline-button"><i class="fas fa-rotate"></i> Regenerate core courses</button>
                            </form>
                        @endif
                    @else
                        <div class="ug-empty-state"><span><i class="fas fa-book-open"></i></span><h3>No courses saved yet</h3><p>Open “Choose courses” to select what you want to register for this session.</p><button type="button" class="ug-primary-button" data-target="selectPanel"><i class="fas fa-plus"></i> Choose courses</button></div>
                    @endif
                </section>

                <section id="selectPanel" class="ug-panel" role="tabpanel" hidden>
                    <div class="ug-panel-heading">
                        <div>
                            <div class="ug-heading-kicker"><i class="fas fa-book-open"></i> {{ $selectedSession }}</div>
                            <h2>Select your courses</h2>
                            <p>Open your level, tick the courses you will take, then save once. Your current level is shown first.</p>
                        </div>
                        <button type="button" class="ug-clear-button" id="clearCourses"><i class="fas fa-rotate-left"></i> Clear selection</button>
                    </div>

                    @if ($levels->count())
                        <form id="courseRegistrationForm" action="{{ url('/register-my-courses') }}" method="POST">
                            @csrf
                            <input type="hidden" name="session" value="{{ $selectedSession }}">
                            @if ($carryoverCount > 0)
                                <div class="ug-carryover-note"><i class="fas fa-circle-exclamation"></i><span><strong>{{ $confirmedCarryoverCount ? $confirmedCarryoverCount . ' carryover course' . ($confirmedCarryoverCount === 1 ? '' : 's') . ' found.' : 'Earlier-level courses are available.' }}</strong> Your current level is shown first. If you still need a course from a lower level, open <strong>Earlier-level courses</strong> and select it here. Only courses you tick will be saved.</span></div>
                            @else
                                <div class="ug-help-note"><i class="fas fa-circle-info"></i><span>Your current level is shown first. Select the courses you need, then click <strong>Save registration</strong>. You can review your choices before saving.</span></div>
                            @endif

                            <div class="ug-selection-tools" role="search" aria-label="Find courses">
                                <label class="ug-search-field" for="courseSearch"><i class="fas fa-search"></i><input id="courseSearch" type="search" placeholder="Search by course code or title" autocomplete="off"><span class="sr-only">Search by course code or title</span></label>
                                <label class="ug-filter-field" for="courseTypeFilter"><span>Type</span><select id="courseTypeFilter"><option value="ALL">All types</option><option value="CORE">Core courses</option><option value="ELECTIVE">Elective courses</option></select></label>
                                <label class="ug-filter-field" for="courseSemesterFilter"><span>Semester</span><select id="courseSemesterFilter"><option value="ALL">All semesters</option><option value="FIRST">First semester</option><option value="SECOND">Second semester</option><option value="THIRD">Third semester</option></select></label>
                                <span class="ug-visible-count" id="visibleCourseCount" aria-live="polite">{{ $availableCourses->count() }} courses</span>
                            </div>

                            @foreach ($levels as $level)
                                @php
                                    $levelCourses = $courseLevels[$level];
                                    $isCurrentLevel = (int) $level === $studentCurrentLevel;
                                @endphp
                                <details class="ug-level-section {{ $isCurrentLevel ? 'current-level' : 'carryover-level' }}" {{ $isCurrentLevel ? 'open' : '' }}>
                                    <summary class="ug-level-heading">
                                        <div><span class="ug-level-number">{{ $level }}</span><div><h3>{{ $isCurrentLevel ? 'Your current level' : (($studentEntryMode === 'DE' && (int) $level < $studentCurrentLevel) ? 'Foundation courses for direct entry' : 'Earlier-level courses') }}</h3><p>{{ $levelCourses->count() }} course{{ $levelCourses->count() === 1 ? '' : 's' }}{{ !$isCurrentLevel ? ' · select only if needed' : '' }}</p></div></div>
                                        <label class="ug-select-all"><input type="checkbox" class="select-level" data-level="{{ $level }}"><span>Select all</span></label>
                                    </summary>
                                    <div class="ug-course-grid">
                                        @foreach ($levelCourses as $course)
                                            @php
                                                $courseData = ['code' => $course->code, 'semester' => $course->semester, 'type' => $course->type, 'unit' => $course->unit, 'level' => $course->level];
                                                $changeAllowed = (string) ($course->change_semester ?? '0') === '1';
                                                $isDirectEntryFoundation = $studentEntryMode === 'DE' && (int) ($course->de ?? 0) === 1 && (int) $course->level < $studentCurrentLevel;
                                            @endphp
                                            @php $isConfirmedCarryover = $carryoverCodes->contains(strtoupper(trim((string) $course->code))); @endphp
                                            <article class="ug-select-card {{ in_array(strtoupper(trim((string) $course->code)), $registeredCodes, true) ? 'is-registered' : '' }}" data-course-card data-search="{{ strtolower(trim(($course->code ?? '') . ' ' . ($course->title ?? ''))) }}" data-course-type="{{ strtoupper($course->type) }}" data-course-semester="{{ strtoupper($course->semester) }}">
                                                <label class="ug-course-select-label">
                                                    <input type="checkbox" class="course-choice" name="courses[]" value="{{ json_encode($courseData) }}" data-level="{{ $level }}" data-code="{{ $course->code }}" data-unit="{{ $course->unit }}" {{ in_array(strtoupper(trim((string) $course->code)), $registeredCodes, true) ? 'checked' : '' }}>
                                                    <span class="ug-checkmark"><i class="fas fa-check"></i></span>
                                                    <span class="ug-course-main"><strong>{{ $course->code }}</strong><span>{{ $course->title ?: 'Course title unavailable' }}</span></span>
                                                </label>
                                                <div class="ug-select-meta"><span class="ug-unit-pill">{{ $course->unit }} unit{{ (int) $course->unit === 1 ? '' : 's' }}</span><span class="ug-type {{ strtoupper($course->type) === 'CORE' ? 'core' : 'elective' }}">{{ ucfirst(strtolower($course->type)) }}</span>@if ($isConfirmedCarryover)<span class="ug-carryover-pill"><i class="fas fa-rotate-left"></i> Carryover</span>@endif @if ($isDirectEntryFoundation)<span class="ug-foundation-pill">Direct-entry foundation</span>@endif</div>
                                                @if (!empty($course->prerequisites))
                                                    <div class="ug-prerequisite-note"><i class="fas fa-link"></i><span><strong>Prerequisite:</strong> {{ $course->prerequisites }}</span></div>
                                                @endif
                                                @if ($changeAllowed)
                                                    <label class="ug-change-semester"><span><i class="fas fa-calendar-alt"></i> Choose semester</span><select class="course-semester" data-code="{{ $course->code }}" aria-label="Semester for {{ $course->code }}"><option value="FIRST" {{ $course->semester === 'FIRST' ? 'selected' : '' }}>First semester</option><option value="SECOND" {{ $course->semester === 'SECOND' ? 'selected' : '' }}>Second semester</option></select></label>
                                                @else
                                                    <div class="ug-fixed-choice"><i class="fas fa-calendar-check"></i> {{ ucfirst(strtolower($course->semester)) }} semester</div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach

                            <div class="ug-save-bar">
                                <div><strong><span id="saveCount">0</span> course<span id="savePlural">s</span> selected</strong><span><span id="saveUnits">0</span> total units</span></div>
                                <button type="submit" class="ug-primary-button" id="saveRegistration"><i class="fas fa-save"></i><span>Save registration</span></button>
                            </div>
                            <div class="ug-no-course-match" id="noCourseMatch" hidden><i class="fas fa-search"></i><strong>No courses match your search.</strong><span>Try another code, title, type or semester.</span></div>
                        </form>
                    @else
                        <div class="ug-empty-state"><span><i class="fas fa-book-open"></i></span><h3>No courses are available</h3><p>Please contact your department if you believe this is incorrect.</p></div>
                    @endif
                </section>
            </div>

        @else
            <div class="ug-course-shell"><div class="ug-empty-state"><span class="warning"><i class="fas fa-lock"></i></span><h3>Course registration is not available</h3><p>Your programme is not enabled for online course registration at this time.</p></div></div>
        @endif
    </div>
</div>

<style>
    .ug-course-page{background:#f4f7fb;min-height:calc(100vh - 70px);padding:clamp(12px,2vw,28px)}
    .ug-course-shell{max-width:1180px;margin:0 auto}
    .ug-course-hero{background:linear-gradient(125deg,#1457c5,#3ea1e4);color:#fff;border-radius:20px;padding:clamp(20px,4vw,36px);display:flex;align-items:flex-end;justify-content:space-between;gap:24px;box-shadow:0 12px 30px rgba(28,88,173,.18)}
    .ug-eyebrow{font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.82}.ug-course-hero h1{font-size:clamp(1.65rem,3vw,2.35rem);margin:8px 0 6px;font-weight:800}.ug-course-hero p{margin:0;opacity:.88;max-width:610px}.ug-session-picker{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);padding:12px 14px;border-radius:14px;min-width:190px}.ug-session-picker label{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;opacity:.8}.ug-session-picker select{width:100%;border:0;border-radius:8px;background:#fff;color:#17325c;padding:9px 10px;font-weight:700}
    .ug-course-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0}.ug-course-stats>div{background:#fff;border:1px solid #e5ebf3;border-radius:14px;padding:14px;display:flex;align-items:center;gap:10px;min-width:0}.ug-course-stats strong,.ug-course-stats small{display:block}.ug-course-stats strong{font-size:1.2rem;color:#19365e}.ug-course-stats small{font-size:.75rem;color:#72829a;margin-top:2px}.ug-stat-icon{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;flex:0 0 auto}.ug-stat-icon.blue{background:#e8f1ff;color:#2765cb}.ug-stat-icon.green{background:#e7f8ef;color:#16834b}.ug-stat-icon.amber{background:#fff4dc;color:#ad6a00}.ug-stat-icon.purple{background:#f0eaff;color:#7046bf}
    .ug-course-tabs{display:flex;gap:8px;background:#fff;border:1px solid #e5ebf3;padding:6px;border-radius:14px;margin:16px 0}.ug-tab{border:0;background:transparent;color:#64758d;border-radius:10px;padding:12px 18px;font-weight:700;cursor:pointer;flex:1}.ug-tab.active{background:#eaf3ff;color:#155cc6}.ug-tab i{margin-right:7px}.ug-panel{background:#fff;border:1px solid #e5ebf3;border-radius:18px;padding:clamp(16px,3vw,28px);box-shadow:0 8px 22px rgba(29,61,103,.04)}.ug-panel[hidden]{display:none}.ug-panel-heading{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:22px}.ug-panel-heading h2{font-size:1.3rem;margin:0 0 4px;color:#19365e}.ug-panel-heading p{margin:0;color:#72829a;font-size:.9rem}.ug-outline-button,.ug-clear-button,.ug-primary-button{border-radius:10px;border:1px solid #dbe5f0;background:#fff;color:#2a5c9d;padding:10px 14px;font-weight:700;white-space:nowrap;cursor:pointer}.ug-primary-button{background:#1769ce;border-color:#1769ce;color:#fff;box-shadow:0 5px 13px rgba(23,105,206,.2)}.ug-clear-button{color:#677991}.ug-outline-button:hover,.ug-clear-button:hover{background:#f2f6fb}.ug-primary-button:hover{background:#0f58b2;color:#fff}.ug-semester-block{margin-bottom:22px}.ug-semester-heading{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf1f6;padding:0 2px 9px;margin-bottom:10px;color:#274c7b}.ug-semester-heading span{font-weight:800}.ug-semester-heading b{font-size:.78rem;color:#8291a6;font-weight:600}.ug-registered-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ug-registered-card{border:1px solid #e4ebf3;border-radius:13px;padding:14px;display:grid;grid-template-columns:auto 1fr auto;column-gap:13px;row-gap:5px;align-items:center}.ug-course-code{font-weight:800;color:#145dc2;grid-row:span 2}.ug-course-name{color:#2d3e55;font-weight:600;font-size:.9rem}.ug-course-meta{grid-column:2/-1;color:#8391a4;font-size:.76rem;display:flex;gap:10px;flex-wrap:wrap}.ug-type{font-size:.68rem;font-weight:700;border-radius:20px;padding:3px 7px;text-transform:capitalize}.ug-type.core{background:#e5f7ed;color:#15864e}.ug-type.elective{background:#fff3dc;color:#a26500}.ug-course-actions{grid-column:1/-1;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f0f3f7;padding-top:9px;margin-top:5px}.ug-semester-form{display:flex;align-items:center;gap:8px}.ug-semester-form label{font-size:.76rem;color:#76869a}.ug-semester-form select{border:1px solid #cbd9e9;border-radius:7px;padding:5px 7px;color:#24578e;font-weight:700;background:#f9fbfd}.ug-fixed-semester{font-size:.78rem;color:#708197}.ug-delete-button{border:0;background:transparent;color:#bf4c58;font-size:.77rem;font-weight:700;cursor:pointer}.ug-delete-button i{margin-right:4px}.ug-regenerate-form{margin-top:10px}.ug-empty-state{text-align:center;padding:48px 16px;color:#72829a}.ug-empty-state>span{width:58px;height:58px;display:grid;place-items:center;background:#eaf3ff;color:#3472c6;border-radius:50%;margin:0 auto 14px;font-size:1.5rem}.ug-empty-state>span.warning{background:#fff3dc;color:#a76c0d}.ug-empty-state h3{color:#2d4a6b;margin:0 0 7px;font-size:1.1rem}.ug-empty-state p{max-width:460px;margin:0 auto 17px;font-size:.9rem}
    .ug-help-note,.ug-carryover-note{display:flex;align-items:flex-start;gap:10px;border-radius:11px;padding:12px 14px;font-size:.84rem;margin-bottom:22px}.ug-help-note{background:#f0f6ff;color:#486888}.ug-help-note i{color:#2771ce;margin-top:2px}.ug-carryover-note{background:#fff7e6;color:#76551a;border:1px solid #f6dfac}.ug-carryover-note i{color:#c27b08;margin-top:2px}.ug-level-section{margin-bottom:25px}.ug-level-heading{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e9eef5;padding-bottom:10px;margin-bottom:12px}.ug-level-heading>div{display:flex;align-items:center;gap:10px}.ug-level-number{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#e9f2ff;color:#1762c8;font-weight:800}.ug-level-section:not(:first-of-type) .ug-level-number{background:#fff4dd;color:#a56800}.ug-level-heading h3{margin:0;color:#27496e;font-size:1rem}.ug-level-heading p{margin:2px 0 0;color:#8493a7;font-size:.77rem}.ug-select-all{font-size:.8rem;color:#426487;display:flex;align-items:center;gap:7px;cursor:pointer}.ug-select-all input{accent-color:#1769ce;width:16px;height:16px}.ug-course-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.ug-select-card{border:1px solid #e1e9f2;border-radius:13px;padding:14px;transition:.18s;background:#fff}.ug-select-card:has(.course-choice:checked){border-color:#5594e2;background:#f7fbff;box-shadow:0 3px 12px rgba(44,111,192,.08)}.ug-select-card.is-registered{border-left:3px solid #3ea1e4}.ug-course-select-label{display:flex;align-items:flex-start;gap:10px;cursor:pointer}.ug-course-select-label input{position:absolute;opacity:0}.ug-checkmark{width:21px;height:21px;display:grid;place-items:center;border:2px solid #c6d3e2;color:#fff;border-radius:6px;flex:0 0 auto;margin-top:1px}.ug-checkmark i{display:none;font-size:.72rem}.course-choice:checked+.ug-checkmark{background:#1769ce;border-color:#1769ce}.course-choice:checked+.ug-checkmark i{display:block}.ug-course-main{display:flex;flex-direction:column;gap:3px;min-width:0}.ug-course-main strong{color:#1c60bd}.ug-course-main span{color:#41546a;font-size:.84rem;line-height:1.3}.ug-select-meta{display:flex;gap:7px;align-items:center;margin:10px 0 8px 31px}.ug-unit-pill{font-size:.7rem;background:#eef4fa;color:#55708c;border-radius:20px;padding:4px 8px;font-weight:700}.ug-change-semester{display:flex;justify-content:space-between;align-items:center;gap:8px;border-top:1px solid #eef2f7;padding-top:9px;margin-left:31px;font-size:.75rem;color:#426487}.ug-change-semester select{border:1px solid #cbd9e9;border-radius:7px;padding:5px 6px;color:#24578e;background:#fff;font-size:.75rem;max-width:145px}.ug-fixed-choice{border-top:1px solid #eef2f7;padding-top:9px;margin-left:31px;font-size:.75rem;color:#8190a3}.ug-save-bar{position:sticky;bottom:10px;margin-top:20px;background:#172f50;color:#fff;border-radius:14px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 8px 22px rgba(20,48,81,.2);z-index:2}.ug-save-bar>div{display:flex;flex-direction:column;gap:2px;font-size:.78rem}.ug-save-bar strong{font-size:.92rem}.ug-save-bar>div>span{color:#b9cce2}.modal-content{border:0;border-radius:16px}.modal-header{border-bottom:1px solid #edf1f6}.modal-footer{border-top:1px solid #edf1f6}
    @media (max-width:767px){.ug-course-page{padding:10px;background:#f6f8fb}.ug-course-hero{border-radius:15px;padding:20px 16px;display:block}.ug-course-hero h1{font-size:1.65rem}.ug-course-hero p{font-size:.85rem}.ug-session-picker{margin-top:17px}.ug-course-stats{grid-template-columns:repeat(2,1fr);gap:8px;margin:10px 0}.ug-course-stats>div{padding:10px;gap:8px}.ug-stat-icon{width:32px;height:32px;font-size:.85rem}.ug-course-stats strong{font-size:1rem}.ug-course-stats small{font-size:.68rem}.ug-course-tabs{margin:10px 0}.ug-tab{font-size:.78rem;padding:11px 7px}.ug-tab i{margin-right:3px}.ug-panel{border-radius:14px;padding:15px 12px}.ug-panel-heading{display:block;margin-bottom:17px}.ug-panel-heading h2{font-size:1.15rem}.ug-panel-heading p{font-size:.8rem;margin-bottom:12px}.ug-outline-button,.ug-clear-button{font-size:.75rem;padding:8px 10px}.ug-registered-list,.ug-course-grid{grid-template-columns:1fr}.ug-unregister-toolbar{display:block;padding:10px}.ug-unregister-actions{justify-content:flex-start;margin-top:9px}.ug-unregister-actions .ug-danger-button{font-size:.7rem;padding:7px 8px}.ug-registered-card{grid-template-columns:auto 1fr;column-gap:10px;padding:12px 12px 12px 42px}.ug-registered-check{left:13px;top:14px}.ug-course-actions{grid-column:1/-1;align-items:flex-start;gap:8px;flex-wrap:wrap}.ug-semester-form{width:100%;justify-content:space-between}.ug-semester-form select{flex:1;max-width:none}.ug-level-heading{align-items:flex-start}.ug-level-heading h3{font-size:.92rem}.ug-select-all{font-size:.72rem}.ug-select-card{padding:12px}.ug-save-bar{bottom:6px;align-items:stretch}.ug-save-bar .ug-primary-button{font-size:.78rem;padding:9px}.ug-help-note,.ug-carryover-note{font-size:.77rem;padding:10px}.ug-regenerate-form .ug-outline-button{width:100%}}
    .ug-level-section.carryover-level .ug-level-number{background:#fff4dd;color:#a56800}
    .ug-unregister-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;background:#f8fafc;border:1px solid #e5ebf3;border-radius:11px;padding:10px 12px;margin:-4px 0 16px}.ug-unregister-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.ug-unregister-actions>span{font-size:.75rem;color:#7b899c}.ug-danger-button{border:1px solid #f0c5c9;border-radius:8px;background:#fff;color:#b23f4b;padding:7px 10px;font-size:.75rem;font-weight:700;cursor:pointer}.ug-danger-button:hover{background:#fff3f4}.ug-danger-button:disabled{opacity:.45;cursor:not-allowed}.ug-danger-button.subtle{background:#fff7f7}.ug-registered-card{position:relative;padding-left:42px}.ug-registered-check{position:absolute;left:14px;top:15px;cursor:pointer}.ug-registered-check input{position:absolute;opacity:0}.ug-registered-check span{display:block;width:19px;height:19px;border:2px solid #c6d3e2;border-radius:5px;background:#fff}.ug-registered-check input:checked+span{background:#b23f4b;border-color:#b23f4b;box-shadow:inset 0 0 0 4px #fff}
    .ug-course-hero{background:#fff;color:#19365e;border:1px solid #e2eaf4;border-left:5px solid #3ea1e4;box-shadow:0 8px 24px rgba(29,61,103,.07);align-items:center}.ug-hero-copy{display:flex;align-items:center;gap:15px}.ug-hero-icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:#e9f4ff;color:#1971c7;font-size:1.3rem;flex:0 0 auto}.ug-course-hero .ug-eyebrow{color:#477398;opacity:1}.ug-course-hero h1{color:#183b64}.ug-course-hero p{color:#637990;opacity:1}.ug-course-stats{grid-template-columns:repeat(5,minmax(0,1fr))}.ug-session-stat{min-width:0}.ug-session-stat-content{min-width:0;flex:1}.ug-session-stat-content small{white-space:nowrap}.ug-session-stat-content form{margin-top:3px}.ug-session-stat-content select{width:100%;border:1px solid #d4e1ee;border-radius:7px;background:#f8fbfe;color:#24578e;font-size:.78rem;font-weight:700;padding:5px 7px}.ug-session-stat .ug-stat-icon{background:#e9f4ff;color:#1971c7}
    .ug-level-section{border:1px solid #e4ebf3;border-radius:13px;padding:0 14px;margin-bottom:12px;background:#fff}.ug-level-heading{list-style:none;padding:13px 0;margin:0;border:0;cursor:pointer}.ug-level-heading::-webkit-details-marker{display:none}.ug-level-heading:after{content:'+';width:26px;height:26px;border-radius:50%;display:grid;place-items:center;background:#eff5fb;color:#3370a9;font-weight:700}.ug-level-section[open]>.ug-level-heading:after{content:'−';background:#e8f3ff;color:#1769ce}.ug-level-section[open]{border-color:#cddff2;box-shadow:0 4px 14px rgba(44,111,192,.06)}.ug-level-section .ug-course-grid{padding:0 0 15px}.ug-level-section .ug-level-heading>div{min-width:0}
    .ug-prerequisite-note{display:flex;align-items:flex-start;gap:6px;margin:8px 0 0 31px;padding:7px 8px;border-radius:8px;background:#fff8e8;color:#8a610c;font-size:.7rem;line-height:1.35}.ug-prerequisite-note i{margin-top:2px;color:#b57908}.ug-prerequisite-note strong{font-weight:800}
    @media (max-width:767px){.ug-course-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ug-session-stat{grid-column:1/-1}.ug-session-stat-content select{min-height:34px}.ug-hero-copy{align-items:flex-start}.ug-hero-icon{width:40px;height:40px;font-size:1.05rem}}
    /* Saved courses use the same compact code-and-title treatment as Results > Course grades. */
    .ug-registered-card{display:flex;align-items:center;gap:11px;min-height:55px;padding:9px 11px 9px 42px;grid-template-columns:none;column-gap:0;row-gap:0}
    .ug-registered-main{min-width:0;flex:1}.ug-registered-main .ug-course-code{display:block;grid-row:auto;font-size:.78rem;line-height:1.15}.ug-registered-main .ug-course-name{display:block;margin-top:3px;color:#71849a;font-size:.67rem;font-weight:400;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-course-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex:0 0 auto;grid-column:auto;border-top:0;padding:0;margin:0}.ug-semester-form{display:flex;align-items:center;gap:0}.ug-semester-form select{font-size:.7rem;padding:5px 7px;min-width:70px}.ug-delete-button{font-size:.7rem;padding:5px 0}.ug-delete-button i{margin-right:3px}
    @media (max-width:767px){.ug-registered-card{display:flex;align-items:center;min-height:53px;padding:9px 9px 9px 42px}.ug-registered-main .ug-course-code{font-size:.75rem}.ug-registered-main .ug-course-name{font-size:.63rem}.ug-course-actions{gap:6px}.ug-semester-form select{min-width:64px;font-size:.66rem;padding:5px}.ug-delete-button{font-size:0}.ug-delete-button i{font-size:.78rem;margin:0}}
    .ug-registered-card{background:#fbfdff;transition:border-color .18s ease,box-shadow .18s ease,background .18s ease}.ug-registered-card:hover{border-color:#a9cde9;box-shadow:0 4px 14px rgba(44,111,192,.08)}.ug-registered-card:has(.registered-choice:checked){background:#fff8f8;border-color:#edc2c7}.ug-registered-main .ug-course-code{letter-spacing:.01em}.ug-course-actions .ug-semester-form select{border-color:#c9dbea;background:#fff}.ug-delete-button{border-radius:7px}.ug-delete-button:hover{background:#fff0f1;color:#a72f3d}
</style>
<style>
    .ug-foundation-pill{font-size:.68rem;background:#fff4df;color:#996000;border:1px solid #f2d79f;border-radius:20px;padding:4px 8px;font-weight:700}
</style>
<style>
    .ug-heading-kicker{display:inline-flex;align-items:center;gap:6px;color:#3576ae;font-size:.68rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}.ug-heading-kicker i{color:#3ea1e4}.ug-heading-action{font-size:.78rem;padding:9px 12px}.ug-course-badges{display:flex;align-items:center;gap:5px;flex-wrap:wrap;margin-top:5px}.ug-carryover-pill{display:inline-flex;align-items:center;gap:4px;border-radius:20px;padding:3px 7px;background:#fff2d8;border:1px solid #f2d39a;color:#96610b;font-size:.62rem;font-weight:800;line-height:1.1}.ug-carryover-pill i{font-size:.58rem}.ug-selection-tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:-5px 0 16px;padding:10px;background:#f7fafc;border:1px solid #e3ebf3;border-radius:11px}.ug-search-field{display:flex;align-items:center;gap:8px;min-width:210px;flex:1;border:1px solid #d7e2ec;border-radius:8px;background:#fff;padding:0 10px;color:#7a91a7}.ug-search-field i{font-size:.77rem}.ug-search-field input{width:100%;min-width:0;border:0;outline:0;background:transparent;color:#264968;padding:8px 0;font-size:.76rem}.ug-filter-field{display:flex;align-items:center;gap:6px;color:#71869b;font-size:.68rem;font-weight:700}.ug-filter-field select{border:1px solid #d7e2ec;border-radius:8px;background:#fff;color:#315a7c;padding:7px 8px;font-size:.72rem;font-weight:700}.ug-visible-count{color:#72879a;font-size:.69rem;font-weight:700;margin-left:auto;white-space:nowrap}.ug-select-card.is-filtered-out{display:none}.ug-level-section.is-filtered-out{display:none}.ug-no-course-match{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;margin:10px 0 0;padding:22px 12px;border:1px dashed #cddce9;border-radius:10px;background:#fbfdff;color:#71869b;font-size:.78rem}.ug-no-course-match i{color:#5895c2}.ug-no-course-match strong{color:#40617d}.ug-save-bar .ug-primary-button:disabled{opacity:.62;cursor:not-allowed;box-shadow:none}.ug-panel-heading h2{letter-spacing:-.01em}.ug-level-section.current-level{border-top:3px solid #3ea1e4}.ug-level-section.carryover-level{border-top:3px solid #edbd57}.ug-select-card:focus-within{outline:2px solid #9bc9e8;outline-offset:1px}.ug-course-select-label input:focus-visible+.ug-checkmark{outline:2px solid #1769ce;outline-offset:2px}
    @media (max-width:767px){.ug-panel-heading{position:relative}.ug-heading-action{width:100%;margin-top:12px}.ug-selection-tools{display:grid;grid-template-columns:1fr 1fr;gap:7px;padding:9px}.ug-search-field{grid-column:1/-1;min-width:0}.ug-filter-field{display:flex;flex-direction:column;align-items:stretch;gap:3px}.ug-filter-field select{width:100%;min-height:34px}.ug-visible-count{grid-column:1/-1;margin:0;text-align:right}.ug-course-badges{margin-top:4px}.ug-save-bar{padding:10px;gap:8px}.ug-save-bar>div{min-width:0}.ug-save-bar strong{font-size:.8rem}.ug-save-bar>div>span{font-size:.68rem}.ug-save-bar .ug-primary-button{min-width:145px}.ug-save-bar .ug-primary-button span{display:inline}.ug-no-course-match{font-size:.73rem;text-align:center}}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.ug-tab');
        const panels = document.querySelectorAll('.ug-panel');
        const choices = Array.from(document.querySelectorAll('.course-choice'));
        const count = document.getElementById('selectedCount');
        const units = document.getElementById('selectedUnits');
        const saveCount = document.getElementById('saveCount');
        const saveUnits = document.getElementById('saveUnits');
        const savePlural = document.getElementById('savePlural');
        const levelSections = Array.from(document.querySelectorAll('.ug-level-section'));
        const courseSearch = document.getElementById('courseSearch');
        const courseTypeFilter = document.getElementById('courseTypeFilter');
        const courseSemesterFilter = document.getElementById('courseSemesterFilter');
        const visibleCourseCount = document.getElementById('visibleCourseCount');
        const noCourseMatch = document.getElementById('noCourseMatch');

        // Keep the level list easy to scan: opening one level closes the others.
        levelSections.forEach(section => {
            section.addEventListener('toggle', function () {
                if (!this.open) return;
                levelSections.forEach(other => { if (other !== this) other.open = false; });
            });
            section.querySelector('.select-level')?.addEventListener('click', event => event.stopPropagation());
        });

        function updateSummary() {
            const selected = choices.filter((input) => input.checked);
            const totalUnits = selected.reduce((sum, input) => sum + Number(input.dataset.unit || 0), 0);
            if (count) count.textContent = selected.length;
            if (units) units.textContent = totalUnits;
            if (saveCount) saveCount.textContent = selected.length;
            if (saveUnits) saveUnits.textContent = totalUnits;
            if (savePlural) savePlural.textContent = selected.length === 1 ? '' : 's';
            document.querySelectorAll('.ug-select-card').forEach(card => {
                const checkbox = card.querySelector('.course-choice');
                card.classList.toggle('is-selected', checkbox && checkbox.checked);
            });
            document.querySelectorAll('.select-level').forEach(master => {
                const levelChoices = choices.filter(input => input.dataset.level === master.dataset.level);
                const levelSelected = levelChoices.filter(input => input.checked).length;
                master.checked = levelChoices.length > 0 && levelSelected === levelChoices.length;
                master.indeterminate = levelSelected > 0 && levelSelected < levelChoices.length;
            });
        }

        function updateCourseFilter() {
            const search = (courseSearch?.value || '').trim().toLowerCase();
            const type = courseTypeFilter?.value || 'ALL';
            const semester = courseSemesterFilter?.value || 'ALL';
            let visible = 0;
            let firstMatchingSection = null;

            document.querySelectorAll('[data-course-card]').forEach(card => {
                const matchesSearch = !search || (card.dataset.search || '').includes(search);
                const matchesType = type === 'ALL' || card.dataset.courseType === type;
                const matchesSemester = semester === 'ALL' || card.dataset.courseSemester === semester;
                const show = matchesSearch && matchesType && matchesSemester;
                card.classList.toggle('is-filtered-out', !show);
                if (show) visible += 1;
            });

            levelSections.forEach(section => {
                const sectionVisible = section.querySelectorAll('[data-course-card]:not(.is-filtered-out)').length;
                section.classList.toggle('is-filtered-out', sectionVisible === 0);
                if (sectionVisible && search && !firstMatchingSection) firstMatchingSection = section;
            });
            if (search && firstMatchingSection) {
                levelSections.forEach(section => { section.open = section === firstMatchingSection; });
            }
            if (visibleCourseCount) visibleCourseCount.textContent = `${visible} course${visible === 1 ? '' : 's'} shown`;
            if (noCourseMatch) noCourseMatch.hidden = visible !== 0;
        }

        function notifySelection(message) {
            if (typeof window.swal === 'function') {
                window.swal('Choose a course', message, 'info');
            } else {
                window.alert(message);
            }
        }

        function showPanel(target) {
            panels.forEach(panel => panel.hidden = panel.id !== target);
            tabs.forEach(tab => {
                const active = tab.dataset.target === target;
                tab.classList.toggle('active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            if (target === 'selectPanel') updateSummary();
        }
        tabs.forEach(tab => tab.addEventListener('click', () => showPanel(tab.dataset.target)));
        document.querySelectorAll('[data-target="selectPanel"]').forEach(button => button.addEventListener('click', () => showPanel('selectPanel')));
        choices.forEach(input => input.addEventListener('change', updateSummary));

        document.querySelectorAll('.select-level').forEach(master => {
            master.addEventListener('change', function () {
                choices.filter(input => input.dataset.level === this.dataset.level).forEach(input => input.checked = this.checked);
                updateSummary();
            });
        });
        [courseSearch, courseTypeFilter, courseSemesterFilter].forEach(control => {
            control?.addEventListener('input', updateCourseFilter);
            control?.addEventListener('change', updateCourseFilter);
        });
        courseSearch?.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                courseSearch.value = '';
                updateCourseFilter();
            }
        });
        document.getElementById('clearCourses')?.addEventListener('click', function () {
            choices.forEach(input => input.checked = false);
            document.querySelectorAll('.select-level').forEach(input => { input.checked = false; input.indeterminate = false; });
            updateSummary();
        });

        document.getElementById('courseRegistrationForm')?.addEventListener('submit', function (event) {
            const selected = choices.filter(input => input.checked);
            if (!selected.length) {
                event.preventDefault();
                notifySelection('Select at least one course before saving your registration.');
                return;
            }
            selected.forEach(input => {
                const semester = document.querySelector(`.course-semester[data-code="${CSS.escape(input.dataset.code)}"]`);
                if (semester) {
                    const course = JSON.parse(input.value);
                    course.semester = semester.value;
                    input.value = JSON.stringify(course);
                }
            });
            const saveButton = document.getElementById('saveRegistration');
            if (saveButton) { saveButton.disabled = true; saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Saving registration…</span>'; }
        });
        document.querySelectorAll('.ug-semester-form').forEach(form => {
            form.addEventListener('submit', function () {
                const button = this.querySelector('select');
                if (button) button.disabled = true;
            });
        });
        window.removeRegisteredCourse = function (id, code) {
            if (!confirm(`Remove ${code} from your registration?`)) return;
            const form = document.createElement('form'); form.method = 'POST'; form.action = '{{ url('/delete-my-course') }}';
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="id" value="${id}">`;
            document.body.appendChild(form); form.submit();
        };

        const registeredChoices = Array.from(document.querySelectorAll('.registered-choice'));
        const selectAllRegistered = document.getElementById('selectAllRegistered');
        const unregisterSelected = document.getElementById('unregisterSelected');
        const registeredSelectionCount = document.getElementById('registeredSelectionCount');

        function updateRegisteredSelection() {
            const selected = registeredChoices.filter(input => input.checked);
            if (registeredSelectionCount) registeredSelectionCount.textContent = `${selected.length} selected`;
            if (unregisterSelected) unregisterSelected.disabled = selected.length === 0;
            if (selectAllRegistered) {
                selectAllRegistered.checked = registeredChoices.length > 0 && selected.length === registeredChoices.length;
                selectAllRegistered.indeterminate = selected.length > 0 && selected.length < registeredChoices.length;
            }
        }

        function submitUnregister(ids) {
            const uniqueIds = [...new Set(ids.filter(Boolean))];
            if (!uniqueIds.length) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('/delete-my-course') }}';
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
            uniqueIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }

        registeredChoices.forEach(input => input.addEventListener('change', updateRegisteredSelection));
        selectAllRegistered?.addEventListener('change', function () {
            registeredChoices.forEach(input => input.checked = this.checked);
            updateRegisteredSelection();
        });
        unregisterSelected?.addEventListener('click', function () {
            const ids = registeredChoices.filter(input => input.checked).map(input => input.value);
            if (confirm(`Unregister ${ids.length} selected course${ids.length === 1 ? '' : 's'}?`)) submitUnregister(ids);
        });
        updateRegisteredSelection();
        updateSummary();
        updateCourseFilter();
    });
</script>
