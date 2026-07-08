@php
    use Illuminate\Support\Facades\DB;
    $protocol =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443
            ? 'https://'
            : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    //$fullUrl = $protocol . $host . $requestUri;
    // Parse the query string from the request URI
    $queryString = parse_url($requestUri, PHP_URL_QUERY);

    // Store the key-value pairs in an associative array
    parse_str($queryString, $queryParams);
    $requestUri = $queryString;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Staff List</h5>
                    </div>
                    <div class="card-block">
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                        {{-- for degree import --}}
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importDegree"><i class="fas fa-upload"></i> {{ 'Import Degree' }}</button>
                        <button href="#" class="btn btn-danger deleteAction" style="float: right;"
                            data-bs-toggle="modal" data-bs-target="#reset"><i class="fas fa-reset"></i>
                            {{ 'Reset' }}</button>
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2 idAction">
                                    <label for="username">SP NO.</label>
                                    <input type="text" name="username" id="username" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="staff_category" class="form-label">Staff Category</label>
                                    <select name="staff_category" id="staff_category" class="form-control">
                                        <option value="">Select Option</option>
                                        <option value="TEACHING STAFF">TEACHING STAFF</option>
                                        <option value="NON TEACHING STAFF">NON TEACHING STAFF</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 departmentAction">
                                    <label for="facultystaffFilter">Faculty</label>
                                    <select class="form-control faculty" id="facultystaffFilter" name="faculty"
                                        lang="staffFilter">
                                        <option value="">Select Option</option>
                                        @foreach ($faculty as $row)
                                            <option value="{{ $row->code }}">{{ $row->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 departmentAction">
                                    <label for="departmentstaffFilter">Department</label>
                                    <select class="form-control department" id="departmentstaffFilter"
                                        name="department" lang="staffFilter">
                                        <option value="">Select Faculty First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 departmentAction">
                                    <label for="programstaffFilter">Program</label>
                                    <select class="form-control" id="programstaffFilter" name="program"
                                        lang="staffFilter">
                                        <option value="">Select Department First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 departmentAction">
                                    <label for="unit">Department/Unit</label>
                                    <select class="form-control" lang="f" name="unit" id="unit">
                                        <option value="">Select Option</option>
                                        @foreach ($unit as $roww)
                                            <option value="{{ $roww->unit }}">{{ $roww->unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 levelAction">
                                    <label for="current_rank">Designation</label>
                                    <select class="form-control" lang="f" name="current_rank" id="current_rank">
                                        <option value="">Select Option</option>
                                        @foreach ($designation as $roww)
                                            <option value="{{ $roww->current_rank }}">{{ $roww->current_rank }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 phoneAction">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="state" class="form-label">State of Origin</label>
                                    <input type="text" name="state" id="state" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="lga" class="form-label">LGA of Origin</label>
                                    <input type="text" name="lga" id="lga" class="form-control">
                                </div>

                                <div class="form-group col-md-2">
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
                                        <th>{{ 'SP No.' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Dept/Unit' }}</th>
                                        <th>{{ 'Designation' }}</th>
                                        <th>{{ 'Phone' }}</th>
                                        <th>{{ 'Degree' }}</th>
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
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->unit }}</td>
                                            <td>{{ $row->current_rank }}</td>
                                            <td>{{ $row->phone }}</td>
                                            <td>{{ $row->degree ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <a href="/staff-record/{{ $row->id }}"
                                                    class="btn btn-icon btn-secondary btn-sm updateAction">
                                                    <i class="far fa-eye"></i>
                                                </a>

                                                <a href="/staff-record-update/{{ $row->id }}"
                                                    class="btn btn-icon btn-primary btn-sm updateAction">
                                                    <i class="far fa-edit"></i>
                                                </a>

                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
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
                                                                    value="{{ $row->user_id }}">
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
                                                        <form class="form-group" action="update-student"
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
<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
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
                        <input type="hidden" name="upload_type" value="new">
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                                <option value="NON ACADEMIC">NON ACADEMIC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1" name="department"
                                lang="1">
                                <option value="NON ACADEMIC">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control" id="program1" name="program" lang="1">
                                <option value="NON ACADEMIC">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control" required>
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
<div id="importDegree" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload Degree</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <form class="form-group" action="upload-degree" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="degree">Degree?</label>
                            <select class="form-control" id="degree" name="degree" lang="1"
                                required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control" required>
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
<div id="reset" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <a href="/reset-staff-password?{{ $requestUri }}" class="btn btn-warning">Reset Password</a>
            </div>

        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="createStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="">
                <form class="form-group" action="create {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="">
                        <!-- Details View Start -->
                        @csrf
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Staff Information</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-block">
                                        <h4 class="text-center">Personal Information</h4>
                                        <div class="form-group">
                                            <label for="username" class="form-label">SP/JP</label>
                                            <input type="text" name="username" id="username"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="name" class="form-label">Fullname</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select name="gender" id="gender" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="marital_status" class="form-label">Marital Status</label>
                                            <select name="marital_status" id="marital_status" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="number" name="phone" id="phone" class="form-control"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="address" class="form-label">Home Address</label>
                                            <input type="text" name="address" id="address" class="form-control"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth" id="date_of_birth"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="state" class="form-label">State of Origin</label>
                                            <input type="text" name="state" id="state" class="form-control"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="lga" class="form-label">LGA of Origin</label>
                                            <input type="text" name="lga" id="lga" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-block">
                                        <h4 class="text-center">Academic Information</h4>
                                        <div class="form-group">
                                            <label for="faculty1">Faculty</label>
                                            <select class="form-control faculty" id="facultya" name="faculty"
                                                lang="a">
                                                <option value="faculty">Select Option</option>
                                                @foreach ($faculty as $row)
                                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                                @endforeach
                                                <option value="NON ACADEMIC">NON ACADEMIC</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="departmenta">Department</label>
                                            <select class="form-control department" id="departmenta"
                                                name="department" lang="a">
                                                <option value="department">Select Faculty First</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="program1">Program</label>
                                            <select class="form-control" id="programa" name="program"
                                                lang="a">
                                                <option value="program">Select Department First</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="unit" class="form-label">Department/Unit</label>
                                            <select name="unit" id="unit" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($unit as $roww)
                                                    <option value="{{ $roww->unit }}">{{ $roww->unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="current_rank" class="form-label">Designation/Rank</label>
                                            <select name="current_rank" id="current_rank" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($designation as $roww)
                                                    <option value="{{ $roww->current_rank }}">
                                                        {{ $roww->current_rank }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="staff_category" class="form-label">Staff Category</label>
                                            <select name="staff_category" id="staff_category" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="TEACHING STAFF">TEACHING STAFF</option>
                                                <option value="NON TEACHING STAFF">NON TEACHING STAFF</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="employee_status" class="form-label">Employee Status</label>
                                            <select name="employee_status" id="employee_status" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="PERMANENT">PERMANENT</option>
                                                <option value="CONTRACT">CONTRACT</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="grade" class="form-label">Grade</label>
                                            <select name="grade" id="grade" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($grade as $roww)
                                                    <option value="{{ $roww->grade }}">{{ $roww->grade }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="step" class="form-label">Step</label>
                                            <select name="step" id="step" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($step as $roww)
                                                    <option value="{{ $roww->step }}">{{ $roww->step }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="date_of_first_appointment" class="form-label">Date of First
                                                Appointment</label>
                                            <input type="date" name="date_of_first_appointment"
                                                id="date_of_first_appointment" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="rank_of_first_appointment" class="form-label">Rank on First
                                                Appointment</label>
                                            <input type="text" name="rank_of_first_appointment"
                                                id="rank_of_first_appointment" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="date_of_asumption" class="form-label">Date of
                                                Assumption</label>
                                            <input type="date" name="date_of_asumption" id="date_of_asumption"
                                                class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="date_of_last_promotion" class="form-label">Date of Last
                                                Promotion</label>
                                            <input type="date" name="date_of_last_promotion"
                                                id="date_of_last_promotion" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-block">
                                        <h4 class="text-center">Next of Kin Information</h4>
                                        <div class="form-group">
                                            <label for="kin_name" class="form-label">Name</label>
                                            <input type="text" name="kin_name" id="kin_name"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="kin_phone" class="form-label">Phone</label>
                                            <input type="number" name="kin_phone" id="kin_phone"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="kin_address" class="form-label">Home Address</label>
                                            <input type="text" name="kin_address" id="kin_address"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-block">
                                        <h4 class="text-center">Bank Details</h4>
                                        <div class="form-group">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <input type="text" name="bank_name" id="bank_name"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="account_number" class="form-label">Account Number</label>
                                            <input type="number" name="account_number" id="account_number"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-block">
                                        <h4 class="text-center">Action</h4>
                                        <div class="form-group">
                                            <label for="" class="form-label">.</label>
                                            <br>
                                            <button style="width: 100%" type="submit"
                                                class="btn btn-success">Create</button>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="form-label">.</label>
                                            <br>
                                            <button style="width: 100%" type="button" class="btn btn-info"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Details View End -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
