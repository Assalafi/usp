@php
    use App\Models\Staff;
    use Illuminate\Support\Facades\DB;
    $staffRecord = Staff::where('username', session('username'))->first();
    $staffName = trim((string) ($staffRecord->name ?? session('username') ?? 'Staff member'));
    $staffId = trim((string) ($staffRecord->username ?? session('username') ?? ''));
    $appointment = trim((string) ($staffRecord->appointment ?? session('appointment') ?? 'Staff'));
    $unit = trim((string) ($staffRecord->unit ?? ''));
    $faculty = trim((string) ($staffRecord->faculty ?? ''));
    $department = trim((string) ($staffRecord->department ?? ''));
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $facultyTitle = $faculty !== ''
        ? (DB::table('faculty')->where('code', $faculty)->value('title') ?: $faculty)
        : '';
    $departmentTitle = $department !== ''
        ? (DB::table('department')->where('code', $department)->value('title') ?: $department)
        : '';
    $unitName = !empty($staffRecord->unit_id)
        ? (DB::table('units')->where('id', $staffRecord->unit_id)->value('name') ?: $unit)
        : $unit;
    $staffCategory = strtolower(trim((string) ($staffRecord->staff_category ?? '')));
    $isAcademicStaff = (str_contains($staffCategory, 'teaching') && !str_contains($staffCategory, 'non'))
        || (str_contains($staffCategory, 'academic') && !str_contains($staffCategory, 'non'))
        || ($departmentTitle !== '' && $unitName === '');
    $staffAreaLabel = $isAcademicStaff ? 'Department' : 'Unit';
    $staffArea = $isAcademicStaff
        ? ($departmentTitle ?: $unitName ?: $facultyTitle ?: 'Not set')
        : ($unitName ?: $departmentTitle ?: 'Not set');
    $photo = trim((string) ($staffRecord->picture ?? ''));
    $photoUrl = $photo ? asset('storage/picture/' . $photo) : null;
    $initials = collect(preg_split('/\s+/', $staffName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $dashboardActions = collect(\App\Support\PortalPermission::actionsForLink('/dash'))
        ->map([\App\Support\PortalPermission::class, 'normalizeAction'])
        ->filter(fn ($action) => $action !== 'view')
        ->unique()
        ->values();
    $assignedCourseRows = DB::table('course_allocation as allocation')
        ->leftJoin('course', 'course.code', '=', 'allocation.course')
        ->whereRaw('UPPER(TRIM(allocation.username)) = ?', [strtoupper(trim((string) session('username')))])
        ->where(function ($query) use ($currentSession) {
            $query->whereNull('allocation.session')
                ->orWhere('allocation.session', '')
                ->orWhere('allocation.session', $currentSession);
        })
        ->select('allocation.course', 'allocation.type', 'allocation.session', 'course.title as course_title')
        ->groupBy('allocation.course', 'allocation.type', 'allocation.session', 'course.title')
        ->orderBy('allocation.course')
        ->get();
    $assignedCourseCount = $assignedCourseRows->pluck('course')->filter()->unique()->count();
    $mainCourseCount = $assignedCourseRows->where('type', 'MAIN')->pluck('course')->filter()->unique()->count();
    $supportCourseCount = $assignedCourseRows->where('type', '!=', 'MAIN')->pluck('course')->filter()->unique()->count();
    $assignedCourseCards = $assignedCourseRows
        ->sortBy(fn ($row) => strtoupper((string) $row->type) === 'MAIN' ? 0 : 1)
        ->unique('course')
        ->values();
    $assignedMainCourseCodes = DB::table('course_allocation')
        ->whereRaw('UPPER(TRIM(username)) = ?', [strtoupper(trim((string) session('username')))])
        ->whereRaw('UPPER(TRIM(type)) = ?', ['MAIN'])
        ->pluck('course')
        ->filter()
        ->unique()
        ->values()
        ->all();
    $resultBatchCount = static function ($query, string $prefix = ''): int {
        $codeColumn = $prefix !== '' ? $prefix . '.code' : 'code';
        $sessionColumn = $prefix !== '' ? $prefix . '.session' : 'session';
        $semesterColumn = $prefix !== '' ? $prefix . '.semester' : 'semester';
        return (int) $query
            ->selectRaw("COUNT(DISTINCT CONCAT_WS('|', {$codeColumn}, {$sessionColumn}, {$semesterColumn})) AS batch_count")
            ->value('batch_count');
    };
    $appointmentCode = strtoupper(trim((string) ($staffRecord->appointment ?? session('appointment') ?? '')));
    $isHod = $appointmentCode === 'HOD';
    $isDean = $appointmentCode === 'DEAN';
    $isCourseSystem = in_array($appointmentCode, ['CS', 'COURSE SYSTEM'], true);
    $lecturerResultsPending = 0;
    if ($assignedMainCourseCodes !== []) {
        $lecturerResultsPending = $resultBatchCount(
            DB::table('results')
                ->where('approve', 'system')
                ->whereIn('code', $assignedMainCourseCodes)
                ->whereRaw('UPPER(TRIM(lecturer)) = ?', [strtoupper(trim((string) session('username')))])
        );
    }
    $hodResultsPending = $isHod
        ? $resultBatchCount(DB::table('results')->where(['approve' => 'lecturer', 'department' => $department]))
        : 0;
    $deanResultsPending = 0;
    if ($isDean && $faculty !== '') {
        $deanResultsPending = $resultBatchCount(
            DB::table('results as result')
                ->leftJoin('course as result_course', 'result_course.code', '=', 'result.code')
                ->leftJoin('program as result_program', 'result_program.code', '=', 'result.program')
                ->where('result.approve', 'hod')
                ->where(function ($query) use ($faculty) {
                    $query->where('result_course.faculty', $faculty)->orWhere('result_program.faculty', $faculty);
                })
            , 'result');
    }
    $courseSystemResultsPending = $isCourseSystem
        ? $resultBatchCount(DB::table('results')->where('approve', 'dean'))
        : 0;
    $changeOfCoursePending = 0;
    $interUniversityPending = 0;
    if ($isHod) {
        $changeOfCoursePending = DB::table('change_of_course')
            ->whereIn('status', ['Awaiting New HOD', 'Awaiting Current HOD'])
            ->where(function ($query) use ($department) {
                $query->where('new_department', $department)->orWhere('current_department', $department);
            })
            ->count();
        $interUniversityPending = DB::table('inter_university_transfer')
            ->where('status', 'Awaiting UNIMAID HOD')
            ->where('new_department', $department)
            ->count();
    } elseif ($isDean) {
        $changeOfCoursePending = DB::table('change_of_course')
            ->whereIn('status', ['Awaiting New Dean', 'Awaiting Current Dean'])
            ->where(function ($query) use ($faculty) {
                $query->where('new_faculty', $faculty)->orWhere('current_faculty', $faculty);
            })
            ->count();
        $interUniversityPending = DB::table('inter_university_transfer')
            ->where('status', 'Awaiting UNIMAID Dean')
            ->where('new_faculty', $faculty)
            ->count();
    }
    $staffWorkSummary = collect();
    if ($assignedCourseCount > 0) {
        $staffWorkSummary->push(['label' => 'Assigned courses', 'value' => $assignedCourseCount, 'description' => $mainCourseCount . ' coordinator / ' . $supportCourseCount . ' lecturer', 'icon' => 'fa-book-open', 'tone' => 'blue', 'url' => '/course allocation']);
    }
    if ($assignedMainCourseCodes !== []) {
        $staffWorkSummary->push(['label' => 'Results awaiting your submission', 'value' => $lecturerResultsPending, 'description' => 'All academic sessions', 'icon' => 'fa-file-arrow-up', 'tone' => 'orange', 'url' => '/approve results?approve=system']);
    }
    if ($isHod) {
        $staffWorkSummary->push(['label' => 'Results awaiting department review', 'value' => $hodResultsPending, 'description' => 'All academic sessions', 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => '/approve results?approve=lecturer']);
    }
    if ($isDean) {
        $staffWorkSummary->push(['label' => 'Results awaiting faculty review', 'value' => $deanResultsPending, 'description' => ($facultyTitle ?: 'Your faculty') . ' · all sessions', 'icon' => 'fa-landmark', 'tone' => 'green', 'url' => '/approve results?approve=hod']);
    }
    if ($isCourseSystem) {
        $staffWorkSummary->push(['label' => 'Results awaiting Course System review', 'value' => $courseSystemResultsPending, 'description' => 'All academic sessions', 'icon' => 'fa-clipboard-check', 'tone' => 'blue', 'url' => '/approve results?approve=dean']);
    }
    if ($isHod || $isDean) {
        $staffWorkSummary->push(['label' => 'Change-of-course requests', 'value' => $changeOfCoursePending, 'description' => $isHod ? 'Awaiting department review' : 'Awaiting faculty review', 'icon' => 'fa-shuffle', 'tone' => 'orange', 'url' => '/change-of-course/admin']);
        $staffWorkSummary->push(['label' => 'Inter-university transfers', 'value' => $interUniversityPending, 'description' => $isHod ? 'Awaiting department review' : 'Awaiting faculty review', 'icon' => 'fa-right-left', 'tone' => 'purple', 'url' => '/inter-university-transfer/admin']);
    }
    $staffQuickLinks = collect([
        ['label' => 'My profile', 'description' => 'Review your staff information.', 'icon' => 'fa-user', 'url' => '/staff-profile', 'tone' => 'blue'],
        ['label' => 'Staff records', 'description' => 'Open the staff directory.', 'icon' => 'fa-users', 'url' => '/staff', 'tone' => 'purple'],
    ])->filter(fn ($link) => \App\Support\PortalPermission::hasPageAccess($link['url']) || $link['url'] === '/staff-profile');
    if ($assignedCourseCount > 0) {
        $staffQuickLinks = $staffQuickLinks->merge([
            ['label' => 'My timetable', 'description' => 'View your teaching timetable.', 'icon' => 'fa-calendar-days', 'url' => '/my-lecture-timetable', 'tone' => 'green'],
            ['label' => 'Course results', 'description' => 'Review results for assigned courses.', 'icon' => 'fa-chart-column', 'url' => '/results', 'tone' => 'orange'],
            ['label' => 'Upload results', 'description' => 'Submit a course result spreadsheet.', 'icon' => 'fa-file-arrow-up', 'url' => '/results/upload', 'tone' => 'blue'],
        ])->filter(fn ($link) => \App\Support\PortalPermission::hasPageAccess($link['url']) || in_array($link['url'], ['/staff-profile', '/my-lecture-timetable'], true));
    }
@endphp
<div class="main-body staff-dashboard-page">
    <div class="page-wrapper">
        <section class="staff-welcome-card">
            <div class="staff-welcome-copy">
                <span class="staff-eyebrow"><i class="fas fa-briefcase"></i> Staff portal</span>
                <h1>Welcome back, {{ \Illuminate\Support\Str::before($staffName, ' ') ?: $staffName }}.</h1>
                <p>Your assigned university workspace, in one place.</p>
                <div class="staff-meta-row">
                    @if ($staffId)<span><i class="fas fa-id-badge"></i> {{ $staffId }}</span>@endif
                    <span><i class="fas fa-calendar-alt"></i> {{ $currentSession ?: 'Current session' }}</span>
                </div>
            </div>
            <div class="staff-avatar-wrap">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $staffName }}" class="staff-avatar">
                @else
                    <div class="staff-avatar staff-avatar-initials">{{ $initials ?: 'ST' }}</div>
                @endif
                <span class="staff-online-dot" title="Active staff account"></span>
            </div>
            <div class="staff-welcome-details">
                <div><span>Appointment</span><strong>{{ $appointment ?: 'Staff' }}</strong></div>
                <div><span>{{ $staffAreaLabel }}</span><strong>{{ $staffArea }}</strong></div>
                <a href="{{ url('/staff-profile') }}" class="staff-profile-link"><i class="fas fa-user-edit"></i> View profile</a>
            </div>
        </section>
