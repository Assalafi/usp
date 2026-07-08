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

                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> {{ ('Add New') }}</a>
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button>
                        <button href="#" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportStudent"><i class="fas fa-download"></i> {{ ('Export') }}</button>

                    </div>

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-3">
                                    <label for="facultyf">Faculty <span>*</span></label>
                                    <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
                                        <option value="">Select Option</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww -> code }}">{{ $roww -> code }}: {{ $roww -> title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="departmentf">Department</label>
                                    <select class="form-control department" lang="f" id="departmentf" name="department">
                                        <option value="">Select Faculty First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="programf">Program</label>
                                    <select class="form-control" id="programf" lang="f" name="program" required>
                                        <option value="">Select Department First</option>
                                    </select>
                                    <div class="invalid-feedback"> You must select Program </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> {{ ('Filter') }}</button>
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
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ ('Code') }}</th>
                                        <th>{{ ('Title') }}</th>
                                        <th>{{ ('Type') }}</th>
                                        <th>{{ ('Class Type') }}</th>
                                        <th>{{ ('Unit') }}</th>
                                        <th>{{ ('Level') }}</th>
                                        <th>{{ ('Semester') }}</th>
                                        <th>{{ ('Program') }}</th>
                                        <th>{{ ('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $row -> code }}</td>
                                        <td>{{ $row -> title }}</td>
                                        <td>{{ $row -> type }}</td>
                                        <td>{{ $row -> class }}</td>
                                        <td>{{ $row -> unit }}</td>
                                        <td>{{ $row -> level }}</td>
                                        <td>{{ $row -> semester }}</td>
                                        <td>{{ DB::table('program')->where('code', $row->program)->value('title') }}</td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group" action="update {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="code">Code</label>
                                                                <input type="text" name="code" value="{{ $row->code }}" id="code" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="title">Title</label>
                                                                <input type="text" name="title" value="{{ $row->title }}" id="title" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="type">Course Type</label>
                                                                <select class="form-control" id="type" name="type" required>
                                                                    <option value="{{ $row -> type }}">Select Option</option>
                                                                    <option value="Core">Core</option>
                                                                    <option value="Elective">Elective</option>
                                                                    <option value="Prerequsite">Prerequsite</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="class">Class Type</label>
                                                                <select class="form-control" id="class" name="class" required>
                                                                    <option value="{{ $row -> class }}">Select Option</option>
                                                                    <option value="Theory">Theory</option>
                                                                    <option value="Practical">Practical</option>
                                                                    <option value="Both">Both</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="unit">Unit</label>
                                                                <select class="form-control" id="unit" name="unit" required>
                                                                    <option value="{{ $row -> unit }}">Select Option</option>
                                                                    @for ($i = 1; $i <= 50; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="level">Level</label>
                                                                <select class="form-control" id="level" name="level" required>
                                                                    <option value="{{ $row -> level }}">Select Option</option>
                                                                    @for ($i = 1; $i <= 7; $i++)
                                                                        <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="semester">Semester</label>
                                                                <select class="form-control" id="semester" name="semester" required>
                                                                    <option value="{{ $row -> semester }}">Select Option</option>
                                                                    <option value="First">First Semester</option>
                                                                    <option value="Second">Second Semester</option>
                                                                    <option value="Third">Third Semester</option>
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
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h4>Are You Sure</h4>
                                                    </div>
                                                    <form class="form-group" action="delete {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                                                        <button type="submit" class="btn btn-danger">Yes</button>
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
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="upload {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control">
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
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST" enctype="multipart/form-data">
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
                                @for ($i = 1; $i <= 50; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i*100 }}">{{ $i*100 }}</option>
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
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2" required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row -> code }}">{{ $row -> code }}: {{ $row -> title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department</label>
                            <select class="form-control department" id="department2" name="department" lang="2" required>
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

<!-- Show modal content -->
<div id="exportStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <div class="card-block">
                    <form class="needs-validation" novalidate method="GET" action="/export-courses">
                        @csrf
                        <div class="row gx-2">
                            <div class="form-group col-md-12">
                                <label for="code">Courses <span>*</span></label>
                                <select class="form-control" name="code" id="code">
                                    <option value="">Select Option</option>
                                    @foreach ($get_courses as $roww)
                                        <option value="{{ $roww -> code }}">{{ $roww -> code }} : {{ $roww -> title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-download"></i> {{ ('Export') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
