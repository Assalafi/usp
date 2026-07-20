@php
    use Illuminate\Support\Facades\DB;
    if (session('accType') != 'Student') {
        $course_system_allocation = DB::table('course_system_allocation');
        $positions = DB::table('staff')
            ->select('appointment')
            ->where('username', session('username'))
            ->value('appointment');
        $nav_allo_ = DB::table('course_allocation')
            ->select('course')
            ->where(['username' => session('username')])
            ->first();
        $nav_ = [];
        $nav_allo = 0;
        if ($nav_allo_) {
            $nav_ = ['Approve Results', 'Results'];
            $nav_allo = 1;
        }
        if ($positions) {
            $roles = DB::table('rolls')->where('username', $positions)->select('page', 'main', 'link');
            $pos = $roles->pluck('page');
            $role = $roles->get();
        } else {
            $poss = DB::table('rolls')->where('username', 'nothing')->select('page', 'main', 'link');
            $pos = $poss->pluck('page');
            $role = $poss->get();
        }

        $nav = DB::table('rolls')
            ->where('username', session('username'))
            ->whereNotIn('page', $pos)
            ->whereNotIn('page', $nav_)
            ->select('page', 'main', 'link')
            ->orderBy('main_order', 'ASC')
            ->orderBy('sub_order', 'ASC')
            ->get();
    }

@endphp
<!-- Sidemenu -->
@php
    $skiplinks = [];
