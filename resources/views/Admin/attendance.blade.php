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
                        <h5>{{ strtoupper($page) }}</h5>
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
                                <div class="form-group col-md-3">
                                    <label for="programf">Program</label>
                                    <select class="form-control program" id="programf" lang="f" name="program">
                                        <option value="">Select Department First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="coursef">Course</label>
                                    <select class="form-control" id="coursef" lang="f" name="code" required>
                                        <option value="">Select Program First</option>
                                    </select>
                                    <div class="invalid-feedback"> You must select Course </div>
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

            @isset($_GET['code'])
                @php
                    $total = DB::table('attendance')
                        ->where(['course' => $_GET['code'], 'session' => session('system_session')])
                        ->distinct('date')
                        ->count();
                    $lectures = DB::table('attendance')
                        ->where(['course' => $_GET['code'], 'session' => session('system_session')])
                        ->distinct('date')
                        ->get('date');
                    $l = 1;
                @endphp
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-block">
                            <!-- [ Data table ] start -->
                            <div class="row">
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                                        data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                                </div>
                                <div class="col-md-2">
                                    <select name="date" id="date" class="form-control getDate">
                                        <option value="">Select Option</option>
                                        <option value="All">Attendance Summary</option>
                                        @foreach ($lectures as $row)
                                            <option value="{{ $row -> date }}">Lecture {{ $l++ }}: ({{ $row -> date }})</option>
                                        @endforeach
                                    </select>
                                    {{-- <input type="date" name="date" id="date" class="form-control getDate"> --}}
                                </div>
                            </div>
                            @error('pdf')
                                <div class="alert alert-danger text-center" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="table-responsive">
                                <table id="export-table" class="display table nowrap table-striped table-hover"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ 'ID Number' }}</th>
                                            <th>{{ 'Attendance' }}({{ $total }})</th>
                                            <th>{{ 'Action' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="displayRecord">
                                        @php
                                            $sn = 1;
                                        @endphp
                                        @foreach ($students as $row)
                                            <tr>
                                                <td>{{ $sn++ }}</td>
                                                <td>{{ $row->username }}</td>
                                                <td>{{ $total == 0 ? 0 : ($row->attendance / $total) * 100 }}%</td>
                                                {{-- <td><a href="/attendance/{{ $row -> id }}" class="btn btn-primary btn-sm">Details</a></td> --}}
                                                <td>
                                                    <a href="#" class="btn btn-icon btn-primary btn-sm updateAction"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $row->id }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>

                                                    <button type="button"
                                                        class="btn btn-icon btn-danger btn-sm deleteAction"
                                                        data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
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
                                                            <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <form class="form-group" action="create {{ $page }}"
                                                                method="POST" enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    {{-- <input type="hidden" name="course" value="{{ $row->code }}"> --}}
                                                                    <div class="form-group">
                                                                        <label for="file" class="form-label">PDF
                                                                            File</label>
                                                                        <input type="file" id="file"
                                                                            name="pdf" class="form-control" required>
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
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- [ Data table ] end -->
                        </div>
                    </div>
                </div>
            @endisset
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Show modal content -->
@isset($_GET['code'])
    <input type="hidden" name="hiddenCode" id="hiddenCode" value="{{ $_GET['code'] }}">
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
                        <div class="card-body table-reponsive">
                            <!-- Details View Start -->
                            @csrf
                            <input type="hidden" name="course" value="{{ $_GET['code'] }}">
                            <!-- Details View End -->
                            <table class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <th style="text-align: center">#</th>
                                    <th>ID Number</th>
                                    <th style="text-align: center">Present</th>
                                    <th style="text-align: center">Absent</th>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($students as $row)
                                        <input type="hidden" name="username[]" id="username"
                                            value="{{ $row->username }}">
                                        <tr>
                                            <td>
                                                {{ $sn++ }}
                                            </td>
                                            <td>
                                                {{ $row->username }}
                                            </td>
                                            <td style="text-align: center">
                                                <input style="height: 20px;width: 20px;" type="radio"
                                                    name="{{ $row->username }}" value="1" id="present">
                                            </td>
                                            <td style="text-align: center">
                                                <input style="height: 20px;width: 20px;" type="radio"
                                                    name="{{ $row->username }}" value="0" checked id="absent">
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endisset
