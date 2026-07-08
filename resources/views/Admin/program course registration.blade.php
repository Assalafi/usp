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

                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>

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
                                            <option value="{{ $roww->code }}">{{ $roww->code }}:
                                                {{ $roww->title }}</option>
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
                                    <select class="form-control" id="programf" lang="f" name="program" required>
                                        <option value="">Select Department First</option>
                                    </select>
                                    <div class="invalid-feedback"> You must select Program </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="level">Level</label>
                                    <select class="form-control" id="level" name="level">
                                        <option value="">Select Option</option>
                                        @for ($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                        @endfor
                                    </select>
                                </div>
                                {{-- Course Structure --}}
                                <div class="form-group col-md-2">
                                    <label for="structure_id">Course Structure</label>
                                    <select class="form-control" id="structure_id" name="structure_id" required>
                                        <option value="">Select Option</option>
                                        @foreach ($courseStructure as $structure)
                                            <option value="{{ $structure->id }}">{{ $structure->name }}
                                                ({{ $structure->from_session }} - {{ $structure->to_session }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"> You must select Course Structure </div>
                                </div>


                                <div class="form-group col-md-2">
                                    <label for="programf">Form</label>
                                    <select class="form-control" id="form" name="form">
                                        <option value="">Select Form</option>
                                        <option value="NEW">NEW</option>
                                        <option value="FORMER">FORMER</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
                                </div>
                                @isset($_GET['structure_id'])
                                    <div class="form-group col-md-2">
                                        <br>
                                        <div class="alert alert-info">
                                            {{ DB::table('course_structure')->where('id', $_GET['structure_id'])->value('name') }}
                                        </div>
                                    </div>
                                @endisset
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
                                        <th>{{ 'PROGRAM' }}</th>
                                        <th>{{ 'CODE' }}</th>
                                        <th>{{ 'TITLE' }}</th>
                                        <th>{{ 'UNIT' }}</th>
                                        <th>{{ 'LEVEL' }}</th>
                                        <th>{{ 'SEMESTER' }}</th>
                                        <th>{{ 'CORE' }}</th>
                                        {{-- <th>{{ 'ELECT.' }}</th> --}}
                                        {{-- <th>{{ 'DE?' }}</th> --}}
                                        {{-- <th>{{ 'CHAN. SEM.' }}</th> --}}
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
                                            <td>{{ DB::table('program')->where('code', $row->program)->value('title') }}
                                            </td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ DB::table('course')->where('code', $row->code)->value('title') }}
                                            </td>
                                            <td>{{ $row->unit }}</td>
                                            <td>{{ $row->level }}</td>
                                            <td>{{ $row->semester }}</td>
                                            <td>{{ $row->type }}</td>
                                            {{-- <td>{{ $row->elective }}</td> --}}
                                            {{-- <td>{{ $row->de == '1' ? 'YES' : 'NO' }}</td> --}}
                                            {{-- <td>{{ $row->change_semester == '1' ? 'YES' : 'NO' }}</td> --}}
                                            <td>
                                                <button class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal" data-bs-target="#update{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
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
                                        <div id="update{{ $row->id }}" class="modal fade" tabindex="-1"
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
                                                                <div class="form-group">
                                                                    <label for="level">Level</label>
                                                                    <select class="form-control" id="level"
                                                                        name="level" required>
                                                                        <option value="{{ $row->level }}">Select
                                                                            Option</option>
                                                                        @for ($i = 1; $i <= 7; $i++)
                                                                            <option value="{{ $i * 100 }}">
                                                                                {{ $i * 100 }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="semester">COURSE SEMESTER</label>
                                                                    <select class="form-control" id="semester"
                                                                        name="semester" required>
                                                                        <option value="{{ $row->semester }}">Current:
                                                                            {{ $row->semester }}</option>
                                                                        <option value="FIRST">FIRST</option>
                                                                        <option value="SECOND">SECOND</option>
                                                                        <option value="THIRD">THIRD</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="type">COURSE TYPE</label>
                                                                    <select class="form-control" id="type"
                                                                        name="type" required>
                                                                        <option value="{{ $row->type }}">Current:
                                                                            {{ $row->type }}</option>
                                                                        <option value="CORE">CORE</option>
                                                                        <option value="ELECTIVE">ELECTIVE</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="elective">ELECTIVE?</label>
                                                                    <select class="form-control" id="elective"
                                                                        name="elective" required>
                                                                        <option value="{{ $row->elective }}">Current:
                                                                            {{ $row->elective }}</option>
                                                                        <option value="1">1</option>
                                                                        <option value="2">2</option>
                                                                        <option value="3">3</option>
                                                                        <option value="4">4</option>
                                                                        <option value="5">5</option>
                                                                        <option value="6">6</option>
                                                                        <option value="7">7</option>
                                                                        <option value="8">8</option>
                                                                        <option value="9">9</option>
                                                                        <option value="10">10</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="change_semester">CHANGE
                                                                        SEMESTER?</label>
                                                                    <select class="form-control" id="change_semester"
                                                                        name="change_semester" required>
                                                                        <option value="{{ $row->change_semester }}">
                                                                            Current:
                                                                            @if ($row->change_semester == '1')
                                                                                YES
                                                                            @else
                                                                                NO
                                                                            @endif
                                                                        </option>
                                                                        <option value="0">NO</option>
                                                                        <option value="1">YES</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="type">Form</label>
                                                                    <select class="form-control" id="form"
                                                                        name="form" required>
                                                                        <option value="{{ $row->form }}">Current:
                                                                            {{ $row->form }}</option>
                                                                        <option value="NEW">NEW</option>
                                                                        <option value="FORMER">FORMER</option>
                                                                    </select>
                                                                </div>
                                                                {{-- Course Structure --}}
                                                                <div class="form-group">
                                                                    <label for="structure_id">Course Structure</label>
                                                                    <select class="form-control" id="structure_id"
                                                                        name="structure_id">
                                                                        <option value="{{ $row->structure_id }}">
                                                                            Course Structures</option>
                                                                        @foreach ($courseStructure as $structure)
                                                                            <option value="{{ $structure->id }}">
                                                                                {{ $structure->name }}
                                                                                ({{ $structure->from_session }} -
                                                                                {{ $structure->to_session }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="de">DE?</label>
                                                                    <select class="form-control" id="de"
                                                                        name="de" required>
                                                                        <option value="{{ $row->de }}">Current:
                                                                            {{ $row->de == '1' ? 'YES' : 'NO' }}
                                                                        </option>
                                                                        <option value="0">NO</option>
                                                                        <option value="1">YES</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="grading">GRADING SYSTEM</label>
                                                                    <select class="form-control" id="grading"
                                                                        name="grading" required>
                                                                        <option value="{{ $row->grading }}">Current:
                                                                            {{ $row->grading }}</option>
                                                                        @foreach ($grading as $gs)
                                                                            <option value="{{ $gs->name }}">
                                                                                {{ $gs->name }}</option>
                                                                        @endforeach
                                                                    </select>
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
                            <label for="faculty2">Faculty</label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}: {{ $row->title }}
                                    </option>
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
                            <label for="form">Form</label>
                            <select class="form-control" id="form" name="form" required>
                                <option value="">Select Form</option>
                                <option value="NEW">NEW</option>
                                <option value="FORMER">FORMER</option>
                            </select>
                        </div>
                        {{-- Course Structure --}}
                        <div class="form-group">
                            <label for="structure_id">Course Structure</label>
                            <select class="form-control" id="structure_id" name="structure_id">
                                <option value="">Select Option</option>
                                @foreach ($courseStructure as $structure)
                                    <option value="{{ $structure->id }}">{{ $structure->name }}
                                        ({{ $structure->from_session }} - {{ $structure->to_session }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="code">Course Code Only (eg ABC101,ABC102)</label>
                            <textarea name="code" rows="10" class="form-control" id="code"
                                placeholder="Course Code Only (eg ABC101,ABC102)" required></textarea>
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