@endphp
<div class="navbar-content scroll-div ps ps--active-y noprint" style="border-top: solid white 3px;">
    <ul class="nav pcoded-inner-navbar">
        <li class="nav-item">
            <a href="/" class="nav-link">
                <span class="pcoded-micon"><i class="fas fa-home"></i></span>
                <span class="pcoded-mtext">Dashboard</span>
            </a>
        </li>
        @if (session('accType') == 'Staff')
            <li class="nav-item">
                <a href="/staff-profile" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-user"></i></span>
                    <span class="pcoded-mtext">Profile</span>
                </a>
            </li>
            @if ($nav_allo == 1)
                <li class="nav-item">
                    <a href="/my-lecture-timetable" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">My Lec. Timetable</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/exam timetable" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Exam Timetable</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/course allocation" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Course
                            Allocation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/results" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Results</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a href="/corrigenda" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-file"></i></span>
                        <span class="pcoded-mtext">Corrigenda</span>
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a href="/approve results" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Approve Results</span>
                    </a>
                </li>

                @php
                    $skiplinks = [
                        '/approve results',
                        '/results',
                        '/corrigenda',
                        '/course allocation',
                        '/exam timetable',
                        '/my-lecture-timetable',
                        '/staff-record-update',
                        '/staff-record',
                        '/admin-applicant',
                    ];
                @endphp
            @endif
        @endif
        @if (session('accType') == 'Staff')
            @foreach ($role->where('page', '!=', 'Dashboard')->where('link', '!=', '/admin-applicant') as $role)
                @if (!in_array($role->link, $skiplinks))
                    @php
                        $skiplinks[] = $role->link;
                    @endphp
                    @if (
                        ($role->link == '/status' ||
                            $role->link == '/summary of graduation' ||
                            $role->link == '/press release' ||
                            $role->link == '/computation record' ||
                            $role->link == '/transcript' ||
                            $role->link == '/statement of result' ||
                            $role->link == '/status' ||
                            $role->link == '/status') &&
                            session('faculty') == 'GST')
                    @else
                        <li class="nav-item">
                            <a href="{{ $role->link }}" class="nav-link">
                                <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                                <span class="pcoded-mtext">{{ $role->page }}</span>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach
            @foreach ($nav->where('page', '!=', 'Dashboard')->where('link', '!=', '/staff-record')->where('link', '!=', '/staff-record-update')->where('link', '!=', '/admin-applicant') as $nav)
                @if (!in_array($nav->link, $skiplinks))
                    @php
                        $skiplinks[] = $nav->link;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ $nav->link }}" class="nav-link">
                            <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                            <span class="pcoded-mtext">{{ $nav->page }}</span>
                        </a>
                    </li>
                @endif
            @endforeach

            {{-- Check if username exist in course_system_allocation table --}}
            @if ($course_system_allocation->where('username', session('username'))->count() > 0)
                <li class="nav-item">
                    <a href="/course-system-results" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Course System Results</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a href="/election general" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-thumbs-up"></i></span>
                    <span class="pcoded-mtext">General Election</span>
                </a>
            </li>


        @endif

        @if (session('accType') == 'Admin')
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'committee meetings' ||
                    $page == 'committee role' ||
                    $page == 'committee' ||
                    $page == 'sub committee' ||
                    $page == 'committee membership') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-users"></i></span>
                    <span class="pcoded-mtext">Council Committee</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'committee meetings') active @endif"><a href="/committee meetings"
                            class="">Meetings</a></li>
                    <li class="@if ($page == 'committee role') active @endif"><a href="/committee role"
                            class="">Role</a></li>
                    {{-- <li class="@if ($page == 'committee') active @endif"><a href="/committee"
                            class="">Group of Committee</a></li> --}}
                    <li class="@if ($page == 'sub committee') active @endif"><a href="/sub committee"
                            class="">Committee</a></li>
                    <li class="@if ($page == 'committee membership') active @endif"><a href="/committee membership"
                            class="">Membership</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-edit"></i></span>
                    <span class="pcoded-mtext">Admission</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=""><a href="/applicants" class="">PUTME</a></li>
                    {{-- <li class=""><a href="#" class="">Admission Letter</a></li> --}}
                    {{-- <li class=""><a href="#" class="">Clearance</a></li> --}}
                    {{-- <li class=""><a href="/jamb-admitted" class="">Jamb Admitted</a></li> --}}
                    <li class=""><a href="/registration" class="">New Registration</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if ($page == 'faculty' || $page == 'department' || $page == 'program' || $page == 'session' || $page == 'semester' || $page == 'unit' || $page == 'designation' || $page == 'grade' || $page == 'step') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-address-card"></i></span>
                    <span class="pcoded-mtext">Academics</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'faculty') active @endif"><a href="/faculty"
                            class="">Faculty</a></li>
                    <li class="@if ($page == 'department') active @endif"><a href="/department"
                            class="">Deparment</a></li>
                    <li class="@if ($page == 'program') active @endif"><a href="/program"
                            class="">Program</a></li>
                    <li class="@if ($page == 'unit') active @endif"><a href="/reference-data/unit"
                            class="">Unit</a></li>
                    <li class="@if ($page == 'designation') active @endif"><a href="/reference-data/designation"
                            class="">Designation</a></li>
                    <li class="@if ($page == 'grade') active @endif"><a href="/reference-data/grade"
                            class="">Grade</a></li>
                    <li class="@if ($page == 'step') active @endif"><a href="/reference-data/step"
                            class="">Step</a></li>
                    <li class="@if ($page == 'semester') active @endif"><a href="/semester"
                            class="">Semester</a></li>
                    <li class="@if ($page == 'session') active @endif"><a href="/session"
                            class="">Session</a></li>
                    <li class="@if ($page == 'affiliated schools') active @endif"><a href="/affiliated-schools"
                            class="">Affiliated Schools</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'program course registration' ||
                    $page == 'course' ||
                    $page == 'course allocation' ||
                    $page == 'course structure' ||
                    $page == 'student course registration') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-address-card"></i></span>
                    <span class="pcoded-mtext">Courses</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'course') active @endif"><a href="/course"
                            class="">Departmental Courses</a></li>
                    <li class="@if ($page == 'program course registration') active @endif"><a
                            href="/program course registration" class="">Program Courses</a></li>
                    {{-- student course registration --}}
                    <li class="@if ($page == 'student course registration') active @endif"><a
                            href="/student course registration" class="">Student Courses</a></li>
                    <li class="@if ($page == 'course allocation') active @endif"><a href="/course allocation"
                            class="">Course
                            Allocation</a></li>
                    <li class="@if ($page == 'course structure') active @endif"><a href="/course structure"
                            class="">Course
                            Structure</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'fees due' ||
                    $page == 'fees type' ||
                    $page == 'hostel fees' ||
                    $page == 'school fees' ||
                    $page == 'fixed assets' ||
                    $page == 'fixed assets depreciation' ||
                    $page == 'fixed assets analysis' ||
                    $page == 'fixed assets disposal' ||
                    $page == 'manage fixed assets' ||
                    $page == 'fees master list') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-money-bill-wave"></i></span>
                    <span class="pcoded-mtext">Bursary</span>
                </a>
                <ul class="pcoded-submenu">
                    <li
                        class="nav-item pcoded-hasmenu @if (
                            $page == 'fees due' ||
                                $page == 'fees type' ||
                                $page == 'hostel fees' ||
                                $page == 'school fees' ||
                                $page == 'fees master list') pcoded-trigger active @endif">
                        <a href="#!" class="nav-link">
                            <span class="pcoded-mtext">Fees</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="@if ($page == 'fees due') active @endif"><a href="/fees due"
                                    class="">Fees Collection</a></li>
                            <li class=""><a href="#" class="">PUTME</a></li>
                            <li class="@if ($page == 'school fees') active @endif"><a href="/school fees"
                                    class="">School fees</a></li>
                            <li class=""><a href="#" class="">Hostel Pin fees</a></li>
                            <li class="@if ($page == 'hostel fees') active @endif"><a href="/hostel fees"
                                    class="">Hostel fees</a></li>
                            <li class=""><a href="/id-card-fees" class="">ID Card Payment</a></li>
                            <li class=""><a href="#" class="">Transcript fees</a></li>
                            <li class=""><a href="#" class="">Certificate fees</a></li>
                        </ul>
                    </li>
                    <li
                        class="nav-item pcoded-hasmenu @if (
                            $page == 'fixed assets' ||
                                $page == 'fixed assets depreciation' ||
                                $page == 'fixed assets analysis' ||
                                $page == 'fixed assets disposal' ||
                                $page == 'manage fixed assets') pcoded-trigger active @endif">
                        <a href="#!" class="nav-link">
                            <span class="pcoded-mtext">Fixed Assets</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="@if ($page == 'fixed assets') active @endif"><a href="/fixed assets"
                                    class="">Register</a></li>
                            <li class="@if ($page == 'fixed assets analysis') active @endif"><a
                                    href="/fixed assets analysis" class="">Analysis</a></li>
                            <li class="@if ($page == 'fixed assets disposal') active @endif"><a
                                    href="/fixed assets disposal" class="">Disposal</a></li>
                            <li class="@if ($page == 'fixed assets depreciation') active @endif"><a
                                    href="/fixed assets depreciation" class="">Depreciation</a></li>
                            <li class="@if ($page == 'manage fixed assets') active @endif"><a
                                    href="/manage fixed assets" class="">Manage</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'students list' ||
                    $page == 'student id card' ||
                    $page == 'course material' ||
                    $page == 'session history' ||
                    $page == 'attendance' ||
                    $page == 'student exit') pcoded-trigger active @endif">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-users"></i></span>
                    <span class="pcoded-mtext">Students</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'students list') active @endif"><a href="/students list"
                            class="">Students List/Info</a></li>
                    <li class="@if ($page == 'student id card') active @endif"><a href="/student id card"
                            class="">Student ID Card</a></li>
                    <li class="@if ($page == 'session history') active @endif"><a href="/session history"
                            class="">Student Session</a></li>
                    <li class="@if ($page == 'change of course admin') active @endif"><a href="/change-of-course/admin"
                            class="">Change of Course</a></li>
                    <li class="@if ($page == 'inter university transfer admin' || $page == 'inter university transfer details') active @endif"><a
                            href="/inter-university-transfer/admin" class="">Inter-University Transfer</a></li>
                    <li class="@if ($page == 'admin siwes') active @endif"><a href="/admin/siwes"
                            class="">SIWES</a></li>

                    {{-- <li class="@if ($page == 'course material') active @endif"><a href="/course material"
                            class="">Course Material</a></li>
                    <li class="@if ($page == 'attendance') active @endif"><a href="/attendance" class="">Attendance</a></li>
                    <li class="@if ($page == 'assignment') active @endif"><a href="/assignment"
                            class="">Assignment</a></li> --}}
                    {{-- <li class="@if ($page == 'student exit') active @endif"><a href="/student exit"
                            class="">Leave</a></li> --}}
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'halls' ||
                    $page == 'lecture timetable' ||
                    $page == 'exam timetable' ||
                    $page == 'ca timetable' ||
                    $page == 'hall allocation') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-address-card"></i></span>
                    <span class="pcoded-mtext">Timetable</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'halls') active @endif"><a href="/halls"
                            class="">Halls</a></li>
                    <li class="@if ($page == 'hall allocation') active @endif"><a href="/hall allocation"
                            class="">Hall Allocation</a></li>
                    <li class="@if ($page == 'lecture timetable') active @endif"><a href="/lecture timetable"
                            class="">Lecture Timetable</a></li>
                    {{-- <li class="@if ($page == 'ca timetable') active @endif"><a href="/ca timetable"
                            class="">CA Timetable</a></li> --}}
                    <li class="@if ($page == 'exam timetable') active @endif"><a href="/exam timetable"
                            class="">Exam Timetable</a></li>
                    {{-- <li class="@if ($page == 'lecturer timetable') active @endif"><a href="#"
                            class="">View Lecturer Timetable</a></li> --}}
                </ul>
            </li>
            {{-- <li class="nav-item">
            <a href="users" class="nav-link">
                <span class="pcoded-micon"><i class="fas fa-users"></i></span>
                <span class="pcoded-mtext">Users</span>
            </a>
            </li> --}}
            <li class="nav-item pcoded-hasmenu">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-bed"></i></span>
                    <span class="pcoded-mtext">Hostel</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=""><a href="pins" class="">Pin</a></li>
                    {{-- <li class=""><a href="#" class="">Hostel Payment</a></li> --}}
                    <li class=""><a href="/available bed space" class="">Reserve Bed Space</a></li>
                    <li class=""><a href="/online bed space" class="">Online Bed Space</a></li>
                    <li class=""><a href="/hostel recipients" class="">Hostel Recipients</a></li>
                    <li class=""><a href="/manage hostel" class="">Manage Hostel</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (in_array($page, [
                    'election settings',
                    'election positions',
                    'election canditades',
                    'election votes',
                    'election general',
                    'election faculty',
                    'election hostel',
                    'election lga',
                    'election live',
                ])) pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-thumbs-up"></i></span>
                    <span class="pcoded-mtext">E-Voting</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'election general') active @endif"><a href="/election general"
                            class="">General Election</a></li>
                    <li class="@if ($page == 'election faculty') active @endif"><a href="/election faculty"
                            class="">Faculty Reps</a></li>
                    <li class="@if ($page == 'election hostel') active @endif"><a href="/election hostel"
                            class="">Hostel Reps</a></li>
                    <li class="@if ($page == 'election lga') active @endif"><a href="/election lga"
                            class="">LGA Reps</a></li>
                    <li class="@if ($page == 'election candidates') active @endif"><a href="/election candidates"
                            class="">Candidates</a></li>
                    <li class="@if ($page == 'election positions') active @endif"><a href="/election positions"
                            class="">Positions</a></li>
                    <li class="@if ($page == 'election votes') active @endif"><a href="/election votes"
                            class="">Voters</a></li>
                    <li class="@if ($page == 'election live') active @endif"><a href="/election live"
                            class="">Live</a></li>
                    <li class="@if ($page == 'election settings') active @endif"><a href="/election settings"
                            class="">Settings</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if (
                $page == 'results' ||
                    $page == 'approve results' ||
                    $page == 'grades by courses' ||
                    $page == 'corrigenda' ||
                    $page == 'status' ||
                    $page == 'summary of graduation' ||
                    $page == 'computation record' ||
                    $page == 'transcript' ||
                    $page == 'statement of result' ||
                    $page == 'certificate' ||
                    $page == 'examination settings' ||
                    $page == 'course system allocation' ||
                    $page == 'press release') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-newspaper"></i></span>
                    <span class="pcoded-mtext">Examination</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'results') active @endif"><a href="/results"
                            class="">Course Results</a></li>
                    <li class="@if ($page == 'course system allocation') active @endif"><a href="/course system allocation"
                            class="">Course System Allocation</a></li>
                    <li class="@if ($page == 'corrigenda') active @endif"><a href="/corrigenda"
                            class="">Results Corrigenda</a></li>
                    <li class="@if ($page == 'result summary') active @endif"><a href="/result-summary"
                            class="">Result Summary</a></li>
                    <li class="@if ($page == 'result summary vc') active @endif"><a href="/result-summary-vc"
                            class="">Result Summary (VC)</a></li>
                    <li class="@if ($page == 'approve results') active @endif"><a href="/approve results"
                            class="">Approve Results</a></li>
                    <li class="@if ($page == 'grades by courses') active @endif"><a href="/grades by courses"
                            class="">Grades by Courses</a></li>
                    <li class="@if ($page == 'status') active @endif"><a href="/status"
                            class="">Academic Status</a></li>
                    <li class="@if ($page == 'summary of graduation') active @endif"><a href="/summary of graduation"
                            class="">Summary of Graduation</a></li>
                    <li class="@if ($page == 'press release') active @endif"><a href="/press release"
                            class="">Press Release</a></li>
                    <li class="@if ($page == 'computation record') active @endif"><a href="/computation record"
                            class="">Computation Sheets</a></li>
                    <li class="@if ($page == 'transcript') active @endif"><a href="/transcript"
                            class="">Transcript</a></li>
                    <li class="@if ($page == 'statement of result') active @endif"><a href="/statement of result"
                            class="">Statement of Result</a></li>
                    <li class="@if ($page == 'certificate') active @endif"><a href="/certificate" class="">Certificate</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-graduation-cap"></i></span>
                    <span class="pcoded-mtext">Alumni</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=""><a href="/alumni" class="">List of Alumni</a></li>
                    <li class=""><a href="#" class="">Pre-Mobilization</a></li>
                    <li class=""><a href="#" class="">Transcript</a></li>
                    <li class=""><a href="#" class="">Certificate</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-bullhorn"></i></span>
                    <span class="pcoded-mtext">Communication</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=""><a href="#" class="">Chat</a></li>
                    <li class=""><a href="{{ route('sms.create') }}" class="">Send SMS</a></li>
                    <li class=""><a href="{{ route('sms.sent') }}" class="">Sent SMS</a></li>
                    <li class=""><a href="#" class="">Event List</a></li>
                    <li class=""><a href="#" class="">Academic Calendar</a></li>
                    <li class=""><a href="#" class="">Notice List</a></li>
                    <li class=""><a href="#" class="">Notice Categories</a></li>
                    <li class=""><a href="#" class="">Complaint</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if ($page == 'staff' || $page == 'staff' || $page == 'staff' || $page == 'staff' || $page == 'staff') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-users"></i></span>
                    <span class="pcoded-mtext">Human Resourse</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'recruitment') active @endif"><a href="/recruitment" class="">Recruitment</a></li>
                    <li class="@if ($page == 'recruitment-management') active @endif"><a href="/recruitment/management" class="">Recruitment Mgt</a></li>
                    <li class="@if ($page == 'staff') active @endif"><a href="/staff"
                            class="">Staff List/Info</a></li>
                    <li class=""><a href="#" class="">Staff ID Card</a></li>
                    <li class=""><a href="#" class="">Payroll</a></li>
                    <li class=""><a href="#" class="">Leave</a></li>
                    <li class=""><a href="#" class="">Posting</a></li>
                    <li class=""><a href="#" class="">Apprisal</a></li>
                    <li class=""><a href="#" class="">Promotion</a></li>
                    <li class=""><a href="#" class="">Death List</a></li>
                    <li class=""><a href="#" class="">Retirees</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-globe"></i></span>
                    <span class="pcoded-mtext">Front Web</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=""><a href="#" class="">Contact Settings</a></li>
                </ul>
            </li>
            <li class="nav-item pcoded-hasmenu @if ($page == 'pages' || $page == 'rolls' || $page == 'grading system' || $page == 'pages' || $page == 'pages') pcoded-trigger active @endif ">
                <a href="#!" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-cog"></i></span>
                    <span class="pcoded-mtext">System Settings</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="@if ($page == 'general settings') active @endif"><a
                            href="{{ route('settings.general') }}" class="">General</a></li>
                    <li class=""><a href="#" class="">User Log</a></li>
                    <li class="@if ($page == 'pages') active @endif"><a href="/pages"
                            class="">Pages</a></li>
                    <li class="@if ($page == 'rolls') active @endif"><a href="/rolls"
                            class="">Role Management</a></li>
                    <li class="@if ($page == 'grading system') active @endif"><a href="/grading system"
                            class="">Grading System</a></li>
                </ul>
            </li>
        @endif
        @if (strtoupper(session('username')) == 'SP11913' || strtoupper(session('username')) == 'SU')
            @if (strtoupper(session('username')) == 'SP11913')
                <li class="nav-item">
                    <a href="/jamb-admitted" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-arrow-circle-right"></i></span>
                        <span class="pcoded-mtext">Jamb Admitted</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="/reset-passwords" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-lock"></i></span>
                    <span class="pcoded-mtext">Reset Password</span>
                </a>
            </li>
        @endif
        @php
            if (strpos(session('faculty'), '.PG') !== false) {
                $pg = 1;
            } else {
                $pg = 0;
            }
        @endphp
        @if (session('accType') == 'Student' || session('username') == '15/07/02/054')
            @if ($pg == 0)
                <li class="nav-item">
                    <a href="payment" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-money-bill-wave"></i></span>
                        <span class="pcoded-mtext">Payment</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="profile" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-user"></i></span>
                    <span class="pcoded-mtext">Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/student course registration" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-book-open"></i></span>
                    <span class="pcoded-mtext">Courses</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/change-of-course" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-exchange-alt"></i></span>
                    <span class="pcoded-mtext">Change of Course</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/siwes" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-briefcase"></i></span>
                    <span class="pcoded-mtext">SIWES</span>
                </a>
            </li>
            @if ($pg == 0)
                <li class="nav-item">
                    <a href="/lecture timetable" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-list"></i></span>
                        <span class="pcoded-mtext">Timetable</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="bed space reservations" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-bed"></i></span>
                        <span class="pcoded-mtext">Hostel</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/validate H-Pin" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-bed"></i></span>
                        <span class="pcoded-mtext">Pin Validation</span>
                    </a>
                </li>
                <li class="nav-item pcoded-hasmenu @if (in_array($page, ['election general', 'election hostel', 'election faculty'])) pcoded-trigger active @endif">
                    <a href="#!" class="nav-link">
                        <span class="pcoded-micon"><i class="fas fa-thumbs-up"></i></span>
                        <span class="pcoded-mtext">E-Voting</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="@if ($page == 'election general') active @endif"><a href="/election general"
                                class="">General Election</a></li>
                        <li class="@if ($page == 'election faculty') active @endif"><a href="/election faculty"
                                class="">Faculty Reps</a></li>
                        @if (DB::table('hostel')->where('occupant', session('id_number'))->value('id') > 0)
                            <li class="@if ($page == 'election hostel') active @endif"><a href="/election hostel"
                                    class="">Hostel Reps</a></li>
                        @endif
                        @if (strtoupper(session('state')) == 'BORNO')
                            <li class="@if ($page == 'election lga') active @endif"><a href="/election lga"
                                    class="">LGA Reps</a></li>
                        @endif

                    </ul>
                </li>
                <li class="nav-item">
                    <a href="/student-result" class="nav-link">
                        <span class="pcoded-micon"><i class="fa fa-file" aria-hidden="true"></i></span>
                        <span class="pcoded-mtext">Results/Status</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="pcoded-micon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                        <span class="pcoded-mtext">Academic Calender</span>
                    </a>
                </li>
            @endif
        @endif
        @if (session('accType') == 'Transfer')
            <li class="nav-item">
                <a href="/inter-university-transfer" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-exchange-alt"></i></span>
                    <span class="pcoded-mtext">My Transfer Application</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/inter-university-transfer/payment" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-credit-card"></i></span>
                    <span class="pcoded-mtext">Payment</span>
                </a>
            </li>
        @endif
        @if (session('accType') == 'Alumni')
            <li class="nav-item">
                <a href="/election general" class="nav-link">
                    <span class="pcoded-micon"><i class="fas fa-thumbs-up"></i></span>
                    <span class="pcoded-mtext">General Election</span>
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a href="/update password" class="nav-link">
                <span class="pcoded-micon"><i class="fas fa-lock"></i></span>
                <span class="pcoded-mtext">Change Password</span>
            </a>
        </li>
        {{-- check if data exist in array --}}
        <li class="nav-item">
            <a href="/logout" class="nav-link">
                <span class="pcoded-micon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="pcoded-mtext">Logout</span>
            </a>
        </li>
    </ul>
</div>
<!-- End Sidebar -->