@if (false)
<div class="row staff-card-row">
    @foreach ($dashboardActions as $item)
            @if ($item == 'active_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Active students</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($student) }}</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'active_staff')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Active staff</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($staff) }}</h3>
                            <i class="fas fa-user-tag f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">School-fee payers</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($schoolFeeStudents) }}</h3>
                            <i class="fas fa-money-bill-wave f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'unpaid_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students with unpaid fees</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($unpaidStudentCount) }}</h3>
                            <i class="fas fa-money-bill-wave f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_hostel_pin')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Hostel PIN payments</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($pin) }}</h3>
                            <i class="fas fa-bed f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_hostel')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Hostel-fee payers</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($hostelFeeStudents) }}</h3>
                            <i class="fas fa-bed f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_certificate')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Certificate-fee payers</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-certificate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_transcript')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Transcript-fee payers</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-address-card f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'suspended')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Suspended students</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'nysc')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Pre-mobilisation</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'exemption')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Exemption students</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'alumni')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Alumni</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($alumni) }}</h3>
                            <i class="fas fa-graduation-cap f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
</div>
@endif
    @if ($staffWorkSummary->isNotEmpty())
        <section class="staff-work-panel">
            <div class="staff-panel-heading">
                <div><span class="staff-kicker">Academic work</span><h2>Current responsibilities</h2><p>Important course and result work for your office.</p></div>
                <i class="fas fa-clipboard-list staff-panel-icon"></i>
            </div>
            <div class="staff-work-grid">
                @foreach ($staffWorkSummary as $summary)
                    <a href="{{ url($summary['url']) }}" class="staff-work-card staff-work-{{ $summary['tone'] }}">
                        <span class="staff-work-icon"><i class="fas {{ $summary['icon'] }}"></i></span>
                        <span class="staff-work-copy"><small>{{ $summary['label'] }}</small><strong>{{ number_format((int) $summary['value']) }}</strong><em>{{ $summary['description'] }}</em></span>
                        <i class="fas fa-arrow-up-right-from-square staff-work-arrow"></i>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
    @if ($staffQuickLinks->isNotEmpty())
        <section class="staff-quick-panel">
            <div class="staff-panel-heading">
                <div><span class="staff-kicker">Workspace</span><h2>Quick links</h2><p>Open the university pages connected to your work.</p></div>
                <i class="fas fa-compass staff-panel-icon"></i>
            </div>
            <div class="staff-quick-grid">
                @foreach ($staffQuickLinks as $link)
                    <a href="{{ url($link['url']) }}" class="staff-quick-item staff-quick-{{ $link['tone'] }}">
                        <span class="staff-quick-icon"><i class="fas {{ $link['icon'] }}"></i></span>
                        <span><strong>{{ $link['label'] }}</strong><small>{{ $link['description'] }}</small></span>
                        <i class="fas fa-chevron-right staff-quick-arrow"></i>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
    @if ($assignedCourseCount > 0)
        <section class="staff-course-panel">
            <div class="staff-panel-heading">
                <div><span class="staff-kicker">Teaching assignments</span><h2>My assigned courses</h2><p>Courses assigned to you for {{ $currentSession ?: 'the current session' }}.</p></div>
                <div class="staff-course-summary"><span><strong>{{ number_format($assignedCourseCount) }}</strong> courses</span><span><strong>{{ number_format($mainCourseCount) }}</strong> coordinators</span><span><strong>{{ number_format($supportCourseCount) }}</strong> lecturers</span></div>
            </div>
            <div class="staff-course-list">
                @foreach ($assignedCourseCards->take(12) as $courseAssignment)
                    @php
                        $courseLink = strtoupper((string) $courseAssignment->type) === 'MAIN'
                            ? '/results?code=' . urlencode($courseAssignment->course) . '&session=' . urlencode($currentSession ?: '')
                            : '/my-lecture-timetable';
                    @endphp
                    <a href="{{ url($courseLink) }}" class="staff-course-item">
                        <span class="staff-course-code">{{ $courseAssignment->course }}</span>
                        <span class="staff-course-name">{{ $courseAssignment->course_title ?: 'Course title not available' }}</span>
                        <span class="staff-course-role">{{ strtoupper((string) $courseAssignment->type) === 'MAIN' ? 'Course coordinator' : 'Course lecturer' }}</span>
                        <i class="fas {{ strtoupper((string) $courseAssignment->type) === 'MAIN' ? 'fa-arrow-up-right-from-square' : 'fa-calendar-days' }}"></i>
                    </a>
                @endforeach
            </div>
            @if ($assignedCourseCount > 12)
                <a href="{{ url('/course allocation') }}" class="staff-course-more">View all {{ number_format($assignedCourseCount) }} assigned courses <i class="fas fa-arrow-right"></i></a>
            @endif
        </section>
    @endif
    @forelse (DB::table('committee_membership')->where(['username' => session('username')])->get() as $item1)
        <div class="main-body">
            <div class="page-wrapper">
                <!-- [ Main Content ] start -->
                <div class="row">
                    @forelse (DB::table('committee_meetings')->where(['committee' => $item1 -> committee, 'sub_committee' => $item1 -> sub_committee])->orderBy('start_at', 'asc')->get() as $item2)
                        <div class="col-sm-12 col-lg-4">
                            <a href="/committee-meeting-members/{{ $item2->id }}">
                                <div class="card">
                                    <div class="card-body row">
                                        <div class="card-block col-md-12">
                                            <p>{{ $item2->sub_committee }}</p>
                                            <p>
                                                {{ date('M d, Y', strtotime($item2->start_at)) }} -
                                                {{ date('M d, Y', strtotime($item2->end_at)) }}
                                            </p>
                                            <p>
                                                {{ date('h:i A', strtotime($item2->start_at)) }} -
                                                {{ date('h:i A', strtotime($item2->end_at)) }}
                                            </p>
                                            <p><i class="fa fa-map-marker"></i> Board Room</p>
                                        </div>
                                    </div>
                                </div>
                            </a>

                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>


    @empty

    @endforelse
    </div>
