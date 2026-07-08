@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;
    $course_flag = DB::table('program')
        ->where(['code' => session('program')])
        ->select('courses')
        ->value('courses');
    // session('student_session') == session('system_session')
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        @if ($course_flag == 1 || strpos(session('faculty'), '.PG') !== false)
            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="pills-bio_data-tab" data-bs-toggle="pill"
                                        href="#pills-bio_data" role="tab" aria-controls="pills-bio_data"
                                        aria-selected="true">Registered Courses</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-admission-tab" data-bs-toggle="pill"
                                        href="#pills-admission" role="tab" aria-controls="pills-admission"
                                        aria-selected="true">Elective Courses</a>
                                </li>
                                <li class="nav-item">
                                    {{-- Select session submit the page on select --}}

                                    <form action="/student course registration" method="GET" class="mb-3">
                                        <select name="session" class="form-select" onchange="this.form.submit()">
                                            <!-- Default System Session -->
                                            <option value="{{ session('system_session') }}"
                                                {{ request('session') == session('system_session') ? 'selected' : '' }}>
                                                SESSION: {{ session('system_session') }}
                                            </option>
                                            <option value="2023/2024"
                                                {{ request('session') == '2023/2024' ? 'selected' : '' }}>
                                                SESSION: 2023/2024
                                            </option>
                                            <option value="2022/2023"
                                                {{ request('session') == '2022/2023' ? 'selected' : '' }}>
                                                SESSION: 2022/2023
                                            </option>
                                            <option value="2021/2022"
                                                {{ request('session') == '2021/2022' ? 'selected' : '' }}>
                                                SESSION: 2021/2022
                                            </option>
                                            <option value="2020/2021"
                                                {{ request('session') == '2020/2021' ? 'selected' : '' }}>
                                                SESSION: 2020/2021
                                            </option>
                                            <option value="2019/2020"
                                                {{ request('session') == '2019/2020' ? 'selected' : '' }}>
                                                SESSION: 2019/2020
                                            </option>
                                            <option value="2018/2019"
                                                {{ request('session') == '2018/2019' ? 'selected' : '' }}>
                                                SESSION: 2018/2019
                                            </option>
                                            <option value="2017/2018"
                                                {{ request('session') == '2017/2018' ? 'selected' : '' }}>
                                                SESSION: 2017/2018
                                            </option>
                                        </select>
                                    </form>
                                </li>
                            </ul>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-bio_data" role="tabpanel"
                                    aria-labelledby="pills-bio_data-tab">
                                    <div class="table-responsive">
                                        <table id="" class="display table nowrap table-striped table-hover"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ 'CODE' }}</th>
                                                    <th>{{ 'UNIT' }}</th>
                                                    <th>{{ 'SEMESTER' }}</th>
                                                    <th>{{ 'TYPE' }}</th>
                                                    {{-- <th>{{ 'GRADE' }}</th> --}}
                                                    {{-- <th>{{ 'STATUS' }}</th> --}}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $sn1 = 1;
                                                @endphp
                                                @foreach ($data as $row)
                                                    @php
                                                        $change = DB::table('program_course_registration')
                                                            ->where([
                                                                'program' => session('program'),
                                                                'code' => $row->code,
                                                            ])
                                                            ->value('change_semester');
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $sn1++ }}</td>
                                                        <td
                                                            title="{{ DB::table('course')->where(['code' => $row->code])->value('title') }}">
                                                            {{ $row->code }}</td>
                                                        <td>{{ $row->unit }}</td>
                                                        <td>
                                                            @if ($change == 1)
                                                                <button class="btn btn-info btn-sm" type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#update{{ $row->id }}">{{ $row->semester }}</button>
                                                            @else
                                                                {{ $row->semester }}
                                                            @endif

                                                        </td>
                                                        <td>{{ $row->type }}</td>
                                                        {{-- <td>{{ $row->grade }}</td> --}}
                                                        {{-- <td>{{ strtoupper($row->status) }}</td> --}}
                                                    </tr>
                                                    @if ($change == 1)
                                                        <div id="update{{ $row->id }}" class="modal fade"
                                                            tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog modal-sm" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="myModalLabel">Change
                                                                            Semester</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"><span
                                                                                aria-hidden="true">&times;</span></button>
                                                                    </div>
                                                                    <div class="card">
                                                                        <form class="form-group"
                                                                            action="change-semester" method="POST"
                                                                            enctype="multipart/form-data">
                                                                            <div class="card-body">
                                                                                <!-- Details View Start -->
                                                                                @csrf
                                                                                <input type="hidden" name="id"
                                                                                    value="{{ $row->id }}">
                                                                                <div class="form-group">
                                                                                    <label
                                                                                        for="semester">SEMESTER</label>
                                                                                    <select class="form-control"
                                                                                        id="semester" name="semester"
                                                                                        required>
                                                                                        <option
                                                                                            value="{{ $row->semester }}">
                                                                                            Current:
                                                                                            {{ $row->semester }}
                                                                                        </option>
                                                                                        <option value="FIRST">FIRST
                                                                                        </option>
                                                                                        <option value="SECOND">SECOND
                                                                                        </option>
                                                                                    </select>
                                                                                </div>
                                                                                <!-- Details View End -->
                                                                                <button type="button"
                                                                                    class="btn btn-info"
                                                                                    data-bs-dismiss="modal">Close</button>
                                                                                <button type="submit"
                                                                                    class="btn btn-success">Change</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-admission" role="tabpanel"
                                    aria-labelledby="pills-admission-tab">
                                    <div class="table-responsive">
                                        <form action="/register-elective-course" method="POST">
                                            @csrf
                                            <table id=""
                                                class="display table nowrap table-striped table-hover"
                                                style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th colspan="5" style="text-align: center">FIRST SEMESTER
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ 'REGISTERED COURSE' }}</th>
                                                        <th>{{ 'SELECT COURSE' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $table = 'student_course_registration';
                                                    @endphp
                                                    <tr>
                                                        <td>1</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 1, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($f1 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 1 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 2, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($f2 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 2 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 3, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($f3 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 3 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>4</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 4, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($f4 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 4 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>5</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 5, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($f5 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 5 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table id=""
                                                class="display table nowrap table-striped table-hover"
                                                style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th colspan="5" style="text-align: center">SECOND SEMESTER
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ 'REGISTERED COURSE' }}</th>
                                                        <th>{{ 'SELECT COURSE' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 1, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($s1 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 1 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 2, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($s2 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 2 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 3, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($s3 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 3 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>4</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 4, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">

                                                                @forelse ($s4 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 4 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>5</td>
                                                        <td>
                                                            {{ DB::table($table)->where(['elective' => 5, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code') }}
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="code[]"
                                                                id="code">
                                                                @forelse ($s5 as $item)
                                                                    <option
                                                                        value="{{ $item->code }},{{ 5 }}">
                                                                        {{ $item->code }}</option>
                                                                @empty
                                                                    <option value="NIL">NIL</option>
                                                                @endforelse
                                                            </select>

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="hidden" value="{{ $ses }}" name="session">
                                            <button type="submit" class="btn btn-icon btn-primary"
                                                style="width: 100%">
                                                <i class="fa fa-registered" aria-hidden="true"></i> Register Elective
                                                Courses
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($sn1 > 1)
                <div class="row">
                    @if (session('student_session') == session('system_session') || strpos(session('faculty'), '.PG') !== false)
                        <div class="col-md-6">
                            <form action="create {{ $page }}" method="POST">
                                @csrf
                                <input type="hidden" value="new" name="register">
                                <button type="submit" class="btn btn-icon btn-primary" style="width: 100%">
                                    <i class="fa fa-registered" aria-hidden="true"></i> Regenerate Core Courses
                                </button>
                            </form>
                        </div>
                    @endif
                    <div class="col-md-6">
                        {{-- <a href="/get-registered-courses" class="btn btn-icon btn-secondary" style="width: 100%">
                            <i class="fas fa-print"></i> Print Courses
                        </a> --}}
                        <button href="#" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#deCourse"><i class="fas fa-print"></i>
                            {{ 'Print Courses' }}</button>
                    </div>


                    <div id="deCourse" class="modal fade" tabindex="-1" role="dialog"
                        aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myModalLabel">Print Course Registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="card">

                                    <form class="form-group" action="/get-registered-courses" method="GET"
                                        enctype="multipart/form-data">
                                        <div class="card-body">
                                            <!-- Details View Start -->
                                            @csrf
                                            <div class="form-group">
                                                <label for="session">Session</label>
                                                <select class="form-control" id="session" name="session" required>
                                                    <option value="">Select Option</option>
                                                    <option value="2024/2025">2024/2025</option>
                                                    <option value="2023/2024">2023/2024</option>
                                                    <option value="2022/2023">2022/2023</option>
                                                    <option value="2021/2022">2021/2022</option>
                                                    <option value="2020/2021">2020/2021</option>
                                                    <option value="2019/2020">2019/2020</option>
                                                    <option value="2018/2019">2018/2019</option>
                                                    <option value="2017/2018">2017/2018</option>
                                                </select>
                                            </div>
                                            <!-- Details View End -->
                                            <button type="button" class="btn btn-info"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Print</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Get where session('faculty') has .PG in the text --}}
                @if (session('student_session') == session('system_session') || strpos(session('faculty'), '.PG') !== false)
                    <div class="row">
                        <div class="col-md-12">
                            <form action="create {{ $page }}" method="POST">
                                @csrf
                                <input type="hidden" value="new" name="register">
                                <button type="submit" class="btn btn-icon btn-primary" style="width: 100%">
                                    <i class="fa fa-registered" aria-hidden="true"></i> Register Core Courses
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endif
            <!-- [ Main Content ] end -->
        @else
            @if (session('student_session') == session('system_session'))
                <div class="card">
                    <div class="card-body text-center">
                        <div class="alert alert-info">
                            Your Department Courses is not ready. Try Later.
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <div class="alert alert-info">
                            Course Registration is ONLY for Part one (100L) students.
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
<!-- End Content-->
