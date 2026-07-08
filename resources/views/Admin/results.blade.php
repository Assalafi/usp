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
                    <div class="card-block">
                        {{-- <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> {{ ('Add New') }}</a> --}}

                        <a href="{{ url('uploads/result template.xlsx') }}" class="btn btn-primary"
                            download="result template.xlsx"><i class="fas fa-download"></i> Download Template</a>
                        @if (session('accType') == 'Staff')
                            <button href="#" class="btn btn-dark" data-bs-toggle="modal"
                                data-bs-target="#import"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                            <button href="#" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#exportStudent"><i class="fas fa-download"></i>
                                {{ 'Registered Students' }}</button>
                        @else
                            <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                                data-bs-target="#import"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                            <button href="#" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#exportStudent"><i class="fas fa-download"></i>
                                {{ 'Registered Students' }}</button>
                        @endif

                        <button href="#" class="btn btn-danger" style="float: right" data-bs-toggle="modal"
                            data-bs-target="#deleteResults"><i class="fas fa-trash"></i> {{ 'Delete Result' }}</button>
                    </div>
                    @if (session('accType') == 'Staff')

                        <div class="card-block">
                            <form class="needs-validation" novalidate method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-3">
                                        <label for="coursef">Course</label>
                                        <select class="form-control" id="coursef" lang="f" name="code"
                                            required>
                                            <option value="">Select Program First</option>
                                            @foreach ($lecturerCourses as $row)
                                                <option value="{{ $row->course }}">{{ $row->course }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"> You must select Course </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session" required>
                                            <option value="{{ $sessions }}">Select Option</option>
                                            @foreach ($session as $ses)
                                                <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-info btn-filter"><i
                                                class="fas fa-search"></i> {{ 'Filter' }}</button>
                                    </div>
                                    @if (isset($_GET['code']))
                                        <div class="form-group col-md-3">
                                            <a href="/print-result-pdf/{{ $_GET['code'] }}/{{ $_GET['session'] }}"
                                                type="submit" class="btn btn-info btn-filter"><i
                                                    class="fas fa-print"></i> Preview</a>
                                        </div>
                                    @endif

                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-block">
                            <form class="needs-validation" novalidate method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-2">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            <option value="">Select Option</option>
                                            @foreach ($faculty as $roww)
                                                <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                            @endforeach
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
                                        <label for="programf">Program</label>
                                        <select class="form-control program" id="programf" lang="f"
                                            name="program">
                                            <option value="">Select Department First</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="coursef">Course</label>
                                        <select class="form-control" id="coursef" lang="f" name="code"
                                            required>
                                            <option value="">Select Program First</option>
                                        </select>
                                        <div class="invalid-feedback"> You must select Course </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session" required>
                                            <option value="{{ $sessions }}">Select Option</option>
                                            @foreach ($session as $ses)
                                                <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <button type="submit" class="btn btn-info btn-filter"><i
                                                class="fas fa-search"></i> {{ 'Filter' }}</button>
                                    </div>
                                    @if (isset($_GET['code']))
                                        <div class="form-group col-md-2">
                                            <a href="/print-result-pdf/{{ $_GET['code'] }}/{{ $_GET['session'] }}"
                                                type="submit" class="btn btn-info btn-filter"><i
                                                    class="fas fa-print"></i> Preview</a>
                                        </div>
                                    @endif

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
                                        <th>#</th>
                                        <th>{{ 'ID' }}</th>
                                        <th>{{ 'CODE' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'EXAM' }}</th>
                                        <th>{{ 'TOTAL' }}</th>
                                        <th>{{ 'GRADE' }}</th>
                                        <th>{{ 'SESSION' }}</th>
                                        <th>{{ 'SEMESTER' }}</th>
                                        <th>{{ 'ACTION' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ $row->ca }}</td>
                                            <td>{{ $row->exam }}</td>
                                            <td>{{ $row->total }}</td>
                                            <td>{{ $row->grade }}</td>
                                            <td>{{ $row->session }}</td>
                                            <td>{{ $row->semester }}</td>
                                            <td>
                                                {{-- @if ((session('appointment') == 'HOD' || session('accType') == 'Admin') && $row->corrigenda == 0)
                                                    <a href="#" class="btn btn-icon btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#corrigenda{{ $row->id }}">
                                                        <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                                                    </a>
                                                @endif --}}
                                                @if ((session('faculty') == 'MS' && $row->lecturer == session('username')) || session('accType') == 'Admin')
                                                    <a href="#" class="btn btn-icon btn-warning btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#med{{ $row->id }}">
                                                        <i class="fa fa-file-excel" aria-hidden="true"></i>
                                                    </a>
                                                @endif

                                                @if ($row->approve == 'system')
                                                    <a href="#" class="btn btn-icon btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $row->id }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>

                                                    <button type="button" class="btn btn-icon btn-danger btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete{{ $row->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
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
                                                        <form class="form-group" action="update {{ $page }}"
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
                                                                        value="{{ $row->ca }}" id="ca"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="exam">EXAM</label>
                                                                    <input type="text" name="exam"
                                                                        value="{{ $row->exam }}" id="exam"
                                                                        class="form-control" required>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Update</button>
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
                                        <div id="corrigenda{{ $row->id }}" class="modal fade" tabindex="-1"
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
                                                            <h4>Are You Sure, you want to initiate corrigendum on
                                                                {{ $row->code }} for {{ $row->username }}</h4>
                                                        </div>
                                                        <form class="form-group" action="initiate corrigenda"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <div class="form-group">
                                                                    <input type="checkbox" required />
                                                                    <label for="code">Yes, initiate</label>
                                                                </div>
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
                                        <div id="med{{ $row->id }}" class="modal fade" tabindex="-1"
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
                                                            <h4>Are You Sure, you want to apply failed clinical on
                                                                {{ $row->code }} for {{ $row->username }}</h4>
                                                        </div>
                                                        <form class="form-group" action="/update-med" method="POST"
                                                            enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <div class="form-group">
                                                                    <input type="checkbox" required />
                                                                    <label for="code">Yes, apply</label>
                                                                </div>
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
                                    <option value="">Select Course</option>
                                    @foreach ($lecturerCourses as $row)
                                        <option value="{{ $row->course }}">{{ $row->course }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="{{ $sessions }}">Select Option</option>
                                    @foreach ($session as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="semester">Semester<span>*</span></label>
                                <select name="semester" class="form-control" id="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="FIRST">FIRST</option>
                                    <option value="SECOND">SECOND</option>
                                    <option value="THIRD">THIRD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="per">Scoring Type</label>
                                <select class="form-control" id="per" name="per" required>
                                    <option value="">Select Option</option>
                                    <option value="70">30/70</option>
                                    <option value="30">70/30</option>
                                    <option value="60">40/60</option>
                                    <option value="40">60/40</option>
                                    <option value="100">0/100</option>
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
                            {{-- session --}}
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="">Select Option</option>
                                    <option value="2017/2018">2017/2018</option>
                                    <option value="2018/2019">2018/2019</option>
                                    <option value="2019/2020">2019/2020</option>
                                    <option value="2020/2021">2020/2021</option>
                                    <option value="2021/2022">2021/2022</option>
                                    <option value="2022/2023">2022/2023</option>
                                    <option value="2023/2024">2023/2024</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                    <option value="2027/2028">2027/2028</option>
                                    <option value="2028/2029">2028/2029</option>
                                    <option value="2029/2030">2029/2030</option>
                                    <option value="2030/2031">2030/2031</option>
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
                <div class="card" id="uploadBeforeProgress" style="display: block;">
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
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="{{ $sessions }}">Select Option</option>
                                    @foreach ($session as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="semester">Semester<span>*</span></label>
                                <select name="semester" class="form-control" id="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="FIRST">FIRST</option>
                                    <option value="SECOND">SECOND</option>
                                    <option value="THIRD">THIRD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="per">Scoring Type</label>
                                <select class="form-control" id="per" name="per" required>
                                    <option value="">Select Option</option>
                                    <option value="70">30/70</option>
                                    <option value="30">70/30</option>
                                    <option value="60">40/60</option>
                                    <option value="40">60/40</option>
                                    <option value="100">0/100</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="file"></label>
                                <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                    class="form-control">
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" onclick="updateProgress()" class="btn btn-success">Upload</button>
                        </div>
                    </form>
                </div>

                <div id="progress-container"
                    style="width: 100%; background: #f3f3f3; border: 1px solid #ccc; padding: 5px; display: none;">
                    <div id="progress-bar"
                        style="width: 0%; background: #4caf50; height: 20px; text-align: center; color: white;">
                        0%
                    </div>
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
                            {{-- session --}}
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session" required>
                                    <option value="">Select Option</option>
                                    <option value="2017/2018">2017/2018</option>
                                    <option value="2018/2019">2018/2019</option>
                                    <option value="2019/2020">2019/2020</option>
                                    <option value="2020/2021">2020/2021</option>
                                    <option value="2021/2022">2021/2022</option>
                                    <option value="2022/2023">2022/2023</option>
                                    <option value="2023/2024">2023/2024</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                    <option value="2027/2028">2027/2028</option>
                                    <option value="2028/2029">2028/2029</option>
                                    <option value="2029/2030">2029/2030</option>
                                    <option value="2030/2031">2030/2031</option>
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

<div id="deleteResults" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Delete Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="delete-results" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="course1">Courses*</label>
                            <select class="form-control" id="code" name="code" lang="1" required>
                                <option value="">Select Program First</option>
                                @foreach ($myUploads as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="semester">Semester<span>*</span></label>
                            <select name="semester" class="form-control" id="semester" required>
                                <option value="">Select Semester</option>
                                <option value="FIRST">FIRST</option>
                                <option value="SECOND">SECOND</option>
                                <option value="THIRD">THIRD</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="session">Session*</label>
                            <select class="form-control" id="session" name="session" required>
                                <option value="">Select Option</option>
                                @foreach ($session as $ses)
                                    <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Delete</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function updateProgress() {
        document.getElementById('uploadBeforeProgress').style.display = 'none';
        document.getElementById('progress-bar').style.display = 'block';

        fetch('/import-progress')
            .then(response => response.json())
            .then(data => {
                const progressBar = document.getElementById('progress-bar');
                progressBar.style.width = `${data.progress}%`;
                progressBar.textContent = `${data.progress}%`;

                // Stop polling if progress reaches 100%
                if (data.progress < 100) {
                    setTimeout(updateProgress, 1000); // Poll every second
                } else {
                    alert("Import completed successfully!");
                }
            })
            .catch(error => console.error('Error fetching progress:', error));
    }
</script>
