@php
    use Illuminate\Support\Facades\DB;
    if (isset($_GET['program'])) {
        $program = $_GET['program'];
        $sessions = $_GET['session'];
    } else {
        $program = null;
        $sessions = DB::table('session')->where('status', '1')->value('title');
    }
@endphp

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    @if (session('accType') == 'Admin' || session('appointment') == 'VC')
                        <div class="">
                            @foreach ($getDept as $item)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#{{ $item->department }}">{{ $item->depts->title }}</button>

                                <!-- Include jQuery -->

                                <!-- Modal Form -->
                                <div id="{{ $item->department }}" class="modal fade" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-md" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $item->depts->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <h6>Are You Sure, you want to take this action?</h6>
                                                </div>
                                                <form class="form-group ajax-form" action="approve-department-results"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="dept"
                                                        value="{{ $item->department }}">
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <select name="status" class="form-control" id="status"
                                                            required>
                                                            <option value="">Select Action</option>
                                                            <option value="Approved">Approved</option>
                                                            <option value="Rejected">Rejected</option>
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-info"
                                                        data-bs-dismiss="modal">No</button>
                                                    <button type="submit"
                                                        class="btn btn-primary process-btn">Process</button>
                                                </form>
                                                <div class="progress mt-3" style="display: none;">
                                                    <div class="progress-bar" role="progressbar" style="width: 0%;"
                                                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                {{-- <div id="{{ $item->department }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="myModalLabel">{{ $item -> depts -> title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h6>Are You Sure, you want to take this action.</h6>
                                            </div>
                                            <form class="form-group" action="approve-department-results" method="POST" enctype="multipart/form-data">
                                                <div class="card-body">
                                                    <!-- Details View Start -->
                                                    @csrf
                                                    <input type="hidden" name="dept" value="{{ $item->department }}">
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <select name="status" class="form-control" id="status" required>
                                                            <option value="">Select Action</option>
                                                            <option value="Approved">Approved</option>
                                                            <option value="Rejected">Rejected</option>
                                                        </select>
                                                    </div>
                                                    <!-- Details View End -->
                                                <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                                                <button type="submit" class="btn btn-primary">Process</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            @endforeach
                        </div>
                    @endif
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}</h5>
                    </div><div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">

                            @if (session('accType') == 'Admin' ||
                            session('appointment') == 'DEAN' ||
                            session('appointment') == 'VC' ||
                            session('appointment') == 'COURSE SYSTEM' ||
                            session('unit') == 'COURSE SYSTEM')

<div class="form-group col-md-2">
    <label for="facultyf">Faculty <span>*</span></label>
    <select class="form-control faculty" lang="f" name="faculty"
        id="facultyf">
        <option value="">Select Option</option>
        @if (session('appointment') == 'DEAN')
            @foreach ($faculty->where('code', session('faculty')) as $roww)
                <option value="{{ $roww->code }}">{{ $roww->title }} ({{ $roww->code }})</option>
            @endforeach
        @else
            @foreach ($faculty as $roww)
                <option value="{{ $roww->code }}">{{ $roww->title }} ({{ $roww->code }})</option>
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
    <label for="programf">Program</label>
    <select class="form-control program" id="programf" lang="f"
        name="program" required>
        <option value="{{ $program }}">Select Department First</option>
    </select>
    <div class="invalid-feedback"> You must select Program </div>
</div>


                            @endif


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

                            </div>
                        </form>
                    </div>
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
                                        <th>{{ 'code' }}</th>
                                        <th>{{ 'session' }}</th>
                                        <th>{{ 'semester' }}</th>
                                        <th>{{ 'Date' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                        $action = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            $approve = $row->approve;
                                            // $approve = DB::table('results')
                                            //     ->where(['code' => $row->code, 'session' => $sessions])
                                            //     ->value('approve');
                                            $comment = DB::table('results')
                                                ->where(['code' => $row->code, 'session' => $sessions])
                                                ->value('comment');
                                            if (
                                                $approve == 'system' &&
                                                strtoupper($row->lecturer) == strtoupper(session('username'))
                                            ) {
                                                $action = 1;
                                            } elseif ($approve == 'lecturer' && session('appointment') == 'HOD') {
                                                $action = 1;
                                            } elseif ($approve == 'hod' && session('appointment') == 'DEAN') {
                                                $action = 1;
                                            } elseif ($approve == 'dean' && session('appointment') == 'COURSE SYSTEM') {
                                                $action = 1;
                                            } elseif ($approve == 'cs' && session('appointment') == 'VC') {
                                                $action = 1;
                                            } else {
                                                $action = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ $row->session }}</td>
                                            <td>{{ $row->semester }}</td>
                                            <td>{{ $row->updated_at }}</td>
                                            <td>
                                                <a href="/print-result-pdf2/{{ $row->code }}/{{ $row->session }}/{{ $approve }}/{{ $row->semester }}"
                                                    type="submit" class="btn btn-info btn-icon btn-sm"><i
                                                        class="fas fa-print"></i></a>

                                                <a href="#" class="btn btn-icon btn-info btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#comment{{ $row->code }}">
                                                    <i class="fa fa-comment" aria-hidden="true"></i>
                                                </a>
                                                    @if(session('username') == 'su')
                                                    <a href="/pending-results?code={{ $row->code }}&session={{ $row->session }}&semester={{ $row->semester }}&approve={{ $approve }}"
                                                        type="submit" class="btn btn-info btn-sm"><i
                                                            class="fas fa-eye"></i> su</a>
                                                    @endif
                                                @if ($action == 1)
                                                    @if ($approve == 'vc')
                                                        <a href="/print-result-pdf/{{ $row->code }}/{{ $sessions }}"
                                                            type="submit" class="btn btn-info btn-sm"><i
                                                                class="fas fa-eye"></i> Approved</a>
                                                    @else
                                                        @if (strtoupper($row->lecturer) == strtoupper(session('username')))
                                                            <a href="#" class="btn btn-icon btn-primary btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#updateStudent{{ $sn }}">
                                                                <i class="far fa-plus"></i>
                                                            </a>
                                                        @endif
                                                        <a href="/pending-results?code={{ $row->code }}&session={{ $row->session }}&semester={{ $row->semester }}&approve={{ $approve }}"
                                                            type="submit" class="btn btn-info btn-sm"><i
                                                                class="fas fa-eye"></i> Preview</a>
                                                    @endif
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
                                        <div id="comment{{ $row->code }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Comment</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <strong>{{ $comment == '' ? 'No Comment' : $comment }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Show modal content -->
                                        @if (strtoupper(session('username')) == strtoupper($row->lecturer))
                                            <div id="updateStudent{{ $sn }}" class="modal fade"
                                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Add Mark</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <form class="form-group" action="/update-mark"
                                                                method="POST" enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="code"
                                                                        value="{{ $row->code }}">
                                                                    <input type="hidden" name="session"
                                                                        value="{{ $row->session }}">
                                                                    <input type="hidden" name="semester"
                                                                        value="{{ $row->semester }}">
                                                                    <div class="form-group">
                                                                        <label for="mark">Add Mark</label>
                                                                        <input type="number" name="mark"
                                                                            id="mark" class="form-control"
                                                                            required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="mark">Target Students</label>
                                                                        <div class="row">
                                                                            <div class="form-group col-md-6">
                                                                                <label for="mark">Min</label>
                                                                                <input type="number" name="min"
                                                                                    id="min"
                                                                                    class="form-control"
                                                                                    placeholder="Min Score" step="0.01" required>
                                                                            </div>
                                                                            <div class="form-group col-md-6">
                                                                                <label for="mark">Max</label>
                                                                                <input type="number" name="max"
                                                                                    id="max"
                                                                                    class="form-control"
                                                                                    placeholder="Max Score" step="0.01" required>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-success">Add
                                                                        Mark</button>
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


<script>
    $(document).ready(function() {
        $('.ajax-form').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();
            const progressBar = form.closest('.modal-content').find('.progress');
            const progressBarInner = progressBar.find('.progress-bar');

            // Show progress bar
            progressBar.show();

            // Start AJAX request
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'processing') {
                        // Update progress bar
                        progressBarInner.css('width', response.progress + '%').attr(
                            'aria-valuenow', response.progress).text(response.progress +
                            '%');

                        if (response.progress < 100) {
                            // Continue processing next batch
                            setTimeout(() => {
                                processNextBatch(response.nextBatchUrl);
                            }, 500); // Small delay between batches
                        } else {
                            // Processing complete
                            alert('Processing complete!');
                            location.reload(); // Refresh the page
                        }
                    } else if (response.status === 'error') {
                        alert('An error occurred: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while processing.');
                }
            });

            function processNextBatch(nextBatchUrl) {
                $.ajax({
                    url: nextBatchUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {

                        //alert(response.progress);
                        progressBarInner.css('width', response.progress + '%').attr(
                            'aria-valuenow', response.progress).text(response.progress +
                            '%');

                        if (response.progress < 100) {
                            setTimeout(() => {
                                processNextBatch(response.nextBatchUrl);
                            }, 500);
                        } else {
                            alert('Processing complete!');
                            location.reload();
                        }
                    },
                    error: function() {
                        alert('An error occurred while processing...');
                    }
                });
            }
        });
    });
</script>
