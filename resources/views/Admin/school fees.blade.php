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
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> {{ ('Add New') }}</a>
                        {{-- <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button> --}}
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
                                            <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="departmentf">Department</label>
                                    <select class="form-control department" lang="f" id="departmentf" name="department">
                                        <option value="">Select Faculty First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="programf">Program</label>
                                    <select class="form-control program" id="programf" lang="f" name="program">
                                        <option value="">Select Department First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="level">Level</label>
                                    <select class="form-control" id="level" name="level">
                                        <option value="">Select Option</option>
                                        @for ($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="type">New/Returning</label>
                                    <select class="form-control" id="type" name="type">
                                        <option value="">Select Option</option>
                                        <option value="NEW">NEW</option>
                                        <option value="RETURNING">RETURNING</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="gst">GST?</label>
                                    <select class="form-control" id="gst" name="gst">
                                        <option value="">Select Option</option>
                                        <option value="YES">YES</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> {{ ('Filter') }}</button>
                                </div>
                                @if (isset($_GET['code']))
                                    <div class="form-group col-md-2">
                                        <a href="/print-result-pdf/{{ $_GET['code'] }}/{{ $_GET['session'] }}" type="submit" class="btn btn-info btn-filter"><i class="fas fa-print"></i> Print PDF</a>
                                    </div>
                                @endif
                                
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
                                        <th>{{ ('FACULTY') }}</th>
                                        <th>{{ ('DEPARTMENT') }}</th>
                                        <th>{{ ('PROGRAM') }}</th>
                                        <th>{{ ('LEVEL') }}</th>
                                        <th>{{ ('TYPE') }}</th>
                                        <th>{{ ('GST') }}</th>
                                        <th>{{ ('AMOUNT') }}</th>
                                        <th>{{ ('DOLLAR') }}</th>
                                        <th>{{ ('ACTION') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}</td>
                                        <td>{{ DB::table('department')->where('code', $row->department)->value('title') }}</td>
                                        <td>{{ DB::table('program')->where('code', $row->program)->value('title') }}</td>
                                        <td>{{ $row -> level }}</td>
                                        <td>{{ $row -> type }}</td>
                                        <td>{{ $row -> gst }}</td>
                                        <td>N{{ number_format($row -> amount,2) }}</td>
                                        <td>${{ number_format($row -> dollar,2) }}</td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-icon btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
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
                                                                <label for="level">Level</label>
                                                                <select class="form-control" id="level" name="level" required>
                                                                    <option value="{{ $row -> level }}">Select Option</option>
                                                                    @for ($i = 1; $i <= 7; $i++)
                                                                        <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="type">New/Returning</label>
                                                                <select class="form-control" id="type" name="type" required>
                                                                    <option value="{{ $row -> type }}">Select Option</option>
                                                                    <option value="NEW">NEW</option>
                                                                    <option value="RETURNING">RETURNING</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="gst">GST?</label>
                                                                <select class="form-control" id="gst" name="gst" required>
                                                                    <option value="{{ $row -> gst }}">Select Option</option>
                                                                    <option value="YES">YES</option>
                                                                    <option value="NO">NO</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="amount">Amount</label>
                                                                <input type="number" name="amount" id="amount" value="{{ $row -> amount }}" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="dollar">Dollar (For foreign)</label>
                                                                <input type="number" name="dollar" id="dollar" value="{{ $row -> dollar }}" class="form-control">
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
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>
                
                <form class="form-group" action="upload {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1" required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row -> code }}">{{ $row -> title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1" name="department" lang="1" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control program" id="program1" name="program" lang="1" required>
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
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control">
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
                            <label for="faculty2">Faculty</label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2" required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row -> code }}">{{ $row -> title }}</option>
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
                            <label for="type">New/Returning</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select Option</option>
                                <option value="NEW">NEW</option>
                                <option value="RETURNING">RETURNING</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gst">GST?</label>
                            <select class="form-control" id="gst" name="gst" required>
                                <option value="">Select Option</option>
                                <option value="YES">YES</option>
                                <option value="NO">NO</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="dollar">Dollar (For foreign)</label>
                            <input type="number" name="dollar" id="dollar" class="form-control" required>
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