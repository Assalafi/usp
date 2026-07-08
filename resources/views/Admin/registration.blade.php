@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row alert alert-info text-center" style="font-size: 24px">
                            <div class="col-md-4">
                                Admitted: {{ $admitted }}
                            </div>
                            <div class="col-md-4">
                                Registered: {{ $admitted - $not_paid }}
                            </div>
                            <div class="col-md-4">
                                Yet to Register: {{ $not_paid }}
                            </div>
                        </div>
                    </div>
                    <div class="card-header">
                        <h5>Student List</h5>
                    </div>
                    <div class="card-block">
                        {{-- <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ ('Add New') }}</a> --}}
                        {{-- <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ 'Upload' }}</button> --}}
                        @if (strtoupper(session('username')) == 'SP11913' || strtoupper(session('username')) == 'SU')
                            <button href="#" class="btn btn-dark" data-bs-toggle="modal"
                                data-bs-target="#importPayment"><i class="fas fa-upload"></i>
                                {{ 'Upload Payment' }}</button>
                        @endif
                    </div>

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="facultyf">Faculty <span>*</span></label>
                                    <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
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
                                    <select class="form-control" id="programf" lang="f" name="program">
                                        <option value="">Select Department First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="session">Session</label>
                                    <select class="form-control" id="session" name="session_of_entry">
                                        <option value="{{ $sessions }}">Select Option</option>
                                        <option value="2024/2025">2024/2025</option>
                                        <option value="2023/2024">2023/2024</option>
                                        {{-- @foreach ($session as $ses)
                                            <option value="{{ $ses -> title }}">{{ $ses -> title }}</option>
                                        @endforeach --}}
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="level">Level</label>
                                    <select class="form-control" id="level" name="level_of_entry">
                                        <option value="">Select Option</option>
                                        @for ($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
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
                                        <th>{{ 'Student ID' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Faculty' }}</th>
                                        {{-- <th>{{ ('Department') }}</th> --}}
                                        <th>{{ 'Program' }}</th>
                                        <th>{{ 'Gender' }}</th>
                                        <th>{{ 'Mode of Entry' }}</th>
                                        <th>{{ 'Level of Level' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>
                                                <a href="#">
                                                    @if ($row->username)
                                                        {{ $row->username }}
                                                    @else
                                                        {{ $row->jamb_no }}
                                                    @endif

                                                </a>
                                            </td>
                                            <td>{{ $row->fullname }}</td>
                                            <td>{{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}
                                            </td>
                                            {{-- <td>{{ $row -> department }}</td> --}}
                                            <td>{{ DB::table('program')->where('code', $row->program)->value('title') }}
                                            </td>
                                            <td>

                                                @if ($row->gender == 'M')
                                                    {{ 'Male' }}
                                                @else
                                                    {{ 'Female' }}
                                                @endif
                                            </td>
                                            <td>{{ $row->mode_of_entry }}</td>
                                            <td>{{ $row->level_of_entry }}</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update Student</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="update-student" method="POST"
                                                            enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="page" value="new">
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <div class="form-group">
                                                                    <label for="jamb_no">Jamb NO</label>
                                                                    <input type="text" name="jamb_no"
                                                                        id="jamb_no" class="form-control"
                                                                        value="{{ $row->jamb_no }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="surname">Surname</label>
                                                                    <input type="text" name="surname"
                                                                        id="surname" class="form-control"
                                                                        value="{{ $row->last_name }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="first_name">First Name</label>
                                                                    <input type="text" name="first_name"
                                                                        id="first_name" class="form-control"
                                                                        value="{{ $row->first_name }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="middle_name">Other Name</label>
                                                                    <input type="text" name="middle_name"
                                                                        id="middle_name" class="form-control"
                                                                        value="{{ $row->other_name }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="gender">Gender</label>
                                                                    <select class="form-control" id="gender"
                                                                        name="gender" required>
                                                                        <option value="{{ $row->gender }}">Select
                                                                            Option</option>
                                                                        <option value="F">Female</option>
                                                                        <option value="M">Male</option>
                                                                    </select>
                                                                </div>
                                                                {{-- <div class="form-group">
                                                                <label for="gender">Gender</label>
                                                                <select class="form-control" id="gender" name="gender" required>
                                                                    <option value="{{ $row->gender }}">Select Option</option>
                                                                    <option value="F">Female</option>
                                                                    <option value="M">Male</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="faculty{{ ($row -> id) + 3 }}">Faculty</label>
                                                                <select class="form-control faculty" id="faculty{{ ($row -> id) + 3 }}" name="faculty" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> faculty }}">Select Option</option>
                                                                    @foreach ($faculty as $roww)
                                                                        <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="department{{ ($row -> id) + 3 }}">Department</label>
                                                                <select class="form-control department" id="department{{ ($row -> id) + 3 }}" lang="{{ ($row -> id) + 3 }}" name="department" required>
                                                                    <option value="{{ $row -> department }}">Select Faculty First</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="program">Program</label>
                                                                <select class="form-control" id="program{{ ($row -> id) + 3 }}" name="program" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> program }}">Select Department First</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="mode_of_entry">Mode of Entry</label>
                                                                <select class="form-control" id="mode_of_entry" name="mode_of_entry" required>
                                                                    <option value="{{ $row->mode_of_entry }}">Select Option</option>
                                                                    <option value="UTME">UTME</option>
                                                                    <option value="DE">DE</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="level_of_entry">Level of Entry</label>
                                                                <select class="form-control" id="level_of_entry" name="level_of_entry" required>
                                                                    <option value="{{ $row->level_of_entry }}">Select Option</option>
                                                                    <option value="100">100</option>
                                                                    <option value="200">200</option>
                                                                    <option value="300">300</option>
                                                                    <option value="400">400</option>
                                                                    <option value="500">500</option>
                                                                    <option value="600">600</option>
                                                                </select>
                                                            </div> --}}
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
<div id="importPayment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload Payments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <form class="form-group" action="upload-payment" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        {{-- Service --}}
                        <div class="form-group">
                            <label for="program1">Service</label>
                            <select class="form-control" name="service" required>
                                <option value="">Select Service</option>
                                <option value="school fees">School Fees</option>
                                <option value="hostel fees">Hostel Fees</option>
                            </select>
                        </div>
                        {{-- Type --}}
                        <div class="form-group">
                            <label for="program1">Type</label>
                            <select class="form-control" name="upload_type" required>
                                <option value="">Select Type</option>
                                <option value="full">Full Payment</option>
                                <option value="part">Part Payment </option>
                            </select>
                        </div>
                        {{-- Sponsor --}}
                        <div class="form-group">
                            <label for="sponsor">Sponsor</label>
                            <select class="form-control" name="sponsor" required>
                                <option value="">Select Sponsor</option>
                                <option value="self sponsor">Self Sponsor</option>
                                <option value="nelfund">NELFUND</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="session">Session</label>
                            <select class="form-control" name="session" required>
                                <option value="">Select Session</option>
                                <option value="2018/2019">2018/2019</option>
                                <option value="2019/2020">2019/2020</option>
                                <option value="2020/2021">2020/2021</option>
                                <option value="2021/2022">2021/2022</option>
                                <option value="2022/2023">2022/2023</option>
                                <option value="2023/2024">2023/2024</option>
                                <option value="2024/2025">2024/2025</option>
                                <option value="2025/2026">2025/2026</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control" required>
                        </div>
                        <br>

                        <div class="alert alert-warning" role="alert">
                            <strong>Warning!</strong> Make sure you have selected the correct faculty, department and
                            program. If you select the wrong one, the data will be uploaded to the wrong program. Please
                            make sure you have selected the correct one.
                        </div>
                        <br>
                        {{-- Add check box and warning the user about his selections --}}
                        <input type="checkbox" name="check" id="check" required>
                        <label for="check"> I have read the instructions and I am sure about my selection</label>

                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="{{ url('uploads/New Student Upload.xlsx') }}" download="New Student Upload.xlsx"><i
                            class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload-student" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="upload_type" value="new">
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
                            <select class="form-control" id="program1" name="program" lang="1" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        {{-- <input type="hidden" name="faculty" value="F">
                        <input type="hidden" name="department" value="F">
                        <input type="hidden" name="program" value="F"> --}}
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

<!-- Show modal content -->
<div id="createStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create-student" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="jamb_no">Jamb NO</label>
                            <input type="text" name="jamb_no" id="jamb_no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" name="surname" id="surname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Other Name</label>
                            <input type="text" name="middle_name" id="middle_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Option</option>
                                <option value="F">Female</option>
                                <option value="M">Male</option>
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
                        <div class="form-group">
                            <label for="mode_of_entry">Mode of Entry</label>
                            <select class="form-control" id="mode_of_entry" name="mode_of_entry" required>
                                <option value="">Select Option</option>
                                <option value="UTME">UTME</option>
                                <option value="DE">DE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level_of_entry">Level of Entry</label>
                            <select class="form-control" id="level_of_entry" name="level_of_entry" required>
                                <option value="">Select Option</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="300">300</option>
                                <option value="400">400</option>
                                <option value="500">500</option>
                                <option value="600">600</option>
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
