@php
    if (!session()->has('log')) {
        return redirect('/');
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    @include('css')
</head>

<body class="">
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
        session()->put('links', $links);
        $datas = DB::table('rolls')
            ->where(['link' => $links, 'username' => session('username')])
            ->select('page', 'action', 'faculty')
            ->get();
    @endphp
    @forelse ($datas as $roll)
        @php
            $action = explode(',', $roll->action);
            $count = count($action);
            if ($roll->faculty != 'all') {
                $sessions = DB::table('session')->where('status', '1')->value('title');
                $faculty = DB::table('faculty')
                    ->where(['status' => '1', 'code' => $roll->faculty])
                    ->select('code', 'title')
                    ->orderBy('title', 'ASC')
                    ->get();
                if ($links == '/registration') {
                    $data = $dataq->where(['faculty' => $roll->faculty, 'session_of_entry' => $sessions])->get();
                    $admitted = DB::table('students')
                        ->where(['session_of_entry' => $sessions, 'faculty' => $roll->faculty])
                        ->select('id')
                        ->orderBy('id', 'DESC')
                        ->count('id');
                    $not_paid = DB::table('students')
                        ->where(['session_of_entry' => $sessions, 'id_no' => 0, 'faculty' => $roll->faculty])
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
                ($page != 'dashboards' &&
                    $page != 'update password' &&
                    $page != 'reset password' &&
                    $page != 'staff profile')
            ) {
                echo 'ACCESS DENIED!!!';
                die();
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
    @else
        @if (session('accType') == 'Staff')
            @include('Admin/' . $page)
        @else
            @include(session('accType') . '/' . $page)
        @endif
    @endif

    <!-- End Content-->

    @include('js')

</body>

</html>
