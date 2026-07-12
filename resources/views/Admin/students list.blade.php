@php
    use Illuminate\Support\Facades\DB;
    $lga = DB::table('students')->distinct()->orderBy('lga_origin', 'ASC')->get('lga_origin');
    $state = DB::table('students')->distinct()->orderBy('state_origin', 'ASC')->get('state_origin');
@endphp

<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ $page }}</h5>
                    </div>
                    <div class="card-block">

                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                        <button href="#" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#exportStudent"><i class="fas fa-download"></i> {{ 'Export' }}</button>

                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2 facultyAction">
                                    <label for="facultyf">Faculty <span>*</span></label>
                                    <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
                                        <option value="">Select Option</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww->code }}">{{ $roww->code }}:
                                                {{ $roww->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 departmentAction">
                                    <label for="departmentf">Department</label>
                                    <select class="form-control department" lang="f" id="departmentf"
                                        name="department">
                                        <option value="">Select Faculty First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 programAction">
                                    <label for="programf">Program</label>
                                    <select class="form-control" id="programf" lang="f" name="program">
                                        <option value="">Select Department First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-1 levelAction">
                                    <label for="level">Level</label>
                                    <select class="form-control" id="level" name="level">
                                        <option value="">Select Option</option>
                                        @for ($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-1 sessionAction">
                                    <label for="session">Session</label>
                                    <select class="form-control" id="session" name="session_of_entry">
                                        <option value="">Select Option</option>
                                        @foreach ($sessions as $ses)
                                            <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-1 genderAction">
                                    <label for="gender">Gender</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Select Option</option>
                                        <option value="M">MALE</option>
                                        <option value="F">FEMALE</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 maritalAction">
                                    <label for="gender">Marital Status</label>
                                    <select class="form-control" id="marital_status" name="marital_status">
                                        <option value="">Select Option</option>
                                        <option value="SINGLE">SINGLE</option>
                                        <option value="MARRIED">MARRIED</option>
                                        <option value="DIVORCED">DIVORCED</option>
                                        <option value="WIDOWED">WIDOWED</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 stateAction">
                                    <label for="state_origin">State</label>
                                    <select class="form-control" id="state_origin" name="state_origin">
                                        <option value="">Select Option</option>
                                        @foreach ($state as $stateee)
                                            <option value="{{ $stateee->state_origin }}">
                                                {{ $stateee->state_origin }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 lgaAction">
                                    <label for="lga_origin">LGA</label>
                                    <select class="form-control" id="lga_origin" name="lga_origin">
                                        <option value="">Select Option</option>
                                        @foreach ($lga as $statess)
                                            <option value="{{ $statess->lga_origin }}">{{ $statess->lga_origin }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 phoneAction">
                                    <label for="contact_phone">Phone Number</label>
                                    <input type="number" name="contact_phone" id="contact_phone" class="form-control">
                                </div>
                                <div class="form-group col-md-2 jambAction">
                                    <label for="jamb_no">JAMB Number</label>
                                    <input type="text" name="jamb_no" id="jamb_no" class="form-control">
                                </div>
                                <div class="form-group col-md-2 idAction">
                                    <label for="username">ID Number</label>
                                    <input type="text" name="username" id="username" class="form-control">
                                </div>
                                <div class="form-group col-md-1">
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
                                        <th>{{ 'Student ID' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Faculty' }}</th>
                                        {{-- <th>{{ ('Department') }}</th> --}}
                                        <th>{{ 'Program' }}</th>
                                        <th>{{ 'Level' }}</th>
                                        <th>{{ 'Gender' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = $data->firstItem() ?? 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            $facultyRow = $faculty->firstWhere('code', $row->faculty);
                                            $facultyTitle = $facultyRow ? $facultyRow->title : 'N/A';
                                            $departmentTitle = DB::table('department')->where('code', $row->department)->value('title') ?? 'N/A';
                                            $programTitle = DB::table('program')->where('code', $row->program)->value('title') ?? 'N/A';
                                        @endphp
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
                                            <td>{{ $facultyTitle }}
                                            </td>
                                            {{-- <td>{{ $row -> department }}</td> --}}
                                            <td>{{ $programTitle }}
                                            </td>
                                            <td>{{ $row->level }}</td>
                                            <td>

                                                @if ($row->gender == 'M')
                                                    {{ 'Male' }}
                                                @else
                                                    {{ 'Female' }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="/student details/{{ $row->id }}"
                                                    class="btn btn-icon btn-success btn-sm viewAction">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <a href="#" class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </a>

                                                <a href="/id card/{{ $row->id }}"
                                                    class="btn btn-icon btn-warning btn-sm idcardAction">
                                                    <i class="fas fa-address-card"></i>
                                                </a>

                                                <a href="/student details pdf/{{ $row->id }}"
                                                    class="btn btn-icon btn-secondary btn-sm getpdfAction">
                                                    <i class="fas fa-print"></i>
                                                </a>

                                                <button class="btn btn-icon btn-info btn-sm passwordAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updatePassword{{ $row->id }}">
                                                    <i class="fas fa-key"></i>
                                                </button>

                                                <button class="btn btn-icon btn-light btn-sm passwordAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#electionStudent{{ $row->id }}">
                                                    <i
                                                        class="fas fa-thumbs-{{ $row->vflag == '0' ? 'up' : 'down' }}"></i>
                                                </button>

                                                <button class="btn btn-icon btn-warning btn-sm statusAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#status{{ $row->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#repeatStudent{{ $row->id }}">
                                                    <i class="fas fa-repeat"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudent{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update Student</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="update-student"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="page" value="return">
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                {{-- <div class="form-group">
                                                                <label for="jamb_no">Jamb</label>
                                                                <input type="text" name="jamb_no" id="jamb_no" class="form-control" value="{{ $row->jamb_no }}">
                                                            </div> --}}
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
                                                                {{-- Phone (contact_phone) --}}
                                                                <div class="form-group">
                                                                    <label for="contact_phone">Phone</label>
                                                                    <input type="number" name="contact_phone"
                                                                        id="contact_phone" class="form-control"
                                                                        value="{{ $row->contact_phone }}">
                                                                </div>
                                                                {{-- Next of kin (kin_name) --}}
                                                                <div class="form-group">
                                                                    <label for="kin_name">Naxt of Kin Name</label>
                                                                    <input type="text" name="kin_name"
                                                                        id="kin_name" class="form-control"
                                                                        value="{{ $row->kin_name }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="kin_phone">Naxt of Kin Phone</label>
                                                                    <input type="number" name="kin_phone"
                                                                        id="kin_phone" class="form-control"
                                                                        value="{{ $row->kin_phone }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="level">Current Level</label>
                                                                    <select class="form-control" id="level"
                                                                        name="level" required>
                                                                        <option value="{{ $row->level }}">Selected:
                                                                            {{ $row->level }}</option>
                                                                        <option value="100">100</option>
                                                                        <option value="200">200</option>
                                                                        <option value="300">300</option>
                                                                        <option value="400">400</option>
                                                                        <option value="500">500</option>
                                                                        <option value="600">600</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="level_of_entry">Entry Level</label>
                                                                    <select class="form-control" id="level_of_entry"
                                                                        name="level_of_entry" required>
                                                                        <option value="{{ $row->level_of_entry }}">
                                                                            Selected: {{ $row->level_of_entry }}
                                                                        </option>
                                                                        <option value="100">100</option>
                                                                        <option value="200">200</option>
                                                                        <option value="300">300</option>
                                                                        <option value="400">400</option>
                                                                        <option value="500">500</option>
                                                                        <option value="600">600</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="mode_of_entry">Mode of Entry</label>
                                                                    <select class="form-control" id="mode_of_entry"
                                                                        name="mode_of_entry" required>
                                                                        <option value="{{ $row->mode_of_entry }}">
                                                                            Selected: {{ $row->mode_of_entry }}
                                                                        </option>
                                                                        <option value="UTME">UTME</option>
                                                                        <option value="DE">DE</option>
                                                                        <option value="TRANSFER">TRANSFER</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="country">Country</label>
                                                                    <input type="text" class="form-control" id="country"
                                                                        name="country" placeholder="Country"
                                                                        value="{{ $row->country }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="state_origin">State of Origin</label>
                                                                    <input type="text" class="form-control" id="state_origin"
                                                                        name="state_origin" placeholder="State of Origin"
                                                                        value="{{ $row->state_origin }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="lga_origin">LGA of Origin</label>
                                                                    <input type="text" class="form-control" id="lga_origin"
                                                                        name="lga_origin" placeholder="LGA of Origin"
                                                                        value="{{ $row->lga_origin }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="facultyp{{ $row->id }}">Faculty</label>
                                                                    <select class="form-control faculty" id="facultyp{{ $row->id }}" name="faculty" lang="p{{ $row->id }}" required>
                                                                        <option value="">Select Faculty</option>
                                                                        @foreach ($faculty as $fac)
                                                                            <option value="{{ $fac->code }}" {{ $fac->code == $row->faculty ? 'selected' : '' }}>{{ $fac->code }}: {{ $fac->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="departmentp{{ $row->id }}">Department</label>
                                                                    <select class="form-control department" id="departmentp{{ $row->id }}" name="department" lang="p{{ $row->id }}" required>
                                                                        <option value="">Select Department</option>
                                                                        @if ($row->department)
                                                                            <option value="{{ $row->department }}" selected>{{ $row->department }}: {{ $departmentTitle }}</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="programp{{ $row->id }}">Program</label>
                                                                    <select class="form-control program" id="programp{{ $row->id }}" name="program" lang="p{{ $row->id }}" required>
                                                                        <option value="">Select Program</option>
                                                                        @if ($row->program)
                                                                            <option value="{{ $row->program }}" selected>{{ $row->program }}: {{ $programTitle }}</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="issue_date">ID Card Issue Date</label>
                                                                    <input type="date" class="form-control" id="issue_date"
                                                                        name="issue_date" placeholder="ID Card Issue Date"
                                                                        value="{{ $row->issue_date }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="expire_date">ID Card Expire Date</label>
                                                                    <input type="date" class="form-control" id="expire_date"
                                                                        name="expire_date" placeholder="ID Card Expire Date"
                                                                        value="{{ $row->expire_date }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="passport_pic">Passport</label>
                                                                    <input type="file" name="passport_pic"
                                                                        id="passport_pic" class="form-control">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="passport_sign">Signature</label>
                                                                    <input type="file" name="passport_sign"
                                                                        id="passport_sign" class="form-control">
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

                                        <div id="updatePassword{{ $row->id }}" class="modal fade"
                                            tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Change Password</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="/update password"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <div class="form-group">
                                                                    <label for="p1">New Password</label>
                                                                    <input type="text" name="p1"
                                                                        id="p1" class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="p2">Comfirm Password</label>
                                                                    <input type="text" name="p2"
                                                                        id="p2" class="form-control" required>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Change</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="electionStudent{{ $row->id }}" class="modal fade"
                                            tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            COMFIRM ELECTION ACTION
                                                        </div>
                                                        <form class="form-group" action="election-student"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <input type="hidden" name="vflag"
                                                                    value="{{ $row->vflag == '0' ? '1' : '0' }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-danger">Yes,
                                                                    Confirm</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="repeatStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            COMFIRM RESET ACTION
                                                        </div>
                                                        <form class="form-group" action="reset-student"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Reset</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="deleteStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            COMFIRM DELETE ACTION
                                                        </div>
                                                        <form class="form-group" action="delete-student"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Delete</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="status{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="suspension" method="POST"
                                                            enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <div class="form-group">
                                                                    <label for="school_status">Status</label>
                                                                    <select class="form-control" id="school_status"
                                                                        name="school_status">
                                                                        <option value="">Select Option</option>
                                                                        <option value="SUSPENSION">SUSPENSION</option>
                                                                        <option value="EXPEL">EXPEL</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="">If status is Suspension
                                                                        select Semester.</label>
                                                                    <label
                                                                        for="school_status_semester">Semester</label>
                                                                    <select class="form-control"
                                                                        id="school_status_semester"
                                                                        name="school_status_semester">
                                                                        <option value="1">Select Option</option>
                                                                        <option value="1">1 Semester</option>
                                                                        <option value="2">2 Semesters</option>
                                                                    </select>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="button"
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

                        <!-- Pagination and Results Info -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }}
                                        of {{ $data->total() ?? 0 }} results
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $data->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<!-- Show modal content -->
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
                        <input type="hidden" name="upload_type" value="old">
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}: {{ $row->title }}
                                    </option>
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
<div id="exportStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <div class="card-block">
                    <form class="needs-validation" novalidate method="GET" action="/export-users">
                        @csrf
                        <div class="row gx-2">
                            <div class="form-group col-md-12 facultyAction">
                                <label for="facultyff">Faculty <span>*</span></label>
                                <select class="form-control faculty" lang="ff" name="faculty" id="facultyff">
                                    <option value="">Select Option</option>
                                    @foreach ($faculty as $roww)
                                        <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12 departmentAction">
                                <label for="departmentff">Department</label>
                                <select class="form-control department" lang="ff" id="departmentff"
                                    name="department">
                                    <option value="">Select Faculty First</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 programAction">
                                <label for="programff">Program</label>
                                <select class="form-control" id="programff" lang="ff" name="program">
                                    <option value="">Select Department First</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 levelAction">
                                <label for="level">Level</label>
                                <select class="form-control" id="level" name="level">
                                    <option value="">Select Option</option>
                                    @for ($i = 1; $i <= 7; $i++)
                                        <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group col-md-12 sessionAction">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session_of_entry">
                                    <option value="">Select Option</option>
                                    @foreach ($sessions as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12 genderAction">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Option</option>
                                    <option value="M">MALE</option>
                                    <option value="F">FEMALE</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 maritalAction">
                                <label for="gender">Marital Status</label>
                                <select class="form-control" id="marital_status" name="marital_status">
                                    <option value="">Select Option</option>
                                    <option value="SINGLE">SINGLE</option>
                                    <option value="MARRIED">MARRIED</option>
                                    <option value="DIVORCED">DIVORCED</option>
                                    <option value="WIDOWED">WIDOWED</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 stateAction">
                                <label for="state_origin">State</label>
                                <select class="form-control" id="state_origin" name="state_origin">
                                    <option value="">Select Option</option>
                                    @foreach ($state as $states)
                                        <option value="{{ $states->state_origin }}">{{ $states->state_origin }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12 lgaAction">
                                <label for="lga_origin">LGA</label>
                                <select class="form-control" id="lga_origin" name="lga_origin">
                                    <option value="">Select Option</option>
                                    @foreach ($lga as $statee)
                                        <option value="{{ $statee->lga_origin }}">{{ $statee->lga_origin }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12 phoneAction">
                                <label for="contact_phone">Phone Number</label>
                                <input type="number" name="contact_phone" id="contact_phone" class="form-control">
                            </div>
                            <div class="form-group col-md-12 jambAction">
                                <label for="jamb_no">JAMB Number</label>
                                <input type="text" name="jamb_no" id="jamb_no" class="form-control">
                            </div>
                            <div class="form-group col-md-12 idAction">
                                <label for="username">ID Number</label>
                                <input type="text" name="username" id="username" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <button type="submit" class="btn btn-info btn-filter"><i
                                        class="fas fa-download"></i> {{ 'Export' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
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
                            <label for="program">Program</label>
                            <select class="form-control" id="program" name="program" required>
                                <option value="">Select Option</option>
                                <option value="Computer Engineering">Computer Engineering</option>
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
