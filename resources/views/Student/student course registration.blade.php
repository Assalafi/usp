@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;

    $courseFlag = DB::table('program')
        ->where('code', session('program'))
        ->value('courses');

    $studentCurrentLevel = (int) (Student::where('user_id', session('id'))->value('level')
        ?? Student::where('username', session('id_number'))->value('level')
        ?? session('current_level')
        ?? session('level')
        ?? 0);

    $selectedSession = request('session', session('system_session'));

    $registeredCourses = DB::table('student_course_registration as scr')
        ->join('course', 'scr.code', '=', 'course.code')
        ->leftJoin('program_course_registration as pcr', function ($join) {
            $join->on('scr.code', '=', 'pcr.code')
                ->whereColumn('scr.level', 'pcr.level')
                ->where('pcr.program', session('program'))
                ->where('pcr.structure_id', session('structure_id'));
        })
        ->where('scr.username', session('id_number'))
        ->where('scr.session', $selectedSession)
        ->select(
            'scr.*',
            'course.title',
            'pcr.change_semester',
            'pcr.type as programme_type'
        )
        ->orderBy('scr.level')
        ->orderBy('scr.semester')
        ->orderBy('scr.code')
        ->get();

    $availableCourses = DB::table('program_course_registration as pcr')
        ->join('course', 'pcr.code', '=', 'course.code')
        ->where('pcr.program', session('program'))
        ->where('pcr.structure_id', session('structure_id'))
        ->where('pcr.level', '<=', $studentCurrentLevel)
        ->select(
            'course.code',
            'course.title',
            'course.unit',
            'pcr.semester',
            'pcr.type',
            'pcr.level',
            'pcr.elective',
            'pcr.change_semester'
        )
        ->orderBy('pcr.level')
        ->orderBy('pcr.semester')
        ->orderBy('pcr.type')
        ->orderBy('course.code')
        ->get();

    $registeredCodes = $registeredCourses->pluck('code')->all();
    $courseLevels = $availableCourses->groupBy('level');
    $levels = $courseLevels->keys()->sort()->values();
    $sessions = DB::table('session')->orderBy('title', 'desc')->get();
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-body ug-course-page">
    <div class="page-wrapper">
        @if ($courseFlag == 1 || strpos((string) session('faculty'), '.PG') !== false)
            <div class="ug-course-shell">
                <div class="ug-course-hero">
                    <div>
                        <span class="ug-eyebrow"><i class="fas fa-graduation-cap"></i> Academic registration</span>
                        <h1>Course registration</h1>
                        <p>Select your courses carefully, then save once. You can review or remove them later.</p>
                    </div>
                    <div class="ug-session-picker">
                        <label for="registrationSession">Session</label>
                        <form action="{{ url('/student course registration') }}" method="GET">
                            <select id="registrationSession" name="session" onchange="this.form.submit()">
                                @foreach ($sessions as $sessionRow)
                                    @php $sessionTitle = $sessionRow->title ?? $sessionRow->session ?? ''; @endphp
                                    @if ($sessionTitle !== '')
                                        <option value="{{ $sessionTitle }}" {{ $selectedSession == $sessionTitle ? 'selected' : '' }}>
                                            {{ $sessionTitle }}{{ $selectedSession == $sessionTitle && $sessionTitle == session('system_session') ? ' (Current)' : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="ug-course-stats" aria-label="Registration summary">
                    <div><span class="ug-stat-icon blue"><i class="fas fa-book"></i></span><span><strong id="registeredCount">{{ $registeredCourses->count() }}</strong><small>Registered</small></span></div>
                    <div><span class="ug-stat-icon green"><i class="fas fa-check-circle"></i></span><span><strong id="selectedCount">0</strong><small>Selected now</small></span></div>
                    <div><span class="ug-stat-icon amber"><i class="fas fa-layer-group"></i></span><span><strong id="selectedUnits">0</strong><small>Selected units</small></span></div>
                    <div><span class="ug-stat-icon purple"><i class="fas fa-calendar-alt"></i></span><span><strong>{{ $studentCurrentLevel ?: '—' }}</strong><small>Current level</small></span></div>
                </div>

                <div class="ug-course-tabs" role="tablist" aria-label="Course registration sections">
                    <button class="ug-tab active" type="button" data-target="registeredPanel" aria-selected="true"><i class="fas fa-list-check"></i> My registered courses</button>
                    <button class="ug-tab" type="button" data-target="selectPanel" aria-selected="false"><i class="fas fa-plus-circle"></i> Select courses</button>
                </div>

                <section id="registeredPanel" class="ug-panel" role="tabpanel">
                    <div class="ug-panel-heading">
                        <div>
                            <h2>Your registered courses</h2>
                            <p>{{ $registeredCourses->count() ? 'These are the courses currently saved for ' . $selectedSession . '.' : 'You have not registered any courses for this session yet.' }}</p>
                        </div>
                        @if ($registeredCourses->count())
                            <button type="button" class="ug-outline-button" data-bs-toggle="modal" data-bs-target="#printModal"><i class="fas fa-file-pdf"></i> Print / download</button>
                        @endif
                    </div>

                    @if ($registeredCourses->count())
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
                                                    <div class="ug-course-code">{{ $course->code }}</div>
                                                    <div class="ug-course-name">{{ $course->title ?: 'Course title unavailable' }}</div>
                                                    <div class="ug-course-meta"><span>{{ $course->unit }} unit{{ (int) $course->unit === 1 ? '' : 's' }}</span><span>{{ $course->level }} level</span><span class="ug-type {{ strtoupper($course->type) === 'CORE' ? 'core' : 'elective' }}">{{ ucfirst(strtolower($course->type)) }}</span></div>
                                                    <div class="ug-course-actions">
                                                        @if ($canChangeSemester)
                                                            <form action="{{ url('/change-semester') }}" method="POST" class="ug-semester-form">
                                                                @csrf
                                                                <input type="hidden" name="id" value="{{ $course->id }}">
                                                                <label for="semester-{{ $course->id }}">Semester</label>
                                                                <select id="semester-{{ $course->id }}" name="semester" onchange="this.form.submit()" aria-label="Change semester for {{ $course->code }}">
                                                                    <option value="FIRST" {{ $course->semester === 'FIRST' ? 'selected' : '' }}>First</option>
                                                                    <option value="SECOND" {{ $course->semester === 'SECOND' ? 'selected' : '' }}>Second</option>
                                                                </select>
                                                            </form>
                                                        @else
                                                            <span class="ug-fixed-semester"><i class="fas fa-calendar-check"></i> {{ $semesterLabel }}</span>
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
                        <div class="ug-empty-state"><span><i class="fas fa-book-open"></i></span><h3>No courses registered yet</h3><p>Open “Select courses” to choose the courses you want to register for this session.</p><button type="button" class="ug-primary-button" data-target="selectPanel"><i class="fas fa-plus"></i> Select courses</button></div>
                    @endif
                </section>

                <section id="selectPanel" class="ug-panel" role="tabpanel" hidden>
                    <div class="ug-panel-heading">
                        <div>
                            <h2>Select your courses</h2>
                            <p>Tick a course to add it. Courses marked “Choose semester” may be taken in either semester.</p>
                        </div>
                        <button type="button" class="ug-clear-button" id="clearCourses"><i class="fas fa-rotate-left"></i> Clear selection</button>
                    </div>

                    @if ($levels->count())
                        <form id="courseRegistrationForm" action="{{ url('/register-my-courses') }}" method="POST">
                            @csrf
                            <input type="hidden" name="session" value="{{ $selectedSession }}">
                            <div class="ug-help-note"><i class="fas fa-circle-info"></i><span>Start with your current level. You may also select outstanding courses from earlier levels. Your choices are saved when you click <strong>Save registration</strong>.</span></div>

                            @foreach ($levels as $level)
                                @php $levelCourses = $courseLevels[$level]; @endphp
                                <div class="ug-level-section">
                                    <div class="ug-level-heading">
                                        <div><span class="ug-level-number">{{ $level }}</span><div><h3>{{ $level }} level courses</h3><p>{{ $levelCourses->count() }} available course{{ $levelCourses->count() === 1 ? '' : 's' }}</p></div></div>
                                        <label class="ug-select-all"><input type="checkbox" class="select-level" data-level="{{ $level }}"><span>Select all</span></label>
                                    </div>
                                    <div class="ug-course-grid">
                                        @foreach ($levelCourses as $course)
                                            @php
                                                $courseData = ['code' => $course->code, 'semester' => $course->semester, 'type' => $course->type, 'unit' => $course->unit, 'level' => $course->level];
                                                $changeAllowed = (string) ($course->change_semester ?? '0') === '1';
                                            @endphp
                                            <article class="ug-select-card {{ in_array($course->code, $registeredCodes) ? 'is-registered' : '' }}">
                                                <label class="ug-course-select-label">
                                                    <input type="checkbox" class="course-choice" name="courses[]" value="{{ json_encode($courseData) }}" data-level="{{ $level }}" data-code="{{ $course->code }}" data-unit="{{ $course->unit }}" {{ in_array($course->code, $registeredCodes) ? 'checked' : '' }}>
                                                    <span class="ug-checkmark"><i class="fas fa-check"></i></span>
                                                    <span class="ug-course-main"><strong>{{ $course->code }}</strong><span>{{ $course->title ?: 'Course title unavailable' }}</span></span>
                                                </label>
                                                <div class="ug-select-meta"><span class="ug-unit-pill">{{ $course->unit }} unit{{ (int) $course->unit === 1 ? '' : 's' }}</span><span class="ug-type {{ strtoupper($course->type) === 'CORE' ? 'core' : 'elective' }}">{{ ucfirst(strtolower($course->type)) }}</span></div>
                                                @if ($changeAllowed)
                                                    <label class="ug-change-semester"><span><i class="fas fa-calendar-alt"></i> Choose semester</span><select class="course-semester" data-code="{{ $course->code }}" aria-label="Semester for {{ $course->code }}"><option value="FIRST" {{ $course->semester === 'FIRST' ? 'selected' : '' }}>First semester</option><option value="SECOND" {{ $course->semester === 'SECOND' ? 'selected' : '' }}>Second semester</option></select></label>
                                                @else
                                                    <div class="ug-fixed-choice"><i class="fas fa-calendar-check"></i> {{ ucfirst(strtolower($course->semester)) }} semester</div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="ug-save-bar">
                                <div><strong><span id="saveCount">0</span> course<span id="savePlural">s</span> selected</strong><span><span id="saveUnits">0</span> total units</span></div>
                                <button type="submit" class="ug-primary-button" id="saveRegistration"><i class="fas fa-save"></i> Save registration</button>
                            </div>
                        </form>
                    @else
                        <div class="ug-empty-state"><span><i class="fas fa-book-open"></i></span><h3>No courses are available</h3><p>Please contact your department if you believe this is incorrect.</p></div>
                    @endif
                </section>
            </div>

            <div id="printModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-file-pdf text-danger me-2"></i>Print course registration</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="{{ url('/get-registered-courses') }}" method="GET"><div class="modal-body"><label class="form-label" for="printSession">Choose session</label><select id="printSession" class="form-select" name="session" required>@foreach ($sessions as $sessionRow) @php $sessionTitle = $sessionRow->title ?? $sessionRow->session ?? ''; @endphp @if ($sessionTitle !== '')<option value="{{ $sessionTitle }}" {{ $selectedSession == $sessionTitle ? 'selected' : '' }}>{{ $sessionTitle }}</option>@endif @endforeach</select></div><div class="modal-footer"><button type="button" class="ug-clear-button" data-bs-dismiss="modal">Cancel</button><button type="submit" class="ug-primary-button"><i class="fas fa-download"></i> Generate PDF</button></div></form></div></div>
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
    .ug-help-note{display:flex;align-items:flex-start;gap:10px;background:#f0f6ff;color:#486888;border-radius:11px;padding:12px 14px;font-size:.84rem;margin-bottom:22px}.ug-help-note i{color:#2771ce;margin-top:2px}.ug-level-section{margin-bottom:25px}.ug-level-heading{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e9eef5;padding-bottom:10px;margin-bottom:12px}.ug-level-heading>div{display:flex;align-items:center;gap:10px}.ug-level-number{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#e9f2ff;color:#1762c8;font-weight:800}.ug-level-heading h3{margin:0;color:#27496e;font-size:1rem}.ug-level-heading p{margin:2px 0 0;color:#8493a7;font-size:.77rem}.ug-select-all{font-size:.8rem;color:#426487;display:flex;align-items:center;gap:7px;cursor:pointer}.ug-select-all input{accent-color:#1769ce;width:16px;height:16px}.ug-course-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.ug-select-card{border:1px solid #e1e9f2;border-radius:13px;padding:14px;transition:.18s;background:#fff}.ug-select-card:has(.course-choice:checked){border-color:#5594e2;background:#f7fbff;box-shadow:0 3px 12px rgba(44,111,192,.08)}.ug-select-card.is-registered{border-left:3px solid #3ea1e4}.ug-course-select-label{display:flex;align-items:flex-start;gap:10px;cursor:pointer}.ug-course-select-label input{position:absolute;opacity:0}.ug-checkmark{width:21px;height:21px;display:grid;place-items:center;border:2px solid #c6d3e2;color:#fff;border-radius:6px;flex:0 0 auto;margin-top:1px}.ug-checkmark i{display:none;font-size:.72rem}.course-choice:checked+.ug-checkmark{background:#1769ce;border-color:#1769ce}.course-choice:checked+.ug-checkmark i{display:block}.ug-course-main{display:flex;flex-direction:column;gap:3px;min-width:0}.ug-course-main strong{color:#1c60bd}.ug-course-main span{color:#41546a;font-size:.84rem;line-height:1.3}.ug-select-meta{display:flex;gap:7px;align-items:center;margin:10px 0 8px 31px}.ug-unit-pill{font-size:.7rem;background:#eef4fa;color:#55708c;border-radius:20px;padding:4px 8px;font-weight:700}.ug-change-semester{display:flex;justify-content:space-between;align-items:center;gap:8px;border-top:1px solid #eef2f7;padding-top:9px;margin-left:31px;font-size:.75rem;color:#426487}.ug-change-semester select{border:1px solid #cbd9e9;border-radius:7px;padding:5px 6px;color:#24578e;background:#fff;font-size:.75rem;max-width:145px}.ug-fixed-choice{border-top:1px solid #eef2f7;padding-top:9px;margin-left:31px;font-size:.75rem;color:#8190a3}.ug-save-bar{position:sticky;bottom:10px;margin-top:20px;background:#172f50;color:#fff;border-radius:14px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 8px 22px rgba(20,48,81,.2);z-index:2}.ug-save-bar>div{display:flex;flex-direction:column;gap:2px;font-size:.78rem}.ug-save-bar strong{font-size:.92rem}.ug-save-bar>div>span{color:#b9cce2}.modal-content{border:0;border-radius:16px}.modal-header{border-bottom:1px solid #edf1f6}.modal-footer{border-top:1px solid #edf1f6}
    @media (max-width:767px){.ug-course-page{padding:10px;background:#f6f8fb}.ug-course-hero{border-radius:15px;padding:20px 16px;display:block}.ug-course-hero h1{font-size:1.65rem}.ug-course-hero p{font-size:.85rem}.ug-session-picker{margin-top:17px}.ug-course-stats{grid-template-columns:repeat(2,1fr);gap:8px;margin:10px 0}.ug-course-stats>div{padding:10px;gap:8px}.ug-stat-icon{width:32px;height:32px;font-size:.85rem}.ug-course-stats strong{font-size:1rem}.ug-course-stats small{font-size:.68rem}.ug-course-tabs{margin:10px 0}.ug-tab{font-size:.78rem;padding:11px 7px}.ug-tab i{margin-right:3px}.ug-panel{border-radius:14px;padding:15px 12px}.ug-panel-heading{display:block;margin-bottom:17px}.ug-panel-heading h2{font-size:1.15rem}.ug-panel-heading p{font-size:.8rem;margin-bottom:12px}.ug-outline-button,.ug-clear-button{font-size:.75rem;padding:8px 10px}.ug-registered-list,.ug-course-grid{grid-template-columns:1fr}.ug-registered-card{grid-template-columns:auto 1fr;column-gap:10px;padding:12px}.ug-course-actions{grid-column:1/-1;align-items:flex-start;gap:8px;flex-wrap:wrap}.ug-semester-form{width:100%;justify-content:space-between}.ug-semester-form select{flex:1;max-width:none}.ug-level-heading{align-items:flex-start}.ug-level-heading h3{font-size:.92rem}.ug-select-all{font-size:.72rem}.ug-select-card{padding:12px}.ug-save-bar{bottom:6px;align-items:stretch}.ug-save-bar .ug-primary-button{font-size:.78rem;padding:9px}.ug-help-note{font-size:.77rem;padding:10px}.ug-regenerate-form .ug-outline-button{width:100%}}
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
        document.getElementById('clearCourses')?.addEventListener('click', function () {
            choices.forEach(input => input.checked = false);
            document.querySelectorAll('.select-level').forEach(input => { input.checked = false; input.indeterminate = false; });
            updateSummary();
        });

        document.getElementById('courseRegistrationForm')?.addEventListener('submit', function (event) {
            const selected = choices.filter(input => input.checked);
            if (!selected.length) {
                event.preventDefault();
                alert('Please select at least one course before saving your registration.');
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
            if (saveButton) { saveButton.disabled = true; saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }
        });
        window.removeRegisteredCourse = function (id, code) {
            if (!confirm(`Remove ${code} from your registration?`)) return;
            const form = document.createElement('form'); form.method = 'POST'; form.action = '{{ url('/delete-my-course') }}';
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="id" value="${id}">`;
            document.body.appendChild(form); form.submit();
        };
        updateSummary();
    });
</script>
