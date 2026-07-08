@php
    use Illuminate\Support\Facades\DB;
    if (isset($_GET['faculty'])) {
        $data = DB::table('department')
            ->where('faculty', $_GET['faculty'])
            ->get();
        $facultyy = DB::table('faculty')->where('code', $_GET['faculty'])->select('title')->value('title');
        $sessions = $_GET['session'];
        $faculty = $faculty->where('code', session('faculty'));
        if(session('appointment') == 'HOD'){
            $data = DB::table('department')
            ->where('code', session('department'))
            ->get();
        }

    } else {
        $data = [];
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
                        <h5>DISTRIBUTION OF {{ strtoupper($page) }}</h5>
                    </div>
                    @if (session('accType') == 'Admin' ||
                            session('appointment') == 'DEAN' ||
                            session('appointment') == 'HOD' ||
                            session('appointment') == 'VC' ||
                            session('unit') == 'COURSE SYSTEM')
                        <div class="card-block">
                            <form class="needs-validation" method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-3">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            <option value="{{ isset($_GET['faculty']) ? $_GET['session'] : '' }}">{{ isset($_GET['faculty']) ? $facultyy : 'Select Option' }}</option>
                                            @if (session('appointment') == 'DEAN' || session('appointment') == 'HOD')
                                                @foreach ($faculty->where('code', session('faculty')); as $roww)
                                                    <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                                @endforeach
                                            @else
                                                @foreach ($faculty as $roww)
                                                    <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session" required>
                                            <option value="{{ isset($_GET['session']) ? $_GET['session'] : '' }}">{{ isset($_GET['session']) ? $_GET['session'] : 'Select Option' }}</option>
                                            @foreach ($session as $ses)
                                                <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- For Semester FIRST and SECOND, is static --}}
                                    <div class="form-group col-md-3">
                                        <label for="semester">Semester</label>
                                        <select class="form-control" id="semester" name="semester" required>
                                            <option value="{{ isset($_GET['semester']) ? $_GET['semester'] : '' }}">{{ isset($_GET['semester']) ? $_GET['semester'] : 'Select Option' }}</option>
                                            <option value="FIRST">FIRST</option>
                                            <option value="SECOND">SECOND</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-info btn-filter"><i
                                                class="fas fa-search"></i> {{ 'Filter' }}</button>
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
                                        <th>#</th>
                                        <th>{{ 'Department' }}</th>
                                        <th>{{ 'session' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                        $action = 0;
                                    @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->title }}</td>
                                            <td>{{ $_GET['session'] }}</td>
                                            <td>
                                                <a href="/grades-by-courses/{{ $row->code }}/{{ $_GET['session'] }}/{{ $_GET['semester'] }}"
                                                    type="submit" class="btn btn-info btn-icon btn-sm"><i
                                                        class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
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
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
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
