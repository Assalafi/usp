@php
    use Illuminate\Support\Facades\DB;
    if (isset($_GET['faculty'])) {
        $facultyy = DB::table('faculty')
            ->where('code', $_GET['faculty'])
            ->select('title')
            ->value('title');
        $sessions = $_GET['session'];
    }
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}</h5>
                    </div>
                    @if (session('accType') == 'Admin' ||
                            session('appointment') == 'DEAN' ||
                            session('appointment') == 'VC' ||
                            session('unit') == 'COURSE SYSTEM')
                        <div class="card-block">
                            <form class="needs-validation" method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-4">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            <option value="{{ isset($_GET['faculty']) ? $_GET['session'] : '' }}">
                                                {{ isset($_GET['faculty']) ? $facultyy : 'Select Option' }}</option>
                                            @if (session('appointment') == 'DEAN')
                                                @foreach ($faculty->where('code', session('faculty')) as $roww)
                                                    <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                                @endforeach
                                            @else
                                                @foreach ($faculty as $roww)
                                                    <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="departmentf">Department</label>
                                        <select class="form-control department" lang="f" id="departmentf"
                                            name="department">
                                            <option value="">Select Faculty First</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session" required>
                                            <option value="{{ isset($_GET['session']) ? $_GET['session'] : '' }}">
                                                {{ isset($_GET['session']) ? $_GET['session'] : 'Select Option' }}
                                            </option>
                                            @foreach ($session as $ses)
                                                <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <button type="submit" class="btn btn-info btn-filter"><i
                                                class="fas fa-search"></i> {{ 'Filter' }}</button>
                                    </div>
                                    <div class="form-group col-md-2">
                                        @isset($_GET['department'])
                                            <label for="">.</label><br>
                                            <a href="/corrigenda-pdf/{{ $_GET['department'] }}/{{ $_GET['session'] }}"
                                                type="submit" class="btn btn-info btn-sm"><i class="fas fa-eye"></i>
                                                Preview</a>
                                        @endisset
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="background-color: rgb(17, 19, 27)" colspan="5"
                                            class="text-center">WRONG RESULT</th>
                                        <th style="background-color: rgb(17, 19, 27)" colspan="3"
                                            class="text-center">CORRECT RESULT</th>
                                    </tr>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'ID' }}</th>
                                        <th>{{ 'CODE' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'EXAM' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'EXAM' }}</th>
                                        <th>{{ 'ACTION' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            $approve = $row->approve;
                                            if ($approve == 'system' && $row->lecturer == session('username')) {
                                                $action = 1;
                                            } elseif ($approve == 'lecturer' && session('appointment') == 'HOD') {
                                                $action = 1;
                                            } elseif ($approve == 'hod' && session('appointment') == 'DEAN') {
                                                $action = 1;
                                            } elseif ($approve == 'dean' && session('unit') == 'COURSE SYSTEM') {
                                                $action = 1;
                                            } elseif ($approve == 'cs' && session('appointment') == 'VC') {
                                                $action = 1;
                                            } else {
                                                $action = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ $row->old_ca }}</td>
                                            <td>{{ $row->old_exam }}</td>
                                            <td>{{ $row->ca }}</td>
                                            <td>{{ $row->exam }}</td>
                                            <td>
                                                @if ($action == 1)
                                                    <a href="#" class="btn btn-icon btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $row->id }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                @else
                                                    @php
                                                        if ($approve == 'system') {
                                                            echo 'Lecturer';
                                                        } elseif ($approve == 'lecturer') {
                                                            echo 'Department';
                                                        } elseif ($approve == 'hod') {
                                                            echo 'Faculty';
                                                        } elseif ($approve == 'dean') {
                                                            echo 'Course System';
                                                        } elseif ($approve == 'cs') {
                                                            echo 'Senate';
                                                        } elseif ($approve == 'vc') {
                                                            echo 'Approved';
                                                        }
                                                    @endphp
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        @if ($approve == 'system')
                                            <div id="updateStudent{{ $row->id }}" class="modal fade"
                                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <p>Initial CA: {{ $row->old_ca }}</p>
                                                                <p>Initial Exam: {{ $row->old_exam }}</p>
                                                            </div>

                                                            <form class="form-group" action="update corrigenda"
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
                                                                            value="" id="ca"
                                                                            class="form-control" placeholder="New CA"
                                                                            required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="exam">EXAM</label>
                                                                        <input type="text" name="exam"
                                                                            id="exam" class="form-control"
                                                                            placeholder="New Exam" required>
                                                                    </div>
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-success">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div id="updateStudent{{ $row->id }}" class="modal fade"
                                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Submission</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <h4>Are you sure?</h4>
                                                            </div>
                                                            <form class="form-group text-center"
                                                                action="update corrigenda" method="POST"
                                                                enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $row->id }}">
                                                                    <input type="hidden" name="code"
                                                                        value="{{ $row->code }}">
                                                                    <input type="hidden" name="username"
                                                                        value="{{ $row->username }}">
                                                                    <input type="hidden" name="result_id"
                                                                        value="{{ $row->result_id }}">
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-success">Yes, Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
                                                        <div class="card-body">
                                                            <p>Initial CA: {{ $row->old_ca }}</p>
                                                            <p>Initial Exam: {{ $row->old_exam }}</p>
                                                        </div>

                                                        <form class="form-group" action="update corrigenda"
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
                                                                        value="" id="ca"
                                                                        class="form-control" placeholder="New CA"
                                                                        required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="exam">EXAM</label>
                                                                    <input type="text" name="exam"
                                                                        id="exam" class="form-control"
                                                                        placeholder="New Exam" required>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Submit</button>
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
                                    @endforeach
                                </tbody>
                            </table>
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
                                    <option value="">Select Program First</option>
                                    @foreach ($lecturerCourses as $row)
                                        <option value="{{ $row->course }}">{{ $row->course }}</option>
                                    @endforeach
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
                                <label for="file"></label>
                                <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                    class="form-control">
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Assign</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
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
                                <option value="First">First Semester</option>
                                <option value="Second">Second Semester</option>
                                <option value="Third">Third Semester</option>
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
