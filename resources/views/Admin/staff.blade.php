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
                        <button href="#" class="btn btn-success" onclick="$('#exportModal').modal('show')"><i class="fas fa-download"></i> {{ 'Export' }}</button>
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
                                    <select class="form-control" lang="f" name="unit_id" id="unit">
                                        <option value="">Select Option</option>
                                        @foreach ($unit as $roww)
                                            <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 levelAction">
                                    <label for="current_rank">Designation</label>
                                    <select class="form-control" lang="f" name="designation_id" id="current_rank">
                                        <option value="">Select Option</option>
                                        @foreach ($designation as $roww)
                                            <option value="{{ $roww->id }}">{{ $roww->name }}
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
                                    <select name="state" id="state-filter" class="form-control">
                                        <option value="">All</option>
                                        <option value="Abia">Abia</option><option value="Adamawa">Adamawa</option><option value="Akwa Ibom">Akwa Ibom</option><option value="Anambra">Anambra</option>
                                        <option value="Bauchi">Bauchi</option><option value="Bayelsa">Bayelsa</option><option value="Benue">Benue</option><option value="Borno">Borno</option>
                                        <option value="Cross River">Cross River</option><option value="Delta">Delta</option><option value="Ebonyi">Ebonyi</option><option value="Edo">Edo</option>
                                        <option value="Ekiti">Ekiti</option><option value="Enugu">Enugu</option><option value="FCT">FCT</option><option value="Gombe">Gombe</option>
                                        <option value="Imo">Imo</option><option value="Jigawa">Jigawa</option><option value="Kaduna">Kaduna</option><option value="Kano">Kano</option>
                                        <option value="Katsina">Katsina</option><option value="Kebbi">Kebbi</option><option value="Kogi">Kogi</option><option value="Kwara">Kwara</option>
                                        <option value="Lagos">Lagos</option><option value="Nasarawa">Nasarawa</option><option value="Niger">Niger</option><option value="Ogun">Ogun</option>
                                        <option value="Ondo">Ondo</option><option value="Osun">Osun</option><option value="Oyo">Oyo</option><option value="Plateau">Plateau</option>
                                        <option value="Rivers">Rivers</option><option value="Sokoto">Sokoto</option><option value="Taraba">Taraba</option><option value="Yobe">Yobe</option>
                                        <option value="Zamfara">Zamfara</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="lga" class="form-label">LGA of Origin</label>
                                    <select name="lga" id="lga-filter" class="form-control">
                                        <option value="">All</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="appointment" class="form-label">Appointment</label>
                                    <select name="appointment" id="appointment" class="form-control">
                                        <option value="">Select Option</option>
                                        @foreach ($appointment as $roww)
                                            <option value="{{ $roww->appointment }}">{{ $roww->appointment }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
                                    <button type="button" class="btn btn-warning btn-clear" onclick="window.location.href='/staff'"><i class="fas fa-times"></i>
                                        {{ 'Clear' }}</button>
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
                                    @foreach ($data->items() as $row)
                                    @php
                                        $unitName = '';
                                        if (!empty($row->unit_id)) {
                                            $unitName = DB::table('units')->where('id', $row->unit_id)->value('name') ?? '';
                                        } elseif (!empty($row->unit)) {
                                            $unitName = $row->unit;
                                        }
                                        $designationName = '';
                                        if (!empty($row->designation_id)) {
                                            $designationName = DB::table('designations')->where('id', $row->designation_id)->value('name') ?? '';
                                        } elseif (!empty($row->current_rank)) {
                                            $designationName = $row->current_rank;
                                        }
                                    @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $unitName }}</td>
                                            <td>{{ $designationName }}</td>
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
                                    {{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="/staff/download-template"><i class="fas fa-download"></i> Download Template</a>
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
                            <label for="unit1">Department/Unit</label>
                            <select class="form-control" id="unit1" name="unit_id">
                                <option value="">Select</option>
                                @foreach ($unit as $roww)
                                    <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="staff_category">Staff Category</label>
                            <select class="form-control" id="staff_category" name="staff_category">
                                <option value="">Select</option>
                                <option value="TEACHING STAFF">TEACHING STAFF</option>
                                <option value="NON TEACHING STAFF">NON TEACHING STAFF</option>
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
                <form class="form-group" action="/reset-staff-password" method="GET">
                    <div class="card-body">
                        <h5 class="card-title">Select Type First</h5>
                        @foreach(request()->query() as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <div class="form-group">
                            <label>Password Generation Method:</label>
                            <select class="form-control" name="password_method">
                                <option value="random" selected>Random</option>
                                <option value="phone">Phone Number</option>
                                <option value="ti_no">TI No.</option>
                                <option value="account_no">Account Number</option>
                                <option value="username">Username</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="generate_pdf" value="1"> Generate PDF
                            </label>
                        </div>

                        <button type="submit" class="btn btn-warning">Reset Password</button>
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
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
                                            <select name="state" id="state-create" class="form-control" required>
                                                <option value="">Select</option>
                                                <option value="Abia">Abia</option><option value="Adamawa">Adamawa</option><option value="Akwa Ibom">Akwa Ibom</option><option value="Anambra">Anambra</option>
                                                <option value="Bauchi">Bauchi</option><option value="Bayelsa">Bayelsa</option><option value="Benue">Benue</option><option value="Borno">Borno</option>
                                                <option value="Cross River">Cross River</option><option value="Delta">Delta</option><option value="Ebonyi">Ebonyi</option><option value="Edo">Edo</option>
                                                <option value="Ekiti">Ekiti</option><option value="Enugu">Enugu</option><option value="FCT">FCT</option><option value="Gombe">Gombe</option>
                                                <option value="Imo">Imo</option><option value="Jigawa">Jigawa</option><option value="Kaduna">Kaduna</option><option value="Kano">Kano</option>
                                                <option value="Katsina">Katsina</option><option value="Kebbi">Kebbi</option><option value="Kogi">Kogi</option><option value="Kwara">Kwara</option>
                                                <option value="Lagos">Lagos</option><option value="Nasarawa">Nasarawa</option><option value="Niger">Niger</option><option value="Ogun">Ogun</option>
                                                <option value="Ondo">Ondo</option><option value="Osun">Osun</option><option value="Oyo">Oyo</option><option value="Plateau">Plateau</option>
                                                <option value="Rivers">Rivers</option><option value="Sokoto">Sokoto</option><option value="Taraba">Taraba</option><option value="Yobe">Yobe</option>
                                                <option value="Zamfara">Zamfara</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="lga" class="form-label">LGA of Origin</label>
                                            <select name="lga" id="lga-create" class="form-control" required>
                                                <option value="">Select State First</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="nationality" class="form-label">Nationality</label>
                                            <select name="nationality" id="nationality-create" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Nigerian">Nigerian</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="nin" class="form-label">NIN <small class="text-muted">(required if Nigerian)</small></label>
                                            <input type="text" name="nin" id="nin-create" class="form-control" placeholder="e.g. 12345678901">
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
                                            <select name="unit_id" id="unit" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($unit as $roww)
                                                    <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="current_rank" class="form-label">Designation/Rank</label>
                                            <select name="designation_id" id="current_rank" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($designation as $roww)
                                                    <option value="{{ $roww->id }}">
                                                        {{ $roww->name }}
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
                                            <label for="employee_status" class="form-label">Employment Status</label>
                                            <select name="employee_status" id="employee_status" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="PERMANENT">PERMANENT</option>
                                                <option value="CONTRACT">CONTRACT</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="grade" class="form-label">Grade</label>
                                            <select name="grade_id" id="grade" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($grade as $roww)
                                                    <option value="{{ $roww->id }}">{{ $roww->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="step" class="form-label">Step</label>
                                            <select name="step_id" id="step" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($step as $roww)
                                                    <option value="{{ $roww->id }}">{{ $roww->name }}
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
                                            <select name="rank_of_first_appointment_id" id="rank_of_first_appointment" class="form-control">
                                                <option value="">Select Option</option>
                                                @foreach ($designation as $roww)
                                                    <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                                @endforeach
                                            </select>
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

{{-- ── Export Modal ── --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download"></i> Export Staff</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="$('#exportModal').modal('hide')"><span>&times;</span></button>
            </div>
            <form id="exportForm" method="POST">
                @csrf
                <input type="hidden" name="export_format" id="exportFormat" value="pdf">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>State of Origin</label>
                                <select class="form-control form-control-sm" name="state" id="export-state">
                                    <option value="">All</option>
                                    <option value="Abia">Abia</option><option value="Adamawa">Adamawa</option><option value="Akwa Ibom">Akwa Ibom</option><option value="Anambra">Anambra</option>
                                    <option value="Bauchi">Bauchi</option><option value="Bayelsa">Bayelsa</option><option value="Benue">Benue</option><option value="Borno">Borno</option>
                                    <option value="Cross River">Cross River</option><option value="Delta">Delta</option><option value="Ebonyi">Ebonyi</option><option value="Edo">Edo</option>
                                    <option value="Ekiti">Ekiti</option><option value="Enugu">Enugu</option><option value="FCT">FCT</option><option value="Gombe">Gombe</option>
                                    <option value="Imo">Imo</option><option value="Jigawa">Jigawa</option><option value="Kaduna">Kaduna</option><option value="Kano">Kano</option>
                                    <option value="Katsina">Katsina</option><option value="Kebbi">Kebbi</option><option value="Kogi">Kogi</option><option value="Kwara">Kwara</option>
                                    <option value="Lagos">Lagos</option><option value="Nasarawa">Nasarawa</option><option value="Niger">Niger</option><option value="Ogun">Ogun</option>
                                    <option value="Ondo">Ondo</option><option value="Osun">Osun</option><option value="Oyo">Oyo</option><option value="Plateau">Plateau</option>
                                    <option value="Rivers">Rivers</option><option value="Sokoto">Sokoto</option><option value="Taraba">Taraba</option><option value="Yobe">Yobe</option>
                                    <option value="Zamfara">Zamfara</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>LGA</label>
                                <select class="form-control form-control-sm" name="lga" id="export-lga">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Gender</label>
                                <select class="form-control form-control-sm" name="gender">
                                    <option value="">All</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Faculty</label>
                                <select class="form-control form-control-sm faculty" name="faculty" id="exportFaculty">
                                    <option value="">All</option>
                                    @foreach ($faculty as $row)
                                        <option value="{{ $row->code }}">{{ $row->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="form-control form-control-sm department" name="department" id="exportDepartment">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Program</label>
                                <select class="form-control form-control-sm program" name="program" id="exportProgram">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Unit</label>
                                <select class="form-control form-control-sm" name="unit_id">
                                    <option value="">All</option>
                                    @foreach ($unit as $roww)
                                        <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Current Rank</label>
                                <select class="form-control form-control-sm" name="designation_id">
                                    <option value="">All</option>
                                    @foreach ($designation as $roww)
                                        <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grade</label>
                                <select class="form-control form-control-sm" name="grade_id">
                                    <option value="">All</option>
                                    @foreach ($grade as $roww)
                                        <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Step</label>
                                <select class="form-control form-control-sm" name="step_id">
                                    <option value="">All</option>
                                    @foreach ($step as $roww)
                                        <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Export Format</label>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-primary btn-sm active">
                                        <input type="radio" name="export_format" value="pdf" checked onchange="document.getElementById('exportFormat').value='pdf'"> PDF
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm">
                                        <input type="radio" name="export_format" value="excel" onchange="document.getElementById('exportFormat').value='excel'"> CSV
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" onclick="$('#exportModal').modal('hide')">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="exportBtn"><i class="fas fa-download"></i> Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Conditional NIN required for Create Staff modal
(function(){
    function syncCreateNin() {
        var natSel = document.getElementById('nationality-create');
        var ninInp = document.getElementById('nin-create');
        if (!natSel || !ninInp) return;
        var isNigerian = (natSel.value || '').toString().toLowerCase() === 'nigerian';
        if (isNigerian) {
            ninInp.setAttribute('required', 'required');
        } else {
            ninInp.removeAttribute('required');
        }
    }
    document.addEventListener('change', function(e){
        if (e.target && e.target.id === 'nationality-create') syncCreateNin();
    });
    var form = document.querySelector('#createStudent form');
    if (form) {
        form.addEventListener('submit', function(){
            syncCreateNin();
        });
    }
    // initial
    setTimeout(syncCreateNin, 0);
})();

@include('includes.nigeria-states-lgas')

// State → LGA cascading for Create Staff modal
bindStateLGA('#state-create', '#lga-create');

// State → LGA cascading for filter section
bindStateLGA('#state-filter', '#lga-filter');

// State → LGA cascading for export modal
bindStateLGA('#export-state', '#export-lga');

// Export modal cascading dropdowns
$(document).ready(function() {
    // Faculty change - load departments
    $('#exportFaculty').on('change', function() {
        var facultyCode = $(this).val();
        var deptSelect = $('#exportDepartment');
        var progSelect = $('#exportProgram');

        deptSelect.empty().append('<option value="">All</option>');
        progSelect.empty().append('<option value="">All</option>');

        if (facultyCode) {
            $.get('/get-departments/' + facultyCode, function(data) {
                $.each(data, function(key, value) {
                    deptSelect.append('<option value="' + value.code + '">' + value.title + '</option>');
                });
            });
        }
    });

    // Department change - load programs
    $('#exportDepartment').on('change', function() {
        var deptCode = $(this).val();
        var progSelect = $('#exportProgram');

        progSelect.empty().append('<option value="">All</option>');

        if (deptCode) {
            $.get('/get-programs/' + deptCode, function(data) {
                $.each(data, function(key, value) {
                    progSelect.append('<option value="' + value.code + '">' + value.title + '</option>');
                });
            });
        }
    });

    // Export form submission
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        var format = $('#exportFormat').val();
        var action = format === 'excel' ? '/staff/export/excel' : '/staff/export/pdf';
        $(this).attr('action', action);
        this.submit();
    });
});
</script>
