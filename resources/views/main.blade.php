@php
    if (!session()->has('log')) {
        echo "<script> window.location.href = '/'; </script>";
        return;
    }
    if (session('activeProfile') == 0 && $page != 'profile' && session('accType') == 'Student') {
        // use js to redirect to profile, with message "Please update your profile" in flash session
        session()->flash('info', 'Please you must update your profile');
        echo "<script> window.location.href = '/profile'; </script>";
        return;
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    @include('css')
</head>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <nav
        class="pcoded-navbar active-lightblue title-lightblue navbar-lightblue brand-lightblue navbar-image-4 menu-item-icon-style2">
        <div class="navbar-wrapper">
            <div class="navbar-brand header-logo">
                <a href="#" class="b-brand">
                    <div class="b-bg">
                        <img src="{{ asset('uploads/logo.png') }}" alt="logo">
                    </div>
                </a>
                <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            </div>
            <!--- Sidemenu -->
            @include('nav')
            <!-- End Sidebar -->

        </div>
    </nav>
    <!-- [ navigation menu ] end -->


    <!-- [ Header ] start -->
    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-lightblue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse1" href="#!"><span></span></a>
            <a href="#" class="b-brand">
                <div class="b-bg">
                    <img src="{{ asset('uploads/logo.png') }}" alt="logo" height="20">
                </div>
            </a>
            <span style="width: 100%; margin-left: 5%">UNIMAID PORTAL</span>
        </div>
        <a class="mobile-menu" id="mobile-header" href="#!">
            <i class="feather icon-more-horizontal"></i>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li><a href="#!" class="full-screen" onclick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a></li>
                <li>
                    <h4 class="topbar-title">UNIMAID PORTAL</h4>
                </li>
            </ul>

            <!-- [ Auth Nav ] start -->
            <ul class="navbar-nav ms-auto">

                <!-- Notification -->

                <!-- Profile -->
            </ul>
            <!-- [ Auth Nav ] end -->

        </div>
    </header>
    <!-- [ Header ] end -->


    <!-- [ chat user list ] start -->
    <section class="header-user-list">
        <div class="h-list-header">
            <div class="input-group">
                <input type="text" id="search-friends" class="form-control" placeholder="Search Friend . . .">
            </div>
        </div>
        <div class="h-list-body">
            <a href="#!" class="h-close-text"><i class="feather icon-chevrons-right"></i></a>
            <div class="main-friend-cont scroll-div">
                <div class="main-friend-list">

                </div>
            </div>
        </div>
    </section>
    <!-- [ chat user list ] end -->

    <!-- [ chat message ] start -->
    <section class="header-chat">
        <div class="h-list-header">
            <h6></h6>
            <a href="#!" class="h-back-user-list"><i class="feather icon-chevron-left"></i></a>
        </div>
        <div class="h-list-body">
            <div class="main-chat-cont scroll-div">
                <div class="main-friend-chat">
                    <div class="media chat-messages">

                        <div class="media-body chat-menu-content">

                        </div>
                    </div>
                    <div class="media chat-messages">
                        <div class="media-body chat-menu-reply">

                        </div>
                    </div>
                    <div class="media chat-messages">

                        <div class="media-body chat-menu-content">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- [ chat message ] end -->

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    @php
                        $currentURL =
                            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') .
                            $_SERVER['HTTP_HOST'] .
                            $_SERVER['REQUEST_URI'];
                        $urlParts = parse_url($currentURL);
                        $links = '/';
                        if (isset($urlParts['path'])) {
                            $links = str_replace('%20', ' ', $urlParts['path']);
                            //echo $links;
                        } else {
                        }
                        //die;
                        use Illuminate\Support\Facades\DB;
                        $uploadb = 'no';
                        $createb = 'no';
                        $updateb = 'no';
                        $deleteb = 'no';

                        $viewb = 'no';
                        $idcardb = 'no';
                        $getpdfb = 'no';
                        $statusb = 'no';
                        $passwordb = 'no';

                        $facultyb = 'no';
                        $departmentb = 'no';
                        $programb = 'no';
                        $levelb = 'no';
                        $genderb = 'no';
                        $maritalb = 'no';
                        $stateb = 'no';
                        $lgab = 'no';
                        $phoneb = 'no';
                        $jambb = 'no';
                        $idb = 'no';
                        $lnk = '/' . strtolower($page);
                        preg_match('/^\/[^\/]+/', $links, $matches);

                        if (!empty($matches)) {
                            $links = $matches[0]; // Output: /staff-record
                        }
                        session()->put('links', $links);
                        //dd($links);

                        $positions = DB::table('staff')
                            ->select('appointment')
                            ->where('username', session('username'))
                            ->value('appointment');
                        if ($positions) {
                            $datas = DB::table('rolls')
                                ->where(['link' => $links, 'username' => $positions])
                                ->select('page', 'action', 'faculty')
                                ->get();

                            $getAllFaculty = DB::table('rolls')
                                ->select('faculty')
                                ->where(['link' => $links, 'username' => $positions])
                                ->pluck('faculty');
                            $ch = DB::table('rolls')
                                ->where(['link' => $links, 'username' => $positions])
                                ->select('id')
                                ->value('id');
                            if ($ch > 0) {
                            } else {
                                $datas = DB::table('rolls')
                                    ->where(['link' => $links, 'username' => session('username')])
                                    ->select('page', 'action', 'faculty')
                                    ->get();
                                $getAllFaculty = DB::table('rolls')
                                    ->select('faculty')
                                    ->where(['link' => $links, 'username' => session('username')])
                                    ->pluck('faculty');
                            }
                        } else {
                            $datas = DB::table('rolls')
                                ->where(['link' => $links, 'username' => session('username')])
                                ->select('page', 'action', 'faculty')
                                ->get();
                            $getAllFaculty = DB::table('rolls')
                                ->select('faculty')
                                ->where(['link' => $links, 'username' => session('username')])
                                ->pluck('faculty');
                        }
                    @endphp
                    @forelse ($datas as $roll)
                        @php
                            $action = explode(',', $roll->action);
                            $count = count($action);
                            if ($roll->faculty != 'all') {
                                $sessionss = DB::table('session')->where('status', '1')->value('title');
                                $faculty = DB::table('faculty')
                                    ->where(['status' => '1'])
                                    ->whereIn('code', $getAllFaculty)
                                    ->select('code', 'title')
                                    ->orderBy('title', 'ASC')
                                    ->get();
                                if ($links == '/registration') {
                                    $data = $dataq
                                        ->where(['faculty' => $roll->faculty, 'session_of_entry' => $sessionss])
                                        ->get();
                                    $admitted = DB::table('students')
                                        ->where(['session_of_entry' => $sessionss, 'faculty' => $roll->faculty])
                                        ->select('id')
                                        ->orderBy('id', 'DESC')
                                        ->count('id');
                                    $not_paid = DB::table('students')
                                        ->where([
                                            'session_of_entry' => $sessionss,
                                            'id_no' => 0,
                                            'faculty' => $roll->faculty,
                                        ])
                                        ->select('id')
                                        ->orderBy('id', 'DESC')
                                        ->count('id');
                                }
                            }

                            for ($i = 0; $i < $count; $i++) {
                                if ($action[$i] == 'upload') {
                                    $uploadb = 'yes';
                                }
                                if ($action[$i] == 'create') {
                                    $createb = 'yes';
                                }
                                if ($action[$i] == 'update') {
                                    $updateb = 'yes';
                                }
                                if ($action[$i] == 'delete') {
                                    $deleteb = 'yes';
                                }

                                if ($action[$i] == 'view') {
                                    $viewb = 'yes';
                                }
                                if ($action[$i] == 'idcard') {
                                    $idcardb = 'yes';
                                }
                                if ($action[$i] == 'getpdf') {
                                    $getpdfb = 'yes';
                                }
                                if ($action[$i] == 'status') {
                                    $statusb = 'yes';
                                }
                                if ($action[$i] == 'password') {
                                    $passwordb = 'yes';
                                }

                                if ($action[$i] == 'faculty') {
                                    $facultyb = 'yes';
                                }
                                if ($action[$i] == 'department') {
                                    $departmentb = 'yes';
                                }
                                if ($action[$i] == 'program') {
                                    $programb = 'yes';
                                }
                                if ($action[$i] == 'level') {
                                    $levelb = 'yes';
                                }
                                if ($action[$i] == 'gender') {
                                    $genderb = 'yes';
                                }
                                if ($action[$i] == 'marital') {
                                    $maritalb = 'yes';
                                }
                                if ($action[$i] == 'state') {
                                    $stateb = 'yes';
                                }
                                if ($action[$i] == 'lga') {
                                    $lgab = 'yes';
                                }
                                if ($action[$i] == 'phone') {
                                    $phoneb = 'yes';
                                }
                                if ($action[$i] == 'jamb') {
                                    $jambb = 'yes';
                                }
                                if ($action[$i] == 'id') {
                                    $idb = 'yes';
                                }
                            }
                        @endphp
                    @empty
                        @php
                            if (
                                session('accType') != 'Admin' &&
                                session('accType') != 'Student' &&
                                session('accType') != 'Transfer' &&
                                ($page != 'dashboards' &&
                                    $page != 'update password' &&
                                    $page != 'reset password' &&
                                    $page != 'staff profile' &&
                                    $page != 'committee meeting members' &&
                                    $page != 'committee meetings' &&
                                    $page != 'course system results' &&
                                    $page != 'election general' &&
                                    $page != 'pending results')
                            ) {
                                if (
                                    DB::table('course_allocation')
                                        ->select('course')
                                        ->where('username', session('username'))
                                        ->first() ||
                                    session('appointment') == 'HOD' ||
                                    session('appointment') == 'DEAN' ||
                                    session('appointment') == 'Dean' ||
                                    session('appointment') == 'Provost' ||
                                    session('appointment') == 'PROVOST' ||
                                    session('appointment') == 'Registrar' ||
                                    session('appointment') == 'REGISTRAR' ||
                                    session('appointment') == 'COC' ||
                                    session('appointment') == 'VC' ||
                                    session('appointment') == 'DSO'
                                ) {
                                } else {
                                    echo 'ACCESS DENIED!!!';
                                    die();
                                }
                            }
                        @endphp
                    @endforelse
                    <style>
                        .createAction,
                        .uploadAction,
                        .updateAction,
                        .deleteAction,
                        .viewAction,
                        .idcardAction,
                        .getpdfAction,
                        .statusAction,
                        .passwordAction,
                        .facultyAction,
                        .departmentAction,
                        .programAction,
                        .levelAction,
                        .genderAction,
                        .maritalAction,
                        .stateAction,
                        .lgaAction,
                        .phoneAction,
                        .jambAction,
                        .idAction {
                            display: none;
                        }
                    </style>
                    <style>
                        @if ($uploadb == 'yes' || session('accType') == 'Admin')
                            .uploadAction {
                                display: inline;
                            }
                        @endif
                        @if ($createb == 'yes' || session('accType') == 'Admin')
                            .createAction {
                                display: inline;
                            }
                        @endif
                        @if ($updateb == 'yes' || session('accType') == 'Admin')
                            .updateAction {
                                display: inline;
                            }
                        @endif
                        @if ($deleteb == 'yes' || session('accType') == 'Admin')
                            .deleteAction {
                                display: inline;
                            }
                        @endif

                        @if ($viewb == 'yes' || session('accType') == 'Admin')
                            .viewAction {
                                display: inline;
                            }
                        @endif
                        @if ($getpdfb == 'yes' || session('accType') == 'Admin')
                            .getpdfAction {
                                display: inline;
                            }
                        @endif
                        @if ($idcardb == 'yes' || session('accType') == 'Admin')
                            .idcardAction {
                                display: inline;
                            }
                        @endif
                        @if ($passwordb == 'yes' || session('accType') == 'Admin')
                            .passwordAction {
                                display: inline;
                            }
                        @endif
                        @if ($statusb == 'yes' || session('accType') == 'Admin')
                            .statusAction {
                                display: inline;
                            }
                        @endif


                        @if ($facultyb == 'yes' || session('accType') == 'Admin')
                            .facultyAction {
                                display: inline;
                            }
                        @endif
                        @if ($departmentb == 'yes' || session('accType') == 'Admin')
                            .departmentAction {
                                display: inline;
                            }
                        @endif
                        @if ($programb == 'yes' || session('accType') == 'Admin')
                            .programAction {
                                display: inline;
                            }
                        @endif
                        @if ($levelb == 'yes' || session('accType') == 'Admin')
                            .levelAction {
                                display: inline;
                            }
                        @endif
                        @if ($genderb == 'yes' || session('accType') == 'Admin')
                            .genderAction {
                                display: inline;
                            }
                        @endif
                        @if ($maritalb == 'yes' || session('accType') == 'Admin')
                            .maritalAction {
                                display: inline;
                            }
                        @endif
                        @if ($stateb == 'yes' || session('accType') == 'Admin')
                            .stateAction {
                                display: inline;
                            }
                        @endif
                        @if ($lgab == 'yes' || session('accType') == 'Admin')
                            .lgaAction {
                                display: inline;
                            }
                        @endif
                        @if ($phoneb == 'yes' || session('accType') == 'Admin')
                            .phoneAction {
                                display: inline;
                            }
                        @endif
                        @if ($idb == 'yes' || session('accType') == 'Admin')
                            .idAction {
                                display: inline;
                            }
                        @endif
                        @if ($jambb == 'yes' || session('accType') == 'Admin')
                            .jambAction {
                                display: inline;
                            }
                        @endif
                    </style>
                    <!-- Start Content-->
                    @if ($page == 'update password')
                        @include($page)
                    @elseif($page == 'election general' && session('accType') == 'Staff')
                        @include('Staff/' . $page)
                    @else
                        @if (session('accType') == 'Staff')
                            @include('Admin/' . $page)
                        @else
                            @include(session('accType') . '.' . $page)
                        @endif
                    @endif

                    <!-- End Content-->

                </div>
            </div>
        </div>
    </div>

    @include('js')

</body>

</html>