</div>

<style>
    .staff-dashboard-page{background:#f5f7fb;min-height:calc(100vh - 70px);padding-bottom:2rem}.staff-dashboard-page .page-wrapper{max-width:1440px;margin:auto;padding:18px}.staff-welcome-card{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(250px,.78fr);align-items:center;gap:20px;padding:24px 28px;border-radius:22px;background:linear-gradient(120deg,#3ea1e4,#2f8fcf 68%,#267bb8);color:#fff;box-shadow:0 12px 28px rgba(62,161,228,.22);overflow:hidden;position:relative}.staff-welcome-card:after{content:"";position:absolute;width:290px;height:290px;border-radius:50%;right:-100px;top:-160px;background:rgba(255,255,255,.11)}.staff-welcome-copy,.staff-welcome-details,.staff-avatar-wrap{position:relative;z-index:1}.staff-eyebrow,.staff-kicker{font-size:.72rem;text-transform:uppercase;letter-spacing:.09em;font-weight:700;opacity:.82}.staff-eyebrow i{margin-right:5px}.staff-welcome-card h1{font-size:clamp(1.45rem,3vw,2.15rem);margin:.45rem 0 .25rem;font-weight:700}.staff-welcome-card p{margin:0;color:rgba(255,255,255,.82)}.staff-meta-row{display:flex;flex-wrap:wrap;gap:.55rem 1rem;margin-top:1rem;font-size:.8rem;color:#dbeeff}.staff-meta-row i{margin-right:.35rem}.staff-avatar-wrap{justify-self:end}.staff-avatar{width:78px;height:78px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.72);background:#e4f3fc;box-shadow:0 8px 18px rgba(23,78,122,.2)}.staff-avatar-initials{display:grid;place-items:center;color:#2783bc;font-size:1.35rem;font-weight:800}.staff-online-dot{position:absolute;right:2px;bottom:5px;width:14px;height:14px;background:#35d17a;border:3px solid #fff;border-radius:50%}.staff-welcome-details{display:grid;grid-template-columns:1fr 1fr;gap:14px 18px;min-width:0}.staff-welcome-details>div{min-width:0}.staff-welcome-details span{display:block;font-size:.63rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.72);margin-bottom:3px}.staff-welcome-details strong{display:block;color:#fff;font-size:.82rem;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.staff-profile-link{grid-column:1/-1;display:inline-flex;justify-content:center;align-items:center;gap:6px;padding:9px 11px;border:1px solid rgba(255,255,255,.48);border-radius:9px;color:#fff;font-size:.72rem;font-weight:700;text-decoration:none}.staff-profile-link:hover{background:rgba(255,255,255,.15);color:#fff}.staff-section-heading{display:flex;justify-content:space-between;align-items:end;gap:15px;margin:22px 2px 12px}.staff-section-heading h2{margin:3px 0 0;color:#17233a;font-size:1.16rem}.staff-section-heading .staff-kicker{color:#79879d;opacity:1}.staff-card-count{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#e4f3fc;color:#258ac8;font-size:.69rem;font-weight:800;white-space:nowrap}.staff-card-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:0}.staff-card-row>.col-sm-6,.staff-card-row>.col-md-6,.staff-card-row>.col-xl-3{width:auto;max-width:none;padding:0}.staff-card-row .card{position:relative;display:flex;min-width:0;min-height:153px;margin:0;border:1px solid #e8edf5;border-left:4px solid #3ea1e4;border-radius:17px;background:#fff!important;box-shadow:0 5px 18px rgba(21,43,77,.06);transition:transform .2s ease,box-shadow .2s ease}.staff-card-row .card:hover{transform:translateY(-2px);box-shadow:0 9px 24px rgba(21,43,77,.1)}.staff-card-row .card-block{width:100%;padding:17px 18px!important}.staff-card-row .card h5{margin:0 0 9px!important;color:#63708a!important;font-size:.77rem;font-weight:600;line-height:1.35}.staff-card-row .card h3{margin:0!important;color:#18243a!important;font-size:1.35rem!important;line-height:1.2}.staff-card-row .card i{position:absolute;right:16px;bottom:15px;color:#3ea1e4!important;font-size:1.55rem!important;opacity:.75}.staff-card-row .bg-c-blue{border-left-color:#3ea1e4}.staff-card-row .bg-c-blue i{color:#3ea1e4!important}.staff-card-row .theme-bg{border-left-color:#119caa}.staff-card-row .theme-bg i{color:#119caa!important}.staff-card-row>.col-sm-6:nth-child(4n+2) .card{border-left-color:#7855dc}.staff-card-row>.col-sm-6:nth-child(4n+2) .card i{color:#7855dc!important}.staff-card-row>.col-sm-6:nth-child(4n+3) .card{border-left-color:#20a66a}.staff-card-row>.col-sm-6:nth-child(4n+3) .card i{color:#20a66a!important}.staff-card-row>.col-sm-6:nth-child(4n+4) .card{border-left-color:#ee9343}.staff-card-row>.col-sm-6:nth-child(4n+4) .card i{color:#ee9343!important}.staff-dashboard-page>.page-wrapper>.row:last-child{display:none}.staff-card-row+.main-body{margin-top:18px}.staff-card-row~.main-body .card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 5px 18px rgba(21,43,77,.05)}.staff-card-row~.main-body .card p{margin:0 0 5px;color:#4c6079;font-size:.78rem}.staff-card-row~.main-body .card p:first-child{color:#1f3552;font-size:.88rem;font-weight:700}.staff-card-row~.main-body .card p:last-child{color:#8190a7}.staff-card-row~.main-body .card i{color:#3ea1e4}.staff-card-row~.main-body .row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.staff-card-row~.main-body .row>[class*=col-]{width:auto;max-width:none;padding:0}.staff-card-row~.main-body .row>[class*=col-]>a{display:block;height:100%;text-decoration:none}.staff-card-row~.main-body .row>[class*=col-]>a>.card{height:100%;margin:0}.staff-dashboard-page>.page-wrapper>.row:last-child{display:none}@media(max-width:1050px){.staff-welcome-card{grid-template-columns:minmax(0,1fr) auto}.staff-welcome-details{grid-column:1/-1;grid-row:2;grid-template-columns:repeat(3,minmax(0,1fr));padding-top:14px;border-top:1px solid rgba(255,255,255,.2)}.staff-profile-link{grid-column:auto}.staff-card-row{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:800px){.staff-card-row{grid-template-columns:repeat(2,minmax(0,1fr))}.staff-card-row~.main-body .row{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.staff-dashboard-page .page-wrapper{padding:11px}.staff-welcome-card{grid-template-columns:minmax(0,1fr) 58px;column-gap:12px;row-gap:0;padding:18px 16px;border-radius:17px}.staff-welcome-card h1{font-size:1.28rem;line-height:1.18;overflow-wrap:anywhere}.staff-welcome-card p{font-size:.75rem;line-height:1.35}.staff-meta-row{display:flex;flex-direction:column;align-items:flex-start;gap:4px;margin-top:11px;font-size:.68rem}.staff-avatar-wrap{grid-column:2;grid-row:1;align-self:start}.staff-avatar{width:56px;height:56px}.staff-welcome-details{grid-column:1/-1;grid-row:2;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:11px 14px;margin-top:15px;padding-top:13px}.staff-welcome-details>div:nth-child(2){grid-column:2}.staff-welcome-details span{font-size:.6rem}.staff-welcome-details strong{font-size:.72rem;line-height:1.25;white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.staff-profile-link{grid-column:1/-1;width:100%;margin-top:1px;padding:8px 10px;font-size:.64rem}.staff-section-heading{align-items:flex-start;margin:16px 2px 10px}.staff-section-heading h2{font-size:1.02rem}.staff-kicker{font-size:.63rem}.staff-card-count{padding:5px 8px;font-size:.62rem}.staff-card-row{gap:9px}.staff-card-row .card{min-height:122px;border-left-width:3px;border-radius:13px}.staff-card-row .card-block{padding:13px 12px!important}.staff-card-row .card h5{font-size:.68rem!important;line-height:1.25;margin-bottom:7px!important}.staff-card-row .card h3{font-size:1.05rem!important}.staff-card-row .card i{right:11px;bottom:12px;font-size:1.15rem!important}.staff-card-row~.main-body .row{grid-template-columns:1fr}.staff-card-row~.main-body .card p{font-size:.7rem}}
</style>
<style>
    /* The legacy layout hides a final row on some dashboard variants. The
       assigned-card row is intentional and must remain visible when there
       are no committee meetings. */
    .staff-dashboard-page > .page-wrapper > .staff-card-row { display: grid !important; }
</style>
<style>
    .staff-card-row{display:none!important}
    .staff-work-panel{margin-top:18px;padding:21px;border:1px solid #e8edf5;border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(21,43,77,.05)}
    .staff-work-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.staff-work-card{position:relative;display:flex;align-items:flex-start;gap:11px;min-width:0;padding:15px 16px;border:1px solid #e8edf5;border-left:4px solid #3ea1e4;border-radius:14px;background:#fbfcff;color:#22314b;text-decoration:none}.staff-work-card:hover{background:#f4f9ff;border-color:#b9d2fb;color:#1d5fa5;transform:translateY(-1px)}.staff-work-icon{display:grid;place-items:center;width:34px;height:34px;flex:0 0 auto;border-radius:10px;color:#fff;font-size:.8rem}.staff-work-copy{display:flex;flex-direction:column;gap:3px;min-width:0}.staff-work-copy small{overflow:hidden;color:#66788d;font-size:.68rem;font-weight:700;line-height:1.25;text-overflow:ellipsis;white-space:nowrap}.staff-work-copy strong{color:#18243a;font-size:1.32rem;line-height:1.1}.staff-work-copy em{overflow:hidden;color:#8493a7;font-size:.64rem;font-style:normal;text-overflow:ellipsis;white-space:nowrap}.staff-work-arrow{position:absolute;top:14px;right:14px;color:#9aa6b8;font-size:.62rem}.staff-work-blue{border-left-color:#3ea1e4}.staff-work-blue .staff-work-icon{background:#3ea1e4}.staff-work-orange{border-left-color:#ee9343}.staff-work-orange .staff-work-icon{background:#ee9343}.staff-work-purple{border-left-color:#7855dc}.staff-work-purple .staff-work-icon{background:#7855dc}.staff-work-green{border-left-color:#20a66a}.staff-work-green .staff-work-icon{background:#20a66a}
    .staff-quick-panel,.staff-course-panel{margin-top:18px;padding:21px;border:1px solid #e8edf5;border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(21,43,77,.05)}
    .staff-panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:16px}.staff-panel-heading h2{margin:3px 0 3px;color:#17233a;font-size:1.08rem}.staff-panel-heading p{margin:0;color:#8493a7;font-size:.72rem}.staff-panel-heading .staff-kicker{color:#79879d;opacity:1}.staff-panel-icon{color:#a2b1c2;font-size:1.05rem;margin-top:3px}
    .staff-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.staff-quick-item{display:flex;align-items:center;gap:10px;min-width:0;padding:12px;border:1px solid #e8edf5;border-radius:12px;background:#fbfcff;color:#22314b;text-decoration:none}.staff-quick-item:hover{background:#f4f9ff;border-color:#b9d2fb;color:#1d5fa5}.staff-quick-icon{display:grid;place-items:center;width:31px;height:31px;flex:0 0 auto;border-radius:9px;color:#fff;font-size:.76rem}.staff-quick-item>span:nth-child(2){display:flex;flex-direction:column;gap:3px;min-width:0}.staff-quick-item strong{font-size:.75rem}.staff-quick-item small{overflow:hidden;color:#7b8aa0;font-size:.65rem;text-overflow:ellipsis;white-space:nowrap}.staff-quick-arrow{margin-left:auto;color:#9aa6b8;font-size:.62rem}.staff-quick-blue .staff-quick-icon{background:#3ea1e4}.staff-quick-purple .staff-quick-icon{background:#7855dc}.staff-quick-green .staff-quick-icon{background:#20a66a}.staff-quick-orange .staff-quick-icon{background:#ee9343}
    .staff-course-summary{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:7px}.staff-course-summary span{display:inline-flex;align-items:center;gap:4px;padding:6px 9px;border-radius:999px;background:#f2f7fb;color:#6b7f93;font-size:.65rem;white-space:nowrap}.staff-course-summary strong{color:#267eaf;font-size:.75rem}.staff-course-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.staff-course-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:8px;min-width:0;padding:11px 12px;border:1px solid #e8edf5;border-radius:11px;background:#fbfcff;color:#22314b;text-decoration:none}.staff-course-item:hover{background:#f4f9ff;border-color:#b9d2fb;color:#1d5fa5}.staff-course-code{padding:4px 6px;border-radius:6px;background:#e4f3fc;color:#258ac8;font-size:.62rem;font-weight:800;white-space:nowrap}.staff-course-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.72rem}.staff-course-role{grid-column:2;font-size:.61rem;color:#8493a7;white-space:nowrap}.staff-course-item>i{grid-column:3;grid-row:1 / span 2;color:#9aa6b8;font-size:.62rem}.staff-course-more{display:inline-flex;align-items:center;gap:7px;margin-top:13px;color:#267eaf;font-size:.72rem;font-weight:800;text-decoration:none}.staff-course-more:hover{color:#124d73}
    @media(max-width:1050px){.staff-work-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.staff-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.staff-course-list{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.staff-work-panel,.staff-quick-panel,.staff-course-panel{padding:16px 14px;border-radius:15px}.staff-panel-heading{margin-bottom:13px}.staff-panel-heading h2{font-size:1.02rem}.staff-panel-heading p{font-size:.67rem}.staff-work-grid,.staff-quick-grid,.staff-course-list{grid-template-columns:1fr;gap:8px}.staff-work-card{padding:13px 12px;border-left-width:3px}.staff-work-copy small{font-size:.66rem}.staff-work-copy strong{font-size:1.12rem}.staff-work-copy em{font-size:.62rem}.staff-quick-item{padding:11px 10px}.staff-quick-item strong{font-size:.72rem}.staff-quick-item small{font-size:.63rem}.staff-course-panel .staff-panel-heading{display:block}.staff-course-summary{justify-content:flex-start;margin-top:10px}.staff-course-summary span{font-size:.61rem}.staff-course-item{padding:10px}.staff-course-name{font-size:.69rem}}
</style>
